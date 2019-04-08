<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="theme-color" content="#222222">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title><?php echo config('web.title'); ?> &rsaquo; Console Login</title>

    <!--link rel="shortcut icon" href="/favicon.ico" /-->

    <link href="<?php echo RESOURCES_URL; ?>css/bootstrap.min.css" rel="stylesheet" />
    <link href="<?php echo RESOURCES_URL; ?>css/font-awesome.min.css" rel="stylesheet" />
    <link href="<?php echo RESOURCES_URL; ?>css/console.css" rel="stylesheet" />
    <link href="<?php echo RESOURCES_URL; ?>css/console-login.css" rel="stylesheet" />
    <link href="<?php echo RESOURCES_URL; ?>css/theme.css" rel="stylesheet" />

    <!--[if lt IE 9]><script src="<?php echo RESOURCES_URL; ?>js/ie8-responsive-file-warning.js"></script><![endif]-->
    <script src="<?php echo RESOURCES_URL; ?>js/ie-emulation-modes-warning.js"></script>

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="<?php echo RESOURCES_URL; ?>js/ie10-viewport-bug-workaround.js"></script>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="<?php echo RESOURCES_URL; ?>js/html5shiv.min.js"></script>
    <script src="<?php echo RESOURCES_URL; ?>js/respond.min.js"></script>
    <link href="<?php echo RESOURCES_URL; ?>js/respond-proxy.html" id="respond-proxy" rel="respond-proxy" />
    <![endif]-->

<style>

</style>
</head>
<body>

    <div class="container">

        <form class="form-signin" role="form" method="POST">
            <h2 class="form-signin-heading">Please sign in</h2>
            <div class="alert alert-danger" role="alert" style="display:none;"></div>
            <input type="text" name="username" class="form-control" placeholder="Username" autocomplete="off" required autofocus>
            <input type="password" name="password" class="form-control" placeholder="Password" required data-enter="login()">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="remember" value="1"> Remember me
                </label>
            </div>
            <button class="btn btn-lg btn-primary btn-block" type="button" onclick="login()">Sign in</button>
        </form>

    </div>


    <script src="<?php echo RESOURCES_URL; ?>js/jquery.min.js"></script>
    <script src="<?php echo RESOURCES_URL; ?>js/bootstrap.min.js"></script>
    <script src="<?php echo RESOURCES_URL; ?>js/console.js"></script>
    <script>
    function login(){
        var postdata = $(".form-signin").serialize();
        $.post("?module=login", postdata, function(data){
            if(data.s == 0){
                location.href = "<?php echo $redirect; ?>";
            }else{
                $(".alert").text(data.err).slideDown(200);
                $(".form-signin .form-control").addClass("refuse");
                setTimeout(function(){ $(".form-signin .form-control").removeClass("refuse"); }, 1000);
            }
        }, "json");
    }
    </script>

</body>
</html>
