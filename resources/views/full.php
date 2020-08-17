<div class="pagination">
    <?php
// Previous Page Link
?>
    <div class="pagination__prev-button<?php echo $paginator->onFirstPage() ? ' disabled' : ''; ?>">
        <?php if ($paginator->onFirstPage()): ?>
            <span><?php echo $paginator->prevPageText(); ?></span>
        <?php else: ?>
            <a href="<?php echo $paginator->previousPageUrl(); ?>"><span><?php echo $paginator->prevPageText(); ?></span></a>
        <?php endif; ?>
    </div>

    <ul class="pagination__page-links">
        <?php foreach ($pageLinks as $pageNumber => $url): ?>
            <?php if ($pageNumber == $paginator->currentPage()): ?>
                <li class="active"><span><?php echo $pageNumber; ?></span></li>
            <?php else: ?>
                <li><a href="<?php echo $url; ?>"><?php echo $pageNumber; ?></a></li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>

    <?php
// Next Page Link
?>
    <div class="pagination__next-button<?php echo $paginator->hasMorePages() ? '' : ' disabled'; ?>">
        <?php if ($paginator->hasMorePages()): ?>
            <a href="<?php echo $paginator->nextPageUrl(); ?>"><span><?php echo $paginator->nextPageText(); ?></span></a>
        <?php else: ?>
            <span><?php echo $paginator->nextPageText(); ?></span>
        <?php endif; ?>
    </div>
</div>