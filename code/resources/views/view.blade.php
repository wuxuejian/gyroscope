<html lang="zh-CN">
<head>
    <title>文档列表</title>
</head>
<style>
    iframe {
        height: 500px;
    }
</style>
<link rel='stylesheet' type='text/css' href='https://www.layuicdn.com/layui/css/layui.css'/>
<body>
<h2>文档列表</h2>
<div style='margin: 20px'>
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>路由地址列表</legend>
    </fieldset>
    <button type="button" onclick="news()" class="layui-btn layui-btn-sm">新建</button>
    <div class="layui-form">
        <table class="layui-table">
            <thead>
            <tr>
                <th>文件名称</th>
                <th>文件ID</th>
                <th>创建人</th>
                <th>操作</th>
            </tr>
            @foreach($data['list'] as $item)
                <tr>
                    <td>{{$item['name']}}</td>
                    <td>{{$item['file_id']}}</td>
                    <td>{{$item['uid']}}</td>
                    <td>
                        <button type="button" class="layui-btn layui-btn-sm" onclick="view('{{$item['file_id']}}')">浏览</button>
                        <button type="button" class="layui-btn layui-btn-sm" onclick="edit('{{$item['file_id']}}')">编辑</button>
                    </td>
                </tr>
            @endforeach
            </thead>
        </table>
    </div>
</div>
</body>

<script type="text/javascript">
    function edit(file_id) {
        location.href = '/edit/'+file_id;
    }

    function view(file_id) {
        location.href = '/view/'+file_id;
    }

    function news() {
        location.href = '/new/';
    }

</script>
</html>
