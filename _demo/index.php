<?php
/**
 * Console
 *
 * @author Nolan
 * @category pt-console
 * @copyright nolanchou.com Copyright(c) 2018
 * @version $Id$
 */


// session start
define("SESSION_ON", true);

// define project's config
define("CONFIG", '/conf/web.conf.php');

// debug switch
define("DEBUG", true);

// include framework entrance file
include('../common.php');

// simplify use class
use pt\framework\debug\console as debug;
use pt\framework\template as template;
use pt\framework\template\pjax as pjax;
use pt\framework\db as db;
use pt\tool\page as page;

pjax::init();

include(COMMON_PATH.'web.func.php');
include(COMMON_PATH.'console.func.php');

// check permission
$module = empty($_GET['module']) ? 'overview' : $_GET['module'];
define('MODULE', $module);

$operate = empty($_GET['operate']) ? $module : $_GET['operate'];
define('OPERATE', $operate);

if (MODULE != 'login')
    rbac();

$module_file = dirname(__FILE__). '/module/'. MODULE. '.inc.php';
if (is_file($module_file))
{
    define('RESOURCES_URL', config('web.resources_url'));
    define('BASE_URL', '/webadm/');

    template::package('webadm');
    template::assign('nav', MODULE);

    $db = db::init();

    include $module_file;
    $template = MODULE;

    if (function_exists($operate))
    {
        template::assign('subnav', $operate);
        $template .= '/'.$operate;

        $call = call_user_func($operate);
        $template = $call ? $call : $template;
    }

    template::display($template);
}
else
{
    header('HTTP/1.1 404 Not Found');
    header("status: 404 Not Found");
}