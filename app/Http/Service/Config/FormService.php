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

namespace App\Http\Service\Config;

use App\Constants\CacheEnum;
use App\Constants\CodeEnum;
use App\Constants\CustomEnum\ContractEnum;
use App\Constants\CustomEnum\CustomEnum;
use App\Constants\CustomEnum\CustomerEnum;
use App\Constants\CustomEnum\LiaisonEnum;
use App\Constants\UserAgentEnum;
use App\Http\Dao\Config\FormCateDao;
use App\Http\Dao\Config\FormDataDao;
use App\Http\Model\BaseModel;
use App\Http\Service\BaseService;
use App\Http\Service\Client\ContractService;
use App\Http\Service\Client\CustomerLiaisonService;
use App\Http\Service\Client\CustomerService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class FormService extends BaseService
{
    public FormDataDao $dataDao;

    public function __construct(FormCateDao $dao, FormDataDao $dataDao)
    {
        $this->dao     = $dao;
        $this->dataDao = $dataDao;
    }

    /**
     * 列表.
     * @param string $sort
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getList(array $where, array $field = ['id', 'title', 'sort', 'types', 'status'], $sort = 'sort', array $with = ['data']): array
    {
        $types = $where['types'] ?? 0;
        return $this->dao->getList($where, $field, 0, 0, $sort, $with, function ($list) use ($types) {
            $field = match ((int) $types) {
                CustomEnum::CUSTOMER => CustomerEnum::CUSTOMER_NOT_ALLOW_DELETE_FIELD,
                CustomEnum::CONTRACT => ContractEnum::CONTRACT_NOT_ALLOW_DELETE_FIELD,
                CustomEnum::LIAISON  => LiaisonEnum::LIAISON_NOT_ALLOW_DELETE_FIELD,
                default              => []
            };
            foreach ($list as $item) {
                foreach ($item->data as $data) {
                    $data->enable_delete = 1;
                    if (in_array($data->key, $field)) {
                        $data->enable_delete = 0;
                    }
                }
            }
        });
    }

    /**
     * 保存表单分类.
     * @throws BindingResolutionException
     */
    public function saveCate(int $types, array $data): BaseModel
    {
        $data['types'] = $types;
        $res           = $this->dao->create($data);
        if ($res) {
            Cache::tags([CacheEnum::TAG_CUSTOMER])->flush();
        }
        return $res;
    }

    /**
     * 更新表单分类.
     * @return mixed
     * @throws BindingResolutionException
     */
    public function updateCate(int $id, array $data)
    {
        $res = $this->transaction(function () use ($id, $data) {
            return $this->dao->update($id, $data);
        });
        return $res && Cache::tags([CacheEnum::TAG_CUSTOMER])->flush();
    }

    /**
     * 删除表单分类.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function deleteCate(int $id): bool
    {
        return $this->transaction(function () use ($id) {
            $res = $this->dao->delete($id);
            if (! $res) {
                throw $this->exception(__('common.delete.fail'));
            }

            if ($this->dataDao->count(['cate_id' => $id])) {
                $dataRes = $this->dataDao->delete(['cate_id' => $id]);
                if (! $dataRes) {
                    throw $this->exception(__('common.delete.fail'));
                }

                Cache::tags([CacheEnum::TAG_CUSTOMER])->flush();
            }
            return true;
        });
    }

    /**
     * 更新状态
     * @throws BindingResolutionException
     */
    public function updateStatus(int $id, int $status): bool
    {
        return $this->dao->update($id, ['status' => $status != 0 ? 1 : 0]) && Cache::tags([CacheEnum::TAG_CUSTOMER])->flush();
    }

    /**
     * 更新排序.
     * @throws BindingResolutionException
     */
    public function updateSort(int $types, array $data): bool
    {
        if (empty($data)) {
            throw $this->exception('参数错误');
        }

        return $this->transaction(function () use ($types, $data) {
            $sort = range(count($data), 1);
            foreach ($data as $key => $datum) {
                $this->dao->update(['types' => $types, 'id' => (int) $datum], ['sort' => $sort[$key] ?? 0]);
            }
            Cache::tags([CacheEnum::TAG_CUSTOMER])->flush();
            return true;
        });
    }

    /**
     * 保存表单.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function saveData(int $types, array $data): mixed
    {
        $cateIds = $this->dao->column(['types' => $types]);
        if (array_diff(array_column($data, 'cate_id'), $cateIds)) {
            throw $this->exception('分组数据异常');
        }

        $res = $this->transaction(function () use ($data) {
            foreach ($data as $item) {
                if (empty($item['data'])) {
                    $this->dataDao->delete(['cate_id' => $item['cate_id']]);
                    continue;
                }
                $formData    = [];
                $cateDataIds = $this->dataDao->column(['cate_id' => $item['cate_id']], 'key', 'id');

                $num = count($item['data']);
                foreach ($item['data'] as $form) {
                    if (empty($form)) {
                        continue;
                    }

                    $tmpForm = [
                        'key_name'      => $form['key_name'] ?? '',
                        'type'          => $form['type'] ?? '',
                        'sort'          => $num,
                        'max'           => $form['max'] ?? 0,
                        'min'           => $form['min'] ?? 0,
                        'status'        => $form['status'] ?? 2,
                        'value'         => $form['value'] ?? '',
                        'param'         => $form['param'] ?? '',
                        'uniqued'       => $form['uniqued'] ?? '',
                        'required'      => $form['required'] ?? '',
                        'input_type'    => $form['input_type'] ?? '',
                        'upload_type'   => $form['upload_type'] ?? 0,
                        'placeholder'   => $form['placeholder'] ?? '',
                        'dict_ident'    => $form['dict_ident'] ?? '',
                        'decimal_place' => $form['decimal_place'] ?? 0,
                    ];

                    if (! isset($form['id']) || $form['id'] < 1) {
                        $tmpForm['cate_id'] = $item['cate_id'];
                        $tmpForm['key']     = $this->getUniKey();
                        $formData[]         = $tmpForm;
                    } else {
                        unset($cateDataIds[$form['id']]);
                        $this->dataDao->update(['id' => $form['id']], $tmpForm);
                    }
                    --$num;
                }

                if ($formData && ! $this->dataDao->insert($formData)) {
                    throw $this->exception('保存失败');
                }

                if ($cateDataIds && ! $this->dataDao->delete(['cate_id' => $item['cate_id'], 'id' => array_keys($cateDataIds)])) {
                    throw $this->exception('保存失败');
                }
            }
            return true;
        });

        if ($res) {
            // remove custom field list from types
            // app()->get(SalesmanCustomService::class)->forgetCustomTableField($types);

            // reload custom table field
            $this->reloadCustomTableField($types, $cateIds);

            Cache::tags([CacheEnum::TAG_CUSTOMER])->flush();
        }
        return $res;
    }

    /**
     * 移动分组.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function moveData(int $types, int $id, int $cateId): bool
    {
        $cateIds = $this->dao->column(['types' => $types]);
        if (! in_array($cateId, $cateIds)) {
            throw $this->exception('分组数据异常');
        }

        $info = $this->dataDao->get($id);
        if (! $info) {
            throw $this->exception('移动失败');
        }
        if (! in_array($info->cate_id, $cateIds)) {
            throw $this->exception('分组数据异常');
        }

        $info->cate_id = $cateId;
        return $info->save() && Cache::tags([CacheEnum::TAG_CUSTOMER])->flush();
    }

    /**
     * 获取 key 标识.
     * @throws BindingResolutionException
     */
    public function getUniKey(): string
    {
        while (true) {
            $tmpKey = substr('c' . md5(uniqid(microtime(), true) . mt_rand(0, 99999)), 0, 8);
            if (! $this->dataDao->exists(['key' => $tmpKey])) {
                $key = $tmpKey;
                break;
            }
        }
        return $key;
    }

    /**
     * 获取自定义表单数据.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getCustomDataByTypes(int $types, array $field = ['*'], array $with = []): array
    {
        $cateIds = $this->dao->column(['types' => $types]);
        return $this->dataDao->getTreeStructure(['cate_id' => $cateIds, 'status' => 1], $field, $with);
    }

    /**
     * 更新业务表字段.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function reloadCustomTableField(int $types, array $cateIds): bool
    {
        $field      = ['id', 'key', 'key_name', 'type', 'input_type', 'value', 'max', 'decimal_place', 'dict_ident'];
        $dataFields = $this->dataDao->select(['cate_id' => $cateIds], $field);
        if ($dataFields->isEmpty()) {
            return true;
        }

        $dictService = app()->get(DictDataService::class);
        $service     = $this->getCustomServiceByTypes($types);
        try {
            $table          = $service->getTable();
            $outOfSyncField = $service->getOutOfSyncField();
            $columns        = array_diff(Schema::getColumnListing($table), ['updated_at', 'deleted_at', 'deleted_at']);
            Schema::table($table, function (Blueprint $table) use ($dataFields, $columns, $outOfSyncField, $dictService) {
                foreach ($dataFields as $dataField) {
                    if ($outOfSyncField && in_array($dataField['key'], $outOfSyncField)) {
                        continue;
                    }
                    $value     = $dataField['value'];
                    $inputType = strtolower($dataField['input_type']);
                    if ($inputType == 'date') {
                        $obj = $table->date($dataField['key'])->nullable();
                        $value !== '' && $obj->default($value);
                    } elseif ($inputType == 'oawangeditor') {
                        $obj = $table->text($dataField['key']);
                    } elseif ($dataField['decimal_place']) {
                        $value = floatval($dataField['value']);
                        $place = intval($dataField['decimal_place']);
                        $obj   = $table->decimal($dataField['key'], 10, min(6, $place));
                    } else {
                        $obj = $table->string($dataField['key'], 255);
                    }

                    $dictIdent = $dataField['dict_ident'] ?? '';
                    if ($dictIdent
                        && $dictService->max(['type_name' => $dictIdent], 'level') == 1
                        && is_array($value)
                        && count($value) > 0
                    ) {
                        $value = $value[0];
                    } else {
                        $value = in_array($inputType, ['date', 'input', 'oawangeditor', 'file', 'radio']) ? $value :
                            json_encode($value);
                    }

                    $inputType !== 'date' && $obj->default($value);
                    $obj->comment($dataField['key_name']);
                    if (in_array($dataField['key'], $columns)) {
                        if ($value !== '') {
                            $inputType == 'date' && $value = null;
                            $obj->default($value);
                        }
                        $obj->change();
                    }
                }
            });
            return true;
        } catch (\Throwable $e) {
            Log::error('业务表字段更新失败:' . json_encode([
                'file'    => $e->getFile(),
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
            ]));
            throw $this->exception('业务表字段更新失败:' . $e->getMessage());
        }
    }

    /**
     * 表单数据.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getFormDataList(int $types): array
    {
        $cateIds = $this->dao->column(['types' => $types, 'status' => 1]);
        if (! $cateIds) {
            return [];
        }

        $field = ['id', 'key', 'key_name', 'type', 'input_type', 'decimal_place', 'required', 'dict_ident', 'value', 'min', 'max', 'sort', 'uniqued'];
        return $this->dataDao->getList(['cate_id' => $cateIds, 'status' => 1], $field, 0, 0, 'sort');
    }

    /**
     * 业务表单数据.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getFormDataWithType(int $types, bool $withOptions = true, string $platform = UserAgentEnum::ADMIN_AGENT): array
    {
        $dictService = app()->get(DictDataService::class);
        $formList    = $this->getList(['types' => $types, 'status' => 1], with: [
            'data' => fn ($q) => $q->where(['status' => 1]),
        ]);

        $field = [$platform == UserAgentEnum::ADMIN_AGENT ? 'name as label' : 'name as text', 'value', 'pid'];
        foreach ($formList as $index => $item) {
            foreach ($item['data'] as $key => $datum) {
                $level   = 0;
                $options = [];
                if (($datum['key'] == 'customer_status' && $types == CustomEnum::CUSTOMER)
                    || ($datum['key'] == 'contract_status' && $types == CustomEnum::CONTRACT)) {
                    unset($formList[$index]['data'][$key]);
                    continue;
                }

                if ($datum['dict_ident']) {
                    $level = $dictService->max(['type_name' => $datum['dict_ident']], 'level');
                    if ($level == 1 && is_array($datum['value']) && count($datum['value']) > 0) {
                        $formList[$index]['data'][$key]['value'] = $datum['value'][0];
                    }
                    if ($withOptions) {
                        $options = $dictService->getTreeData(['type_name' => $datum['dict_ident'], 'status' => 1], $field);
                    }
                }

                if ($types == CustomEnum::CONTRACT && $datum['key'] == 'contract_customer') {
                    $level   = 1;
                    $options = app()->get(CustomerService::class)->setPlatform($platform)->getCurrentSelect();
                }
                $formList[$index]['data'][$key]['options']       = $options;
                $formList[$index]['data'][$key]['options_level'] = $level;
            }
            $formList[$index]['data'] = array_values($formList[$index]['data']);
        }

        return $formList;
    }

    /**
     * 获取字段状态
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getFieldStatus(array|string $field): array
    {
        return $this->dataDao->column(['key' => $field, 'status' => 1], 'status', 'key');
    }

    /**
     * 自定义字段回显数据.
     * @param string $sort
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getTypeDataList(array $where, array $field = ['id', 'title', 'sort', 'types', 'status'], $sort = 'sort'): array
    {
        return $this->dao->getList($where, $field, 0, 0, $sort, ['data' => function ($query) {
            $query->select(['id', 'cate_id', 'key', 'key_name', 'type', 'input_type', 'dict_ident']);
        }]);
    }

    /**
     * 提取字段.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getRequestFields(int $types): array
    {
        $fields = [];
        $list   = $this->getCustomDataByTypes($types);
        foreach ($list as $item) {
            $fields[] = [$item['key'], $item['value']];
        }
        return $fields;
    }

    /**
     * 获取业务类型.
     * @return mixed|string
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function getCustomServiceByTypes(int $types): mixed
    {
        $service = match ($types) {
            CustomEnum::CUSTOMER => CustomerService::class,
            CustomEnum::CONTRACT => ContractService::class,
            CustomEnum::LIAISON  => CustomerLiaisonService::class,
            default              => null
        };

        if (! $service) {
            throw $this->exception('业务类型异常');
        }
        return app()->get($service);
    }

    /**
     * 字段数据验证
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function fieldValueCheck(array $data, int $types, int $id, array $list = [], int $force = 0): void
    {
        if (! $list) {
            $list = $this->getFormDataList($types);
        }

        $tz      = config('app.timezone');
        $service = $this->getCustomServiceByTypes($types);
        foreach ($list as $item) {
            $min       = $item['min'] ?? '';
            $max       = $item['max'] ?? '';
            $type      = strtolower($item['type']);
            $inputType = strtolower($item['input_type']);

            foreach ($data as $key => $value) {
                if ($item['key'] == $key) {
                    $len = 0;
                    if ($inputType == 'input') {
                        $len = mb_strlen($value);
                        if ($item['required'] && ! $len) {
                            throw $this->exception('请输入' . $item['key_name']);
                        }

                        if (empty($value)) {
                            continue;
                        }

                        $text = $type == 'number' ? '数字' : '字';
                        if ($len > $max) {
                            throw $this->exception(sprintf('%s最多输入%d个%s', $item['key_name'], $max, $text));
                        }
                        if ($len < $min) {
                            throw $this->exception(sprintf('%s最少输入%d个%s', $item['key_name'], $min, $text));
                        }
                    }

                    if (in_array($inputType, ['select', 'checked', 'file'])) {
                        if ($type != 'single') {
                            is_array($value) && $len = count($value);
                            if ($item['required'] && (! $value || ! $len)) {
                                throw $this->exception('请选择' . $item['key_name']);
                            }
                        } else {
                            $len = 1;
                            if ($item['required'] && ! $value) {
                                throw $this->exception('请选择' . $item['key_name']);
                            }
                        }

                        if (empty($value)) {
                            continue;
                        }

                        if ($len > $max) {
                            throw $this->exception(sprintf('%s最多选择数量%d', $item['key_name'], $max));
                        }
                        if ($len < $min) {
                            throw $this->exception(sprintf('%s最少选择数量%d', $item['key_name'], $min));
                        }
                    }

                    if ($inputType == 'radio') {
                        if ($item['required'] && ! mb_strlen($value)) {
                            throw $this->exception('请选择' . $item['key_name']);
                        }
                    }

                    if ($inputType == 'date') {
                        if ($item['required'] && ! $value) {
                            throw $this->exception('请选择' . $item['key_name']);
                        }

                        if (empty($value)) {
                            continue;
                        }

                        if ($max && Carbon::parse($value, $tz)->gt(Carbon::parse($max, $tz))) {
                            throw $this->exception(sprintf('%s不能晚于%s', $item['key_name'], $value));
                        }

                        if ($min && Carbon::parse($value, $tz)->lt(Carbon::parse($min, $tz))) {
                            throw $this->exception(sprintf('%s不能早于%s', $item['key_name'], $value));
                        }
                    }

                    if ($inputType == 'oawangeditor') {
                        if ($item['required'] && ! $value) {
                            throw $this->exception('请输入' . $item['key_name']);
                        }

                        if (empty($value)) {
                            continue;
                        }

                        $len = mb_strlen($value);
                        if ($len > 65535) {
                            throw $this->exception(sprintf('最多输入65535个字'));
                        }

                        if ($len < $min) {
                            throw $this->exception(sprintf('最少输入字数%d', $min));
                        }
                    }

                    if ($item['uniqued']) {
                        if ($inputType == 'select' && $type == 'single') {
                            $value = intval(is_array($value) ? ($value[0] ?? 0) : $value);
                        } elseif ($inputType == 'radio') {
                            $value = (int) $value;
                        } elseif (! in_array($inputType, ['date', 'input', 'oawangeditor'])) {
                            sort($value);
                            $value = json_encode($value);
                        }

                        $where = [$key => $value];
                        if ($id) {
                            $where['not_id'] = $id;
                        }
                        if ($service->exists($where)) {
                            throw $this->exception($item['key_name'] . '已存在');
                        }
                    }

                    // save customer notice
                    if (! $force && in_array($key, ['customer_name', 'b37a3f16'])) {
                        $where = [$key => $value];
                        if ($id) {
                            $where['not_id'] = $id;
                        }
                        if ($service->exists($where)) {
                            $msg = $item['key_name'] . '已存在，是否继续添加客户？';
                            throw $this->exception($msg, CodeEnum::VERIFY_CODE);
                        }
                    }
                }
            }
        }
    }

    /**
     * 获取表单数据.
     */
    public function getFormValue(string $type, string $inputType, mixed $value): mixed
    {
        if ($type == 'single' && ! is_array($value)) {
            return $value;
        }

        if ($inputType == 'date' && empty($value)) {
            return null;
        }

        if (! in_array($inputType, ['date', 'input', 'oawangeditor', 'radio'])) {
            if (! is_array($value)) {
                $value = [];
            }
            $value = json_encode($value);
        }
        return $value;
    }

    /**
     * 获取自定义表单导出数据.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getExportField(int $types): array
    {
        $where  = ['types' => $types];
        $field  = ['id', 'title', 'sort', 'types', 'status'];
        $list   = $this->dao->getList($where, $field, 0, 0, 'sort', ['data' => fn ($q) => $q->where('status', 1)->whereNotIn('type', ['file', 'oaWangeditor', 'images'])]);
        $fields = [];
        foreach ($list as $item) {
            $fields = array_merge($fields, $item['data']);
        }
        return $fields;
    }
}
