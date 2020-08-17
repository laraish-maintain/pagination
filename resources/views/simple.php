<ul class="pagination">
    <?php
// Previous Page Link
?>
    <?php if ($paginator->onFirstPage()): ?>
        <li class="disabled"><span><?php echo $paginator->prevPageText(); ?></span></li>
    <?php else: ?>
        <li><a href="<?php echo $paginator->previousPageUrl(); ?>" rel="prev"><?php echo $paginator->prevPageText(); ?></a></li>
    <?php endif; ?>

    <?php
// Next Page Link
?>
    <?php if ($paginator->hasMorePages()): ?>
        <li><a href="<?php echo $paginator->nextPageUrl(); ?>" rel="next"><?php echo $paginator->nextPageText(); ?></a></li>
    <?php else: ?>
        <li class="disabled"><span><?php echo $paginator->nextPageText(); ?></span></li>
    <?php endif; ?>
</ul>