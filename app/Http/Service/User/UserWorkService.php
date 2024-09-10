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

namespace App\Http\Service\User;

use App\Http\Model\User\UserWorkDao;
use App\Http\Service\BaseService;
use crmeb\interfaces\ResourceServicesInterface;
use crmeb\services\FormService;
use crmeb\traits\service\ResourceServiceTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * 工作经历
 * Class UserWorkService.
 */
class UserWorkService extends BaseService implements ResourceServicesInterface
{
    use ResourceServiceTrait;

    /**
     * 名片ID.
     * @var int
     */
    protected $cardId;

    /**
     * @var FormService
     */
    protected $build;

    public function __construct(UserWorkDao $dao, FormService $build)
    {
        $this->dao   = $dao;
        $this->build = $build;
    }

    /**
     * 设置名片id.
     */
    public function setCardId(int $cardId): void
    {
        $this->cardId = $cardId;
    }

    /**
     * 查询数据.
     * @param array|string[] $field
     * @param null|string $sort
     */
    public function getList(array $where, array $field = ['*'], $sort = null, array $with = []): array
    {
        return parent::getList($where, $field, 'id');
    }

    /**
     * 创建获取工作经历表单.
     */
    public function resourceCreate(array $other = []): array
    {
        return $this->elForm('添加工作经历', $this->createWorkRule(collect()), '/ent/work');
    }

    /**
     * 修改获取工作经历表单.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function resourceEdit(int $id, array $other = []): array
    {
        if (! ($info = $this->dao->get($id))) {
            throw $this->exception('修改的工作经历不存在');
        }

        return $this->elForm('修改工作经历', $this->createWorkRule(collect($info->toArray())), '/ent/work/' . $id, 'PUT');
    }

    /**
     * 创建数据.
     * @return mixed|Model
     * @throws BindingResolutionException
     */
    public function resourceSave(array $data)
    {
        /** @var EnterpriseUserCardService $service */
        $service = app()->get(EnterpriseUserCardService::class);
        if (! $service->exists($data['card_id'])) {
            throw $this->exception('企业名片不存在');
        }

        return $this->dao->create($data);
    }

    /**
     * 创建工作经历表单规则.
     * @return array
     */
    protected function createWorkRule(Collection $info)
    {
        return [
            $this->build->hidden('card_id', $this->cardId),
            $this->build->date('start_time', '开始时间：', $info->get('start_time', ''))
                ->validate([$this->build->validateStr()->required()->message('请选择开始时间')]),
            $this->build->date('end_time', '结束时间：', $info->get('end_time', ''))
                ->validate([$this->build->validateStr()->required()->message('请选择结束时间')]),
            $this->build->input('company', '所在公司：', $info->get('company'))->required(),
            $this->build->input('position', '职位：', $info->get('position'))->required(),
            $this->build->textarea('describe', '工作描述：', $info->get('describe'))->required(),
            $this->build->textarea('quit_reason', '离职原因：', $info->get('quit_reason')),
        ];
    }
}
