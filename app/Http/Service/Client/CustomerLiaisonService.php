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

use App\Constants\CustomEnum\LiaisonEnum;
use App\Http\Contract\Client\ClientLiaisonInterface;
use App\Http\Dao\Client\CustomerLiaisonDao;
use App\Http\Service\Attach\AttachService;
use App\Http\Service\BaseService;
use App\Http\Service\Client\Custom\LiaisonListService;
use App\Http\Service\Config\DictDataService;
use App\Http\Service\Config\FormService;
use App\Http\Service\System\RolesService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 联系人.
 * Class CustomerLiaisonService.
 */
class CustomerLiaisonService extends BaseService implements ClientLiaisonInterface
{
    use LiaisonListService;

    /**
     * CustomerLiaisonService constructor.
     */
    public function __construct(CustomerLiaisonDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 保存联系人.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function saveLiaison(array $data, int $eid, string $uuid): mixed
    {
        $formService = app()->get(FormService::class);
        $list        = $formService->getFormDataList(LiaisonEnum::LIAISON);
        $formService->fieldValueCheck($data, LiaisonEnum::LIAISON, 0, $list);

        $attaches = [];

        foreach ($list as $item) {
            $type      = strtolower($item['type']);
            $inputType = strtolower($item['input_type']);
            foreach ($data as $key => &$datum) {
                if ($item['key'] == $key) {
                    if (in_array($inputType, ['file', 'images'])) {
                        $attaches = array_merge($attaches, (array) $datum);
                    }
                    $datum = $formService->getFormValue($type, $inputType, $datum);
                }
            }
        }

        $data['eid'] = $eid;
        $data['uid'] = $data['creator_uid'] = uuid_to_uid($uuid);
        $attaches    = array_filter($attaches);
        return $this->transaction(function () use ($data, $attaches) {
            $res = $this->dao->create($data);
            if (! $res) {
                throw $this->exception('保存失败');
            }

            // save relation attach
            if ($attaches) {
                app()->get(AttachService::class)->update(['id' => $attaches], ['relation_id' => $res->id, 'relation_type' => 8]);
            }

            return $res;
        });
    }

    public function getSearchField(): array
    {
        return [
            ['eid', ''],
        ];
    }

    /**
     * 更新过滤字段.
     */
    public function getUpdateFilterField(): array
    {
        return ['creator_uid', 'uid', 'eid'];
    }

    /**
     * 修改联系人.
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function updateLiaison(array $data, int $id): mixed
    {
        $info = $this->dao->get($id);
        if (! $info) {
            throw $this->exception('数据获取异常');
        }

        $formService = app()->get(FormService::class);

        $list = $formService->getFormDataList(LiaisonEnum::LIAISON);
        $formService->fieldValueCheck($data, LiaisonEnum::LIAISON, $id, $list);

        $attaches = [];

        foreach ($list as $item) {
            $type      = strtolower($item['type']);
            $inputType = strtolower($item['input_type']);
            foreach ($data as $key => &$datum) {
                if ($item['key'] == $key) {
                    if (in_array($inputType, ['file', 'images'])) {
                        $attaches = array_merge($attaches, (array) $datum);
                    }
                    $datum = $formService->getFormValue($type, $inputType, $datum);
                }
            }
        }
        $attaches = array_filter($attaches);
        return $this->transaction(function () use ($id, $data, $attaches) {
            $res = $this->dao->update($id, $data);
            if (! $res) {
                throw $this->exception('保存失败');
            }

            // save relation attach
            if ($attaches) {
                app()->get(AttachService::class)->update(['id' => $attaches], ['relation_id' => $id, 'relation_type' => 8]);
            }

            return $res;
        });
    }

    /**
     * 删除.
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function deleteLiaison(int $id, string $uuid): int
    {
        $uid     = uuid_to_uid($uuid);
        $infoUid = $this->dao->value($id, 'uid');
        if ($infoUid != $uid && ! in_array($infoUid, app()->get(RolesService::class)->getDataUids($uid))) {
            throw $this->exception('common.operation.noPermission');
        }
        return $this->dao->delete($id);
    }

    /**
     * 联系人详情.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getInfo(int $id, string $uuid): mixed
    {
        $info = toArray($this->dao->get($id));
        if (! $info) {
            throw $this->exception('数据获取异常');
        }

        $attachField     = $this->getAttachField();
        $attachService   = app()->get(AttachService::class);
        $dictDataService = app()->get(DictDataService::class);

        $list = app()->get(FormService::class)->getFormDataWithType(LiaisonEnum::LIAISON, false);
        foreach ($list as &$item) {
            foreach ($item['data'] as &$datum) {
                if (array_key_exists($datum['key'], $info)) {
                    $type           = strtolower($datum['type']);
                    $inputType      = strtolower($datum['input_type']);
                    $datum['value'] = $this->handleFieldValue($inputType, $type, $info[$datum['key']]);
                    if ($datum['dict_ident']) {
                        if (is_dimensional_data($datum['value'])) {
                            $datum['value'] = $this->handleDictValue($datum['dict_ident'], $inputType, $datum['value']);
                        } else {
                            $datum['value'] = $dictDataService->getNamesByValue($datum['dict_ident'], $datum['value']);
                        }
                    }
                    if (in_array($inputType, ['file', 'images'])) {
                        $datum['files'] = empty($datum['value']) ? [] :
                            $attachService->getListByRelationType(AttachService::RELATION_TYPE_CLIENT, $datum['value'], $attachField);
                    }
                }
            }
        }

        return $list;
    }

    /**
     * 联系人编辑表单数据.
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getEditInfo(int $id, string $uuid): mixed
    {
        $info = toArray($this->dao->get($id));
        if (! $info) {
            throw $this->exception('数据获取异常');
        }

        $attachField   = $this->getAttachField();
        $attachService = app()->get(AttachService::class);

        $list = app()->get(FormService::class)->getFormDataWithType(LiaisonEnum::LIAISON, platform: $this->getPlatform());
        foreach ($list as &$item) {
            foreach ($item['data'] as &$datum) {
                if (array_key_exists($datum['key'], $info)) {
                    $type           = strtolower($datum['type']);
                    $inputType      = strtolower($datum['input_type']);
                    $datum['value'] = $this->handleFieldValue($inputType, $type, $info[$datum['key']]);
                    if ($inputType == 'select' && $type == 'single') {
                        $datum['value'] = $datum['value'] > 0 ? [$datum['value']] : [];
                    }

                    if (in_array($inputType, ['file', 'images'])) {
                        $datum['files'] = empty($datum['value']) ? [] :
                            $attachService->getListByRelationType(AttachService::RELATION_TYPE_CLIENT, $datum['value'], $attachField);
                    }
                }
            }
        }

        return $list;
    }

    /**
     * 无需同步字段.
     * @return string[]
     */
    public function getOutOfSyncField(): array
    {
        return [];
    }
}
