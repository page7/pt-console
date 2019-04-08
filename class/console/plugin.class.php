<?php
namespace console;

class plugin extends \console
{

    static public function colorpicker(&$attr, &$group_class, &$inner)
    {
        $attr['data-colorpicker'] = "data-colorpicker=\"true\"";

        $inner['plugins']['colorpicker'] = '
<script src="' . RESOURCES_URL . 'js/jquery.colorpicker.js"></script>
<link href="' . RESOURCES_URL . 'css/colorpicker.css" rel="stylesheet" />
<script>
$(function(){
    $("input[data-colorpicker]")
        .bind("colorChange colorInit", function(e, val){ $(this).prev(".input-group-addon").css({color:val}); })
        .colorPicker();
})
</script>';

    }

    static public function datepicker(&$attr, &$group_class, &$inner)
    {
        $attr['data-datepicker'] = "data-datepicker=\"true\"";

        $inner['plugins']['datepicker'] = '
<script src="' . RESOURCES_URL . 'js/jquery.zdatepicker.js"></script>
<link href="' . RESOURCES_URL . 'css/zdatepicker.css" rel="stylesheet" />
<script>
$(function(){
    $("input[data-datepicker]").zdatepicker({viewmonths:1});
})
</script>';

    }

}