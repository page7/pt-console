<!-- page and operation -->
<div class="row">
    <form class="col-xs-4 col-sm-6 col-md-6 col-lg-8 form-inline" action="" method="GET" role="form">
        <!--- filter -->
        <div class="btn-group" style="margin:20px 0px; margin-right:10px;">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                操作 <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" role="menu">
                <?php
                $modal = array();

                foreach ($config['menu'] as $k => $v)
                {
                    echo console::button($k, $v, null, 'li', $_suffix, $modal);
                }
                ?>
            </ul>
        </div>
        <!-- end filter -->

        <!-- search -->
        <?php if ($config['search']) { ?>
        <div class="input-group hidden-xs hidden-sm">
            <input type="text" name="keyword" class="form-control" autocomplete="off" value="<?php echo $keyword; ?>" data-search />
            <span class="input-group-btn">
                <button class="btn btn-default" type="submit"><span class="glyphicon glyphicon-search"></span></button>
            </span>
        </div>
        <!-- end search -->
        <?php } ?>

        <input type="hidden" name="module" value="<?php echo MODULE; ?>" />
        <input type="hidden" name="operate" value="<?php echo OPERATE; ?>" />
    </form>

    <div class="col-xs-8 col-sm-6 col-md-6 col-lg-4 text-right">
        <!-- page -->
        <ul class="pagination">
            <?php include(dirname(__FILE__).'/_page.tpl.php');  ?>
        </ul>
        <!-- end page -->
    </div>
</div>
<!--  page and operation  -->


<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th style="width:64px;"><input type="checkbox" class="checkbox checked-all" value="" /></th>
                <?php
                $inner = array('styles'=>array());

                foreach($config['fields'] as $v)
                {
                    echo '<th';
                    if (!empty($v['width']))
                    {
                        echo " style=\"width:{$v['width']}\"";
                    }
                    echo '>' . $v['title'] . '</th>';

                    if (!empty($v['style']))
                        array_push($inner['styles'], $v['style']);
                }
                if (!empty($config['buttons']))
                {
                    echo '<th class="bw'.count($config['buttons']).'">操作</th>';
                }
                ?>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th><input type="checkbox" class="checkbox checked-all" value="" /></th>
                <?php
                foreach($config['fields'] as $v)
                {
                    echo '<th>' . $v['title'] . '</th>';
                }
                if (!empty($config['buttons']))
                {
                    echo '<th>操作</th>';
                }
                ?>
            </tr>
        </tfoot>
        <tbody>
            <?php
            if (empty($list))
            {
            ?>
                <tr class="active">
                    <td colspan="<?php echo count($config['fields']) + (!empty($config['buttons']) ? 2 : 1); ?>">
                        <div class="empty">还没有任何数据</div>
                    </td>
                </tr>
            <?php
            }
            else
            {
                $length = count($list);

                foreach ($list as $i => $data)
                {
            ?>
            <tr data-id="<?php echo $data['id']; ?>">
                <td><input type="checkbox" class="checkbox" value="<?php echo $data['id']?>" /></td>
                <?php

                foreach($config['fields'] as $field)
                {
                    echo '<td>';

                    $type = isset($field['type']) ? $field['type'] : 'text';

                    $val = $data[$field['field']];
                    $sub = isset($field['sub']) ? $data[$field['sub']] : null;
                    if (isset($field['callback']))
                    {
                        call_user_func_array($field['callback'], array(&$val, &$sub, $data, $i+1, $length));
                    }

                    switch ($type)
                    {
                        case 'null':
                            break;

                        case 'image':
                            echo "<img src=\"{$val}\" class=\"{$field['class']}\" />";
                            break;

                        case 'text':
                        default:
                            echo $val;

                            if ($sub)
                                echo '<br /><span class="info">', $sub , '</span>';
                    }

                    echo '</td>';
                }

                if (!empty($config['buttons']))
                {
                    echo '<td class="md-nowrap">';

                    foreach ($config['buttons'] as $k => $v)
                    {
                        echo console::button($k, $v, $data, 'a', $_suffix, $modal). ' ';
                    }

                    echo '</td>';
                }

                ?>
            </tr>
            <?php
                }
            }
            ?>
        </tbody>
    </table>
</div>


<!-- page and operation -->
<div class="row">
    <div class="col-xs-4 col-sm-4 col-md-4 col-lg-3">
        <!--- filter -->
        <div class="btn-group" style="margin:20px 0px;">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                操作 <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" role="menu">
                <?php
                foreach ($config['menu'] as $k => $v)
                {
                    echo console::button($k, $v, null, 'li', $_suffix, $modal);
                }
                ?>
            </ul>
        </div>
        <!-- end filter -->
    </div>

    <div class="col-xs-8 col-sm-8 col-md-8 col-lg-9 text-right">
        <!-- page -->
        <ul class="pagination">
            <?php include(dirname(__FILE__).'/_page.tpl.php');  ?>
        </ul>
        <!-- end page -->
    </div>
</div>
<!--  page and operation  -->



<!-- modal -->
<?php
if ($modal)
{
    $inner['script_modal'] = array();

    foreach($modal as $id => $conf)
    {
        $inner['script_modal'][] = $id;
?>
<div id="modal-<?php echo $id; ?>" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog <?php echo empty($conf['size']) ? '' : 'modal-'.$conf['size']; ?>">
        <div class="modal-content">
        </div>
    </div>
</div>
<?php
    }
}
?>




<!-- inner styles -->
<?php
if (!empty($inner['styles']))
{
    echo "<style>\r\n", implode("\r\n", $inner['styles']), "\r\n</style>";
}
?>


<!-- inner script -->
<script>

$(function(){
    <?php
    if (isset($inner['script_modal']))
    {
        foreach($inner['script_modal'] as $v)
            echo "window.modal('{$v}');\r\n ";
    }
    ?>
});
</script>
