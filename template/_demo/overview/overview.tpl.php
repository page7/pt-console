<?php self::unpjax('container', array('title'=>'站点概况', 'preload'=>'')); ?>

<h1 class="page-header">站点概况</h1>

<!-- page and operation -->
<div>
    <form class="form-inline" action="" method="GET" role="form">
        <!--- filter -->
        <div class="btn-group" style="margin:20px 0px; margin-right:10px;">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                操作 <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" role="menu">
                <li><a href="javascript:;" class="btn-refresh">手动更新数据</a></li>
            </ul>
        </div>
        <!-- end filter -->

        <!-- search -->
        <div class="form-group">
            <input type="text" name="start" class="form-control ui-datepicker" value="<?php echo $start; ?>" placeholder="开始日期" />
        </div>
        <div class="form-group">
            <input type="text" name="end" class="form-control ui-datepicker" value="<?php echo $end; ?>" placeholder="结束日期" />
        </div>
        <button class="btn btn-default btn-flat" type="submit"><span class="glyphicon glyphicon-search"></span></button>
        <!-- end search -->
    </form>

</div>
<!--  page and operation  -->



<!-- modal -->
<div id="modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title">更新数据</h4>
            </div>
            <form class="modal-body form-horizontal">

                <blockquote>
                    <p>由于接口限制，每次只能更新一日数据，日期最大值为昨日</p>
                </blockquote>

                <div class="form-group">
                    <label class="col-sm-4 control-label">更新日期</label>
                    <div class="col-sm-6">
                        <input type="text" name="date" class="form-control ui-datepicker" value=""  />
                    </div>
                </div>

            </form>
            <div class="modal-footer">
                <button type="button" class="btn btn-flat btn-default" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary btn-submit" data-loading-text="更新中..">更新</button>
            </div>
        </div>
    </div>
</div>


<div id="chartdiv" style="height:500px;">

</div>



<script src="<?php echo RESOURCES_URL; ?>js/jquery.zdatepicker.js"></script>
<link href="<?php echo RESOURCES_URL; ?>css/zdatepicker.css" rel="stylesheet" />
<script src="<?php echo RESOURCES_URL; ?>js/amcharts/amcharts.js"></script>
<script src="<?php echo RESOURCES_URL; ?>js/amcharts/serial.js"></script>

<script>
$(function(){

    $(".ui-datepicker").zdatepicker({ viewmonths:1 });

    var modal = $("#modal");

    $(".btn-refresh").click(function(){
        var btn = $(this);
        modal.find("form")[0].reset();
        modal.modal("show");
    });

    modal.find(".btn-submit").click(function(){
        var btn = $(this), form = modal.find("form"), post = form.serialize();
        btn.button('loading');
        $.post("?module=overview&operate=refresh", post, function(data){
            btn.button('reset');
            if (data.s == 0){
                alert('更新成功', 'success', function(){}, modal.find(".modal-body"));
            } else {
                alert(data.err, 'error', null, modal.find(".modal-body"));
            }
        }, "json");
    });

    AmCharts.makeChart("chartdiv",
    {
        "type": "serial",
        "categoryField": "date",
        "dataDateFormat": "YYYY-MM-DD",
        "handDrawn": true,
        "handDrawScatter": 0,
        "handDrawThickness": 0,
        "theme": "default",
        "categoryAxis": {
            "parseDates": true
        },
        "chartCursor": {
            "enabled": true
        },
        "chartScrollbar": {
            "enabled": true
        },
        "trendLines": [],
        "graphs": [
            {
                "bullet": "round",
                "id": "ag-1",
                "title": "累计用户数",
                "valueField": "visit_total"
            },
            {
                "bullet": "square",
                "id": "ag-2",
                "title": "转发次数",
                "valueField": "share_pv"
            },
            {
                "bullet": "square",
                "id": "ag-3",
                "title": "转发次数",
                "valueField": "share_uv"
            },
            {
                "bullet": "square",
                "id": "ag-4",
                "title": "打开次数",
                "valueField": "session_cnt"
            },
            {
                "bullet": "square",
                "id": "ag-5",
                "title": "访问次数",
                "valueField": "visit_pv"
            },
            {
                "bullet": "square",
                "id": "ag-6",
                "title": "访问人数",
                "valueField": "visit_uv"
            },
            {
                "bullet": "square",
                "id": "ag-7",
                "title": "新用户数",
                "valueField": "visit_uv_new"
            },
            {
                "bullet": "square",
                "id": "ag-8",
                "title": "人均停留时长",
                "valueField": "stay_time_uv"
            },
            {
                "bullet": "square",
                "id": "ag-9",
                "title": "次均停留时长",
                "valueField": "stay_time_session"
            },
            {
                "bullet": "square",
                "id": "ag-10",
                "title": "平均访问深度",
                "valueField": "visit_depth"
            }
        ],
        "guides": [],
        "valueAxes": [],
        "allLabels": [],
        "balloon": {},
        "legend": {
            "enabled": true,
            "useGraphSettings": true
        },
        "dataProvider": <?php echo json_encode($data); ?>
    }
);

});
</script>