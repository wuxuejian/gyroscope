<!doctype html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>{{$Title}} - {{$Powered}}</title>
    <link rel="stylesheet" href="/install/css/install.css" />
    <link rel="stylesheet" href="/install/css/step5.css" />
    <script src="/install/js/jquery.js"></script>
    <?php
    $uri = $_SERVER['REQUEST_URI'];
    $root = substr($uri, 0,strpos($uri, "install"));
    $admin = $root."../index.php/admin/index/";
    $host = $_SERVER['HTTP_HOST'];
    ?>
</head>
<body>
<div class="wrap">
    <div class="title">
        安装完成
    </div>
    <section class="section">
        <div class="title">
            <img src="/install/images/success.png" alt="">
            <h1>安装成功</h1>
        </div>
        <div class="progress">
            <div class="trip p8">
                为了您站点的安全，安装完成后即可将网站根目录下的“install”文件夹下的所有文件删除，防止重复安装。
            </div>
        </div>
        <div class="bottom-btn">
{{--            <div class="pre btn">--}}
{{--                <a href="<?php echo 'http://'.$host;?>/work" class="btn mid">进入前台</a>--}}
{{--            </div>--}}
            <div class="admin btn">
                <a href="<?php echo 'http://'.$host;?>/admin" class="btn btn_submit J_install_btn mid">进入后台</a>
            </div>
        </div>
    </section>
</div>
@include('install/footer')
<script>
    $(function(){
        $.ajax({
            type: "POST",
            url: "http://shop.crmeb.net/index.php/admin/server.upgrade_api/updatewebinfo",
            header:{
                'Access-Control-Allow-Origin':'*',
                'Access-Control-Allow-Headers':'X-Requested-With',
                'Access-Control-Allow-Methods':'PUT,POST,GET,DELETE,OPTIONS'
            },
            data: {
                host:'<?php echo $host;?>',
                https:'<?php echo 'http://'.$host;?>',
                version:'<?php echo $version;?>',
                version_code:'<?php echo $platform;?>',
                ip:'<?php echo $ip;?>'
            },
            dataType: 'json',
            success: function(){}
        });
    });
</script>
</body>
</html>
