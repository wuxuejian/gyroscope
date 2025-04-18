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

use App\Http\Dao\Chat\ChatModelsDao;
use App\Http\Service\BaseService;
use crmeb\interfaces\ResourceServicesInterface;
use crmeb\services\ai\BaidubceOption;
use crmeb\services\ai\DeepseekOption;
use crmeb\traits\service\ResourceServiceTrait;

/**
 *  ai模型.
 */
class ChatModelsService extends BaseService implements ResourceServicesInterface
{
    use ResourceServiceTrait;

    public function __construct(ChatModelsDao $dao)
    {
        $this->dao = $dao;
    }

    public function getList(array $where, array $field = ['*'], $sort = ['id'], array $with = ['user']): array
    {
        return parent::getList($where, $field, $sort, $with);
    }

    public function list()
    {
        $res     = $this->dao->search([])->get()?->toArray();
        $options = array_merge(DeepseekOption::MODEL_OPTIONS, BaidubceOption::MODEL_OPTIONS);
        $data    = [];
        foreach ($res as $datum) {
            $data[] = [
                'lable' => $datum['name'],
                'value' => $datum['id'],
                'min'   => $options[$datum['is_model']]['min'] ?? 1,
                'max'   => $options[$datum['is_model']]['max'] ?? 2048,
            ];
        }
        return $data;
    }

    public function resourceUpdate($id, array $data)
    {
        unset($data['uid']);
        return $this->dao->update($id, $data);
    }

    public function resourceEdit(int $id, array $other = []): array
    {
        if (! $id) {
            throw $this->exception('缺少必要参数');
        }
        return $this->dao->get($id)?->toArray();
    }

    public function resourceDelete($id, ?string $key = null)
    {
        $res = $this->dao->search(['id' => $id])->with([
            'applications' => function ($query) {
                $query->select(['id', 'models_id', 'name']);
            },
        ])->get()?->first()->toArray();
        if ($res['applications']) {
            $applications = array_column($res['applications'], 'name');
            $name         = implode(',', $applications);
            throw $this->exception('请先在应用中解除关联！【' . $name . '】');
        }
        return $this->dao->delete($id, $key);
    }

    public function resourceCreate(array $other = []): array {}
}
