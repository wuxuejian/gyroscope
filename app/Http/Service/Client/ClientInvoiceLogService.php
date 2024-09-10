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

use App\Http\Dao\Client\ClientInvoiceLogDao;
use App\Http\Model\BaseModel;
use App\Http\Service\BaseService;
use crmeb\traits\service\ResourceServiceTrait;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * 客户发票操作日志
 * Class ClientInvoiceLogService.
 */
class ClientInvoiceLogService extends BaseService
{
    use ResourceServiceTrait;

    /**
     * @var array|string[] 发送方式
     */
    public array $invoiceType = [
        'mail'    => '邮件',
        'express' => '快递',
    ];

    /**
     * @var array|string[] 发票类型
     */
    public array $types = [
        1 => '个人普通发票',
        2 => '企业普通发票',
        3 => '企业专用发票',
    ];

    /**
     * @var array|string[] 操作记录
     */
    public array $operationType = [
        -1 => '撤回开票申请',
        0  => '申请开票',
        1  => '记录开票结果',
        2  => '记录开票结果',
        3  => '申请发票作废',
        4  => '审核发票作废',
        5  => '审核发票作废',
        6  => '撤回发票作废',
        7  => '重新申请开票',
    ];

    public function __construct(ClientInvoiceLogDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取列表.
     * @param array|string[] $field
     * @param string $sort
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function getList(array $where, array $field = ['*'], $sort = 'created_at', array $with = ['card']): array
    {
        [$page, $limit] = $this->getPageValue();
        $list           = $this->dao->getList($where, $field, $page, $limit, $sort, $with);
        foreach ($list as &$item) {
            $item['operation_name'] = $this->operationType[$item['type']] ?? '';
        }
        $count = $this->dao->count($where);
        return $this->listData($list, $count);
    }

    /**
     * 保存操作记录.
     * @return BaseModel|Model
     * @throws BindingResolutionException
     */
    public function saveRecord(int $entId, int $invoiceId, int $uid, int $type, array $param)
    {
        if (! isset($this->operationType[$type])) {
            throw $this->exception('操作类型错误');
        }

        $res = $this->dao->create([
            'uid'        => $uid,
            'type'       => $type,
            'entid'      => $entId,
            'invoice_id' => $invoiceId,
            'operation'  => $this->generatorOperation($invoiceId, $type, $param),
        ]);
        if (! $res) {
            throw $this->exception('操作记录添加失败');
        }
        return $res;
    }

    /**
     * 生成操作记录内容.
     * @throws BindingResolutionException
     */
    public function generatorOperation(int $invoiceId, int $type, array $param): array
    {
        return match ($type) {
            -1 => [
                ['name' => '备注：', 'val' => $param['remark'] ?? ''],
            ],
            0 => [],
            1 => $this->getInvoiceResult($invoiceId, $param),
            2 => [
                ['name' => '开票结果：', 'val' => '拒绝'],
                ['name' => '拒绝原因：', 'val' => $param['remark'] ?? ''],
            ],
            3 => [
                ['name' => '作废原因：', 'val' => $param['card_remark'] ?? ''],
            ],
            4 => [
                ['name' => '审核结果：', 'val' => '已作废'],
                ['name' => '备注：', 'val' => $param['finance_remark'] ?? ''],
            ],
            5 => [
                ['name' => '审核结果：', 'val' => '已拒绝'],
                ['name' => '拒绝原因：', 'val' => $param['finance_remark'] ?? ''],
            ],
            6       => [],
            7       => $this->getInvoiceChange($invoiceId, $param),
            default => [],
        };
    }

    /**
     * 获取开票结果.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getInvoiceResult(int $invoiceId, array $param): array
    {
        $invoiceType = $param['invoice_type'] ?? '';
        $record      = [
            ['name' => '开票结果：', 'val' => '已开票'],
            ['name' => '发送方式：', 'val' => $this->invoiceType[$invoiceType] ?? ''],
        ];

        if ($invoiceType == 'express') {
            $record[] = ['name' => '联系人：', 'val' => $param['collect_name'] ?? ''];
            $record[] = ['name' => '联系电话：', 'val' => $param['collect_tel'] ?? ''];
        }
        $record[] = [
            'name' => $param['invoice_type'] == 'mail' ? '邮箱地址：' : '邮寄地址：',
            'val'  => $param['invoice_address'] ?? '',
        ];
        $record[] = [
            'name' => '开票备注：',
            'val'  => $param['remark'] ?? '',
        ];
        $info = app()->get(ClientInvoiceService::class)->get($invoiceId, ['*'], ['attachs'])->toArray();
        $file = array_column($info['attachs'], 'thumb_dir');
        if ($file) {
            $record[] = ['name' => '开票凭证：', 'val' => $file];
        }
        return $record;
    }

    /**
     * 获取发票变更.
     * @throws BindingResolutionException
     */
    public function getInvoiceChange(int $invoiceId, array $before): ?array
    {
        $record = [];
        $info   = app()->get(ClientInvoiceService::class)->get($invoiceId, ['*'], ['category', 'clientBill'])->toArray();

        $beforeBillNo = array_column($before['client_bill'], 'bill_no');
        $afterBillNo  = array_column($info['client_bill'], 'bill_no');
        $beforeBillNo != $afterBillNo
        && $record[] = ['name' => '付款单号：', 'val' => implode('、', $beforeBillNo) . ' 改为 ' . implode('、', $afterBillNo)];

        $before['category_id'] != $info['category_id']
        && $record[] = ['name' => '发票类目：', 'val' => ($before['category']['name'] ?? '') . ' 改为 ' . ($info['category']['name'] ?? '')];

        $before['bill_date'] != $info['bill_date']
        && $record[] = ['name' => '期望开票日期：', 'val' => $before['bill_date'] . ' 改为 ' . $info['bill_date']];

        $before['price'] != $info['price']
        && $record[] = ['name' => '开票金额：', 'val' => $before['price'] . ' 改为 ' . $info['price']];

        $before['amount'] != $info['amount']
        && $record[] = ['name' => '发票金额：', 'val' => $before['amount'] . ' 改为 ' . $info['amount']];

        $before['types'] != $info['types']
        && $record[] = ['name' => '发票金额：', 'val' => ($this->types[$before['types']] ?? '') . ' 改为 ' . $this->types[$info['types']] ?? ''];

        $before['bank'] != $info['bank']
        && $record[] = ['name' => '开户行：', 'val' => $before['bank'] . ' 改为 ' . $info['bank']];

        $before['account'] != $info['account']
        && $record[] = ['name' => '开户账号：', 'val' => $before['account'] . ' 改为 ' . $info['account']];

        $before['address'] != $info['address']
        && $record[] = ['name' => '开票地址：', 'val' => $before['address'] . ' 改为 ' . $info['address']];

        $before['tel'] != $info['tel']
        && $record[] = ['name' => '电话：', 'val' => $before['tel'] . ' 改为 ' . $info['tel']];

        $before['title'] != $info['title']
        && $record[] = ['name' => '发票抬头：', 'val' => $before['title'] . ' 改为 ' . $info['title']];

        $before['ident'] != $info['ident']
        && $record[] = ['name' => '纳税人识别号：', 'val' => $before['ident'] . ' 改为 ' . $info['ident']];

        $before['collect_type'] != $info['collect_type']
        && $record[] = ['name' => '发送方式：', 'val' => $this->invoiceType[$before['collect_type']] . ' 改为 ' . $this->invoiceType[$info['collect_type']]];

        $before['collect_name'] != $info['collect_name']
        && $record[] = ['name' => '联系人：', 'val' => $before['collect_name'] . ' 改为 ' . $info['collect_name']];

        $before['collect_tel'] != $info['collect_tel']
        && $record[] = ['name' => '联系电话：', 'val' => $before['collect_tel'] . ' 改为 ' . $info['collect_tel']];

        $before['collect_email'] != $info['collect_email']
        && $record[] = ['name' => '邮箱地址：', 'val' => $before['collect_email'] . ' 改为 ' . $info['collect_email']];

        $before['mail_address'] != $info['mail_address']
        && $record[] = ['name' => '邮寄地址：', 'val' => $before['mail_address'] . ' 改为 ' . $info['mail_address']];
        return $record;
    }
}
