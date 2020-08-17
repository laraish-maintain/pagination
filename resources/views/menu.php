<div class="pagination-menu">
    <?php
// Previous Page Link
?>
    <div class="pagination-menu__prev-button<?php echo $paginator->onFirstPage() ? ' disabled' : ''; ?>">
        <?php if ($paginator->onFirstPage()): ?>
            <span><?php echo $paginator->prevPageText(); ?></span>
        <?php else: ?>
            <a href="<?php echo $paginator->previousPageUrl(); ?>"><span><?php echo $paginator->prevPageText(); ?></span></a>
        <?php endif; ?>
    </div>

    <div class="pagination-menu__links">
        <select id="js-pagination-menu">
            <?php foreach ($pageLinks as $pageNumber => $url): ?>
                <option value="<?php echo $url; ?>"<?php echo $pageNumber == $paginator->currentPage()
    ? ' selected="selected"'
    : ''; ?>><?php echo "{$pageNumber}/{$this->lastPage()}"; ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php
// Next Page Link
?>
    <div class="pagination-menu__next-button<?php echo $paginator->hasMorePages() ? '' : ' disabled'; ?>">
        <?php if ($paginator->hasMorePages()): ?>
            <a href="<?php echo $paginator->nextPageUrl(); ?>"><span><?php echo $paginator->nextPageText(); ?></span></a>
        <?php else: ?>
            <span><?php echo $paginator->nextPageText(); ?></span>
        <?php endif; ?>
    </div>
</div>

<script>
    document.getElementById('js-pagination-menu').addEventListener('change', function () {
        location.href = this.value;
    });
</script>