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

namespace App\Task\message;

use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Support\Facades\Log;

/**
 * 消息发送job
 * Class MessageSendTask.
 */
class MessageSendTask extends Task
{
    /**
     * MessageSendJob constructor.
     * @param mixed $setDelay
     * @param mixed $entid
     * @param mixed $i
     * @param mixed $type
     */
    public function __construct(protected $entid, protected $i, protected $type, protected array $toUid = [], protected array $bathTo = [], protected array $phone = [], protected array $params = [], protected array $other = [], protected int $linkId = 0, protected ?int $linkStatus = 0, protected $setDelay = '') {}

    public function handle()
    {
        try {
            $message = message()->ent($this->entid)->i($this->i);
            if ($this->toUid) {
                $message->to($this->toUid);
            }
            if ($this->bathTo) {
                $message->bathTo($this->bathTo);
            }
            if ($this->phone) {
                $message->setPhone($this->phone);
            }
            if ($this->setDelay) {
                $message->delay($this->setDelay);
            }
            $message->sendMessage($this->type, $this->params, $this->other, $this->linkId, $this->linkStatus);
        } catch (\Throwable $e) {
            Log::error(__CLASS__ . ':' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'data' => [
                'entid'      => $this->entid,
                'i'          => $this->i,
                'type'       => $this->type,
                'toUid'      => $this->toUid,
                'bathTo'     => $this->bathTo,
                'phone'      => $this->phone,
                'params'     => $this->params,
                'other'      => $this->other,
                'linkId'     => $this->linkId,
                'linkStatus' => $this->linkStatus,
                'setDelay'   => $this->setDelay,
            ]]);
        }
    }

    public function failed(\Throwable $e)
    {
        Log::error('消息发送队列执行失败：' . json_encode([
            'msg'   => $e->getMessage(),
            'line'  => $e->getLine(),
            'file'  => $e->getFile(),
            'trace' => $e->getTrace(),
        ]));
    }
}
