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

namespace App\Http\Service\Approve;

use App\Constants\ApproveEnum;
use App\Constants\CacheEnum;
use App\Http\Dao\Approve\ApproveApplyDao;
use App\Http\Service\Admin\AdminService;
use App\Http\Service\BaseService;
use App\Http\Service\Crud\SystemCrudApproveProcessService;
use App\Http\Service\Crud\SystemCrudApproveRuleService;
use App\Http\Service\Crud\SystemCrudApproveService;
use App\Http\Service\Frame\FrameAssistService;
use App\Http\Service\Frame\FrameService;
use App\Task\approve\ApplySavedTask;
use App\Task\approve\ApprovedTask;
use App\Task\approve\ApproveRevokeTask;
use App\Task\message\BusinessAdoptApplyRemind;
use App\Task\message\BusinessAdoptCcRemind;
use App\Task\message\BusinessApprovalRemind;
use App\Task\message\BusinessFailRemind;
use App\Task\message\BusinessRecallRemind;
use crmeb\traits\service\ResourceServiceTrait;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * 申请记录表.
 */
class ApproveApplyService extends BaseService
{
    use ResourceServiceTrait;

    protected $userId;

    protected $page = 1;

    protected $limit = 0;

    public function __construct(ApproveApplyDao $dao)
    {
        $this->dao = $dao;
    }

    public function setLimit($page, $limit)
    {
        $this->page  = $page;
        $this->limit = $limit;
        return $this;
    }

    /**
     * 列表.
     * @param array|string[] $field
     * @param string $sort
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getList(array $where, array $field = ['*'], $sort = 'id', array $with = []): array
    {
        $this->getCardId();
        $where = $this->getWhere($where);
        if ($this->page && $this->limit) {
            [$page, $limit] = [$this->page, $this->limit];
        } else {
            [$page, $limit] = $this->getPageValue();
        }
        $approveUser        = app()->get(ApproveUserService::class);
        $frameServices      = app()->get(FrameService::class);
        $contentServices    = app()->get(ApproveContentService::class);
        $approveService     = app()->get(ApproveService::class);
        $crudApproveService = app()->get(SystemCrudApproveService::class);
        $list               = $this->dao->getApplyList($where, $field, $page, $limit, $sort, [
            'card', 'rules',
        ])->each(function (&$item) use ($approveUser, $frameServices, $contentServices, $approveService, $crudApproveService) {
            if ($item['crud_id']) {
                $item['approve'] = $crudApproveService->setTrashed()->get($item['approve_id'], ['id', 'name', 'icon', 'color', 'info', 'types']);
                $item['content'] = $crudApproveService->getContent($item['crud_id'], $item['link_id']);
            } else {
                $item['approve'] = $approveService->setTrashed()->get($item['approve_id'], ['id', 'name', 'icon', 'color', 'info', 'types']);
                $item['content'] = $contentServices->getContent($item['id'], ['uploadFrom']);
            }
            if ($item['status'] == -1) {
                $item['verify_status'] = -1;
            } else {
                $item['verify_status'] = $approveUser->getVerifyStatus($this->userId, $item['node_id'], $item['id']);
            }
            $item['frame'] = $frameServices->getMasterFrame($item['user_id'], ['id', 'name']);
        })?->toArray();
        $count = $this->dao->count($where);
        return $this->listData($list, $count);
    }

    /**
     * 获取绩效导出列表.
     * @param mixed $where
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getListForExport($where): array
    {
        $this->getCardId();
        $where          = $this->getWhere($where);
        $contentService = app()->get(ApproveContentService::class);
        $list           = $this->dao->setDefaultSort('created_at')->select($where, with: [
            'approve' => fn ($q) => $q->select(['id', 'name']),
            'card'    => fn ($q) => $q->with(['frame']),
        ])->each(function ($item) use ($contentService) {
            $item['content'] = $contentService->getContent($item['id']);
        })?->toArray();
        $exports = $title = [];
        if (! empty($list)) {
            $title = ['审批编号', '审批类型', '申请人', '部门'];
            if (end($list)['content']) {
                $title = array_merge($title, array_column(end($list)['content'], 'label'));
            }
            foreach ($list as $key => $item) {
                $export = [
                    $item['number'],
                    ! empty($item['approve']) ? $item['approve']['name'] : '未知',
                    ! empty($item['card']) ? $item['card']['name'] : '未知',
                    ! empty($item['card']['frame']) ? $item['card']['frame']['name'] : '未知',
                ];
                if ($item['content']) {
                    foreach ($item['content'] as $value) {
                        $export[] = is_array($value['value']) ? ($value['value'][0]['name'] ?? '--') : $value['value'];
                    }
                }
                if ($key == 0) {
                    $title[] = '审批状态';
                }
                $export[] = match ($item['status']) {
                    -1      => '撤回',
                    1       => '已通过',
                    2       => '已拒绝',
                    default => '待审批',
                };
                if ($key == 0) {
                    $title[] = '申请时间';
                }
                $export[]  = $item['created_at'];
                $exports[] = $export;
            }
        }
        return compact('title', 'exports');
    }

    /**
     * 获取详情.
     * @return array|mixed
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function resourceEdit(int $id, array $other = [])
    {
        $this->getCardId();
        Cache::tags([CacheEnum::TAG_APPROVE])->flush();
        return Cache::tags([CacheEnum::TAG_APPROVE])->remember(md5(json_encode($other) . $this->userId . $id), (int) sys_config('system_cache_ttl', 3600), function () use ($id, $other) {
            if (! $this->dao->exists(['id' => $id])) {
                return ['users' => [], 'reply' => []];
            }
            $userService = app()->get(ApproveUserService::class);
            if ($other['types']) {
                $info = toArray($this->dao->get(['id' => $id], ['*'], [
                    'card'    => fn ($query) => $query->select(['id', 'name', 'avatar', 'uid']),
                    'approve' => fn ($query) => $query->select(['id', 'name', 'icon', 'color', 'info', 'types']),
                ]));
                if (! $info) {
                    return [];
                }
                if ($info['crud_id']) {
                    $crudApproveService = app()->get(SystemCrudApproveService::class);
                    $info['approve']    = $crudApproveService->setTrashed()->get($info['approve_id'], ['id', 'name', 'icon', 'color', 'info'])?->toArray();
                    $info['content']    = $crudApproveService->getContent($info['crud_id'], $info['link_id']);
                } else {
                    $info['content'] = app()->get(ApproveContentService::class)->getContent($id);
                }
                $uniqueds     = $userService->getUniqueds($id);
                $replyService = app()->get(ApproveReplyService::class);
                foreach ($uniqueds as $v) {
                    $process = $userService->value(['node_id' => $v], 'process_info');
                    $title   = $process['name'];
                    $users[] = [
                        'uniqued'      => $v,
                        'apply_id'     => $id,
                        'types'        => $process['types'],
                        'title'        => $title ?? '',
                        'examine_mode' => $process['examine_mode'],
                        'updated_at'   => $userService->getValue(['node_id' => $v, 'apply_id' => $id, 'status' => 1], 'updated_at', ['updated_at' => 'desc']) ?: '',
                        'users'        => toArray($userService->getUserList(['node_id' => $v, 'apply_id' => $id], ['status', 'updated_at', 'user_id'], ['sort' => 'asc', 'id' => 'desc'], [
                            'card' => fn ($query) => $query->select(['id', 'name', 'avatar', 'uid']),
                        ])),
                    ];
                    $reply = $replyService->dao->setDefaultSort('id')->select(['apply_id' => $id], ['id', 'user_id', 'apply_id', 'content', 'created_at'], [
                        'card' => function ($query) {
                            $query->select(['id', 'name', 'avatar', 'uid']);
                        },
                    ])?->toArray();
                    $info['users'] = $users;
                    $info['reply'] = $reply;
                }
                if ($info['status'] == -1) {
                    $info['verify_status'] = -1;
                } else {
                    $info['verify_status'] = $userService->getVerifyStatus($this->userId, $info['node_id'], $id);
                }
                return $info;
            }
            $info = $this->dao->get(['id' => $id], ['*'], [
                'content' => fn ($query) => $query->select(['apply_id', 'uniqued', 'value', 'content', 'options', 'symbol']),
                'card'    => fn ($query) => $query->select(['id', 'name', 'avatar', 'uid']),
                'approve' => fn ($query) => $query->select(['id', 'name', 'icon', 'color', 'info', 'types']),
            ])?->toArray();
            if ($info['crud_id']) {
                $info['content'] = app()->get(SystemCrudApproveService::class)->getContent($info['crud_id'], $info['link_id']);
            }
            if (! $info['content']) {
                return [];
            }
            $content = [];
            foreach ($info['content'] as $v) {
                $content[$v['uniqued']] = is_numeric($v['value']) ? (float) $v['value'] : $v['value'];
            }
            return $content;
        });
    }

    /**
     * 保存审批申请.
     * @param int $applyId
     * @param mixed $form
     * @param mixed $process
     * @param mixed $approveId
     * @return mixed
     * @throws BindingResolutionException
     */
    public function saveForm($form, $process, $approveId, $applyId = 0)
    {
        $entId    = 1;
        $forms    = toArray(app()->get(ApproveFormService::class)->select(['approve_id' => $approveId]));
        $saveForm = [];
        foreach ($forms as $v) {
            if (isset($v['content']['children'])) {
                foreach ($v['content']['children'] as $value) {
                    if (is_string($value)) {
                        $v['value']  = $form[$v['uniqued']];
                        $v['symbol'] = $v['content']['symbol'] ?? '';
                    } else {
                        $v['value']   = $form[$value['field']];
                        $v['types']   = $value['type'];
                        $v['uniqued'] = $value['field'];
                        $v['symbol']  = $value['symbol'] ?? '';
                        $v['content'] = $value;
                    }
                    $saveForm[] = $v;
                }
            } else {
                $v['value']  = $form[$v['uniqued']];
                $v['symbol'] = $v['content']['symbol'] ?? '';
                $saveForm[]  = $v;
            }
        }
        $this->getCardId();
        // TODO 无须审核流程
        if (! app()->get(ApproveService::class)->value($approveId, 'examine')) {
            $res = $this->transaction(function () use ($entId, $approveId, $saveForm) {
                $newId = $this->dao->create([
                    'entid'      => $entId,
                    'user_id'    => $this->userId,
                    'approve_id' => $approveId,
                    'number'     => $entId . $approveId . substr((string) time(), 4, 6),
                    'node_id'    => '',
                    'status'     => 1,
                    'examine'    => 0,
                ])->id;
                $res1 = app()->get(ApproveContentService::class)->saveMore($saveForm, $newId);
                if (! $res1) {
                    throw $this->exception('保存失败');
                }
                return $newId;
            });
        } else {
            if (! $process) {
                throw $this->exception('缺少审批流程');
            }
            $count = count($process);
            $empty = 0;
            foreach ($process as $val) {
                if (! $val['users']) {
                    ++$empty;
                }
            }
            if ($empty == $count) {
                throw $this->exception('流程节点至少存在一个人员');
            }
            $res = $this->transaction(function () use ($saveForm, $process, $approveId, $applyId, $entId) {
                if ($applyId && $this->dao->value(['id' => $applyId], 'status') != 1) {
                    $this->dao->update(['id' => $applyId], ['status' => -1]);
                }
                $newId = $this->dao->create([
                    'entid'      => $entId,
                    'user_id'    => $this->userId,
                    'approve_id' => $approveId,
                    'number'     => $entId . $approveId . substr((string) time(), 4, 6),
                    'node_id'    => $process[0]['uniqued'],
                    'status'     => 0,
                ])->id;
                $res1 = app()->get(ApproveContentService::class)->saveMore($saveForm, $newId);
                $res2 = app()->get(ApproveUserService::class)->saveMore($process, $approveId, $newId);
                if (! $res1 || ! $res2) {
                    throw $this->exception('保存失败');
                }
                return $newId;
            });
            // 自动走抄送流程
            event('approve.autoCopy', [$process, $res, $entId, app()->get(ApproveUserService::class)]);
            // 提醒下个审核人
            Task::deliver(new BusinessApprovalRemind($entId, (int) $res));
        }
        Task::deliver(new ApplySavedTask($res, $saveForm));
        Cache::tags([CacheEnum::TAG_APPROVE])->flush();
        return $res;
    }

    /**
     * 保存实体审批.
     * @param mixed $crudId
     * @param mixed $data
     * @param mixed $approveId
     * @param mixed $userId
     * @return mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function saveCrudApply($crudId, $data, $approveId, $userId, int $entId = 1, int $linkId = 0)
    {
        $process = app()->get(SystemCrudApproveProcessService::class)->verifyForm($data, $approveId, $userId);
        if (! $process) {
            throw $this->exception('缺少审批流程');
        }
        $count = count($process);
        $empty = 0;
        foreach ($process as $val) {
            if (! $val['users']) {
                ++$empty;
            }
        }
        if ($empty == $count) {
            throw $this->exception('流程节点至少存在一个人员');
        }
        $res = $this->transaction(function () use ($crudId, $process, $approveId, $entId, $userId, $linkId) {
            $newId = $this->dao->getIncId([
                'entid'      => $entId,
                'crud_id'    => $crudId,
                'link_id'    => $linkId,
                'user_id'    => $userId,
                'approve_id' => $approveId,
                'number'     => $approveId . substr((string) time(), 4, 6),
                'node_id'    => $process[0]['uniqued'],
                'status'     => 0,
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ]);
            $res = app()->get(ApproveUserService::class)->saveMore($process, $approveId, $newId, true);
            ! $res && throw $this->exception('保存失败');
            return $newId;
        });
        // 自动走抄送流程
        event('approve.autoCopy', [$process, $res, $entId, app()->get(ApproveUserService::class)]);
        // 提醒下个审核人
        Task::deliver(new BusinessApprovalRemind($entId, (int) $res));
        Cache::tags([CacheEnum::TAG_APPROVE])->flush();
        return $res;
    }

    /**
     * 审批.
     * @param mixed $id
     * @param mixed $uid
     * @param mixed $status
     * @return mixed
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function verify(int $id, int $uid, int $status)
    {
        $applyInfo   = $this->dao->get(['id' => $id], ['approve_id', 'crud_id', 'node_id'])?->toArray() ?: [];
        $userService = app()->get(ApproveUserService::class);
        if (! $applyInfo) {
            throw $this->exception('无效的审批信息');
        }
        if (! in_array($uid, $userService->column(['apply_id' => $id], 'user_id'))) {
            throw $this->exception('您暂时没有操作权限');
        }
        if ($status) {
            if ($applyInfo['crud_id']) {
                $ruleInfo = app()->get(SystemCrudApproveRuleService::class)->get(['approve_id' => $applyInfo['approve_id']]);
            } else {
                $ruleInfo = app()->get(ApproveRuleService::class)->get(['approve_id' => $applyInfo['approve_id']]);
            }
            $approveUsers = $userService->dao->setDefaultSort(['level' => 'asc'])->select(['apply_id' => $id])?->toArray() ?: [];
            $userInfo     = array_filter($approveUsers, function ($item) use ($applyInfo, $uid) {
                return $item['node_id'] == $applyInfo['node_id'] && $item['user_id'] == $uid && $item['status'] == 0;
            });
            if (! $userInfo) {
                return true;
            }
            $this->autoVerify($ruleInfo, $userService, $id, $applyInfo, reset($userInfo));
            $approveUsers = $userService->dao->setDefaultSort(['level' => 'asc'])->select(['apply_id' => $id])?->toArray() ?: [];
            $userService->update([
                'apply_id' => $id,
                'user_id'  => $uid,
                'types'    => 1,
                'node_id'  => $applyInfo['node_id'],
            ], [
                'verify' => 1,
                'status' => 1,
            ]);
            foreach ($approveUsers as $item) {
                if ($item['user_id'] == $uid && $item['types'] == 1 && $item['node_id'] == $applyInfo['node_id']) {
                    $item['status'] = 1;
                    $item['verify'] = 1;
                }
            }
            // 1、或签；2、会签；3、依次审批；(0、无此条件)
            switch (reset($userInfo)['process_info']['examine_mode']) {
                case 1:
                    $edit['node_id'] = $this->nextNode($approveUsers, $userService, $id, reset($userInfo)['level'], $applyInfo['node_id']);
                    break;
                case 2:
                case 3:
                    $edit['node_id'] = $this->nextNode($approveUsers, $userService, $id, reset($userInfo)['level'], $applyInfo['node_id'], false);
                    break;
                default:
                    $edit['node_id'] = $applyInfo['node_id'];
            }
            $nodeUsers = $userService->dao->select(['apply_id' => $id, 'node_id' => $edit['node_id']])?->toArray() ?: [];
            $end       = array_filter($nodeUsers, function ($item) {
                return $item['status'] == 0;
            });
            if ($end) {
                if (reset($end)['process_info']['examine_mode'] > 1) {
                    $edit['status'] = 0;
                } else {
                    $end = array_filter($nodeUsers, function ($item) {
                        return $item['status'] == 1;
                    });
                    $edit['status'] = $end ? 1 : 0;
                }
            } else {
                $edit['status'] = 1;
            }
        } else {
            $userService->update(['apply_id' => $id, 'node_id' => $applyInfo['node_id'], 'user_id' => $uid, 'types' => 1], ['verify' => 1, 'status' => 2]);
            $edit['status'] = 2;
        }
        $this->dao->update(['id' => $id], $edit);
        $res = $edit['node_id'] ?? true;
        Cache::tags([CacheEnum::TAG_APPROVE])->flush();
        // 【业务类型】审批提醒
        Task::deliver(new BusinessApprovalRemind(1, (int) $id));
        switch ($edit['status']) {
            case 1:
                Task::deliver(new ApprovedTask((int) $id));
                // 申请人【业务类型】审批通过提醒
                Task::deliver(new BusinessAdoptApplyRemind(1, $uid, (int) $id));
                break;
            case 2:
                Task::deliver(new ApproveRevokeTask((int) $id));
                // 【业务类型】未通过审批醒
                Task::deliver(new BusinessFailRemind(1, (int) $id));
                break;
        }
        return $res;
    }

    /**
     * 获取下级流程节点ID.
     * @param mixed $applyId
     * @param mixed $level
     * @param mixed $node_id
     * @param mixed $approveUsers
     * @param mixed $userService
     * @return mixed
     */
    public function nextNode($approveUsers, $userService, $applyId, $level, $node_id, bool $isOr = true)
    {
        if (! $isOr) {
            $next = array_filter($approveUsers, function ($item) use ($node_id) {
                return $item['node_id'] == $node_id && $item['types'] == 1 && $item['status'] == 0;
            });
            if ($next) {
                return $node_id;
            }
        }
        $next = array_filter($approveUsers, function ($item) use ($level) {
            return $item['level'] == $level + 1;
        });
        if ($next) {
            if (reset($next)['types'] == 2) {
                // 抄送人【业务类型】审批通过提醒
                Task::deliver(new BusinessAdoptCcRemind(1, (int) $applyId, reset($next)['node_id']));
                $userService->update(['apply_id' => $applyId, 'node_id' => reset($next)['node_id']], ['status' => 1]);
                foreach ($approveUsers as $item) {
                    if ($item['node_id'] == reset($next)['node_id']) {
                        $item['status'] = 1;
                    }
                }
                return $this->nextNode($approveUsers, $userService, $applyId, reset($next)['level'], reset($next)['node_id']);
            }
            if (reset($next)['process_info']['examine_mode'] == 1) {
                $isVerify = array_filter($next, function ($item) {
                    return $item['status'] == 1;
                });
                return $isVerify ? $this->nextNode($approveUsers, $userService, $applyId, reset($next)['level'], reset($next)['node_id']) : reset($next)['node_id'];
            }
            $isVerify = array_filter($next, function ($item) {
                return $item['status'] == 0;
            });
            return $isVerify ? reset($next)['node_id'] : $this->nextNode($approveUsers, $userService, $applyId, reset($next)['level'], reset($next)['node_id'], false);
        }
        return $node_id;
    }

    /**
     * 撤销申请.
     * @param mixed $userId
     * @param mixed $id
     * @return int
     * @throws BindingResolutionException
     */
    public function revokeApply($id, int $userId = 0)
    {
        if (! $userId) {
            $userId = auth('admin')->id();
        }
        $info = $this->dao->get(['id' => $id, 'user_id' => $userId]);
        if (! $info) {
            throw $this->exception('暂无可操作记录！');
        }
        if ($info->status == 1 && ! app()->get(ApproveRuleService::class)->value(['approve_id' => $info->approve_id], 'recall')) {
            throw $this->exception('已通过的申请不允许撤销！');
        }
        Cache::tags([CacheEnum::TAG_APPROVE])->flush();
        // 【业务类型】撤回提醒
        Task::deliver(new BusinessRecallRemind(1, $userId, $info->toArray()));
        Task::deliver(new ApproveRevokeTask((int) $id));
        return $this->dao->update(['id' => $id], ['status' => -1]);
    }

    /**
     * 待我审批数量.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getApproveCount(int $cardId): int
    {
        $this->userId = $cardId;
        $where        = ['types' => 1, 'name' => '', 'verify_status' => 1];
        return $this->dao->count($this->getWhere($where));
    }

    /**
     * 审批催办.
     * @param mixed $uid
     * @param mixed $id
     * @throws BindingResolutionException
     * @throws InvalidArgumentException
     * @throws \ReflectionException
     */
    public function urge($id, $uid)
    {
        $info = toArray($this->dao->get($id));
        if ($info['status'] != 0) {
            throw $this->exception('该审批信息无需催办');
        }
        if (Cache::tags([CacheEnum::TAG_APPROVE])->has(md5('urge' . $uid . $id))) {
            throw $this->exception('操作太过频繁，请稍后再试');
        }
        Task::deliver(new BusinessApprovalRemind(1, (int) $id));
        Cache::tags([CacheEnum::TAG_APPROVE])->set(md5('urge' . $uid . $id), 1, ApproveEnum::APPROVE_URGE_INTERVAL);
    }

    /**
     * 根据类型获取审批通过条数.
     * @throws BindingResolutionException
     */
    public function getApplyNumByTypes(int $uid, int $types): int
    {
        $approveIds = app()->get(ApproveService::class)->column(['types' => $types], 'id');
        return $approveIds ? $this->dao->count(['user_id' => $uid, 'approve_id' => $approveIds, 'status' => 1]) : 0;
    }

    /**
     * 处理where条件.
     * @param mixed $where
     * @return array|mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getWhere($where)
    {
        $approveUser  = app()->get(ApproveUserService::class);
        $adminService = app()->get(AdminService::class);
        return Cache::tags([CacheEnum::TAG_APPROVE])->remember(
            md5(json_encode($where) . $this->userId),
            (int) sys_config('system_cache_ttl', 3600),
            function () use ($where, $approveUser, $adminService) {
                switch ($where['types']) {
                    case 2:
                        if ($where['name']) {
                            $where['user_id'] = $adminService->column(['name' => $where['name']], 'id');
                        }
                        if ($where['frame_id']) {
                            $user_id = app()->get(FrameAssistService::class)->column(['frame_id' => $where['frame_id'], 'is_mastart' => 1], 'user_id');
                            if (isset($where['user_id'])) {
                                $where['user_id'] = array_intersect($adminService->column(['id' => $user_id], 'id'), $where['user_id']);
                            } else {
                                $where['user_id'] = $adminService->column(['id' => $user_id], 'id');
                            }
                        }
                        break;
                    case 1:
                        if ($where['name']) {
                            $where['user_id'] = $adminService->column(['name' => $where['name']], 'id');
                        }
                        switch ($where['verify_status']) {
                            case 4:// 已撤销
                                $ids1            = $approveUser->column(['user_id' => $this->userId, 'types' => 1, 'status' => [1, 2]], 'apply_id');
                                $ids2            = $this->getNowApplyId($this->userId);
                                $where['id']     = array_unique(array_merge($ids1, $ids2));
                                $where['status'] = -1;
                                break;
                            case 3:// 抄送我的
                                $where['id']         = $approveUser->column(['user_id' => $this->userId, 'types' => 2, 'status' => 1], 'apply_id');
                                $where['not_status'] = -1;
                                break;
                            case 2:// 已处理
                                $where['id']         = $approveUser->column(['user_id' => $this->userId, 'types' => 1, 'status' => [1, 2]], 'apply_id');
                                $where['not_status'] = -1;
                                break;
                            case 1:// 待审核
                                $where['id']     = $this->getNowApplyId($this->userId);
                                $where['status'] = 0;
                                break;
                            default:// 全部
                                $ids1        = $approveUser->column(['user_id' => $this->userId, 'types' => 2, 'status' => 1], 'apply_id');
                                $ids2        = $approveUser->column(['user_id' => $this->userId, 'types' => 1, 'status' => [1, 2]], 'apply_id');
                                $ids3        = $this->getNowApplyId($this->userId);
                                $where['id'] = array_unique(array_merge($ids1, $ids2, $ids3));
                        }
                        break;
                    default:
                        if ($where['verify_status'] == 5) {
                            $where['not_status'] = -1;
                        }
                        $where['user_id'] = $this->userId;
                }
                unset($where['frame_id'], $where['types'], $where['name'], $where['verify_status']);
                return $where;
            }
        );
    }

    /**
     * 自动审批.
     * @param mixed $ruleInfo
     * @param mixed $approveUserService
     * @param mixed $id
     * @param mixed $applyInfo
     * @param mixed $userInfo
     */
    protected function autoVerify($ruleInfo, $approveUserService, $id, $applyInfo, $userInfo): void
    {
        // 自动审批：0、首个节点处理，其他自动同意；1、连续审批自动同意；2、每个节点都需审批；
        switch ($ruleInfo->auto) {
            case 0:
                $approveUserService->update(['apply_id' => $id, 'user_id' => $userInfo['user_id'], 'types' => 1], ['verify' => 1, 'status' => 1]);
                break;
            case 1:
                $this->isAuto($approveUserService, $id, $userInfo['level'], $applyInfo->node_id, uid: $userInfo['user_id']);
                break;
            case 2:
                $approveUserService->update(['apply_id' => $id, 'user_id' => $userInfo['user_id'], 'types' => 1, 'node_id' => $applyInfo->node_id], ['verify' => 1, 'status' => 1]);
                break;
        }
    }

    protected function isAuto($approveUserService, $id, $level, $node_id, $first = true, $uid = ''): void
    {
        if ($first) {
            $approveUserService->update(['apply_id' => $id, 'user_id' => $uid, 'types' => 1, 'node_id' => $node_id], ['verify' => 1, 'status' => 1]);
        } else {
            $approveUserService->update(['apply_id' => $id, 'user_id' => $uid, 'types' => 1, 'node_id' => $node_id], ['verify' => 1]);
        }
        ++$level;
        if ($nextNodeId = $approveUserService->value(['apply_id' => $id, 'user_id' => $uid, 'types' => 1, 'level' => $level], 'node_id')) {
            $this->isAuto($approveUserService, $id, $level, $nextNodeId, false, $uid);
        }
    }

    protected function checkType($setType): string
    {
        return match ($setType) {
            1       => '指定成员审批',
            2       => '指定部门主管',
            7       => '连续多级审批',
            5       => '申请人自己审批',
            4       => '申请人自选',
            default => '无效类型',
        };
    }

    /**
     * 获取用户名片ID.
     * @return $this
     * @throws BindingResolutionException
     */
    protected function getCardId()
    {
        $this->userId = auth('admin')->id();
        return $this;
    }

    /**
     * 获取当前审批ID.
     * @param mixed $userId
     * @return array|mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getNowApplyId($userId)
    {
        return Cache::tags([CacheEnum::TAG_APPROVE])->remember($userId . '_approve_apply_verify_ids', (int) sys_config('system_cache_ttl', 3600), function () use ($userId) {
            $userServices  = app()->get(ApproveUserService::class);
            $applyServices = app()->get(ApproveApplyService::class);
            $applys        = toArray($userServices->select(['user_id' => $userId, 'types' => 1, 'status' => 0], ['apply_id', 'node_id', 'sort']));
            $applyIds      = [];
            if ($applys) {
                foreach ($applys as $value) {
                    if ($value['sort'] > 1) {
                        if ($userServices->exists(['apply_id' => $value['apply_id'], 'node_id' => $value['node_id'], 'sort' => $value['sort'] - 1, 'status' => 1])) {
                            $applyIds[] = $value['apply_id'];
                        }
                    } elseif ($applyServices->exists(['id' => $value['apply_id'], 'node_id' => $value['node_id']])) {
                        $applyIds[] = $value['apply_id'];
                    }
                }
            }
            return $applyIds;
        });
    }
}
