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

namespace App\Http\Service\System;

use App\Http\Contract\System\LogInterface;
use App\Http\Dao\System\LogDao;
use App\Http\Service\BaseService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/**
 * 企业日志
 * Class LogService.
 */
class LogService extends BaseService implements LogInterface
{
    protected array $filter = [
        'api/ent/enterprise/log',
    ];

    public function __construct(LogDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 日志查询.
     * @param null|mixed $sort
     * @throws BindingResolutionException
     */
    public function getLogPageList(array $where, int $page = 1, int $limit = 20, array $field = ['*'], $sort = null, array $with = []): array
    {
        if ($page === 1 && $limit === 20) {
            [$page, $limit] = $this->getPageValue();
        }
        return $this->dao->getLogList($where, $page, $limit);
    }

    /**
     * 保存日志.
     * @throws BindingResolutionException
     */
    public function createLog(string $userId, int $entId, string $userName, string $type): bool
    {
        /** @var Request $request */
        $request = app()->request;
        $rule    = $request->route()->uri();
        if (in_array($rule, $this->filter)) {
            return true;
        }
        $routes = collect(Route::getRoutes()->getRoutes())->map(function ($route) {
            return [
                'method' => $route->methods()[0] ?? 'GET',
                'uri'    => $route->uri(),
                'name'   => $route->getName(),
            ];
        })->filter(function ($route) use ($rule, $request) {
            return $route['uri'] == $rule && $request->method() == $route['method'] && str_starts_with($route['uri'], 'api/');
        })->all();
        $data = [
            'method'     => $request->method(),
            'uid'        => $userId,
            'entid'      => $entId,
            'user_name'  => $userName,
            'path'       => $rule,
            'event_name' => end($routes) ? end($routes)['name'] : '未知',
            'last_ip'    => $request->server('HTTP_X_REAL_IP') ?: $request->ip(),
            'type'       => $type,
            'terminal'   => get_os(),
        ];
        if ($this->dao->getModel(false)->create($data)) {
            DB::table('sub_table')->where('table_name', $this->dao->table)->increment('count');
            return true;
        }
        return false;
    }
}
