This is a simple PHP library for creating pagination.

# Basic Usage
Here is an example for creating the pagination in WordPress.

```php
global $wp_query;

// The total number of items
$total       = (int)$wp_query->found_posts;

// The number of items are going to be displayed per page.
$perPage     = (int)$wp_query->query_vars['posts_per_page'];

// The current page number.
$currentPage = (int)$wp_query->query_vars['paged'];

// additional options
$options     = ['urlStyle' => 'pretty'];


$paginator   = new Paginator($total, $perPage, $currentPage, $options);
    
echo $paginator->toHtml();
```

## Customize the markup
If you don't like the default markup, you can specify your own view file to output the markup.

```php
$paginator = new Paginator($total, $perPage, $currentPage, ['view'=> '/www/var/example.com/pagination.php']);
```

You can also specify the view with using the dot notation, for example `['view'=> 'components.pagination']`; by using the dot notation it will try to use the [Blade Templates](https://laravel.com/docs/master/blade) if possible.

Take a look at the [preset views](https://github.com/laraish/pagination/tree/master/resources/views) for more details.

# Options

## onEachSide
| Type | Default |
|------|-------- |
| int  | 3       |

The number of links on each side of the center link.


## type
| Type   | Default   |
|--------|---------- |
| string | 'default' |

The rendering type.

* `default`
* `menu`
* `simple`

## view
| Type   | Default |
|--------|-------- |
| string | null    |

The path of view file.

Could be either a blade template or a regular php file.  
If you wish to use a php file, you should add the `.php` at the end of the string.

## urlStyle
| Type   | Default  |
|--------|--------- |
| string | 'pretty' |

The link style.

* `pretty`: example.com/news/page/10
* `queryString`: example.com/news/?page=10

## nextPageText
| Type   | Default  |
|--------|--------- |
| string | '»'      |

The next page link text.

## prevPageText
| Type   | Default  |
|--------|--------- |
| string | '«'      |

The previous page link text.

## path
| Type   | Default |
|--------|-------- |
| string | null    |

The user-defined base path.

## suffix
| Type   | Default |
|--------|-------- |
| string | ''      |

The suffix to be added to the very end of the url. Such as fragment or query-strings.