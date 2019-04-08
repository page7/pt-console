<?php
/**
 * 不使用自动化 DEMO
 +-----------------------------------------
 * @author nolan.chou
 * @category
 * @version $Id$
 */


use pt\framework\template as template;


if (!defined('MODULE')) exit;

function banner()
{
    global $db;

    if ($_POST)
    {
        // Do sth.
        json_return(true);
    }

    $banners = $db -> prepare("SELECT * FROM `db_banner` WHERE 1=1 ORDER BY `id` ASC;") -> execute();
    template::assign('banners', $banners);

}
