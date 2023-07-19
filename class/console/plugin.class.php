<?php
namespace console;

class plugin extends \console
{

    static public function __callStatic($method, $args)
    {
        $class = null;
        if (strpos($method, '_'))
            list($class, $method) = explode('_', $method, 2);

        $method = $class ? "{$class}:{$method}" : $method;

        return call_user_func_array($method, $args);
    }


    // Colorpicker
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


    // Datepicker
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


    // Datepicker & Timepicker
    static public function datetimepicker(&$attr, &$group_class, &$inner)
    {
        $attr['data-datepicker'] = "data-datetimepicker=\"true\"";

        $inner['plugins']['datepicker'] = '
<script src="' . RESOURCES_URL . 'js/jquery.zdatepicker.js"></script>
<script src="' . RESOURCES_URL . 'js/jquery.timepicker.js"></script>
<link href="' . RESOURCES_URL . 'css/zdatepicker.css" rel="stylesheet" />
<link href="' . RESOURCES_URL . 'css/timepicker.css" rel="stylesheet" />
<script>
$(function(){
    $("input[data-datetimepicker]").focus(function(){
        var inp = $(this),
            step = inp.data("step"),
            val = inp.val()
        if(!step) {
            inp.zdatepicker({viewmonths:1, event:"none", show:true, onReturn:function(date, dateObj, input, calendar, a, selected){
                $(input).val(date).data({step:1, date:date})
                    .timepicker({showOnFocus:false, timeFormat:"H:i", step:5}).timepicker("show")
                    .next(".zdatepicker").hide();
            }});
        }
    })
    .on("selectTime", function(){
        var inp = $(this),
            time = inp.val(),
            date = inp.data("date")

        inp.val(date + " " + time).data({step:0});
    });
})
</script>';

    }


}