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

namespace App\Http\Service\Client\Custom;

use App\Constants\CustomEnum\ContractEnum;
use App\Constants\CustomEnum\CustomerEnum;
use App\Constants\UserAgentEnum;
use App\Http\Service\Admin\AdminService;
use App\Http\Service\Client\CustomerService;
use App\Http\Service\Config\DictDataService;
use App\Http\Service\Config\FormService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

trait CommonService
{
    protected string $platform = UserAgentEnum::ADMIN_AGENT;

    public function getPlatform(): string
    {
        if (! $this->platform) {
            $this->platform = UserAgentEnum::ADMIN_AGENT;
        }
        return $this->platform;
    }

    public function getFollowUpData(array $intersects, array $list, int $uid): array
    {
        $followUpField = $this->followUpField();
        if (! $followUpField) {
            return [];
        }

        return in_array($followUpField, $intersects)
            ? $this->getFollowStatus($uid, array_column($list, 'id')) : [];
    }

    /**
     * 关注状态
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function getFollowStatus(int $uid, array $ids, bool $isSubscribe = false): array
    {
        if (! $ids) {
            return [];
        }

        $followMap = [];
        if ($isSubscribe) {
            foreach ($ids as $item) {
                $followMap[$item] = 1;
            }
        } else {
            $followMap = $this->getSubscribeStatus($uid, $ids);
        }
        return $followMap;
    }

    /**
     * 业务员或创建人.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getCreatorAndSalesman(array $intersects, array $list): array
    {
        $ids = $userMap = [];
        if (in_array('salesman', $intersects) || in_array('creator', $intersects)) {
            $ids = array_merge(array_column($list, 'uid'), array_column($list, 'creator_uid'));
        }

        if (in_array('before_salesman', $intersects)) {
            $ids = array_merge(array_column($list, 'before_uid'), $ids);
        }

        if (empty($ids)) {
            return $userMap;
        }
        $list = app()->get(AdminService::class)->select(['id' => array_unique($ids)], ['id', 'avatar', 'name'])->toArray();
        foreach ($list as $item) {
            $userMap[$item['id']] = $item;
        }

        return $userMap;
    }

    /**
     * 处理字典数据.
     */
    public function handleDataValue(mixed $value): array|int
    {
        return is_array($value)
            ? array_map(fn ($v) => is_array($v) ? array_map('intval', $v) : (int) $v, $value) : (int) $value;
    }

    /**
     * 处理字段数据.
     * @return array|int|mixed
     */
    public function handleFieldValue(string $inputType, string $type, mixed $value): mixed
    {
        if (! $inputType && ! $type) {
            return $value;
        }

        if ($inputType == 'oawangeditor') {
            return $value ? htmlspecialchars_decode($value) : '';
        }

        if ($type == 'single') {
            $tmp = json_decode((string) $value, true);
            if (is_array($tmp) && ! empty($tmp)) {
                return $tmp;
            }
            return $value;
        }

        if (in_array($inputType, ['date', 'input', 'radio'])) {
            return $value;
        }

        if (in_array($inputType, ['file', 'images'])) {
            return $this->handleDataValue(json_decode($value, true));
        }

        return $value ? json_decode($value, true) : [];
    }

    /**
     * 获取字典字段.
     */
    public function getDictField(array $fields): array
    {
        $dictField = array_filter(array_column($fields, 'dict_ident', 'key'));

        $callback = function ($dictField) {
            $filterField = $this->dictFilterField();
            if ($filterField) {
                foreach ($filterField as $field) {
                    if (array_key_exists($field, $dictField)) {
                        unset($dictField[$field]);
                    }
                }
            }
            return $dictField;
        };
        return $this->getPlatform() == UserAgentEnum::ADMIN_AGENT && $dictField ? $callback($dictField) : $dictField;
    }

    /**
     * 获取字典回显.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getNameListByIdent(string $ident, mixed $value): array
    {
        $dictDataService = app()->get(DictDataService::class);
        if (! is_array($value)) {
            $data = $dictDataService->get(['dict_value' => $value, 'type_name' => $ident], ['is_default']);
            if ($data && $data->is_default == 1) {
                return [$value];
            }
        }
        return $dictDataService->getNamesByValue($ident, $value);
    }

    /**
     * 时间查询.
     * @param mixed $dao
     * @param mixed $field
     * @param mixed $value
     * @return mixed|void
     */
    public function getDateSearch($dao, $field, $value)
    {
        if (str_contains($value, '-')) {
            [$startTime, $endTime] = explode('-', $value);
            $startTime             = str_replace('/', '-', trim($startTime));
            $endTime               = str_replace('/', '-', trim($endTime));
            if (! str_contains($startTime, ':') && ! str_contains($endTime, ':')) {
                $endDate = Carbon::parse($endTime)->timezone(config('app.timezone'))->addDay()->toDateString();
                return $dao->whereDate($field, '>=', $startTime)->whereDate($field, '<', $endDate);
            }
            if ($startTime && $endTime && $startTime != $endTime) {
                return $dao->whereBetween($field, [$startTime, $endTime]);
            }
            if ($startTime && $endTime && $startTime == $endTime) {
                return $dao->whereBetween($field, [$startTime, date('Y-m-d H:i:s', strtotime($endTime) + 86400)]);
            }
            if (! $startTime && $endTime) {
                return $dao->whereTime($field, '<', $endTime);
            }
            if ($startTime && ! $endTime) {
                return $dao->whereTime($field, '>=', $startTime);
            }
        } elseif (preg_match('/^lately+[1-9]{1,3}/', $value)) {
            // 最近天数 lately[1-9] 任意天数
            $day = (int) str_replace('lately', '', $value);
            if ($day) {
                return $dao->whereBetween($field, [Carbon::today()->subDays($day)->toDateTimeString(), Carbon::today()->toDateTimeString()]);
            }
        } else {
            return $dao->whereDate($field, $value);
        }
    }

    /**
     * 获取列表客户数据.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getCustomerData(array $list): array
    {
        $map = [];

        if (! $list) {
            return $map;
        }

        $ids = array_column($list, 'eid');
        if (! $ids) {
            return $map;
        }

        $list = app()->get(CustomerService::class)->select(['id' => $ids], ['id', 'customer_name'])->toArray();
        foreach ($list as $item) {
            $map[$item['id']] = $item;
        }
        return $map;
    }

    public function setPlatform(string $platform): mixed
    {
        $this->platform = $platform;
        return $this;
    }

    /**
     * 获取附件字段.
     */
    public function getAttachField(): array
    {
        return match ($this->platform) {
            UserAgentEnum::ADMIN_AGENT => ['id', 'att_dir as url', 'real_name as name', 'att_size as size', 'att_type as type'],
            default                    => ['id', 'att_dir', 'att_size', 'real_name'],
        };
    }

    /**
     * 获取字典数据回显.
     * @return array|mixed|string
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function handleDictValue(string $ident, mixed $data, string $type = 'text', string $inputType = ''): mixed
    {
        if (is_dimensional_data($data)) {
            $val = [];
            foreach ($data as $itemVal) {
                $val[] = $this->getNameListByIdent($ident, $itemVal);
            }
            return $type == 'multiple' ? call_user_func_array('array_merge', $val) : $val;
        }
        $val = $this->getNameListByIdent($ident, $data);
        return $inputType == 'radio' ? (end($val) ?? '') : $val;
    }

    private function getWhere($where, $types)
    {
        $fieldSet = app()->get(FormService::class)->getCustomDataByTypes($types, ['key as field', 'input_type', 'type']);
        $fieldSet = match ((int) $where['types']) {
            1 => array_merge($fieldSet, CustomerEnum::CUSTOMER_SEARCH_FIELD, CustomerEnum::CUSTOMER_VIEWER_SEARCH_FIELD),
            2 => array_merge($fieldSet, CustomerEnum::CUSTOMER_SEARCH_FIELD, CustomerEnum::CUSTOMER_CHARGE_SEARCH_FIELD),
            3 => array_merge($fieldSet, CustomerEnum::CUSTOMER_SEARCH_FIELD, CustomerEnum::CUSTOMER_HEIGHT_SEAS_SEARCH_FIELD),
            5 => array_merge(array_filter($fieldSet, function ($item) {
                return ! in_array(strtolower($item['field']), ['contract_customer']);
            }), ContractEnum::CONTRACT_SEARCH_FIELD, ContractEnum::CONTRACT_VIEWER_SEARCH_FIELD),
            default => array_merge(array_filter(
                $fieldSet,
                function ($item) {
                    return ! in_array(strtolower($item['field']), ['contract_customer']);
                }
            ), ContractEnum::CONTRACT_SEARCH_FIELD, ContractEnum::CONTRACT_CHARGE_SEARCH_FIELD),
        };
        $field = array_keys($where);
        foreach ($fieldSet as $value) {
            if (isset($where[$value['field']])) {
                if ($where[$value['field']] === '') {
                    unset($where[$value['field']]);
                } elseif (in_array($value['field'], $field)) {
                    $type                   = $value['type'] ?? '';
                    $where[$value['field']] = [
                        'input_type' => $value['input_type'],
                        'value'      => $type == 'multiple' ? call_user_func_array('array_merge', $where[$value['field']]) : $where[$value['field']],
                        'type'       => $type,
                    ];
                }
            }
        }
        return $where;
    }

    private function getInputSearch($dao, $field, $value)
    {
        if (method_exists($this, 'search' . Str::studly($field))) {
            return $this->{'search' . Str::studly($field)}($dao, $value);
        }
        return $dao->where($field, 'like', "%{$value}%");
    }

    private function getSelectSearch($dao, $field, $value)
    {
        if (method_exists($this, 'search' . Str::studly($field))) {
            return $this->{'search' . Str::studly($field)}($dao, $value);
        }
        return is_array($value) ? $dao->whereIn($field, $value) : $dao->where($field, $value);
    }

    private function getPersonnelSearch($dao, $field, $value)
    {
        if (method_exists($this, 'search' . Str::studly($field))) {
            return $this->{'search' . Str::studly($field)}($dao, $value);
        }
        return $dao->whereIn($field, $value);
    }

    private function getMoreSelectSearch(mixed $dao, int|string $field, mixed $value, mixed $type)
    {
        if ($type == 'multiple') {
            return $this->getMultipleSelectSearch($dao, $field, $value);
        }
        return $this->getSelectSearch($dao, $field, $value);
    }

    private function getMultipleSelectSearch($dao, $field, $value)
    {
        if (method_exists($this, 'search' . Str::studly($field))) {
            return $this->{'search' . Str::studly($field)}($dao, $value);
        }
        if (is_array($value)) {
            $dao->where(function ($q) use ($value, $field) {
                foreach ($value as $v) {
                    $q->orWhere($field, 'like', "%\"{$v}\"%");
                }
            });
        } else {
            $dao->where($field, 'like', "%\"{$value}\"%");
        }
        return $dao;
    }
}
