<?php
/**
 * 文件管理
 +-----------------------------------------
 * @author nolan.chou
 * @category
 * @version $Id$
 */


use pt\framework\template as template;
use pt\tool\page as page;
use pt\tool\file as file;
use pt\tool\upload as upload;


if (!defined('MODULE')) exit;


function upload()
{
    if (empty($_REQUEST['type']))
        $_REQUEST['type'] = '';

    $path = $_path = '';

    switch ($_REQUEST['type'])
    {
        case 'banner':
            $path = 'upload/banner/';
            $_path = 'banner/';
            break;
        case 'product':
            $path = 'upload/product/';
            $_path = 'product/';
            break;
        // ... more
        default:
            $path = 'upload/temp/';
            $_path = 'temp/';
            break;
    }

    @file::mkdirs(PT_PATH.$path);

    $upload = new upload();
    $rs = $upload -> save(PT_PATH.$path, $_path);

    if ($upload -> file_upload_count)
    {
        json_return($rs['file']['savepath'].$rs['file']['savename']);
    }
    else
    {
        json_return('', ($rs['file']['error'] ? $rs['file']['error'] : $upload->error), $upload -> get_error($rs['file']['error']));
    }
}