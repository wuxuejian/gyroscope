<?php

declare(strict_types=1);
/**
 *  +----------------------------------------------------------------------
 *  | 陀螺匠 [ 赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2025 https://www.tuoluojiang.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed 陀螺匠并不是自由软件，未经许可不能去掉陀螺匠相关版权
 *  +----------------------------------------------------------------------
 *  | Author: 陀螺匠 Team <admin@tuoluojiang.com>
 *  +----------------------------------------------------------------------
 */

namespace App\Http\Controller\AdminApi;

use crmeb\traits\service\MenusRouteTrait;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('ent')]
class TestController extends AuthController
{
    use MenusRouteTrait;

    #[Get('route/{type}', '接口列表')]
    public function route($api = 'ent')
    {
        echo "<html lang=\"zh-CN\">
<head>
    <title>路由地址列表</title>
</head>
<link rel='stylesheet' type='text/css' href='https://www.layuicdn.com/layui/css/layui.css' />
<body>
<div style='margin: 20px'>
<fieldset class=\"layui-elem-field layui-field-title\" style=\"margin-top: 20px;\">
  <legend>路由地址列表</legend>
</fieldset>
<div class=\"layui-form\">
  <table class=\"layui-table\">
    <thead>
      <tr>
        <th>序号</th>
        <th>请求方式</th>
        <th>接口地址</th>
        <th>接口名称</th>
        <th>接口方法</th>
      </tr>
    </thead>
    <tbody>
  ";
        $routeList = $this->setOption('path', 'api/' . $api)->getRoutes(null);
        $count     = count($routeList);
        $i         = 0;
        echo "<tr>
<td>总条数：共{$count}个接口</td>
</tr>";
        foreach ($routeList as $route) {
            ++$i;
            echo "<tr>
<td>{$i}</td>
<td>{$route['method']}</td>
<td>{$route['uri']}</td>
<td>{$route['name']}</td>
<td>{$route['action']}</td>
</tr>";
        }
        echo '</table></div></div>';
    }
}
