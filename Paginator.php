<?php

namespace Laraish\Pagination;

use Laraish\Support\Traits\ClassHelper;
use Laraish\Contracts\Pagination\Paginator as PaginatorContracts;
use Illuminate\Contracts\Support\Htmlable;

class Paginator implements PaginatorContracts, Htmlable
{
    use ClassHelper;

    /**
     * The total number of items before slicing.
     *
     * @var int
     */
    private $total;

    /**
     * The last available page.
     *
     * @var int
     */
    private $lastPage;

    /**
     * The number of items to be shown per page.
     *
     * @var int
     */
    private $perPage;

    /**
     * The current page being "viewed".
     *
     * @var int
     */
    private $currentPage;

    /**
     * The suffix to be added to the very end of the url.
     * Such as fragment or query-strings.
     * @var string
     */
    protected $suffix = '';

    /**
     * The number of links on each side of the center link.
     * @var int
     */
    protected $onEachSide = 3;

    /**
     * The next page link text.
     * @var string
     */
    protected $nextPageText = '»';

    /**
     * The previous page link text.
     * @var string
     */
    protected $prevPageText = '«';

    /**
     * The path of view file.
     * Could be either a blade template or a regular php file.
     * If you wish to use a php file,
     * you should add the '.php' at the end of the string.
     * @var string
     */
    protected $view;

    /**
     * The template engine to be used.
     * @var string
     */
    protected $templateEngine;

    /**
     * The rendering type.
     * @var string (default, menu, simple)
     */
    protected $type = 'default';

    /**
     * The user-defined base path.
     * @var string
     */
    protected $path;

    /**
     * The hostname.
     * @var string
     */
    protected $hostname;

    /**
     * The normalised base path.
     * @var string
     */
    protected $baseUrl;

    /**
     * The link style.
     * pretty: example.com/news/page/10
     * queryString: example.com/news/?page=10
     * @var
     */
    protected $urlStyle = 'pretty';

    /**
     * Determines if SSL is used.
     *
     * @return bool True if SSL, otherwise false.
     */
    protected static function isHttps()
    {
        if (isset($_SERVER['HTTPS'])) {
            if ('on' == strtolower($_SERVER['HTTPS'])) {
                return true;
            }

            if ('1' == $_SERVER['HTTPS']) {
                return true;
            }
        } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
            return true;
        }

        return false;
    }

    /**
     * Determine if it is in the WordPress environment.
     * @return bool
     */
    protected static function isWordPress()
    {
        return defined('WPINC');
    }

    /**
     * Resolve current page number
     * @return int
     */
    protected function resolveCurrentPage()
    {
        if ($this->urlStyle == 'pretty') {
            preg_match('@/page/(\d+)@', $_SERVER['REQUEST_URI'], $matches);
            $pageNumber = isset($matches[1]) ? (int)$matches[1] : 1;
        } else {
            $key        = static::isWordPress() ? 'paged' : 'page';
            $pageNumber = isset($_GET[$key]) ? (int)$_GET[$key] : 1;
        }

        return abs($pageNumber);
    }

    /**
     * Paginator constructor.
     *
     * @param int $total
     * @param int $perPage
     * @param int|null $currentPage
     * @param array $options ($fragment, $onEachSide, $view)
     */
    public function __construct($total, $perPage, $currentPage = null, array $options = [])
    {
        $this->convertMapToProperties($options, ['onEachSide', 'type', 'view', 'urlStyle', 'nextPageText', 'prevPageText', 'path', 'suffix', 'hostname']);
        $this->sanitizeOptions();
        $this->setupTemplateEngine();
        $this->setupBaseUrl();

        $this->total    = $total;
        $this->perPage  = $perPage;
        $this->lastPage = (int)ceil($total / $perPage);
        if (is_numeric($currentPage)) {
            $this->currentPage = $currentPage <= 0 ? 1 : (int)$currentPage;
        } else {
            $this->currentPage = $this->resolveCurrentPage();
        }
    }

    private function sanitizeOptions()
    {
        // onEachSide should be a positive integer number.
        $this->onEachSide = abs((int)$this->onEachSide);

        // if the view is undefined use a preset view
        if ( ! isset($this->view)) {
            if ( ! in_array($this->type, ['default', 'full', 'menu', 'simple'])) {
                throw new \InvalidArgumentException("The value of `type` for pagination should be one of these: 'default', 'menu', 'simple'. The given type is `{$this->type}`.");
            }
            $this->view = __DIR__ . '/resources/views/' . $this->type . '.php';
        }

        // set the hostname. If no hostname given, get it from current hostname.
        $this->hostname = $this->hostname ? trim($this->hostname) : $_SERVER['HTTP_HOST'];

        // set the base path. if no path given, get the path from current uri.
        $this->path = $this->path ? '/' . trim($this->path, '/') : rtrim(preg_replace('@/page/\d+.*@', '', (parse_url($_SERVER['REQUEST_URI']))['path']), '/');

        // the link style
        if ( ! in_array($this->urlStyle, ['pretty', 'queryString'])) {
            throw new \InvalidArgumentException("The value of `urlStyle` for pagination should be one of these: 'pretty', 'queryString'. The given type is `{$this->urlStyle}`.");
        }
    }

    /**
     * Setup the template engine.
     */
    protected function setupTemplateEngine()
    {
        if ( ! preg_match('/\.php$/', $this->view)) {
            $this->templateEngine = 'blade';
        }
    }

    /**
     * Setup the base url.
     */
    protected function setupBaseUrl()
    {
        $scheme = static::isHttps() ? 'https://' : 'http://';

        $this->baseUrl = $scheme . $this->hostname . $this->path;
    }

    /**
     * Get the URL range.
     *
     * @param int $from
     * @param int $to
     *
     * @return array
     */
    public function getUrlRange($from, $to)
    {
        $urls = [];

        for ($pageNumber = $from; $pageNumber <= $to; $pageNumber++) {
            $urls[$pageNumber] = $this->getUrl($pageNumber);
        }

        return $urls;
    }

    /**
     * Get the url.
     *
     * @param $pageNumber
     *
     * @return string
     */
    public function getUrl($pageNumber)
    {
        $baseUrl = $this->baseUrl;
        $suffix  = $this->suffix;

        if ($pageNumber === 1) {
            return $this->baseUrl . $suffix;
        }

        if ($this->urlStyle == 'pretty') {
            return $baseUrl . "/page/{$pageNumber}{$suffix}";
        } else {
            $pageName = self::isWordPress() ? 'paged' : 'page';

            return $baseUrl . "?{$pageName}={$pageNumber}{$suffix}";
        }
    }

    /**
     * Render the view with given data.
     *
     * @param array $__data
     *
     * @return string
     */
    protected function render(array $__data)
    {
        if ($this->templateEngine === 'blade') {
            return view($this->view, $__data);
        }

        ob_start();

        extract($__data, EXTR_SKIP);

        include $this->view;

        return ltrim(ob_get_clean());
    }

    /**
     * Get the pagination parts.
     * @return array
     */
    protected function getPaginationParts()
    {
        $minimumBeginningLinksNumber = 1;
        $minimumEndingLinksNumber    = 1;
        $maximumDotsNumber           = 2;
        $centerLinkNumber            = 1;
        $bothSidesLinksNumber        = $this->onEachSide * 2;
        $maximumLinksNumber          = $minimumBeginningLinksNumber + $minimumEndingLinksNumber + $maximumDotsNumber + $centerLinkNumber + $bothSidesLinksNumber;
        $notEnoughPagesToSlide       = $maximumLinksNumber >= $this->lastPage();
        $preloadLinksNumber          = 2;
        $dots                        = 1;

        if ($notEnoughPagesToSlide OR in_array($this->type(), ['menu', 'full'])) {
            return $this->getFullPagination();
        }

        if ($this->type() === 'simple') {
            return [];
        }

        $maximumPageNumberFromFirstPage = $maximumLinksNumber - ($minimumEndingLinksNumber + $dots);
        if ($this->currentPage() <= $maximumPageNumberFromFirstPage - $preloadLinksNumber) {
            return $this->getPaginationCloseToBeginning($maximumPageNumberFromFirstPage);
        }

        $minimumPageNumberFromLastPage = $this->lastPage() - ($maximumLinksNumber - $minimumBeginningLinksNumber - $dots) + 1;
        if ($this->currentPage() >= $minimumPageNumberFromLastPage + $preloadLinksNumber) {
            return $this->getPaginationCloseToEnding($minimumPageNumberFromLastPage);
        }

        return $this->getPaginationMiddle();
    }

    /**
     * Get all the URLs of the pagination.
     * [1][2][3][4][5]
     * @return array
     */
    protected function getFullPagination()
    {
        return [$this->getUrlRange(1, $this->lastPage())];
    }

    /**
     * The current page is close to the beginning of the pagination.
     * [1][2][3][4][5]...[100]
     *
     * @param int $maximumPageNumberFromFirstPage
     *
     * @return array
     */
    protected function getPaginationCloseToBeginning($maximumPageNumberFromFirstPage)
    {
        $beginning = $this->getUrlRange(1, $maximumPageNumberFromFirstPage);
        $ending    = $this->getUrlRange($this->lastPage(), $this->lastPage());

        return [
            $beginning,
            '...',
            $ending
        ];
    }

    /**
     * The current page is close to the ending of the pagination.
     * [1]...[96][97][98][99][100]
     *
     * @param int $minimumPageNumberFromLastPage
     *
     * @return array
     */
    protected function getPaginationCloseToEnding($minimumPageNumberFromLastPage)
    {
        $beginning = $this->getUrlRange(1, 1);
        $ending    = $this->getUrlRange($minimumPageNumberFromLastPage, $this->lastPage());

        return [
            $beginning,
            '...',
            $ending
        ];
    }

    /**
     * The current page is somewhere around the middle of the pagination.
     * [1]...[50][51][52][53]...[100]
     *
     * @return array
     */
    protected function getPaginationMiddle()
    {
        $currentPage = $this->currentPage();
        $onEachSide  = $this->onEachSide;

        $beginning = $this->getUrlRange(1, 1);
        $middle    = $this->getUrlRange($currentPage - $onEachSide, $currentPage + $onEachSide);
        $ending    = $this->getUrlRange($this->lastPage(), $this->lastPage());

        return [
            $beginning,
            '...',
            $middle,
            '...',
            $ending
        ];
    }

    /**
     * Get the URL for the next page.
     *
     * @return string|null
     */
    public function nextPageUrl()
    {
        if ($this->lastPage() > $this->currentPage()) {
            return $this->getUrl($this->currentPage() + 1);
        }

        return null;
    }

    /**
     * Get the URL for the previous page.
     *
     * @return string|null
     */
    public function previousPageUrl()
    {
        if ($this->currentPage() > 1) {
            return $this->getUrl($this->currentPage() - 1);
        }

        return null;
    }

    /**
     * Determine if there are more items in the data source.
     *
     * @return bool
     */
    public function hasMorePages()
    {
        return $this->currentPage() < $this->lastPage();
    }

    /**
     * Determine if there are enough items to split into multiple pages.
     *
     * @return bool
     */
    public function hasPages()
    {
        return ! ($this->currentPage() == 1 && ! $this->hasMorePages());
    }

    /**
     * Determine if the paginator is on the first page.
     *
     * @return bool
     */
    public function onFirstPage()
    {
        return $this->currentPage() <= 1;
    }

    /**
     * Get the total number of items being paginated.
     *
     * @return int
     */
    public function total()
    {
        return $this->total;
    }

    /**
     * Get the last page.
     *
     * @return int
     */
    public function lastPage()
    {
        return $this->lastPage;
    }

    /**
     * Get the number of items shown per page.
     *
     * @return int
     */
    public function perPage()
    {
        return $this->perPage;
    }

    /**
     * Get the current page.
     *
     * @return int
     */
    public function currentPage()
    {
        return $this->currentPage;
    }

    /**
     * Get the type of the pagination.
     *
     * @return int
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * Get the next page link text.
     * @return string
     */
    public function nextPageText()
    {
        return $this->nextPageText;
    }

    /**
     * Get the previous page link text.
     * @return string
     */
    public function prevPageText()
    {
        return $this->prevPageText;
    }

    public function toHtml()
    {
        if ($this->lastPage() <= 1) {
            return '';
        }

        $paginationParts = $this->getPaginationParts();
        $partsLength     = count($paginationParts);

        if ($partsLength === 1) {
            if (in_array($this->type(), ['menu', 'full'])) {
                $data['pageLinks'] = $paginationParts[0];
            } else {
                $data['paginationParts'] = $paginationParts;
            }
        } elseif ($partsLength > 1) {
            $data['paginationParts'] = $paginationParts;
        }

        $data['paginator'] = $this;

        return (string)$this->render($data);
    }

    /**
     * Render the contents of the paginator when casting to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toHtml();
    }
}