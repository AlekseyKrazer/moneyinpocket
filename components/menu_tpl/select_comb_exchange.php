<?php if ($category['hide']!=1) : ?>
    <?php if ($category['type'] == 'dep') : ?>
        <option value="<?= $category['id'] ?>"
            <?php
            if (isset($this->model->deposit_to)) {
                if ($category['id'] == "dep_" .$this->model->deposit_to) {
                    echo "selected";
                }
            }
            ?>
        ><?= $tab .$category['name'] ?></option>
    <?php endif; ?>
<?php endif; ?>
<?php if ($category['type']=='cat' and $category['hide']!=1) : ?>
    <optgroup label="<?= $tab.$category['name'] ?>">
<?php endif; ?>

<?php if (isset($category['childs'])): ?>
    <?= $this->getMenuHtml ($category['childs'], $tab."&nbsp;&#9;&nbsp;&#9;&nbsp;&#9;") ?>
<?php endif?>