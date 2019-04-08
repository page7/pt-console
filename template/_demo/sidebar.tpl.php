
<div id="sidebar">
    <div class="row">

        <ul class="nav nav-sidebar">
            <li class="overview<?php if($nav == 'overview') echo ' active'; ?>">
                <a href="<?php echo BASE_URL; ?>?module=overview" data-pjax-container="#main"><span class="glyphicon glyphicon-dashboard"></span>概况</a>
            </li>
            <li class="nouse<?php if($nav == 'nouse') echo ' active'; ?>">
                <a href="<?php echo BASE_URL; ?>?module=nouse" data-pjax-container="#main"><span class="glyphicon glyphicon-picture"></span>轮播</a>
            </li>
        </ul>

        <ul class="nav nav-sidebar">
            <li class="simple<?php if($nav == 'simple') echo ' active'; ?>">
                <a href="<?php echo BASE_URL; ?>?module=simple" data-pjax-container="#main"><span class="glyphicon glyphicon-map-marker"></span>地区</a>
            </li>
            <li class="normal<?php if($nav == 'normal') echo ' active'; ?>">
                <a href="<?php echo BASE_URL; ?>?module=normal" data-pjax-container="#main"><span class="glyphicon glyphicon-bed"></span>产品</a>
            </li>
        </ul>


        <ul class="nav nav-sidebar">
            <li class="order<?php if($nav == 'order') echo ' active'; ?>">
                <a href="javascript:;" data-pjax-container="#main"><span class="glyphicon glyphicon-list"></span>订单</a>
            </li>
        </ul>

        <ul class="nav nav-sidebar">
            <li class="user<?php if($nav == 'user') echo ' active'; ?>">
                <a href="javascript:;" data-pjax-container="#main"><span class="glyphicon glyphicon-user"></span>用户</a>
            </li>
        </ul>

        <ul class="nav nav-sidebar">
            <li class="admin<?php if($nav == 'admin') echo ' active'; ?>">
                <a href="javascript:;" data-pjax-container="#main"><span class="glyphicon glyphicon-asterisk"></span>管理员</a>
            </li>
        </ul>

    </div>
</div>
