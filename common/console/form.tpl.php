<!-- form -->
<form class="row" id="form-<?php echo NOW; ?>" role="form" onsubmit="return false;">

    <div class="col-md-8 col-lg-9 form-horizontal">

    <?php
        if (isset($config['intro'])) {
            echo '<div class="well">', $config['intro'], '</div>';
        }

        $inner = array('plugins'=>array());

        $modal = array();

        $upload_image = array();

        foreach ($config['fields'] as $name => $field)
        {
            $class = ' ';

            $inp = console::input($data, $class, $inner, $name, $field);
            if (false === $inp) continue;

            echo '<div class="form-group">',
                 '<label class="control-label col-sm-2">' . $field['name'] . '</label>',
                 '<div class="col-sm-' . (empty($field['width']) ? 9 : $field['width']) . $class . '">',
                $inp,
                (!empty($field['help']) ? '<p class="help-block">'.$field['help'].'</p>' : ''),
                '</div>',
                '</div>';

            if (!empty($field['bind']))
            {
                $script = '$("#form-' . NOW . ' [name=' . $field['field'] . ']")';

                foreach ($field['bind'] as $event => $callback)
                {
                    $script .= '.bind("'.$event.'", '.$callback.')';
                }

                $inner['script'][] = $script.";\r\n";
            }
        }
    ?>
    </div>

    <!-- Right Bar -->
    <div class="col-md-4 col-lg-3">

        <!-- panel -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">发布</h3>
            </div>
            <div class="panel-body panel-sm">
                <p><span class="glyphicon glyphicon-time"></span> 上次更新: <?php echo $data && $data['updatetime'] ? date('Y-m-d H:i:s', $data['updatetime']) : '无'; ?></p>
                <p><span class="glyphicon glyphicon-user"></span> 最后更新: <?php echo $data && $data['updator'] ? $data['updator'] : '无'; ?></p>
                <p id="preview_qrcode"></p>
            </div>

            <div class="panel-footer text-right">
                <a href="<?php echo BASE_URL?>?module=<?php echo MODULE; ?><?php echo $_suffix; ?>" data-pjax-container="#main" class="btn btn-flat btn-default btn-sm">返回</a>
                <button type="button" class="btn btn-primary btn-sm btn-save">保存</button>
            </div>

        </div>
        <!-- panel -->


        <?php

        if (!empty($config['subitems']))
        {
            foreach($config['subitems'] as $key => $conf)
            {
                $modal['subitem-'.$key] = array('size'=>empty($conf['size']) ? '' : $conf['size']);

                if (!empty($conf['style']))
                    array_push($inner['styles'], $conf['style']);
        ?>
        <!-- panel -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><?php echo $conf['name']; ?></h3>
                <?php
                if ($conf['type'] == 'extend')
                {
                    if (!empty($$key))
                    {
                        $_id = ${$key}['id'];
                        $_edconf = array('ico'=>'glyphicon glyphicon-edit', 'title'=>'修改', 'name'=>'修改', 'mode'=>'modal', 'size'=>(empty($conf['size']) ? '' : $conf['size']), 'url'=> BASE_URL .'?module=' .MODULE ."&operate=edit&&id={$data['id']}&item={$key}&sid={$_id}");
                        $conf['operate'] = array_merge(array('_edit'=>$_edconf), !empty($conf['operate']) ? $conf['operate'] : array());
                    }
                }

                if (!empty($conf['operate']))
                {
                    echo '<div class="panel-more">';
                    if (count($conf['operate']) > 2)
                    {
                        echo '<div class="btn-group"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-option-vertical"></span></button><ul class="dropdown-menu dropdown-menu-right">';

                        $_data = array('itemkey'=>$key, 'config'=>$conf, 'parent'=>$data);
                        foreach ($conf['operate'] as $k => $v)
                        {
                            echo console::button($k, $v, $_data, 'li', $_suffix, $modal);
                        }

                        echo '</ul></div>';
                    }
                    else
                    {
                        $_data = array('itemkey'=>$key, 'config'=>$conf, 'parent'=>$data);
                        foreach ($conf['operate'] as $k => $v)
                        {
                            unset($v['name']);
                            echo console::button($k, $v, $_data, 'button', $_suffix, $modal);
                        }
                    }
                    echo '</div>';
                }
                ?>
            </div>
            <?php
            if (empty($data))
            {
            ?>
            <div class="list-group list-group-sm" style="padding:20px 0;">
                <div class="empty" style="margin:0 20px"><?php echo $conf['placeholder']; ?></div>
            </div>
            <?php
            }
            else
            {
                $subitems[] = $key;

                if ($conf['type'] == 'child')
                {
                    $itemdata = empty($conf['group']) ? array(array('items'=>$$key)) : $$key;

                    foreach ($itemdata as $group)
                    {
                        echo "<div class=\"list-group list-group-sm\" id=\"subitems-{$key}\">\r\n";

                        if (!empty($conf['format']) && !empty($conf['group']))
                            $gval = call_user_func($conf['format'], $group, false);

                        if (isset($gval['content']))
                        {
                            echo '<div class="list-group-item'.(isset($gval['class']) ? ' ' . $gval['class'] : '').'">'
                                    .( $gval['title'] ? '<h4 class="list-group-item-heading">'.$gval['title'].'</h4>' : '' )
                                    .( $gval['title'] ? '<p class="list-group-item-text">'.$gval['content'].'</p>' : $gval['content'] )
                                    . '</div>';
                        }

                        foreach ( $group['items'] as $v )
                        {
                            if (!empty($conf['format']))
                                $val = call_user_func($conf['format'], $v, true);

                            $class = isset($val['class']) ? ' ' . $val['class'] : '';

                            echo "<a class=\"list-group-item{$class}\" href=\"#modal-subitem-{$key}\" data-toggle=\"modal\" data-url=\"?module=".MODULE."&operate=edit&id={$data['id']}&item={$key}&sid={$v['id']}\">";

                            if (isset($val['badge']))
                                echo "<span class=\"badge\">{$val['badge']}</span>";

                            if (isset($val['title']))
                            {
                                echo "<h4 class=\"list-group-item-heading\">{$val['title']}</h4>",
                                     "<p class=\"list-group-item-text\">{$val['content']}</p>";
                            }
                            else
                            {
                                echo $val['content'];
                            }

                            echo "</a>\r\n";
                        }
                        echo "</div>\r\n\r\n";
                    }
                }
                else if ($conf['type'] == 'extend')
                {
                    echo "<ul class=\"list-group list-group-sm\" id=\"subitems-{$key}\">\r\n";

                    $itemdata = $$key;

                    foreach ( $itemdata as $field => $v )
                    {
                        if (!empty($conf['format']))
                            $val = call_user_func($conf['format'], $v, $field);

                        if ($val === false) continue;

                        $class = isset($val['class']) ? ' ' . $val['class'] : '';

                        echo "<li class=\"list-group-item{$class}\">";

                        if (isset($val['badge']))
                            echo "<span class=\"badge\">{$val['badge']}</span>";

                        if (isset($val['title']))
                        {
                            echo "<h4 class=\"list-group-item-heading\">{$val['title']}</h4>",
                                 "<p class=\"list-group-item-text\">{$val['content']}</p>";
                        }
                        else
                        {
                            echo is_string($val) ? $val : $val['content'];
                        }

                        echo "</li>\r\n";
                    }

                    echo "</ul>\r\n";
                }

                if ($conf['type'] == 'child' || ($conf['type'] == 'extend' && empty($$key)))
                {
                    echo '<div class="list-group list-group-sm"><a class="list-group-item empty" href="#modal-subitem-'.$key.'" data-toggle="modal" data-url="?module='.MODULE.'&operate=edit&id='.$data['id'].'&item='.$key.'&sid=0">点击添加</a></div>';
                }
            }
            ?>
        </div>
        <!-- panel -->
        <?php
            }
        }
        ?>


    </div>

    <input type="hidden" name="id"  value="<?php echo $data['id']; ?>"  />

</form>
<!-- end form -->


<!-- inner styles -->
<?php
if (!empty($inner['styles']))
{
    echo "<style>\r\n", implode("\r\n", $inner['styles']), "\r\n</style>";
}
?>


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

<?php if (isset($inner['script_image'])) { ?>
<script src="<?php echo RESOURCES_URL; ?>js/plupload/plupload.full.min.js"></script>
<?php } ?>

<?php if (isset($inner['script_editor'])) { ?>
<script src="<?php echo RESOURCES_URL; ?>js/tinymce/jquery.tinymce.min.js"></script>
<?php if (!isset($inner['script_image']) && !isset($inner['script_file'])) { ?>
<script src="<?php echo RESOURCES_URL; ?>js/plupload/plupload.full.min.js"></script>
<?php } ?>
<?php } ?>

<?php if (isset($inner['script_select'])) { ?>
<link href="<?php echo RESOURCES_URL; ?>css/chosen.css" rel="stylesheet" />
<script src="<?php echo RESOURCES_URL; ?>js/jquery.chosen.js"></script>
<?php } ?>

<script>
$(function(){

    // Save
    $("#form-<?php echo NOW; ?> .btn-save").click(function(){
        var btn = $(this), data = $("#form-<?php echo NOW; ?>").serialize();
        btn.prop("disabled", true).text("保存中..");
        $.post("<?php echo BASE_URL; ?>?module=<?php echo MODULE; ?>&operate=edit", data, function(data){
            btn.prop("disabled", false).text("保存");
            if (data.s == 0){
                <?php
                echo 'var url="?module='.MODULE.'&operate=edit&id=" + data.rs + "' . $_suffix .'#success";
                if ($.support.pjax) {
                    $.pjax({ url: url, container: "#main" });
                } else {
                    location.href = url;
                }';
                ?>
            } else if (data.s < 0 && data.rs.alert !== undefined) {
                alert(data.err, data.rs.alert);
            } else {
                alert(data.err, 'error');
            }
        }, "json");
    });

    if (location.hash == "#success") {
        alert('保存成功', 'success');
        location.hash = '';
    }


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

    if (isset($inner['script_modal']))
    {
        foreach($inner['script_modal'] as $v)
            echo "window.modal('{$v}');\r\n";
    }

    // Tinymce Editor
    if (isset($inner['script_editor']))
    {
        foreach($inner['script_editor'] as $v)
        {
            $base = array(
                'script_url'    => RESOURCES_URL . '/js/tinymce/tinymce.min.js',
                'language'      => 'zh_CN',
                'height'        => 450,
                'menubar'       => false,
                'theme'         => 'silver',
                'inline_styles' => true,
                'images_upload_base_path'   => '/upload/',
                'images_upload_url'         => BASE_URL . '?module=file&operate=upload&type=editor',
                'paste_data_images'         => true,
            );

            $app = array(
                'plugins'       => 'image imagetools code',
                'toolbar'       => 'bold italic | forecolor backcolor | link image | code',
                'content_css'   => RESOURCES_URL . '/js/tinymce/h5.css',
                'image_dimensions'  => false,
                'valid_elements'    => 'b/strong,i/em,span[style],p[style],img[src],br',
            );

            $h5 = array(
                'plugins'       => 'autolink link image imagetools code',
                'toolbar'       => 'styleselect | forecolor | link image | removeformat paste code',
                'content_css'   => RESOURCES_URL . '/js/tinymce/h5.css',
                'image_dimensions'  => false,
                'valid_elements'    => 'h1[style],h2[style],h3[style],h4[style],h5[style],h6[style],a[href|target|style],span[style],p[style],div[style],section[style],blockquote[style],pre,strong/b,em/i,img[src|style|align|border=0],br',
            );

            $full = array(
                'plugins'       => 'autolink link image textcolor lists table imagetools code',
                'toolbar'       => 'styleselect | bold italic strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | link image | numlist bullist outdent indent  | removeformat paste code',
                'content_css'   => RESOURCES_URL . '/js/tinymce/full.css',
            );

            $conf = array_merge($base, ($v['mode'] && isset($$v['mode']) ? $$v['mode'] : $full), $v['config']);

            echo "window.tinymces.push($(\"#{$v['id']}\").tinymce(" . json_encode($conf) . "));";
        }
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
