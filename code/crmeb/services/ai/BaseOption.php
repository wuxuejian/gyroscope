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

namespace crmeb\services\ai;

use crmeb\exceptions\HttpServiceExceptions;

abstract class BaseOption
{
    public const RULE_SYSTEM = 'system';

    public const RULE_USER = 'user';

    public const RULE_TOOL = 'tool';

    public const RULE_ASSISTANT = 'assistant';

    /**
     * 聊天上下文信息.
     * @var array
     */
    public $messages = [];

    /**
     * 模型名称.
     * @var null
     */
    public $model;

    /**
     * 默认0.95，范围 (0, 1.0]
     * 较高的数值会使输出更加随机，而较低的数值会使其更加集中和确定.
     */
    public ?int $temperature = null;

    /**
     * 默认0.7，取值范围 [0, 1.0]
     * 影响输出文本的多样性，取值越大，生成文本的多样性越强.
     */
    public ?float $topP = null;

    /**
     * 取值范围：[-2.0, 2.0]
     * 值根据迄今为止文本中的现有频率对新token进行惩罚，从而降低模型逐字重复同一行的可能性.
     * @var null
     */
    public $frequencyPenalty;

    /**
     * 取值范围：[-2.0, 2.0]
     * 正值根据token记目前是否出现在文本中来对其进行惩罚，从而增加模型谈论新主题的可能性.
     * @var null
     */
    public $presencePenalty;

    /**
     * 是否以流式接口的形式返回数据.
     * @var bool
     */
    public $stream = false;

    /**
     * @var array
     */
    public $tools = [];

    // 枚举规则
    protected array $roleEnum = [
        self::RULE_SYSTEM,
        self::RULE_USER,
        self::RULE_ASSISTANT,
        self::RULE_TOOL,
    ];

    protected array $options = [];

    /**
     * 设置消息.
     * @return $this
     */
    public function setMessage(string $content, string $role = self::RULE_USER, ?string $toolCallId = null, ?string $name = null, array $toolCalls = [])
    {
        if (! in_array($role, $this->roleEnum)) {
            throw new HttpServiceExceptions('类型错误');
        }
        $message = [
            'role'    => $role,
            'content' => $content,
        ];
        if ($toolCallId) {
            $message['tool_call_id'] = $toolCallId;
        }
        if ($name) {
            $message['name'] = $name;
        }
        if ($toolCalls) {
            $message['tool_calls'] = $toolCalls;
        }
        $this->messages[] = $message;
        return $this;
    }

    /**
     * @return $this
     */
    public function setTool(array $function, string $type = 'function')
    {
        $this->tools[] = [
            'type'     => $type,
            'function' => $function,
        ];
        return $this;
    }

    /**
     * @return $this
     */
    public function options(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return array|array[]
     */
    public function toArray(): array
    {
        $data = [
            'messages' => $this->messages,
        ];
        if ($this->model) {
            $data['model'] = $this->model;
        }
        if (! is_null($this->temperature)) {
            $data['temperature'] = $this->temperature;
        }
        if (! is_null($this->topP)) {
            $data['top_p'] = $this->topP;
        }
        if (! is_null($this->frequencyPenalty)) {
            $data['frequency_penalty'] = $this->frequencyPenalty;
        }
        if (! is_null($this->presencePenalty)) {
            $data['presence_penalty'] = $this->presencePenalty;
        }
        if (! is_null($this->stream)) {
            $data['stream'] = $this->stream;
        }
        if ($this->tools) {
            $data['tools'] = $this->tools;
        }

        if ($this->options) {
            $data = array_merge($data, $this->options);
        }

        return $data;
    }
}
