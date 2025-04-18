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

use App\Constants\CustomEnum\LiaisonEnum;
use App\Http\Service\Attach\AttachService;
use App\Http\Service\Config\FormService;
use App\Http\Service\Config\SalesmanCustomService;
use crmeb\traits\service\ResourceServiceTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 联系人列表.
 */
trait LiaisonListService
{
    use CommonService;
    use ResourceServiceTrait;

    /**
     * 获取列表.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getListByType(int $customType, array $where): array
    {
        if ($customType > LiaisonEnum::LIAISON_VIEWER && $where['eid'] < 1) {
            throw $this->exception('参数错误');
        }

        $uid          = uuid_to_uid($this->uuId(false));
        $fields       = app()->get(FormService::class)->getCustomDataByTypes(LiaisonEnum::LIAISON, ['key', 'key_name', 'input_type', 'type', 'dict_ident']);
        $types        = array_column($fields, 'type', 'key');
        $inputTypes   = array_column($fields, 'input_type', 'key');
        $customFields = app()->get(SalesmanCustomService::class)->getCustomField($uid, $customType, LiaisonEnum::LIST_SELECT);

        $tmpFields  = [];
        $dictField  = $this->getDictField($fields);
        $localField = ['id', 'uid', 'creator_uid', 'created_at', 'updated_at'];
        $field      = array_merge($localField, array_diff(array_intersect(array_column($fields, 'key'), $customFields), $tmpFields));

        $attachService = app()->get(AttachService::class);

        [$page, $limit] = $this->getPageValue();
        $list           = $this->dao->getList($where, $field, $page, $limit);
        $intersects     = array_diff(array_merge($field, array_intersect($customFields, $tmpFields)), $localField);
        $userMap        = $this->getCreatorAndSalesman($intersects, $list);
        foreach ($list as &$item) {
            foreach ($item as $key => $customer) {
                if (! in_array($key, $localField)) {
                    $inputType  = strtolower($inputTypes[$key]);
                    $type       = strtolower($types[$key]);
                    $item[$key] = $this->handleFieldValue($inputType, $type, $customer);
                    if (in_array($key, $customFields) && array_key_exists($key, $dictField)) {
                        $item[$key] = $this->handleDictValue($dictField[$key], $item[$key], $type);
                    }
                }

                if (isset($inputTypes[$key]) && in_array($inputTypes[$key], ['file', 'images'])) {
                    $item[$key] = empty($item[$key]) ? []
                        : $attachService->getListByRelationType(AttachService::RELATION_TYPE_CLIENT, $item[$key]);
                }

                foreach ($intersects as $intersect) {
                    if (! isset($item[$intersect])) {
                        $item[$intersect] = match ($intersect) {
                            'salesman', 'creator' => $userMap[$item['uid']] ?? [],
                            default => ''
                        };
                    }
                }
            }
        }

        $count = $this->dao->count($where);
        return $this->listData($list, $count);
    }

    /**
     * 字段过滤.
     * @return string[]
     */
    public function dictFilterField(): array
    {
        return [];
    }

    public function followUpField(): string
    {
        return '';
    }

    public function followUpService(): string
    {
        return '';
    }

    /**
     * 关注状态
     */
    public function getSubscribeStatus(int $uid, array $ids): array
    {
        return [];
    }

    /**
     * 获取移动端列表.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getUniListByType(array $where): array
    {
        $field          = ['id', 'eid', 'liaison_name', 'liaison_tel', 'e06d7152', 'e06d7159', 'e06d7153', 'l753bf282', 'liaison_job'];
        [$page, $limit] = $this->getPageValue();
        $list           = $this->dao->getList($where, $field, $page, $limit);
        $count          = $this->dao->count($where);
        return $this->listData($list, $count);
    }
}
