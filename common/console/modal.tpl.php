<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
    <h4 class="modal-title"><?php echo ($data ? '修改' : '新增'), $config['name']; ?></h4>
</div>
<div class="modal-body">
    <form id="form-<?php echo NOW; ?>" class="<?php echo isset($config['size']) && $config['size'] == 'sm' ? '' : 'form-horizontal'; ?>" action="<?php echo $_SERVER['REQUEST_URI']; ?>" style="padding: 0 15px;" onsubmit="return false;">
        <?php
        $inner = array('plugins'=>array(), 'script'=>array());
        $class = ' ';

        foreach ($config['fields'] as $name => $field)
        {
            $class = ' ';

            if (!isset($config['size']) || in_array($config['size'], array('md','lg')))
                $class = 'col-md-'.(empty($field['width']) ? '9' : $field['width']);

            $inp = console::input($data, $class, $inner, $name, $field);
            if (false === $inp) continue;

            echo '<div class="form-group">',
                 '<label class="control-label ' . (isset($config['size']) && $config['size'] == 'sm' ? '' : 'col-md-3') . '">' . $field['name'] . '</label>',
                 '<div class="' . trim($class) . '">',
                $inp,
                '</div></div>';

            if (!empty($field['bind']))
            {
                $script = '$("#form-' . NOW . ' [name=' . $name . ']")';

                foreach ($field['bind'] as $event => $callback)
                {
                    $script .= '.bind("'.$event.'", '.$callback.')';
                }

                $inner['script'][] = $script.";\r\n";
            }
        }
        ?>

        <input type="hidden" name="id" value="<?php echo $data['id']; ?>" />
        <?php if (isset($pid)) { ?>
        <input type="hidden" name="pid" value="<?php echo $pid; ?>" />
        <?php } ?>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
    <button type="button" class="btn btn-primary btn-save">保存</button>
</div>


<!-- inner styles -->
<?php
if (!empty($inner['styles']))
{
    echo "<style>\r\n", implode("\r\n", $inner['styles']), "\r\n</style>";
}
?>

<?php if (isset($inner['script_image'])) { ?>
<script src="<?php echo RESOURCES_URL; ?>js/plupload/plupload.full.min.js"></script>
<?php } ?>

<?php if (isset($inner['script_select'])) { ?>
<link href="<?php echo RESOURCES_URL; ?>css/chosen.css" rel="stylesheet" />
<script src="<?php echo RESOURCES_URL; ?>js/jquery.chosen.js"></script>
<?php } ?>

<script>
$(function(){
    <?php
    if (!empty($inner['script']))
    {
        foreach ($inner['script'] as $v)
            echo $v;
    }

    if (isset($inner['script_text'])) {
        echo "window.list_append('#" . implode(',#', $inner['script_text']) . "');\r\n";
    }

    if (isset($inner['script_select'])) {
        echo "$(\".ui-select\").chosen({disable_search_threshold:10, width:\"100%\"});\r\n";
    }

    if (isset($inner['script_image'])) {
        foreach($inner['script_image'] as $v)
            echo "window.image_uploader('".MODULE."', '{$v['name']}', {$v['multiple']}, '{$v['tmpl']}');\r\n";
    }
    ?>
});
</script>

<?php
if ($inner['plugins']) {
    foreach($inner['plugins'] as $v)
        echo $v;
}
?>
