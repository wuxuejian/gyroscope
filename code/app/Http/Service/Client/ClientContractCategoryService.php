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

namespace App\Http\Service\Client;

use App\Http\Contract\Client\ClientContractInterface;
use App\Http\Dao\Client\ClientContractCategoryDao;
use App\Http\Model\BaseModel;
use App\Http\Service\BaseService;
use App\Http\Service\Finance\BillCategoryService;
use crmeb\services\FormService as Form;
use crmeb\traits\service\ResourceServiceTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * 客户合同分类
 * Class ClientContractCategoryService.
 */
class ClientContractCategoryService extends BaseService implements ClientContractInterface
{
    use ResourceServiceTrait;

    /**
     * ClientContractCategoryService constructor.
     */
    public function __construct(ClientContractCategoryDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 列表.
     * @param string $sort
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getList(array $where, array $field = ['id', 'pid', 'path', 'level', 'name', 'cate_no', 'bill_cate_id', 'sort', 'created_at', 'updated_at'], $sort = ['sort', 'created_at'], array $with = []): array
    {
        if ($where['bill_cate_name']) {
            if ($list = app()->get(BillCategoryService::class)->column(['name_like' => $where['bill_cate_name']], 'id')) {
                $where['bill_cate_id'] = $list;
            }
        }

        unset($where['bill_cate_name']);
        $list = $this->dao->getList($where, $field, 0, 0, $sort, $with);
        if (in_array('billCategory', $with)) {
            foreach ($list as &$item) {
                $billCategory               = $item['bill_category'];
                $item['bill_category_name'] = $billCategory['name'] ?? '';
                unset($item['bill_category']);
            }
        }
        return get_tree_children($list);
    }

    /**
     * 获取创建分类表单.
     */
    public function resourceCreate(array $other = []): array
    {
        return $this->elForm('添加分类', $this->getFormRule(collect($other)), '/ent/client/contract_category');
    }

    /**
     * 获取修改分类表单.
     */
    public function resourceEdit(int $id, array $other = []): array
    {
        $info = $this->dao->get($id);
        if (! $info) {
            throw $this->exception('修改的分类不存在');
        }
        return $this->elForm('修改分类', $this->getFormRule(collect($info->toArray())), '/ent/client/contract_category/' . $id, 'PUT');
    }

    /**
     * 路径整理.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getParentPath(array $path = [], int $pid = 0): array
    {
        if ($pid && ! $path) {
            $info = $this->dao->get(['id' => $pid], ['id', 'path']);
            if ($info) {
                $path   = $info->getAttribute('path') ?? [];
                $path[] = $pid;
            }
        }
        array_unshift($path, 0);
        return $path;
    }

    /**
     * 保存数据.
     * @return BaseModel|Model
     * @throws BindingResolutionException
     */
    public function resourceSave(array $data)
    {
        $data = $this->getData($data);

        if ($this->dao->exists(['pid' => $data['pid'], 'name' => $data['name'], 'entid' => $data['entid']])) {
            throw $this->exception('分类已存在, 请勿重复添加');
        }

        if ($this->count(['pid' => $data['pid']]) >= 99) {
            throw $this->exception(($data['pid'] ? '直属' : '一级') . '分类数量到达上限');
        }

        $data['cate_no'] = $this->generateNo((int) $data['pid']);
        return $this->dao->create($data);
    }

    /**
     * 修改数据.
     * @param mixed $id
     * @throws BindingResolutionException
     */
    public function resourceUpdate($id, array $data): int
    {
        $data = $this->getData($data);

        if ($data['pid'] == $id) {
            throw $this->exception('前置分类不能为自己');
        }

        if ($this->dao->exists(['notid' => $id, 'pid' => $data['pid'], 'name' => $data['name'], 'entid' => $data['entid']])) {
            throw $this->exception('分类已存在, 请勿重复添加');
        }

        $info = $this->dao->get($id, ['id', 'cate_no', 'path']);
        if (! $info) {
            throw $this->exception('分类信息获取异常');
        }
        $info         = $info->toArray();
        $data['path'] = array_filter(array_map('intval', $data['path']));
        sort($data['path']) && sort($info['path']);

        if (! $info['cate_no'] || $data['path'] != $info['path']) {
            $data['cate_no'] = $this->generateNo((int) $data['pid']);
        }
        return $this->dao->update($id, $data);
    }

    /**
     * 删除数据.
     * @param mixed $id
     */
    public function resourceDelete($id, ?string $key = null): int
    {
        $info = $this->dao->get($id);
        if (! $info) {
            throw $this->exception(__('common.operation.noExists'));
        }
        if (app()->get(ContractService::class)->count(['category_id' => $id])) {
            throw $this->exception('该合同分类已经被使用，不可删除！');
        }

        if ($this->count(['pid' => $id])) {
            throw $this->exception('该合同分类已经被使用，不可删除！');
        }
        return $this->dao->delete($id);
    }

    /**
     * 请求数据处理.
     */
    public function getData(array $data): array
    {
        if ($path = $data['path'] ?? []) {
            $data['pid'] = $path[count($path) - 1];
        }
        if (! isset($data['pid']) || ! $data['pid']) {
            $data['pid']  = 0;
            $data['path'] = [];
        }

        $data['path']  = array_filter($data['path']);
        $data['level'] = count($data['path']) + 1;

        if ($data['level'] > 5) {
            throw $this->exception('合同分类最多为5级');
        }

        $data['bill_cate_id'] = 0;
        $path                 = $data['bill_cate_path'] ?? [];
        if ($path) {
            $data['bill_cate_id'] = intval($path[count($path) - 1]);
        }
        return $data;
    }

    /**
     * 获取名称集合.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getCategoryData(array $names, int $entId): array
    {
        return $this->dao->column(['names' => $names, 'entid' => $entId], 'id', 'name');
    }

    /**
     * 生成分类ID.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function generateNo(int $pid = 0): string
    {
        $parentNo = '';
        if ($pid) {
            $info = $this->dao->get(['id' => $pid], ['id', 'name', 'path', 'cate_no']);
            if (! $info) {
                throw $this->exception('分类信息获取异常');
            }
            $parentNo = $info->cate_no;
        }

        for ($i = 0; $i < 99; ++$i) {
            $no = $parentNo . sprintf('%02d', $i + 1);
            if (! $this->dao->count(['pid' => $pid, 'cate_no' => $no])) {
                break;
            }
            $no = '';
        }
        if (! $no) {
            throw $this->exception('分类ID生成失败');
        }
        return $no;
    }

    /**
     * 获取父级数据.
     * @param mixed $sort
     */
    public function getParent(array $where, array $field = ['*'], $sort = ['level' => 'asc']): array
    {
        return $this->dao->getList($where, $field, 0, 0, $sort);
    }

    /**
     * 下拉列表.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getSelectList(): array
    {
        $list = $this->dao->getList([], ['id as value', 'name as label', 'pid'], 0, 0, ['sort', 'created_at']);
        return get_tree_children($list, keyName: 'value');
    }

    /**
     * 获取分类表单规则.
     * @return array
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    protected function getFormRule(Collection $collection)
    {
        $billCategoryList = app()->get(BillCategoryService::class)->getList(['types' => 1], ['name as label', 'id as value', 'id', 'pid', 'types'], null, []);

        return [
            Form::cascader('path', '前置分类')
                ->options($this->getContractCateTree())->value($this->getParentPath((array) $collection->get('path', []), (int) $collection->get('pid', 0)))
                ->props(['props' => ['checkStrictly' => true]])->clearable(true),
            Form::input('name', '分类名称', $collection->get('name'))->required(),
            Form::cascader('bill_cate_path', '关联账目分类')->value($collection->get('bill_cate_path', []) ?? [])
                ->options($billCategoryList)->props(['props' => ['checkStrictly' => true]])->clearable(true),
            Form::number('sort', '排序', $collection->get('sort', 0))->min(0)->max(999999)->precision(0),
        ];
    }

    private function getContractCateTree(): array
    {
        $list = get_tree_children($this->dao->getList([], ['name as label', 'id as value', 'pid'], 0, 0, 'id'), 'children', 'value');
        return ['label' => '顶级分类', 'value' => 0, 'pid' => 0, 'children' => $list];
    }
}
