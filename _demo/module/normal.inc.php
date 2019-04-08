<?php
/**
 * 标准模块
 +-----------------------------------------
 * @author nolan.chou
 * @category
 * @version $Id$
 */

use pt\framework\template as template;

if (!defined('MODULE')) exit;



function normal()
{
    global $db;

    $config = array(
        'search'    => array(
            'name',
        ),
        'page'      => true,
        'menu'      => array(
            'create'    => array('name'=>'新增产品'),
            'deletes'   => array('name'=>'批量删除'),
        ),
        'buttons'   => array(
            'edit'      => true,
            'status'    => array(
                'callback'  => function($data) {
                    if ($data['status'] > 0)
                        return array('class'=>'btn-warning', 'ico'=>'glyphicon glyphicon-save', 'name'=>'下线', 'data'=>array('id'=>$data['id'], 'status'=>-1, 'operate'=>'status'));
                    else
                        return array('class'=>'btn-success', 'ico'=>'glyphicon glyphicon-open', 'name'=>'上线', 'data'=>array('id'=>$data['id'], 'status'=>1, 'operate'=>'status'));
                }
            ),
            'delete'    => true,
        ),
        'fields'    => array(
            array(
                'title' => '产品名',
                'field' => 'name',
                'sub'   => 'en'
            ),
            array(
                'title' => '等级',
                'field' => 'star',
                'width' => '130px',
                'callback'  => function(&$val, &$sub, $data) {
                    $star = '<font class="glyphicon glyphicon-star"></font>';
                    $stars = '';
                    for ($i=1; $i<=$val; $i++) {
                        $stars .= $star;
                    }

                    $val = '<span style="color:orange">'.$stars.'</span>';
                },
            ),
            array(
                'title' => '状态',
                'field' => 'status',
                'width' => '130px',
                'callback'  => function(&$val, &$sub, $data) {
                    if ($val == 1)
                        $val = '<span class="label label-success">已发布</span>';
                    else if ($val == -1)
                        $val = '<span class="label label-default">已下架</span>';
                    else
                        $val = '<span class="label label-info">未发布</span>';
                },
            )
        ),
    );

    console::$dbtable = 'db_product';

    console::table($config);

    template::assign('title', '产品');
    return 'default/table';
}






function edit()
{
    global $db;

    $config = array(
        'fields' => array(
            'cover'         => array(
                'name'          => '产品图片',
                'length'        => '50',
                'type'          => 'image',
                'must'          => true,
                'size'          => array(200, 200),
                'placeholder'   => '建议上传尺寸：800 x 800，图像将根据实际展示尺寸裁剪',
            ),
            'pictures'      => array(
                'name'          => '轮播图',
                'length'        => '50',
                'type'          => 'image',
                'multiple'      => true,
                'must'          => true,
                'size'          => array(240, 120),
                'placeholder'   => '建议上传尺寸：1200 x 600',
            ),
            'name'          => array(
                'name'          => '产品名称',
                'width'         => '7',
                'length'        => '50',
                'type'          => 'text',
                'must'          => true,
            ),
            'en'            => array(
                'name'          => '英文名称',
                'length'        => '200',
                'type'          => 'text',
                'must'          => true,
            ),
            'star'          => array(
                'name'          => '等级',
                'width'         => '3',
                'length'        => '1',
                'type'          => 'number',
                'must'          => false,
                'default'       => '0',
                'suffix'        => 'ico:glyphicon glyphicon-star',
            ),
            'area'          => array(
                'name'          => '地区',
                'width'         => '9',
                'type'          => 'select',
                'callback'      => function($v) {
                    if (console::$mode == console::LOAD)
                    {
                        global $db;
                        return $db -> prepare('SELECT `id` AS `val`, CONCAT(`city`, "-", `name`) AS `option`, IF(`id`=:val,1,0) AS `selected` FROM `db_area` ORDER BY `id` ASC;') -> execute(array(':val'=>$v));
                    }
                    return $v;
                },
            ),
            'tags'          => array(
                'name'          => '标签',
                'width'         => '9',
                'type'          => 'select',
                'multiple'      => true,
                'length'        => 10,
                'callback'      => function($v) {
                    if (console::$mode == console::LOAD)
                    {
                        global $db;
                        return $db -> prepare('SELECT `id` AS `val`, `tag` AS `option`, FIND_IN_SET(`id`,:val) AS `selected` FROM `db_product_tag` ORDER BY `id` ASC;') -> execute(array(':val'=>$v));
                    }
                    return $v;
                },
            ),
            'intro'         => array(
                'name'          => '产品简介',
                'length'        => '500',
                'rows'          => 5,
                'type'          => 'textarea',
            ),
            'sold'         => array(
                'name'          => '月销量',
                'width'         => '3',
                'length'        => '10',
                'type'          => 'number',
                'placeholder'   => '',
            ),
            'assess'        => array(
                'name'          => '单项评分',
                'width'         => '3',
                'type'          => 'custom',
                'callback'      => function($val){
                    if (console::$mode == console::LOAD)
                    {
                        $label = array('质量', '服务', '物流', '售后');
                        $val = empty($val) ? array('', '', '', '') : explode('|', $val);

                        $inp = '';
                        foreach ($val as $k => $v)
                            $inp .= '<input type="number" name="assess[]" title="'.$label[$k].'" autocomplete="off" class="form-control" placeholder="'.$label[$k].'" value="'.$v.'" /></div><div class="col-sm-3'.($k == 2 ? ' col-sm-offset-2' : '').'">';

                        return substr($inp, 0, -strlen('</div><div class="col-sm-3">'));
                    }
                    else
                    {
                        return implode('|', $val);
                    }

                }
            ),
            'recommend'     => array(
                'name'          => '推荐',
                'width'         => '9',
                'type'          => 'radio',
                'options'       => array(
                    array('val'=>0, 'option'=>'否'),
                    array('val'=>1, 'option'=>'是'),
                )
            ),
            'updatetime'=> array(
                'name'          => '更新时间',
                'type'          => 'auto',
                'default'       => NOW,
            ),
            'updator'   => array(
                'name'          => '更新人',
                'type'          => 'auto',
                'default'       => $_SESSION['name'],
            ),
        ),
        'subitems' => array(
            'item'  => array(
                'name'          => '产品选项',
                'placeholder'   => '请先保存产品内容',
                'db'            => 'db_product_item',
                'data'          => function($pid){
                    global $db;
                    $items = $db -> prepare("SELECT * FROM `db_product_item` WHERE `pid`=:id ORDER BY `sort` DESC, `id` ASC;") -> execute(array(':id'=>$pid));
                    return $items;
                },
                'type'          => 'child',
                'mode'          => 'modal',
                'operate'       => array(
                    'resort'        => array('ico'=>'glyphicon glyphicon-sort',     'title'=>'排序',       'mode'=>'modal', 'size'=>'sm', 'url'=> BASE_URL .'?module=' .MODULE .'&operate=resort&id={pid}' ),
                    'calendar'      => array('ico'=>'glyphicon glyphicon-calendar', 'title'=>'库存日历',   'mode'=>'modal', 'size'=>'sm', 'url'=> BASE_URL .'?module=' .MODULE .'&operate=calendar&id={pid}')
                ),
                'format'        => function($data) {
                    return array('title'=>$data['name'], 'content'=>($data['type'] == 'A' ? '【满赠】' : ($data['type'] == 'B' ? '【加价购】' : '')) . $data['content'], 'badge'=>$data['price']);
                },
                'fields'    => array(
                    'name'     => array(
                        'name'          => '名称',
                        'length'        => '30',
                        'width'         => '8',
                        'type'          => 'text',
                        'must'          => true,
                    ),
                    'type'     => array(
                        'name'          => '类型',
                        'length'        => '4',
                        'width'         => '6',
                        'type'          => 'select',
                        'must'          => true,
                        'options'       => array(
                            array('val'=>'',   'option'=>'选项'),
                            array('val'=>'A',   'option'=>'满赠'),
                            array('val'=>'B', 'option'=>'加价购'),
                        ),
                    ),
                    'start'     => array(
                        'name'          => '开始日期',
                        'type'          => 'text',
                        'width'         => '6',
                        'prefix'        => 'ico:glyphicon glyphicon-calendar',
                        'plugin'        => 'datepicker',
                        'placeholder'   => '售卖开始日期（包含）',
                        'callback'      => function($v) {
                            if (console::$mode)
                            {
                                if ($data['type'] == 'item' && !$v) json_return(null, 1, '请输入售卖开始日期');
                                if ($v)
                                {
                                    $v = strtotime($v);
                                    if (!$v) json_return(null, 1, '开始日期格式错误');
                                }
                                return $v;
                            }
                            else
                            {
                                return $v ? date('Y-m-d', $v) : '';
                            }
                        },
                    ),
                    'end'       => array(
                        'name'          => '结束日期',
                        'type'          => 'text',
                        'width'         => '6',
                        'prefix'        => 'ico:glyphicon glyphicon-calendar',
                        'plugin'        => 'datepicker',
                        'placeholder'   => '售卖结束日期（包含）',
                        'callback'      => function($v) {
                            if (console::$mode)
                            {
                                if ($data['type'] == 'item' && !$v) json_return(null, 1, '请输入售卖结束日期');
                                if ($v)
                                {
                                    $v = strtotime($v);
                                    if (!$v) json_return(null, 1, '结束日期格式错误');
                                }
                                return $v;
                            }
                            else
                            {
                                return $v ? date('Y-m-d', $v) : '';
                            }
                        },
                    ),
                    'floor'     => array(
                        'name'          => '底价',
                        'width'         => '5',
                        'length'        => '10',
                        'type'          => 'number',
                        'must'          => true,
                        'prefix'        => '¥',
                        'callback'      => function($v) {
                            return console::$mode ? (int)$v : $v;
                        },
                    ),
                    'price'     => array(
                        'name'          => '价格',
                        'width'         => '5',
                        'length'        => '10',
                        'type'          => 'number',
                        'must'          => true,
                        'prefix'        => '¥',
                        'callback'      => function($v) {
                            return console::$mode ? (int)$v : $v;
                        },
                    ),
                    'content'   => array(
                        'name'          => '产品描述',
                        'length'        => '500',
                        'width'         => '8',
                        'rows'          => 5,
                        'type'          => 'textarea',
                    ),
                    'remove'   => array(
                        'name'          => '',
                        'width'         => '6',
                        'button'        => '移除此项',
                        'type'          => 'remove'
                    ),
                ),
            ),
        ),
    );

    console::$dbtable = 'db_product';

    console::form($config);

    template::assign('title', '产品');
    return 'default/form';
}




function resort()
{
    global $db;

    if ($_POST)
    {
        $data = array();
        foreach ($_POST['item'] as $k => $v)
        {
            $data[] = array('id'=>$v, 'sort'=>count($_POST['item'])-$k);
        }

        list($columns, $sql, $values) = array_values(insert_array($data));
        $rs = $db -> prepare("INSERT INTO `db_product_item` {$columns} VALUES {$sql} ON DUPLICATE KEY UPDATE `sort`=VALUES(`sort`);") -> execute($values);
        if (false === $rs)
            json_return(null, 1, '操作失败，请重试');

        json_return(true);
    }

    $items = $db -> prepare("SELECT `id`, `pid`, `name` FROM `db_product_item` WHERE `pid`=:id ORDER BY `sort` DESC, `id` ASC;") -> execute(array(':id'=>$_GET['id']));

    template::assign('items', $items);
    return 'default/sort';
}






function calendar()
{
    global $db;

    if ($_POST)
    {
        if (empty($_POST['dates']))
            json_return(null, 1, '请选择日期后再操作');

        if ($_POST['filled'])
        {
            $data = array();
            foreach ($_POST['dates'] as $v)
                $data[] = array('item'=>$_POST['item'], 'date'=>$v);

            list($columns, $sql, $values) = array_values(insert_array($data));
            $rs = $db -> prepare("INSERT INTO `db_date` {$columns} VALUES {$sql} ON DUPLICATE KEY UPDATE `date`=VALUES(`date`);") -> execute($values);
        }
        else
        {
            $dates = implode(',', $_POST['dates']);

            $rs = $db -> prepare("DELETE FROM `db_date` WHERE `item`=:item AND `date` IN ({$dates});") -> execute(array(':item'=>$_POST['item']));
        }

        if (false === $rs)
            json_return(null, 1, '操作失败，请重试');

        json_return(true);
    }

    if (empty($_GET['item']))
    {
        $id = (int)$_GET['id'];

        $items = $db -> prepare("SELECT `id`, `pid`, `name` FROM `db_product_item` WHERE `pid`=:id ORDER BY `sort` DESC, `id` ASC;") -> execute(array(':id'=>$id));

        template::assign('id', $id);
        template::assign('items', $items);
    }
    else
    {
        $id = (int)$_GET['item'];

        $item = $db -> prepare("SELECT `start`, `end` FROM `db_product_item` WHERE `id`=:id;") -> execute(array(':id'=>$id));

        $month = !empty($_GET['month']) ? $_GET['month'] : date('Y-m');

        $first = strtotime($month.'-1');
        if (!$first) $first = strtotime(date('Y-m-1'));

        $first_day  = date('N', $first);

        $start  = $first_day == 7 ? $first : $first - $first_day * 86400;

        $end    = $start + 41 * 86400;

        $date = array();

        for ($i=$start; $i<=$end; $i=$i+86400)
        {
            $date[$i] = array('date'=>$i, 'filled'=>0);
        }

        $_full = $db -> prepare("SELECT `id`, `date` FROM `db_date` WHERE `item`=:item AND `date`>=:start and `date`<=:end") -> execute(array(':item'=>$id,':start'=>$start,':end'=>$end));

        if ( $_full )
        {
            foreach ( $_full as $v )
            {
                $date[$v['date']]['filled'] = 1;
            }
        }

        template::assign('item', $id);
        template::assign('bookstart', $item[0]['start']);
        template::assign('bookend',   $item[0]['end']);
        template::assign('first', $first);
        template::assign('start', $start);
        template::assign('end', $end);
        template::assign('date', $date);
    }

    return 'default/calendar';
}




function status()
{
    global $db;

    $id = (int)$_POST['id'];
    $status = (int)$_POST['status'];

    $rs = $db -> prepare("UPDATE `db_product` SET `status`=:status WHERE `id`=:id") -> execute(array(':id'=>$id, ':status'=>$status));
    if (false === $rs)
        json_return(null, 1, '操作失败，请重试');

    json_return(true);
}



