<?php

use pt\framework\template as template;
use pt\framework\db as db;
use pt\framework\event as event;
use pt\tool\page as page;

class console
{
    const SAVE = 1;

    const LOAD = 0;

    // 主表表名
    public static $dbtable;

    // 当前操作模式
    public static $mode = 0;

    // 模板路径
    public static $tmpl_path = '/common/console/';

    // 文件上传路径
    public static $upload_path = '/upload/';


    // =================== 列表项 ===================

    public static function table($config, $where='1=1', $condition=array(), $sort='`id` DESC', $page=10)
    {
        $db = db::init();
        $table = self::$dbtable;

        $keyword = '';
        if (!empty($_GET['keyword']) && !empty($config['search']))
        {
            $keyword = $_GET['keyword'];
            $_search = array();
            foreach ($config['search'] as $field)
            {
                if (false === strpos($field, '.'))
                    $_search[] = "`{$field}` LIKE :keyword";
                else
                    $_search[] = $field;
            }

            $where .= ' AND (' . implode(' OR ', $_search) . ')';
            $condition[':keyword'] = '%'.$keyword.'%';
        }

        $fields = array('a.`id`');
        $join = array();
        foreach ($config['fields'] as $v)
        {
            if (!empty($v['join']))
            {
                $join[] = $v['join'];
            }

            if (!empty($v['as']))
            {
                $fields[] = "{$v['as']} AS `{$v['field']}`";
            }
            else
            {
                $fields[] = "a.`{$v['field']}`";
            }
            if (isset($v['sub']))
                $fields[] = "a.`{$v['sub']}`";
        }
        $fields = implode(',', $fields);
        $join = implode("\n", $join);

        if ($page)
        {
            $count = $db -> prepare("SELECT COUNT(*) AS `c` FROM `{$table}` AS a {$join} WHERE {$where};") -> execute($condition);
            $page = new page($count[0]['c'], $page);
            $limit = 'LIMIT '.$page -> limit();
            $page = $page -> show();
        }
        else
        {
            $limit = '';
            $page = array();
        }

        $sql = "SELECT {$fields}
                FROM `{$table}` AS a
                    {$join}
                WHERE {$where}
                ORDER BY {$sort}
                {$limit};";
        $list = $db -> prepare($sql) -> execute($condition);

        $assign = array(
            'config'    => $config,
            'keyword'   => $keyword,
            'page'      => $page,
            'list'      => $list,
            '_suffix'   => ($page['now'] ? '&page=' . $page['now'] : '') . (isset($_GET['keyword']) ? '&keyword='.$_GET['keyword'] : ''),
        );

        self::display($assign, 'table');
    }





    // =================== 表单项 ===================

    public static function form($config)
    {
        $db = db::init();

        if (isset($_GET['item']) && isset($_GET['sid']))
        {
            $item = $_GET['item'];

            $table = $config['subitems'][$item]['db'];
            $config = $config['subitems'][$item];
            $id = (int)$_GET['sid'];
        }
        else
        {
            $item = false;

            $table = self::$dbtable;
            $id = (int)$_REQUEST['id'];
        }

        $_modal = !empty($_GET['modal']) ? true : false;

        if ($_POST)
        {
            $id = (int)$_POST['id'];

            console::$mode = self::SAVE;

            if (isset($_POST['remove']))
            {
                $id = $_POST['remove'];

                $rs = $db -> prepare("DELETE FROM `{$table}` WHERE `id`=:id") -> execute(array(':id'=>$id));

                if (!$item && !empty($config['subitems']))
                {
                    foreach ($config['subitems'] as $conf)
                        $db -> prepare("DELETE FROM `{$conf['db']}` WHERE `pid`=:id") -> execute(array(':id'=>$id));
                }
            }
            else
            {
                $data = array();
                $inner = $class = null;

                foreach ($config['fields'] as $name => $field)
                {
                    switch ($field['type'])
                    {
                        case 'auto':
                            $val = null;

                            if (isset($field['update']) && $field['update'] == false)
                                continue 2;

                            if (isset($field['default']))
                                $val = $field['default'];

                            if (isset($field['callback']))
                                $val = call_user_func_array($field['callback'], array($val, &$data, &$inner, &$class));
                            break;

                        case 'custom':
                            $val = isset($_POST[$name]) ? $_POST[$name] : null;

                            if (!$val && isset($field['default']))
                                $val = $field['default'];

                            if (isset($field['callback']))
                                $val = call_user_func_array($field['callback'], array($val, &$data, &$inner, &$class));
                            break;

                        case 'remove':
                            continue 2;

                        default:
                            $val = isset($_POST[$name]) ? $_POST[$name] : null;

                            if (is_array($val))
                            {
                                $val = array_filter($val);
                                if (is_bool($field['multiple']))
                                    $val = implode(',', $val);
                                else if (is_callable($field['multiple']))
                                    $val = call_user_func($field['multiple'], $val);
                                else
                                    $val = implode((string)$field['multiple'], $val);
                            }

                            if (isset($field['callback']))
                                $val = call_user_func_array($field['callback'], array($val, &$data, &$inner, &$class));

                            if (!empty($field['must']) && ($val === null || $val === ""))
                            {
                                event::trigger('console::form_save_field_empty', $field, $val);

                                if (is_bool($field['must']))
                                    json_return(null, 1, '请填写'.$field['name']);
                                else
                                    json_return(null, 1, $field['must']);
                            }
                    }

                    if ($val === false) continue;

                    $data[$name] = $val;
                }


                if ($id)
                {
                    list($sql, $value) = array_values(update_array($data));
                    $value[':id'] = $id;
                    $rs = $db -> prepare("UPDATE `{$table}` SET {$sql} WHERE `id`=:id") -> execute($value);
                    $data['id'] = $id;
                }
                else
                {
                    if ($item)
                        $data['pid'] = (int)$_POST['pid'];

                    list($columns, $sql, $value) = array_values(insert_array($data));
                    $data['id'] = $id = $rs = $db -> prepare("INSERT INTO `{$table}` {$columns} VALUES {$sql};") -> execute($value);
                }

            }

            if (false === $rs)
            {
                event::trigger('console::form_save_fail', 101, $data);
                json_return(null, 101, '数据保存失败，请重试');
            }

            event::trigger('console::form_save_success', $rs, $data);
            json_return($id);
        }

        if ($id)
        {
            $data = $db -> prepare("SELECT * FROM `{$table}` WHERE `id`=:id") -> execute(array(':id'=>$id));

            if (!$data)
                redirect('?module=' . MODULE);

            $data = $data[0];
        }
        else
        {
            $data = null;
        }

        $assign = array(
            'config'    => $config,
            'data'      => $data,
            '_suffix'   => (isset($_GET['page']) ? '&page='.$_GET['page'] : '') . (isset($_GET['keyword']) ? '&keyword='.$_GET['keyword'] : ''),
        );

        if (!$item)
        {
            if (!empty($config['subitems']) && !$_modal)
            {
                foreach ($config['subitems'] as $key => $conf)
                {
                    if ($data)
                    {
                        $items = null;

                        if (isset($conf['data']))
                        {
                            if (is_callable($conf['data']))
                                $items = call_user_func($conf['data'], $data['id']);
                            else if (is_array($conf['data']))
                                $items = $conf['data'];
                        }

                        if (is_null($items))
                            $items = $db -> prepare("SELECT * FROM `{$conf['db']}` WHERE `pid`=:pid ORDER BY `id` ASC;") -> execute(array(':pid'=>$data['id']));

                        if (!empty($conf['callback']))
                            $items = call_user_func($conf['callback'], $items);

                        $assign[$key] = $conf['type'] == 'child' ? $items : $items[0];
                    }
                    else
                    {
                        $assign[$key] = $conf['type'] == 'child' ? array() : null;
                    }
                }
            }
        }
        else
        {
            $assign['pid'] = (int)$_REQUEST['id'];
        }

        self::display($assign, $item || $_modal ? 'modal' : 'form');

        if ($item || $_modal)
            exit(self::display());
    }




    // 模板显示
    public static function display($assign=null, $template=null)
    {
        static $_template = null;
        static $_assign = null;

        if ($assign && $template)
        {
            $_assign = $assign;
            $_template = $template;
        }
        else
        {
            extract($_assign, EXTR_OVERWRITE | EXTR_REFS );

            $path = PT_PATH . self::$tmpl_path . $_template . '.tpl.php';
            include($path);
        }
    }




    // 表单字段批处理
    public static function input($data, &$class, &$inner, $name, $config)
    {
        $val = !isset($data[$name]) || $data[$name] === null ? null : $data[$name];

        $attr = array("name=\"{$name}\"");

        $inp = '';

        switch ($config['type'])
        {

            // ========================= 文本类 =========================
            case 'text':
            case 'number':

                if (!empty($config['multiple']))
                {
                    $attr[0] = "name=\"{$name}[]\"";
                    if (is_bool($config['multiple']))
                        $vals = array_filter(explode(',', $val));
                    else if (is_callable($config['multiple']))
                        $vals = call_user_func($config['multiple'], $val);
                    else
                        $vals = explode((string)$config['multiple'], $val);

                    if (!$vals) $vals = array('');
                }
                else
                {
                    $vals = array($val);
                }

                if (!empty($config['placeholder']))
                    $attr[] = "placeholder=\"{$config['placeholder']}\"";

                if (!empty($config['length']))
                    $attr[] = "maxlength=\"{$config['length']}\"";

                if (!empty($config['plugin']))
                    call_user_func_array('\console\plugin::'.$config['plugin'], array(&$attr, &$class, &$inner, $config));

                $inp_class = implode(' ',  isset($attr['class']) ? array_unshift($attr['class'], 'form-control') : array('form-control'));

                foreach ($vals as $val)
                {
                    if (!empty($config['callback']))
                        $val = call_user_func_array($config['callback'], array($val, &$data, &$class, &$inner));

                    if (!empty($config['prefix']))
                    {
                        $inp .= '<div class="input-group"><span class="input-group-addon">'.(substr($config['prefix'], 0, 3) == 'ico' ? '<font class="'.substr($config['prefix'], 4).'"></font>' : $config['prefix']).'</span><input type="'.$config['type'].'" autocomplete="off" class="'.$inp_class.'" '.implode(' ', $attr).' value="'.$val.'" /></div>';
                    }
                    else if (!empty($config['suffix']))
                    {
                        $inp .= '<div class="input-group"><input type="'.$config['type'].'" autocomplete="off" class="'.$inp_class.'" '.implode(' ', $attr).' value="'.$val.'" /><span class="input-group-addon">'.(substr($config['suffix'], 0, 3) == 'ico' ? '<font class="'.substr($config['suffix'], 4).'"></font>' : $config['suffix']).'</span></div>';
                    }
                    else
                    {
                        $inp .= '<input type="'.$config['type'].'" autocomplete="off" class="'.$inp_class.'" '.implode(' ', $attr).' value="'.$val.'" />';
                    }
                }

                if (!empty($config['multiple']))
                {
                    if (!isset($inner['script_text']))
                        $inner['script_text'] = array();

                    $inner['script_text'][] = 'input_append_'.NOW;

                    $inp .= '<div id="input_append_'.NOW.'" data-max="'.$config['max'].'" class="empty">增加'.$config['name'].'</div>';
                }
                break;


            // ========================= 多行文本 =========================
            case 'textarea':
                if (!empty($config['placeholder']))
                    $attr[] = "placeholder=\"{$config['placeholder']}\"";

                if (!empty($config['length']))
                    $attr[] = "maxlength=\"{$config['length']}\"";

                if (!empty($config['rows']))
                    $attr[] = "rows=\"{$config['rows']}\"";

                $inp = '<textarea class="form-control" ' . implode(' ', $attr) . '>' . $val . '</textarea>';
                break;


            // ========================= 文本编辑器 =========================
            case 'editor':
                if (!isset($inner['script_editor']))
                    $inner['script_editor'] = array();

                $inner['script_editor'][] = array(
                    'id'        => 'editor_'.$name.'_'.NOW,
                    'mode'      => empty($config['mode']) ? 'h5' : $config['mode'],
                    'config'    => empty($config['config']) ? array() : $config['config'],
                );

                $inp = '<textarea id="editor_' . $name.'_'.NOW . '" class="form-control" ' . implode(' ', $attr) . '>' . $val . '</textarea>';
                break;


            // ========================= 单选/多选 =========================
            case 'select':
                if (!isset($inner['script_select']))
                    $inner['script_select'] = array();

                if (!empty($config['options']))
                    $options = $config['options'];

                $selected = array($val);

                if (!empty($config['multiple']))
                {
                    $attr[0] = "name=\"{$name}[]\"";
                    $attr[] = 'multiple size=1';
                    if (is_bool($config['multiple']))
                        $selected = array_filter(explode(',', $val));
                    else if (is_callable($config['multiple']))
                        $selected = call_user_func($config['multiple'], $val);
                    else
                        $selected = explode((string)$config['multiple'], $val);
                }

                if (!empty($config['callback']))
                {
                    $selected = array();
                    $options = call_user_func_array($config['callback'], array($val, &$data, &$class, &$inner));
                }

                $inp = '<select class="form-control ui-select" ' . implode(' ', $attr) . '><option value="">请选择..</option>';
                foreach ($options as $opt)
                    $inp .= '<option value="'.$opt['val'].'"'.(!empty($opt['selected']) || in_array($opt['val'], $selected) ? ' selected' : '').'>'.$opt['option'].'</option>';

                $inp .= '</select>'
                     .  (empty($config['placeholder']) ? '' : "<p class=\"help-block\">{$config['placeholder']}</p>");
                break;


            // ========================= Radio / Checkbox =========================
            case 'checkbox':
                $attr[0] = "name=\"{$name}[]\"";
                $attr[] = 'multiple size=1';
                if (is_bool($config['multiple']))
                    $checked = array_filter(explode(',', $val));
                else if (is_callable($config['multiple']))
                    $checked = call_user_func($config['multiple'], $val);
                else
                    $checked = explode((string)$config['multiple'], $val);

            case 'radio':

                if (!isset($checked))
                    $checked = array($val);

                if (!empty($config['options']))
                    $options = $config['options'];

                if (!empty($config['callback']))
                {
                    $checked = array();
                    $options = call_user_func_array($config['callback'], array($val, &$data, &$class, &$inner));
                }

                $inp = '<div class="btn-group btn-'.$config['type'].'" data-toggle="buttons">';
                foreach ($options as $opt)
                {
                    $_checked = !empty($opt['checked']) || in_array($opt['val'], $checked);
                    $inp .= '<label class="btn btn-default' . ($_checked ? ' active' : '') . '"><input type="'.$config['type'].'" ' . implode(' ', $attr) .' autocomplete="off" value="'.$opt['val'].'"'.($_checked ? ' checked' : '').' />'.$opt['option'].'</label>';
                }
                $inp .= '</div>'
                     .  (empty($config['placeholder']) ? '' : "<p class=\"help-block\">{$config['placeholder']}</p>");

                break;



            // ========================= 图片上传 =========================
            case 'image':

                $class .= "image-upload up_{$name} ";

                if (!isset($inner['styles']))
                    $inner['styles'] = array();

                if (!isset($inner['script_image']))
                    $inner['script_image'] = array();

                $multiple = isset($config['multiple']) && $config['multiple'];
                $val = array_filter(explode(',', $val));

                $tmpl = isset($config['action_tmpl']) ? $config['action_tmpl'] : '<label class="action"><a class="rm" href="javascript:;"><span class="glyphicon glyphicon-remove"></span></a></label>';

                $inner['script_image'][] = array(
                    'name'      => $name,
                    'multiple'  => var_export($multiple, true),
                    'tmpl'      => $tmpl
                );

                array_push($inner['styles'],
                    ".up_{$name} .image { width:{$config['size'][0]}px; height:{$config['size'][1]}px; }",
                    ".up_{$name} .image:before { padding-top:". round(($config['size'][1] - 70)/2) ."px; }"
                );

                if ($val)
                {
                    foreach ($val as $v)
                        $inp .= '<div class="image" style="background-image:url('.self::$upload_path.$v.'); background-size:cover;"><input type="hidden" name="'.$name.($multiple ? '[]' : '').'" value="'.$v.'" />'.$tmpl.'</div>';
                }

                $inp .= '<div id="' . $name . '" '.($val && !$multiple ? 'style="display:none" ' : '').'class="image image-empty" data-path="'.self::$upload_path.'">'.($val && !$multiple ? '' : '<input type="hidden" name="'.$name.($multiple ? '[]' : '').'" value="" />').'选择图片</div>'
                     .  (empty($config['placeholder']) ? '' : "<p class=\"help-block\">{$config['placeholder']}</p>");
                break;


            // ========================= 移除项目 =========================
            case 'remove':
                if (!$data) return false;

                $inp = '<div class="form-group"><button class="btn btn-warning btn-operate btn-remove" data-operate="edit" data-remove="'.$data['id'].'" data-url="'.$_SERVER['REQUEST_URI'].'">'.$config['button'].'</button></div>';
                break;


            // ========================= 完全自定义 =========================
            case 'custom':
                $inp = call_user_func_array($config['callback'], array($val, &$data, &$class, &$inner));
                break;


            case 'auto':
                return false;
        }

        return $inp;
    }



    // 按钮格式化
    static public function button($operate, $config, $data, $type='button', $suffix='', &$modal)
    {
        $_conf = $config;

        if ($config === true)
            $config = array();

        if (isset($config['callback']))
            $config = call_user_func($config['callback'], $data);

        // Init
        $class = array('btn');
        $class[] = !empty($config['class']) ? $config['class'] : 'btn-default';
        $attr = array();

        if (!empty($config['title']))
            $attr['title'] = "title=\"{$config['title']}\"";

        // URL
        $url = null;
        if (!empty($config['url']))
        {
            if (is_callable($config['url']))
                $url = call_user_func($config['url'], $data);
            else
                $url = str_replace(array('{id}', '{pid}'), array((!empty($data['id']) ? $data['id'] : ''), (!empty($data['parent']) ? $data['parent']['id'] : '')), $config['url']);
        }

        // 基本操作提前预置
        switch ($operate)
        {
            case 'create':
                $operate = 'edit';
                if (!$url)
                    $url = BASE_URL . '?module=' . MODULE . '&operate=edit&id=0';
                break;

            case 'edit':
                if (!$url)
                    $url = BASE_URL . '?module=' . MODULE . '&operate=edit&id=' . $data['id'];

                if (empty($config['name']))
                    $config['name'] = '编辑';

                if (empty($config['ico']))
                    $config['ico'] = 'glyphicon glyphicon-pencil';

                break;

            case 'deletes':
                $class[] = 'btn-removes';
                break;

            case 'delete':
                if (empty($config['name']))
                    $config['name'] = '移除';

                if (empty($config['ico']))
                    $config['ico'] = 'glyphicon glyphicon-trash';

                $class[] = 'btn-danger btn-remove btn-operate';
                $attr["data-operate"] = 'data-operate="edit"';
                $attr["data-remove"] = 'data-remove='.$data['id'];
                break;

            default:
                $class[] = 'btn-operate';
        }


        // Mode
        if (isset($config['mode']) && $config['mode'] == 'modal')
        {
            if ($type == 'button')
            {
                $attr['data-target'] = 'data-target="#modal-'.$operate.'"';
            }
            else
            {
                $attr['href'] = 'href="#modal-'.$operate.'"';
            }

            $attr['data-toggle'] = 'data-toggle="modal"';

            if ($url)
            {
                $attr['data-url'] = 'data-url="'.$url.'"';
            }

            $modal[$operate] = array('size'=>empty($config['size']) ? '' : $config['size']);
        }
        else if ($type != 'button')
        {
            if ($url)
            {
                $attr['data-pjax-container'] = 'data-pjax-container="#main"';
                $attr['href'] = 'href="'.$url.$suffix.'"';
            }
            else
            {
                $attr['href'] = 'href="javascript:;"';
            }
        }

        // Data
        if (!empty($config['data']))
        {
            foreach ($config['data'] as $k => $v)
            {
                $attr['data-'.$k] = "data-{$k}=\"{$v}\"";
            }
        }

        // Display
        if (!empty($config['ico']))
        {
            $content = "<span class=\"{$config['ico']} " . (!empty($config['name']) ? 'hidden-md' : '') . "\"></span>" . (!empty($config['name']) ? "<span class=\"hidden-xs hidden-sm\"> {$config['name']}</span>" : "");
        }
        else
        {
            $content = $config['name'];
        }

        if (!empty($config['class']))
            $class[] = $config['class'];

        $class = implode(' ', $class);
        $attr = implode(' ', $attr);

        switch ($type)
        {
            case 'li':
                $class = str_replace(array('btn ', 'btn-default', 'btn-primary', 'btn-success', 'btn-info', 'btn-warning', 'btn-danger'), array('', '', 'active', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger'), $class);

                return "<li><a class=\"{$class}\" {$attr}>{$content}</a></li>";

            case 'button':
            case 'a':
                return "<a class=\"{$class}\" {$attr}>{$content}</a>";

        }

    }




}

