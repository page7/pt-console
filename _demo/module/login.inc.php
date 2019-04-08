<?php
/**
 * Login / Logout
 +-----------------------------------------
 * @author nolan.chou
 * @category
 * @version $Id$
 */

use pt\framework\template as template;
use pt\tool\string as str;


if (!defined('MODULE')) exit;

function login()
{
    global $db;

    if ($_POST)
    {
        if (empty($_POST['username']) || empty($_POST['password']))
            json_return(null, 1, '用户名或密码不能为空');

        if (str::check($_POST['username'], 'email')) $key = 'email';
        else if (str::check($_POST['username'], '/^1[0-9]{10}$/')) $key = 'tel';
        else $key = 'username';

        $user = $db -> prepare("SELECT `id`,`name`,`password`,`md`,`role` FROM `rbac_user` WHERE BINARY `{$key}`=:user") -> execute(array(':user'=>$_POST['username']));

        if (!$user || $user[0]['password'] != md5(md5($_POST['password']).$user[0]['md']))
        {
            json_return(null, 1, '帐号或用户名不正确');
        }
        else
        {
            $_SESSION['adid'] = $user[0]['id'];
            $_SESSION['name'] = $user[0]['name'];

            // remember user to login in one week
            if (!empty($_POST['remember']))
            {
                $ck = $user[0]['id'] % 4;
                $md = cipher($user[0]['id'].'|'.$user[0]['password'], $user[0]['md']) . $user[0]['md'];
                setcookie('sess', $md, NOW + 86400 * 7);
            }

            json_return(1);
        }
    }

    $referer = '?';
    if (!empty($_GET['referer']) || !empty($_SERVER['HTTP_REFERER']))
    {
        $_referer = !empty($_GET['referer']) ? urldecode($_GET['referer']) : $_SERVER['HTTP_REFERER'];
        $_url = parse_url($_referer);
        $_args = !empty($_url['query']) ? parse_str($_url['query']) : array();
        if ((empty($_url['host']) || $_url['host'] == $_SERVER['HTTP_HOST']) && isset($_args['module']) && $_args['module'] != 'login')
            $referer = $_referer;
    }

    if (!empty($_COOKIE['sess'])) redirect($referer);

    template::assign('redirect', $referer);
}





function logout()
{
    // destory session
    session_unset();
    session_destroy();

    setcookie('sess', '', NOW - 86400);

    // redirect login page
    redirect('?module=login');
}

