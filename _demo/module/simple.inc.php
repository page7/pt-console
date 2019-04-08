<?php
/**
 * 简单模块
 +-----------------------------------------
 * @author nolan.chou
 * @category
 * @version $Id$
 */

use pt\framework\template as template;

if (!defined('MODULE')) exit;



function simple()
{
    global $db;

    $config = array(
        // 搜索字段
        'search'    => array('name'),
        // 是否需要分页
        'page'      => false,
        // 操作菜单
        'menu'      => array(
            'create'    => array('name'=>'新增地区', 'mode'=>'modal', 'size'=>'sm'),
            'deletes'   => array('name'=>'批量删除'),
        ),
        // 列表按钮
        'buttons'   => array(
            'edit'      => array('mode'=>'modal', 'size'=>'sm'),
            'delete'    => true,
        ),
        // 列表显示字段
        'fields'    => array(
            array(
                'title' => '地区名',
                'field' => 'name',
            ),
            array(
                'title' => '城市',
                'field' => 'city',
            ),
            array(
                'title' => '排序',
                'field' => 'sort',
                'width' => '120px',
                'type'  => 'custom',
                'style' => ".table .btn-sort { font-size:10px; line-height:1; color:#999; }",
                'callback'  =>  function(&$val, &$sub, $data, $i, $len) {
                    $btn = '<button data-operate="resort" data-type="up" data-id="{id}" {d1}class="btn btn-default btn-sm btn-sort btn-operate"><font class="glyphicon glyphicon-triangle-top"></font></button>'
                            .'<button data-operate="resort" data-type="down" data-id="{id}" {d2}class="btn btn-default btn-sm btn-sort btn-operate"><font class="glyphicon glyphicon-triangle-bottom"></font></button>';
                    if ($i == 1)
                        $val = str_replace(array('{d1}', '{d2}', '{id}'), array('disabled ', '', $data['id']), $btn);
                    else if ($i == $len)
                        $val = str_replace(array('{d1}', '{d2}', '{id}'), array('', 'disabled ', $data['id']), $btn);
                    else
                        $val = str_replace(array('{d1}', '{d2}', '{id}'), array('', '', $data['id']), $btn);
                },
            ),
        ),
    );

    // 数据库
    console::$dbtable = 'db_area';

    console::table($config, '1=1', array(), '`sort` DESC, `id` ASC');

    template::assign('title', '地区');
    return 'default/table';
}






function edit()
{
    global $db;

    $config = array(
        'name'  => '地区',
        'url'   => $_SERVER['REQUEST_URI'],
        'size'  => 'sm',
        'fields' => array(
            'name'  => array(
                'name'          => '地区名',
                'length'        => '5',
                'type'          => 'text',
                'must'          => true,
            ),
            'city'  => array(
                'name'          => '城市',
                'length'        => '10',
                'type'          => 'text',
                'must'          => true,
            ),
        )
    );

    console::$dbtable = 'db_area';

    console::form($config);

    template::assign('title', '地区');
    return 'default/form';
}



function resort()
{
    global $db;

    $id = (int)$_POST['id'];
    $type = $_POST['type'];

    $_list = $db -> prepare("SELECT `id` FROM `db_area` ORDER BY `sort` DESC, `id` ASC") -> execute();

    $data = array();
    $len = count($_list);
    $now = 0;

    foreach ($_list as $i => $v)
    {
        $data[] = array('id'=>$v['id'], 'sort'=>$len - $i);
        if ($v['id'] == $id)
            $now = $i;
    }

    if ($type == 'up' && isset($data[$now-1]))
    {
        $data[$now-1]['sort'] = $data[$now-1]['sort'] - 1;
        $data[$now]['sort'] = $data[$now]['sort'] + 1;
    }
    else if ($type == 'down' && isset($data[$now+1]))
    {
        $data[$now+1]['sort'] = $data[$now+1]['sort'] + 1;
        $data[$now]['sort'] = $data[$now]['sort'] - 1;
    }

    list($columns, $sql, $values) = array_values(insert_array($data));
    $rs = $db -> prepare("INSERT INTO `db_area` {$columns} VALUES {$sql} ON DUPLICATE KEY UPDATE `sort`=VALUES(`sort`);") -> execute($values);
    if (false === $rs)
        json_return(null, 1, '操作失败，请重试');

    json_return(true);
}


