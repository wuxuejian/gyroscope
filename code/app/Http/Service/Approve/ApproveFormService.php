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

namespace App\Http\Service\Approve;

use App\Constants\ApproveEnum;
use App\Constants\CacheEnum;
use App\Constants\CommonEnum;
use App\Http\Dao\Approve\ApproveFormDao;
use App\Http\Service\BaseService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 审核流程表单.
 */
class ApproveFormService extends BaseService
{
    public function __construct(ApproveFormDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * @param mixed $data
     * @param mixed $approve_id
     * @return bool
     * @throws BindingResolutionException
     */
    public function saveMore($data, $approve_id)
    {
        foreach ($data as $val) {
            $val['approve_id'] = $approve_id;
            if ($this->dao->exists(['approve_id' => $approve_id, 'uniqued' => $val['uniqued']])) {
                $this->dao->update(['approve_id' => $approve_id, 'uniqued' => $val['uniqued']], $val);
            } else {
                $this->dao->create($val);
            }
        }
        return true;
    }

    /**
     * 提交审批表单.
     * @param mixed $origin
     * @return array
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getApplyForm(int $id, int $uid, array $data = [], string $origin = CommonEnum::ORIGIN_WEB)
    {
        return Cache::tags([CacheEnum::TAG_APPROVE])->remember(
            md5('approve_config_' . $id . $uid . $origin . json_encode($data)),
            (int) sys_config('system_cache_ttl', 3600),
            function () use ($id, $uid, $data, $origin) {
                $forms = $this->dao->setDefaultSort(['sort' => 'asc'])->column(['approve_id' => $id], 'content');
                $info  = app()->get(ApproveService::class)->get($id, ['types', 'name', 'icon', 'color', 'info', 'examine'])?->toArray();
                // 处理系统相关审批
                if (in_array($info['types'], ApproveEnum::CUSTOMER_TYPES) || in_array($info['types'], [ApproveEnum::PERSONNEL_HOLIDAY, ApproveEnum::PERSONNEL_SIGN])) {
                    foreach ($forms as &$form) {
                        if (isset($form['children']) && $form['children']) {
                            foreach ($form['children'] as &$child) {
                                if (isset($child['symbol']) && method_exists(ApproveAssistService::class, $child['symbol'])) {
                                    $child = app()->get(ApproveAssistService::class)->{$child['symbol']}(uid: $uid, data: $data, child: $child, origin: $origin) ?: $child;
                                }
                            }
                        }
                    }
                }
                return compact('info', 'forms');
            }
        );
    }

    /**
     * 获取审批配置所有唯一值
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getUniques(int $approve_id): array
    {
        $uniques = [];
        $list    = $this->dao->select(['approve_id' => $approve_id], ['uniqued', 'content']);
        foreach ($list as $item) {
            if (isset($item->content['children'])) {
                $uniques = array_merge($uniques, array_column($item->content['children'], 'field'));
            } else {
                $uniques[] = $item->uniqued;
            }
        }
        return array_unique($uniques);
    }
}
