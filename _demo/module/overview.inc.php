<?php
/**
 * 站点概况
 +-----------------------------------------
 * @author nolan.chou
 * @category
 * @version $Id$
 */


use pt\framework\template as template;


if (!defined('MODULE')) exit;

function overview()
{
    global $db;

    $today = strtotime('today 00:00:00');

    $start = !empty($_GET['start']) ? strtotime($_GET['start']) : '';
    $end   = !empty($_GET['end']) ? strtotime($_GET['end']) : '';

    if (!$start || !$end)
    {
        $start = $today - 7 * 86400;
        $end = $today;
    }

    $data = array();
    $_data = $db -> prepare("SELECT * FROM `db_analys` WHERE `date` < :end AND `date` >= :start") -> execute(array(':start'=>$start, ':end'=>$end));

    foreach ($_data as $v)
    {
        $date = $v['date'];
        $v['date'] = date('Y-m-d', $v['date']);
        $data[$date] = $v;
    }

    $empty = array("date"=>0,"visit_total"=>0,"share_pv"=>0,"share_uv"=>0,"session_cnt"=>0,"visit_pv"=>0,"visit_uv"=>0,"visit_uv_new"=>0,"stay_time_uv"=>0,"stay_time_session"=>0,"visit_depth"=>0);
    for ($i = $start; $i < $end; $i = $i + 86400)
    {
        $date = date('Y-m-d', $i);
        if (!isset($data[$i]))
        {
            $empty['date'] = $date;
            $data[$i] = $empty;
        }
    }

    template::assign('start', date('Y-m-d', $start));
    template::assign('end', date('Y-m-d', $end));
    template::assign('data', array_values($data));
}




function refresh()
{
    global $db;

    $date = strtotime($_POST['date']);
    if (!$date)
        json_return(null, 1, '日期不正确');

    // Refresh data, do sth..

    json_return(true);
}