
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
    <h4 class="modal-title">售卖日期管理</h4>
</div>

<?php
if (isset($items)) {
?>

<style>
.calendar-items a { padding-left:32px; }
.calendar-items span { position:absolute; color:#ccc; top:10px; left:16px; }
</style>

<div class="calendar-items list-group">
    <?php foreach ($items as $v) { ?>
    <a href="javascript:;" class="list-group-item" data-id="<?php echo $v['id']; ?>">
        <span class="glyphicon glyphicon-calendar"></span>
        <?php echo $v['name']; ?>
    </a>
    <?php } ?>
</div>

<script>
$(function(){
    $(".calendar-items a").click(function(){
        var a = $(this),
            id = a.data("id"),
            modal = $(this).parents(".modal");
        modal.children(".modal-dialog").addClass("modal-loading").children(".modal-content").html('');

        $.get("<?php echo BASE_URL; ?>?module=<?php echo MODULE ?>&operate=calendar&id=<?php echo $id; ?>", {item:id}, function(data){
            modal.children(".modal-dialog").removeClass("modal-loading modal-sm").children(".modal-content").html(data);
        }, "html");

        modal.on('hidden.bs.modal', function (e) {
            console.log(e);
        })
    });
});
</script>






<?php
} else {
?>

<style>
.calendar .filled b { background:#fdf2f1; }
</style>

<div id="calendar-<?php echo NOW ?>" class="calendar modal-body">
    <div class="title">
        <a class="prev" href="javascript:;"><span class="glyphicon glyphicon-chevron-left"></span></a>
        <a class="next" href="javascript:;"><span class="glyphicon glyphicon-chevron-right"></span></a>
        <div><?php echo date('Y-m', $first); ?></div>
    </div>

    <div class="week">
        <span class="sun"><b class="glyphicon glyphicon-check"></b>日</span><span class="mon"><b class="glyphicon glyphicon-check"></b>一</span><span class="tue"><b class="glyphicon glyphicon-check"></b>二</span><span class="wed"><b class="glyphicon glyphicon-check"></b>三</span><span class="thu"><b class="glyphicon glyphicon-check"></b>四</span><span class="fri"><b class="glyphicon glyphicon-check"></b>五</span><span class="sat"><b class="glyphicon glyphicon-check"></b>六</span>
    </div>

    <ul class="date">
        <?php
        for($i = $start; $i <= $end; $i = $i + 86400)
        {
            $class = 'w'.date('N', $i).' ';
            if (date('Y-m', $i) != date('Y-m', $first)) $class .= 'opacity ';
            if ($i < NOW - 86400 || $i < $bookstart || ($bookend && $i > $bookend)) $class .= 'disabled ';
            $sold = 0;

            $label = $title = '';

            if (isset($date[$i]))
            {
                $d = &$date[$i];

                if ($d['filled'])
                {
                    $label = "<span class=\"label label-danger hidden-xs\">不可预订</span>";
                    $class .= 'filled ';
                }
            }
        ?>
        <li class="<?php echo trim($class); ?>" data-date="<?php echo $i; ?>"><b><?php echo date('j', $i); ?></b><?php echo $label; ?></li>
        <?php } ?>
    </ul>

    <div class="loading">
        <span class="glyphicon glyphicon-refresh glyphicon-loading"></span> Loading..
    </div>
</div>


<div class="modal-footer">
    <button type="button" class="btn btn-default btn-unfilled">可购买</button>
    <button type="button" class="btn btn-danger btn-filled">不可购买</button>
</div>


<script>
$(function(){

    var modal = $("#calendar-<?php echo NOW ?>").parents(".modal");

    modal.find(".calendar .date li").click(function(e){
        var li = $(this);
        if (li.is(".disabled")) return;
        if (li.is(".selected")) {
            li.removeClass("selected");
            var type = "remove";
        } else {
            li.addClass("selected");
            var type = "add";
        }
        if (e.shiftKey) {
            var list = $(".calendar .date li");
            var start = $(".calendar .date .start");
            if (start.length) {
                var s = list.index(start.eq(0));
                var e = list.index(li);
                var li = (s <= e) ? list.slice(s, e) : list.slice(e, s);
                if (type == "add") li.addClass("selected");
                else li.removeClass("selected");
                start.removeClass("start");
                li.focus().addClass("start");
            }
        } else {
            $(".calendar .date .start").removeClass("start");
            li.addClass("start");
        }
    });

    modal.find(".calendar .week span").click(function(){
        var w = $(this);
        var i = w.prevAll("span").length;
        if (i == 0) i = 7;
        if (w.is(".freeze")) {
            $(".calendar .date .w"+i).removeClass("freeze");
            w.removeClass("freeze");
            w.children("b").removeClass("glyphicon glyphicon-unchecked").addClass("glyphicon glyphicon-check");
        } else {
            $(".calendar .date .w"+i).addClass("freeze");
            w.addClass("freeze");
            w.children("b").removeClass("glyphicon glyphicon-check").addClass("glyphicon glyphicon-unchecked");
        }
    });


    modal.find(".modal-footer .btn-filled, .modal-footer .btn-unfilled").click(function(){
        var filled = $(this).is(".btn-filled") ? 1 : 0,
            sels = $(".calendar .date .selected:not(.freeze)"),
            i, dates = [];

        for (i=0; i<sels.length; i++)
        {
            dates.push( sels.eq(i).data("date") );
        }
        $(".calendar .loading").show();

        $.post(
            "<?php echo BASE_URL; ?>?module=<?php echo MODULE ?>&operate=calendar",
            {item:<?php echo $item; ?>, dates:dates, filled:filled},
            function(data){
                $(".calendar .loading").hide();
                if (data.s == 0) {
                    sels.each(function(){
                        var s = $(this);
                        s.removeClass("filled selected").children(".label").remove();
                        if (filled) {
                            s.addClass("filled");
                            s.append("<span class=\"label label-danger hidden-xs\">不可购买</span>");
                        }
                    });
                } else {
                    alert(data.err, "error", null, modal.find(".modal-body"));
                }
            },
            "json"
        );
    });

    modal.find(".calendar .title .next, .calendar .title .prev").click(function(){
        var btn = $(this);
        if (btn.is(".next")){
            var month = "<?php echo date('Y-m', $first + 86400 * 31); ?>";
        }else {
            var month = "<?php echo date('Y-m', $first - 86400); ?>";
        }
        $(".calendar .loading").show();

        $.get("<?php echo BASE_URL; ?>?module=<?php echo MODULE ?>&operate=calendar", {item:<?php echo $item; ?>, month:month}, function(data){
            modal.children(".modal-dialog").removeClass("modal-loading modal-sm").children(".modal-content").html(data);
        }, "html");
    });


});
</script>

<?php
}
?>