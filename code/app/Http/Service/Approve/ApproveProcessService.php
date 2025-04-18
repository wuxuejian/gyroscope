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

use App\Http\Dao\Approve\ApproveProcessDao;
use App\Http\Dao\Approve\ApproveUserDao;
use App\Http\Service\Admin\AdminService;
use App\Http\Service\BaseService;
use App\Http\Service\Frame\FrameService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 审核流程树.
 */
class ApproveProcessService extends BaseService
{
    protected $cacheTtl;

    public function __construct(ApproveProcessDao $dao, protected ApproveUserDao $userDao)
    {
        $this->dao      = $dao;
        $this->cacheTtl = (int) sys_config('system_cache_ttl', 3600);
    }

    /**
     * @param mixed $data
     * @param mixed $approve_id
     * @return bool
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function saveMore($data, $approve_id)
    {
        foreach ($data as $val) {
            if ($this->dao->exists(['approve_id' => $approve_id, 'uniqued' => $val['uniqued']])) {
                $this->dao->update(['approve_id' => $approve_id, 'uniqued' => $val['uniqued']], $val);
            } else {
                $val['approve_id'] = $approve_id;
                $this->dao->create($val);
            }
        }
        return true;
    }

    /**
     * 验证表单数据、获取处理人员列表.
     * @param mixed $data
     * @param mixed $approveId
     * @param mixed $uid
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function verifyForm($data, $approveId, $uid): array
    {
        if (! app()->get(ApproveService::class)->value($approveId, 'examine')) {
            return [];
        }
        if (! in_array($approveId, app()->get(ApproveService::class)->getUsableApprove(auth('admin')->id()))) {
            throw $this->exception('无效的申请');
        }
        $process = $this->dao->setDefaultSort(['groups' => 'asc'])->select(['approve_id' => $approveId])?->toArray() ?: [];
        $rules   = app()->get(ApproveRuleService::class)->get(['approve_id' => $approveId], ['*'], ['card'])?->toArray();
        $data    = $this->getNodeList((int) $approveId, $process, $data, $uid);
        if ($data) {
            foreach ($data as $key => $item) {
                if ($item['types'] == 3) {
                    unset($data[$key]);
                }
            }
            $list = array_values($data);
        } else {
            $list = [];
        }
        return compact('list', 'rules');
    }

    /**
     * 获取流程节点数据(新).
     * @param mixed $approveId
     * @param mixed $unique
     * @param mixed $data
     * @param mixed $userNodes
     * @param mixed $uniques
     * @param mixed $userId
     * @return array|mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getNodeList(int $approveId, array $process, array $data, int $userId, string $unique = '', array $routes = [], array $userNodes = [], array $uniques = [], bool $end = false)
    {
        if (! $unique) {
            $children = array_filter($process, function ($item) {
                return $item['types'] == 0;
            });
            $uniques = array_column($children, 'uniqued');
            return $children ? $this->getNodeList($approveId, $process, $data, $userId, $uniques[0], $routes, $userNodes, $uniques) : $userNodes;
        }
        $children = array_filter($process, function ($item) use ($unique, $uniques) {
            return $item['parent'] == $unique && ! in_array($item['uniqued'], $uniques);
        });
        if (! $children) {
            if (! $routes) {
                return $userNodes;
            }
            $key = array_search($unique, $routes);
            if ($end && $unique == $routes[0]) {
                return $userNodes;
            }
            if ($key) {
                return $this->getNodeList($approveId, $process, $data, $userId, $routes[$key - 1], $routes, $userNodes, $uniques, ! ($key - 1));
            }
            return $this->getNodeList($approveId, $process, $data, $userId, end($routes), $routes, $userNodes, $uniques, true);
        }
        if ($condition = array_filter($children, function ($item) {
            return $item['types'] == 3;
        })) {
            $children             = $condition;
            $children && $uniques = array_merge($uniques, array_column($children, 'uniqued'));
        } elseif ($haveRoute = array_filter($children, function ($item) {
            return $item['types'] == 4;
        })) {
            $children = $haveRoute;
        }

        foreach ($children as $child) {
            ! in_array($child['uniqued'], $uniques) && $uniques[] = $child['uniqued'];
            switch ($child['types']) {
                case 4:
                    if (in_array($child['uniqued'], $routes)) {
                        if (($key = array_search($unique, $routes)) !== false) {
                            return $this->getNodeList($approveId, $process, $data, $userId, $routes[$key - 1], $routes, $userNodes, $uniques);
                        }
                        return $userNodes;
                    }
                    $routes[] = $child['uniqued'];
                    return $this->getNodeList($approveId, $process, $data, $userId, $child['uniqued'], $routes, $userNodes, $uniques);
                case 3:
                    $nextUnique = $this->verifyCondition($child, $data, $approveId, $userId);
                    if (is_bool($nextUnique)) {
                        break;
                    }
                    if ($nextUnique) {
                        if (in_array($nextUnique['types'], [3, 4])) {
                            $routes[] = $nextUnique['uniqued'];
                            return $this->getNodeList($approveId, $process, $data, $userId, $nextUnique['uniqued'], $routes, $userNodes, $uniques);
                        }
                        if (! in_array($nextUnique['uniqued'], array_column($userNodes, 'uniqued'))) {
                            $userNodes[] = $this->formatUserData($nextUnique, $userId);
                            return $this->getNodeList($approveId, $process, $data, $userId, $nextUnique['uniqued'], $routes, $userNodes, $uniques);
                        }
                    } else {
                        return $this->getNodeList($approveId, $process, $data, $userId, end($routes), $routes, $userNodes, $uniques);
                    }
                    break;
                default:
                    if (! in_array($child['uniqued'], array_column($userNodes, 'uniqued'))) {
                        $userNodes[] = $this->formatUserData($child, $userId);
                    }
                    return $this->getNodeList($approveId, $process, $data, $userId, $child['uniqued'], $routes, $userNodes, $uniques);
            }
        }
        return $userNodes;
    }

    /**
     * 通过条件验证获取下一步节点.
     * @param mixed $condition
     * @param mixed $data
     * @param mixed $approveId
     * @param mixed $userId
     * @return array|false
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function verifyCondition($condition, $data, $approveId, $userId = 0)
    {
        foreach ($condition['condition_list'] as $value) {
            if ($value['field']) {
                $val = isset($data[$value['field']]['holiday_id']) ? $data[$value['field']]['holiday_id']['value'] : $data[$value['field']];
            } else {
                $val = '';
            }
            switch ($value['type']) {
                case 'inputNumber':
                case 'moneyFrom':
                    $res = match ($value['value']) {
                        '3'     => $val >= $value['option'],
                        '2'     => $val <= $value['option'],
                        '1'     => $val == $value['option'],
                        default => $val < $value['option']
                    };
                    if (! $res) {
                        return false;
                    }
                    break;
                case 'select':
                    if ($value['option']) {
                        if (! in_array($val, $value['option'])) {
                            return false;
                        }
                    } else {
                        if ($value['option'] != [$val]) {
                            return false;
                        }
                    }
                    break;
                case 'radio':
                    if ($value['option'] != $val) {
                        return false;
                    }
                    break;
                case 'checkbox':
                    if ($value['value']) {
                        if (is_array($val)) {
                            if (! array_intersect($val, $value['option'])) {
                                return false;
                            }
                        } else {
                            return false;
                        }
                    } else {
                        if (is_array($val)) {
                            if (array_diff($val, $value['option'])) {
                                return false;
                            }
                        } else {
                            return false;
                        }
                    }
                    break;
                case 'departmentTree':
                    if (! $val && $value['category'] > 1) {
                        return false;
                    }
                    switch ($value['category']) {
                        case 3:
                            $cardIds = array_column($value['options']['depList'], 'id');
                            $val     = is_array($val) ? array_column($val, 'id') : [];
                            if (isset($value['options']['value']) && $value['options']['value']) {
                                if (! array_diff($val, $cardIds)) {
                                    return false;
                                }
                            } else {
                                if (array_diff($val, $cardIds)) {
                                    return false;
                                }
                            }
                            break;
                        case 2:
                            $cardIds = array_column($value['options']['userList'], 'card_id');
                            $val     = is_array($val) ? array_column($val, 'id') : [];
                            if (isset($value['options']['value']) && $value['options']['value']) {
                                if (! array_diff($val, $cardIds)) {
                                    return false;
                                }
                            } else {
                                if (array_diff($val, $cardIds)) {
                                    return false;
                                }
                            }
                            break;
                        default:// 申请人
                            $cardIds = array_column($value['options']['userList'], 'id');
                            if (! in_array($userId, $cardIds)) {
                                return false;
                            }
                    }
                    break;
                case 'timeFrom':
                    $val = $val['duration'] ?? 0;
                    switch ($value['value']) {
                        case 4:
                            if ($val < $value['min'] || $val > $value['max']) {
                                return false;
                            }
                            break;
                        case 3:
                            if ($val < $value['option']) {
                                return false;
                            }
                            break;
                        case 2:
                            if ($val > $value['option']) {
                                return false;
                            }
                            break;
                        case 1:
                            if ($val != $value['option']) {
                                return false;
                            }
                            break;
                        default:
                            if ($val >= $value['option']) {
                                return false;
                            }
                    }
                    break;
            }
        }
        return $this->dao->get(['approve_id' => $approveId, 'parent' => $condition['uniqued'], 'groups' => $condition['groups']])?->toArray() ?: [];
    }

    /**
     * 格式化审批节点配置.
     * @param mixed $data
     * @param mixed $userId
     * @return array
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function formatUserData($data, $userId)
    {
        $save                = $users = [];
        $save['types']       = $data['types'];
        $save['title']       = $data['name'];
        $save['uniqued']     = $data['uniqued'];
        $save['level']       = $data['level'];
        $save['select_mode'] = $data['select_mode'];
        if ($data['types'] == 1) {
            $save['examine_mode'] = $data['examine_mode'];
            $save['settype']      = $data['settype'];
            $save['no_hander']    = $data['no_hander'];
            switch ($data['settype']) {
                case 1:// 指定成员
                    if ($data['user_list'] && $card = array_column($data['user_list'], 'card')) {
                        $users = app()->get(AdminService::class)->select(['uid' => array_column($card, 'uid'), 'status' => 1], ['id', 'uid', 'name', 'avatar'])?->toArray();
                    } else {
                        $users = app()->get(AdminService::class)->select(['uid' => array_column($data['user_list'], 'uid'), 'status' => 1], ['id', 'uid', 'name', 'avatar'])?->toArray();
                    }
                    break;
                case 2:// 指定部门主管
                    $ups = app()->get(FrameService::class)->getLevelSuperUser($userId, 0, true);
                    if (! $data['director_order']) {
                        $ups = array_reverse($ups);
                    }
                    foreach ($ups as $k => $v) {
                        if ($data['director_level'] == $k + 1) {
                            if ($data['no_hander'] == 1 && ! $v && isset($ups[$k + 2])) {
                                $users[] = $ups[$k + 2];
                            } else {
                                $users[] = $v;
                            }
                        }
                    }
                    break;
                case 7:// 连续多级审批
                    if ($data['director_order']) {
                        $users = app()->get(FrameService::class)->getLevelSuperUser($userId, $data['director_level'], true);
                    } else {
                        $ups           = app()->get(FrameService::class)->getLevelSuperUser($userId, 0, true);
                        $ups           = array_reverse($ups, false);
                        $ups && $users = array_slice($ups, 0, $data['director_level']);
                    }
                    break;
                case 5:// 申请人自己
                    $arr   = app()->get(AdminService::class)->get($userId, ['id', 'uid', 'name', 'avatar'])?->toArray();
                    $users = [
                        [
                            'id'      => $arr['id'],
                            'card_id' => $arr['id'],
                            'card'    => $arr,
                        ],
                    ];
                    break;
                case 4:// 申请人自选
                    $save['uniqued'] = $data['uniqued'];
                    $users           = [];
                    $save['options'] = $data['select_range'] > 1 ? $data['user_list'] : [];
                    break;
            }
        } else {
            $save['self_select'] = $data['self_select'];
            if ($data['dep_head']) {
                $ups = app()->get(FrameService::class)->getLevelSuperUser($userId, 0, true);
                if ($ups) {
                    foreach ($ups as $k => $v) {
                        if (in_array($k + 1, $data['dep_head'])) {
                            $users[] = $v;
                        }
                    }
                }
            }
            if ($data['user_list']) {
                if ($card = array_column($data['user_list'], 'card')) {
                    $user_list = app()->get(AdminService::class)->select(['uid' => array_column($card, 'uid'), 'status' => 1], ['id', 'uid', 'name', 'avatar'])?->toArray();
                } else {
                    $user_list = app()->get(AdminService::class)->select(['uid' => array_column($data['user_list'], 'uid'), 'status' => 1], ['id', 'uid', 'name', 'avatar'])?->toArray();
                }
                $users = array_merge($users, $user_list);
            }
            $users = assoc_unique($users, 'id');
        }
        $save['users'] = $users;
        return $save;
    }
}
