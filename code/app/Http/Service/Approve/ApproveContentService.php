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

use App\Http\Dao\Approve\ApproveContentDao;
use App\Http\Service\BaseService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 申请内容表
 * Class ApproveContentService.
 * @method null|array|Model select($where, array $field = [], array $with = [], int $page = 0, int $limit = 0) 获取多条数据
 */
class ApproveContentService extends BaseService
{
    protected array $blackSymbol = [
        'billId',
    ];

    public function __construct(ApproveContentDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 批量保存数据.
     * @param mixed $data
     * @param mixed $applyId
     * @return bool
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function saveMore($data, $applyId)
    {
        $uniqueds = [];
        foreach ($data as $val) {
            unset($val['id']);
            if ($this->dao->exists(['apply_id' => $applyId, 'uniqued' => $val['uniqued']])) {
                $this->dao->update(['apply_id' => $applyId, 'uniqued' => $val['uniqued']], $val);
            } else {
                $val['apply_id'] = $applyId;
            }
            $this->dao->create($val);
            $uniqueds[] = $val['uniqued'];
        }
        $this->dao->delete(['apply_id' => $applyId, 'not_uniqued' => $uniqueds]);
        return true;
    }

    /**
     * 获取审批内容.
     * @param mixed $filter
     * @param mixed $applyId
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     * @throws BindingResolutionException
     */
    public function getContent($applyId, $filter = [])
    {
        $list = $this->dao->select(['apply_id' => $applyId]);
        if (! $list) {
            return [];
        }
        $content = [];
        foreach ($list as $item) {
            if ($filter && in_array($item->types, $filter)) {
                continue;
            }
            $val            = [];
            $val['uniqued'] = $item['uniqued'];
            $val['type']    = $item['types'];
            if ($item->symbol && method_exists(ApproveAssistService::class, $item->symbol)) {
                if (in_array($item->symbol, $this->blackSymbol)) {
                    continue;
                }

                $data = $item->value;
                if ($item->symbol == 'attendanceExceptionRecord') {
                    $data = [
                        'value'    => $item->value,
                        'apply_id' => $applyId,
                    ];
                }

                $val['value'] = app()->get(ApproveAssistService::class)->{$item->symbol}(uid: $item->user_id, data: [
                    'customer_id' => 0,
                    'bill_id'     => [],
                    'invoice_id'  => 0,
                    'contract_id' => 0,
                ], child: [], value: $data);
            } else {
                if ($item->types == 'uploadFrom') {
                    $val['value'] = $item->value;
                } elseif (is_array($item->value)) {
                    $val['value'] = implode('，', array_column($item->value, 'name'));
                } else {
                    $val['value'] = $item->value;
                }
            }
            if (is_array($val['value']) && $item->types != 'uploadFrom') {
                foreach ($val['value'] as $v) {
                    $content[] = $v;
                }
            } elseif (isset($item->content['title']) && $item->content['title']) {
                $val['label'] = $item->content['title'] ?: '';
                $val['value'] = $val['value'] ?: ($item->content['children'][0] ?? '');
                $content[]    = $val;
            } elseif (isset($item->content['props'])) {
                $val['label'] = $item->content['props']['titleIpt'];
                if (is_array($val['value']) && $val['value']) {
                    $val['value'] = $val['value']['duration'] . ($val['value']['timeType'] == 'day' ? '天' : '小时');
                } else {
                    $val['value'] = '';
                }
                $content[] = $val;
            }
        }
        return $content;
    }
}
