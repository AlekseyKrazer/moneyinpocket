<option value="<?= $category['id'] ?>" <?php

if (isset($this->model->parent_id)) {
    if ($category['id'] == $this->model->parent_id) {
        echo "selected";
    }
} elseif (isset($this->model->group_id)) {
    if ($category['id'] == $this->model->group_id) {
        echo "selected";
    }
}
elseif (isset($this->model->category_id)) {
    if ($category['id'] == $this->model->category_id) {
        echo "selected";
    }
}
?>
><?= $tab.$category['name'] ?></option>

<?php if (isset($category['childs'])): ?>
    <?= $this->getMenuHtml ($category['childs'], $tab."&nbsp;&#9;&nbsp;&#9;&nbsp;&#9;") ?>
<?php endif?>

