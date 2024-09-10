<html lang="zh-CN">
<head>
    <title>测试</title>
</head>
<style>
    iframe {
        height: 500px !important;
    }
</style>
<body>
<h2>测试文档</h2>
<div class="custom-mount">

</div>

</body>
<script src="https://oa.lfmn.fun/web-office-sdk-v1.1.8.umd.js"></script>
<script type="text/javascript">
    var token = "{{$token}}";
    var demo = WebOfficeSDK.config({
        url: '{{$url}}',
        mount: document.querySelector('.custom-mount')
    });
    demo.setToken({token: token})
    demo.on('fileOpen', function (data) {
        console.log(data.success)
    })


</script>
</html>
