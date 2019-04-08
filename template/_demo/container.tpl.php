<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="theme-color" content="#222222">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title><?php echo config('web.title'); ?> Console &rsaquo; <?php echo $title; ?></title>

    <link href="<?php echo RESOURCES_URL; ?>css/bootstrap.min.css" rel="stylesheet" />
    <link href="<?php echo RESOURCES_URL; ?>css/console.css?v=<?php echo NOW; ?>" rel="stylesheet" />
    <link href="<?php echo RESOURCES_URL; ?>css/theme.css?v=<?php echo NOW; ?>" rel="stylesheet" />

    <!--[if lt IE 9]><script src="<?php echo RESOURCES_URL; ?>js/ie8-responsive-file-warning.js"></script><![endif]-->
    <script src="<?php echo RESOURCES_URL; ?>js/ie-emulation-modes-warning.js"></script>

    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="<?php echo RESOURCES_URL; ?>js/ie10-viewport-bug-workaround.js"></script>

    <script src="<?php echo RESOURCES_URL; ?>js/jquery.min.js"></script>
    <script src="<?php echo RESOURCES_URL; ?>js/jquery.pjax.js"></script>
    <script src="<?php echo RESOURCES_URL; ?>js/bootstrap.min.js"></script>
    <script src="<?php echo RESOURCES_URL; ?>js/jquery.highlightRegex.js"></script>
    <script src="<?php echo RESOURCES_URL; ?>js/console.js?v=<?php echo NOW; ?>"></script>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="<?php echo RESOURCES_URL; ?>js/html5shiv.min.js"></script>
    <script src="<?php echo RESOURCES_URL; ?>js/respond.min.js"></script>
    <link href="<?php echo RESOURCES_URL; ?>js/respond-proxy.html" id="respond-proxy" rel="respond-proxy" />
    <![endif]-->
</head>
<body>

    <!-- header -->
    <?php self::append('header'); ?>
    <!-- end header -->


    <div class="container-fluid">

        <!-- sidebar -->
        <?php self::append('sidebar'); ?>
        <!-- end sidebar -->

        <!-- main -->
        <div id="main" class="row">
            <?php self::wrap(); ?>
        </div>
        <!-- end main -->

    </div>

</body>
</html>
