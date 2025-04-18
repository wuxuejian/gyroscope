<?php

declare(strict_types=1);
/**
 *  +----------------------------------------------------------------------
 *  | 陀螺匠 [ 赋能开发者，助力企业发展 ]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2024 https://www.tuoluojiang.com All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed 陀螺匠并不是自由软件，未经许可不能去掉陀螺匠相关版权
 *  +----------------------------------------------------------------------
 *  | Author: 陀螺匠 Team <admin@tuoluojiang.com>
 *  +----------------------------------------------------------------------
 */

namespace App\Http\Controller\AdminApi\Client;

use App\Http\Controller\AdminApi\AuthController;
use App\Http\Service\System\SystemGroupDataService;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * 客户相关业务设置
 * Class ClientConfigController.
 */
class ClientConfigController extends AuthController
{
    /**
     * 模块前缀
     * @var string
     */
    protected $prefix = 'client_';

    /**
     * @var string[]
     */
    protected $configTypes = [
        'way'     => '客户来源',
        'renew'   => '续费类型',
        'invoice' => '发票类型',
        'cate'    => '客户分类',
    ];

    /**
     * ClientConfigController constructor.
     */
    public function __construct(SystemGroupDataService $services)
    {
        parent::__construct();
        $this->service = $services;
    }

    /**
     * 客户配置列表组.
     * @return mixed
     * @throws BindingResolutionException
     */
    public function getConfigList()
    {
        [$keys, $page, $limit] = $this->request->postMore([
            ['keys', []],
            ['page', 0],
            ['limit', 0],
        ], true);
        if (! $keys) {
            return $this->fail(\__('common.empty.attrs'));
        }
        $data = [];
        foreach ($keys as $key) {
            if (! array_key_exists($key, $this->configTypes)) {
                return $this->fail(\__('common.empty.attrs'));
            }
            $data[$key] = \ent_data($this->entId, $this->prefix . $key, $limit, $page);
        }
        return $this->success($data);
    }

    /**
     * 客户配置列表.
     * @return mixed
     * @throws BindingResolutionException
     */
    public function getClientConfig()
    {
        [$key, $page, $limit] = $this->request->getMore([
            ['key', ''],
            ['page', 0],
            ['limit', 0],
        ], true);
        if (! array_key_exists($key, $this->configTypes)) {
            return $this->fail(\__('common.empty.attrs'));
        }
        return $this->success(\ent_data($this->entId, $this->prefix . $key, (int) $limit, (int) $page));
    }

    /**
     * 保存客户配置.
     * @return mixed
     * @throws BindingResolutionException
     */
    public function saveClientConfig()
    {
        [$data, $delete, $key] = $this->request->postMore([
            ['data', []],
            ['delete', []],
            ['key', ''],
        ], true);
        if (! array_key_exists($key, $this->configTypes)) {
            return $this->fail(\__('common.empty.attrs'));
        }
        $this->saveData($key, $data, $delete, $this->entId);
        return $this->success('common.operation.succ');
    }

    /**
     * 保存更新数据.
     * @return bool|mixed
     * @throws BindingResolutionException
     */
    public function saveData($key, $data, $delete, $entid)
    {
        $group = [
            'group_name' => $this->configTypes[$key],
            'group_info' => $this->configTypes[$key],
            'group_key'  => $this->prefix . $key,
            'entid'      => $entid,
            'fields'     => [
                [
                    'name'  => 'title',
                    'param' => '',
                    'title' => '名称',
                    'type'  => 'input',
                ], ],
        ];
        return $this->service->saveData($data, $group, $delete);
    }
}
