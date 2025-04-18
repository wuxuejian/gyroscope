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

namespace App\Http\Service\Chat;

use App\Http\Dao\Chat\ChatApplicationsDao;
use App\Http\Service\Admin\AdminService;
use App\Http\Service\BaseService;
use crmeb\interfaces\ResourceServicesInterface;
use crmeb\traits\service\ResourceServiceTrait;
use Illuminate\Support\Facades\DB;

/**
 *  chat应用管理.
 */
class ChatApplicationsService extends BaseService implements ResourceServicesInterface
{
    use ResourceServiceTrait;

    public function __construct(ChatApplicationsDao $dao)
    {
        $this->dao = $dao;
    }

    public function getList(array $where, array $field = ['id', 'uid', 'info', 'name', 'pic', 'status', 'edit'], $sort = ['sort', 'id'], array $with = ['user']): array
    {
        [$page, $limit] = $this->getPageValue();
        if (isset($where['cate_id']) && is_array($where['cate_id'])) {
            $where['cate_id'] = array_map(function ($item) {
                return str_replace(['[', ']'], '', $item);
            }, $where['cate_id']);
        }
        $list = $this->dao->getList($where, $field, $page, $limit, $sort, $with);
        $uid  = auth('admin')->id();
        foreach ($list as &$item) {
            $item['auth'] = $uid == $item['uid'] || in_array($uid, $item['edit']);
        }
        $count = $this->dao->count($where);
        return $this->listData($list, $count);
    }

    public function resourceSave(array $data)
    {
        $data['tooltip_text'] = <<<'MD'
#角色规范
你是一个 XXXX 小助手，你的任务是 XXXX。

#思考规范

- 在回答问题时，你需要分析用户的问题，确保理解需求和上下文。
- 当用户的需求不明确时，你应该主动优先明确用户需求。
- 对于超出 本角色 小助手服务范围的需求，你需要按如下话术委婉拒答：抱歉，并引导用户提出关于 本角色 相关的问题。

#回复规范

- 你需要以 简洁高效的语气风格 回复用户。
- 在每次结束对话时你可以向用户提问并引导相关话题深入。
MD;

        return DB::transaction(function () use ($data) {
            $create = $this->dao->create($data);
            app()->get(ChatAppAuthService::class)->save($create->id, $data['auth_ids']);
            return $create;
        });
    }

    public function resourceUpdate($id, array $data)
    {
        $id = (int) $id;
        unset($data['uid']);
        return DB::transaction(function () use ($id, $data) {
            app()->get(ChatAppAuthService::class)->clear($id);
            app()->get(ChatAppAuthService::class)->save($id, $data['auth_ids']);
            return $this->dao->update($id, $data);
        });
    }

    public function resourceEdit(int $id, array $other = []): array
    {
        if (! $id) {
            throw $this->exception('缺少必要参数');
        }
        $data = $this->dao->get($id)?->toArray();
        if (! $data) {
            throw $this->exception('数据不存在');
        }

        $adminService     = app()->get(AdminService::class);
        $data['auth_ids'] = $adminService->search([])->whereIn('id', $data['auth_ids'])
            ->select(['id', 'name', 'avatar', 'uid', 'phone'])->get()?->toArray();
        $data['edit'] = $adminService->search([])->whereIn('id', $data['edit'])
            ->select(['id', 'name', 'avatar', 'uid', 'phone'])->get()?->toArray();
        return $data;
    }

    public function resourceCreate(array $other = []): array {}

    /**
     * @return string
     */
    public function getDatabaseTooltipText(array $tables = [])
    {
        $tableContent = '';
        foreach ($tables as $table) {
            $sql = (array) DB::select("SHOW CREATE TABLE `{$table}`");
            $tableContent .= $sql[0]->{'Create Table'} . "\n\n";
        }
        // 替换掉换行符和制表符
        $tableContent = str_replace('COLLATE utf8mb4_unicode_ci', ' ', $tableContent);
        // 补充提示词模版
        $tooltipText = <<<MD
# 数据库表结构
{$tableContent}
# 表之间引用关系
eb_admin的id和 XXX 表的uid关联；

## 查询规则
返回字段：[逗号分隔的字段列表，如 id, name AS 姓名]
筛选条件：[如 is_delete = 1 AND id > 1]
排序规则：[如 ORDER BY updated_at DESC, id ASC]
分组与聚合：[如 GROUP BY num HAVING COUNT(*) > 5]
连接操作：[如 LEFT JOIN a ON a.id = b.id]
限制结果：[如 LIMIT 10 OFFSET 20]
其他操作：[如 DISTINCT, 子查询等]

## 补充说明

MD;

        // 截取字符串超出1w字节
        if (strlen($tooltipText) > 10000) {
            $tooltipText = substr($tooltipText, 0, 10000);
        }
        return $tooltipText;
    }
}
