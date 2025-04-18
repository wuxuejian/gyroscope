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

namespace App\Http\Service\Chat;

use App\Http\Dao\Chat\ChatHistoryDao;
use App\Http\Model\BaseModel;
use App\Http\Model\Chat\ChatApplications;
use App\Http\Model\Chat\ChatModels;
use App\Http\Service\Admin\AdminService;
use App\Http\Service\BaseService;
use crmeb\services\ai\BaidubceOption;
use crmeb\services\ai\BaseCurl;
use crmeb\services\ai\BaseOption;
use crmeb\services\ai\DeepseekOption;
use crmeb\services\SmsService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Swoole\Http\Response as SwooleResponse;

use function Ramsey\Uuid\v4;

class ChatHistoryService extends BaseService
{
    public const CHAT_HISTORY_TABLE = 'chat_history';

    public function __construct(ChatHistoryDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取置顶对话列表.
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getTopUpList(int $userId, array $field = ['*'])
    {
        return $this->dao->topUpModel($userId)->select($field)->orderBy('top_up', 'asc')->get()->toArray();
    }

    /**
     * 创建历史对话.
     * @return BaseModel|Model
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function saveHistory(int $applicationId, string $title, int $userId)
    {
        return $this->dao->create([
            'user_id'             => $userId,
            'chat_application_id' => $applicationId,
            'title'               => $title,
        ]);
    }

    /**
     * @return array
     * @throws BindingResolutionException
     */
    public function runSql(ChatApplications $appInfo, string $message, array $recordData = [], bool $interrupt = true, array $saveData = [])
    {
        $listData = [];
        $run      = false;
        // 进行数据库的查询
        try {
            $res = (new SmsService())->dialog([
                [
                    'content' => $appInfo->content,
                    'role'    => 'system',
                ],
                [
                    'content' => $message,
                    'role'    => 'user',
                ],
            ]);

            $this->sendMessage('输出res：' . json_encode($res), 'info');

            $sql                    = $res['data']['data']['choices'][0]['message']['content'] ?? '';
            $sql                    = str_replace(['```sql', '```', 'json'], '', $sql);
            $sql                    = trim(str_replace("\n", '', $sql));
            $recordData['sql_text'] = $sql;

            $this->sendMessage('输出sql：' . $sql, 'info');

            $saveData['recordData'] = $recordData;
            $sql                    = $sql ? json_decode($sql, true) : [];
            if (! empty($sql['list_sql'])) {
                try {
                    if (str_contains($sql['list_sql'], '${page}')) {
                        $this->sendMessage($recordData + ['is_page' => 1], 'data');
                    }

                    $sql['list_sql']  = str_replace(['${page}', '${limit}'], [0, 10], $sql['list_sql']);
                    $listData['list'] = DB::select($sql['list_sql']);

                    if ($sql['page_sql']) {
                        $listData['totalNum'] = DB::select($sql['page_sql']);
                    }

                    $this->sendMessage($listData, 'info');

                    $run = true;
                } catch (\Throwable $e) {
                    $interrupt && $this->sendMessage($e->getMessage() . '|' . $e->getLine() . '|' . $e->getFile(), 'error', $interrupt ? $saveData : []);
                }
            } else {
                $interrupt && $this->sendMessage('没有查询到数据', 'error', $interrupt ? $saveData : []);
            }
        } catch (\Throwable $e) {
            $interrupt && $this->sendMessage($e->getMessage(), 'error', $interrupt ? $saveData : []);
        }

        return [$recordData, $run, $listData];
    }

    /**
     * 执行sql用当前模型执行.
     * @return array
     * @throws BindingResolutionException
     */
    public function runSqlV2(ChatApplications $appInfo, BaseOption $option, string $key, string $message, array $recordData = [], bool $interrupt = true, array $saveData = [], array $userInfo = [])
    {
        $listData = [
            'list'     => [],
            'totalNum' => [],
        ];
        $run = false;
        // 我的名字
        $myNmae       = $userInfo['name'] ?? '';
        $nowTime      = date('Y-m-d H:i:s');
        $nl2sqlPrompt = <<<EOT

**任务类型**: NL2SQL转换

**目标数据库**: MySQL 5.7

**查询类型**: SELECT语句

**安全要求**:

- 操作限制: 仅允许SELECT查询

- 禁止项: 存储过程、自定义函数

- 防护机制: SQL注入防护（参数化处理）

- 无效处理: 无关问题返回空SQL

**查询规则**:

- 表别名: 必须使用，简洁且具有描述性（如`table_name AS t`）

- 字段引用: 必须包含表别名（如`t.field_name`）

- 用户标识查询:
  - 方式: 通过子查询从`_admin`表的`name`字段获取`id`
  - 示例: 用户名为“小寇”时，子查询为`(SELECT id FROM eb_admin WHERE name = '小寇')`

- 个人数据查询: 我的名字是 {$myNmae} ,使用上述子查询结果作为条件

- 多表连接: 必须指定JOIN条件

- 排序与分页:
  - 排序: 指定字段及顺序（ASC/DESC）
  - 分页: 使用`LIMIT \${page}, \${limit}`

- 模糊查询: 使用 LIKE 时需要转义特殊字符，使用 CONCAT('%', 参数, '%')

- 时间查询：
  - 当前时间: {$nowTime}
  - 时间范围： 当用户查询只说月、周、日的时候，默认查询当前时间之前最近年、月、周、日的数据

**性能优化建议**:

- 字段选择: 避免使用`SELECT *`，明确指定所需字段

- 查询方式: 优先使用JOIN替代子查询

- 索引利用: WHERE条件中考虑索引使用

- 索引提示: 复杂查询中可使用索引提示（如FORCE INDEX）

- 字段类型匹配: JOIN操作时确保关联字段类型匹配

- COUNT查询优化: 避免使用子查询进行统计

- SUM查询优化: 聚合查询别名需具有描述性


**数据处理注意事项**:

- 数值类型: 确保类型匹配，避免精度损失

- 字符串处理: 使用合适的字符集和排序规则

- NULL值处理: 明确比较逻辑，避免遗漏或误判

{$appInfo->content}

**返回格式必须为json格式**:
```json
{
    "list_sql": "具体的SELECT查询语句",
    "page_sql": "用于统计总条数的SELECT COUNT(*)查询语句，别名需为count_nums",
    "table_fields": {
        "字段名": "字段的中文名称"
    }
}
EOT;
        // 进行数据库的查询
        try {
            $option           = clone $option;
            $option->messages = [
                [
                    'role'    => BaseOption::RULE_SYSTEM,
                    'content' => $nl2sqlPrompt,
                ],
                [
                    'content' => $message,
                    'role'    => BaseOption::RULE_USER,
                ],
            ];
            // 设置非流式返回
            $option->stream        = false;
            $option->streamOptions = [
                'include_usage' => false,
            ];

            $curl = new BaseCurl($key);
            // 向AI模型发送请求
            $res = $curl->setBody($option)->send(url: $option->url);
            // 输出请求结果
            $this->sendMessage('输出res：' . json_encode($res), 'info');

            // 提取并清理 SQL 内容
            $sqlContent = $res['choices'][0]['message']['content'] ?? '';
            $sqlContent = str_replace(['```sql', '```', 'json'], '', $sqlContent);
            $sqlContent = trim(str_replace("\n", '', $sqlContent));

            // 准备保存的数据
            $recordData['sql_text'] = $sqlContent;
            $saveData['recordData'] = $recordData;

            // 输出sql 信息
            $this->sendMessage('输出sql：' . $sqlContent, 'info');

            // 解析sql
            $sql = $sqlContent ? json_decode($sqlContent, true) : [];
            // 定义危险关键字的正则表达式
            $ref = '/\b(UPDATE|DELETE|INSERT|DROP|ALTER)\b/i';

            // 判断是否包含危险关键字
            if (isset($sql['list_sql']) && ! empty($sql['list_sql']) && ! preg_match($ref, $sql['list_sql'])) {
                $listSql = $sql['list_sql'];
                try {
                    // 判断是否包含分页
                    $isPage = str_contains($listSql, '${page}');
                    // 如果没有分页，则添加分页
                    if (! $isPage && ! str_contains($listSql, 'LIMIT')) {
                        // 如果最后一位字符串是；号则切割掉
                        if (str_ends_with($listSql, ';')) {
                            $listSql = substr($listSql, 0, -1);
                        }
                        $listSql .= ' LIMIT ${page},${limit}';
                        $isPage                 = true;
                        $recordData['sql_text'] = json_encode($sql);
                    }
                    // 替换占位符为实际值
                    $listSql = str_replace(['${page}', '${limit}'], [0, 10], $listSql);
                    $this->sendMessage('查询sql：' . $listSql, 'info');
                    // 执行参数化查询
                    $listData['list'] = DB::select($listSql);

                    // 如果包含page_sql，则执行总条数查询
                    if (isset($sql['page_sql']) && ! empty($sql['page_sql']) && ! preg_match($ref, $sql['page_sql'])) {
                        // 执行总条数查询
                        $this->sendMessage('查询条数sql：' . $sql['page_sql'], 'info');
                        $listData['totalNum'] = DB::select($sql['page_sql']);
                    }
                    // 如果查询结果大于等于10条，则标记为分页
                    if ($isPage && count($listData['list']) >= 10) {
                        // 标记为分页
                        $this->sendMessage($recordData + ['is_page' => 1], 'data');
                    }
                    // 输出查询结果
                    $this->sendMessage($listData, 'info');

                    $run = true;
                } catch (\Throwable $e) {
                    $this->sendMessage($e->getMessage() . '|' . $e->getLine() . '|' . $e->getFile(), $interrupt ? 'error' : 'info', $interrupt ? $saveData : []);
                }
            } else {
                // 没有获取到sql语句
                $this->sendMessage('没有查询到数据', $interrupt ? 'error' : 'info', $interrupt ? $saveData : []);
            }
        } catch (\Throwable $e) {
            $this->sendMessage($e->getMessage(), $interrupt ? 'error' : 'info', $interrupt ? $saveData : []);
        }

        return [$recordData, $run, $listData];
    }

    /**
     * 中断对话.
     * @return bool
     * @throws InvalidArgumentException
     */
    public function interrupt(string $chatRecordUuid)
    {
        return Cache::tags([self::CHAT_HISTORY_TABLE])->set($chatRecordUuid, 'stop', 8400);
    }

    /**
     * 清理对话记录.
     * @return mixed
     * @throws BindingResolutionException
     */
    public function cleanUpDialog(int $historyId)
    {
        $chatRecordService = app()->make(ChatRecordService::class);

        return $chatRecordService->delete(['chat_history_id' => $historyId]);
    }

    /**
     * 对话 优点执行sql快；缺点每次需要先请求一号通，消耗的token也会增长，请求时效底；
     * 先访问一号通-》然后通过返回的结果判断是否执行sql-》是-》执行sql-》把数据交给模型进行整理数据
     * ------------------------------------------》否-》把用户的提问交给模型进行回复.
     * @return bool
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function dialogV2(string $message, int $historyId, int $userId, ?string $chatRecordUuid = null, int $isShow = 1)
    {
        $chatRecordService = app()->make(ChatRecordService::class);
        $startTime         = microtime(true);

        if ($chatRecordUuid) {
            $record['chat_record_uuid'] = $chatRecordUuid;
        } else {
            $record = [];
        }

        $saveData = [
            'userId'         => $userId,
            'historyId'      => $historyId,
            'chatRecordUuid' => $chatRecordUuid,
            'recordData'     => $record,
            'startTime'      => $startTime,
            'message'        => $message,
        ];

        $chatApplicationId = $this->dao->value(['id' => $historyId], 'chat_application_id');
        if (! $chatApplicationId) {
            return $this->sendMessage('没有查询到应用ID', 'error', $saveData);
        }

        $appInfo = app()->make(ChatApplicationsService::class)->get($chatApplicationId);
        if (! $appInfo) {
            return $this->sendMessage('没有查询到应用信息', 'error', $saveData);
        }
        if ($appInfo->member_id && ! in_array($userId, $appInfo->member_id)) {
            return $this->sendMessage('暂无权限访问此应用', 'error', $saveData);
        }
        if ($appInfo->use_limit && $chatRecordService->getDialogNum($userId, $appInfo->id, $appInfo->use_limit)) {
            return $this->sendMessage('今日使用已达到上限', 'error', $saveData);
        }

        $modelsInfo = app()->make(ChatModelsService::class)->get($appInfo['models_id']);
        if (! $modelsInfo) {
            return $this->sendMessage('没有查询到模型信息', 'error', $saveData);
        }
        if (! $modelsInfo->key) {
            return $this->sendMessage('模型配置有问题，请检查模型配置', 'error', $saveData);
        }

        $option = $this->option($modelsInfo->provider);

        // 放入其他参数
        if ($appInfo->json) {
            $other = [];
            foreach ((array) $appInfo->json as $item) {
                if (isset($item['field'], $item['value'])) {
                    $other[$item['field']] = $item['value'];
                }
            }
            if ($other) {
                $option->options($other);
            }
        }

        $systemMessage = $appInfo->tooltip_text . " **今天的日期：\r" . date('Y-m-d H:i:s');

        if ($systemMessage) {
            $option->setMessage($systemMessage, 'system');
        }

        // 加入历史对话内容
        if ($appInfo->count_number) {
            $chatRecordList = $chatRecordService->getDialogRecord($historyId, $appInfo->count_number);
            if ($chatRecordList) {
                foreach ($chatRecordList as $item) {
                    if (! $item['problem_text'] || ! $item['answer_text']) {
                        continue;
                    }
                    $option->setMessage($item['problem_text']);
                    $option->setMessage($item['answer_text'], BaseOption::RULE_ASSISTANT);
                }
            }
        }

        if ($chatRecordUuid) {
            $recordData = $chatRecordService->get(['chat_record_uuid' => $chatRecordUuid])?->toArray();
            if (! $recordData) {
                return $this->sendMessage('没查询到记录，无法从新生成', 'error', $saveData);
            }
        } else {
            $recordData = [
                'chat_record_uuid'     => v4(),
                'is_show'              => $isShow,
                'chat_applications_id' => $appInfo->id,
            ];
            $this->sendMessage($recordData, 'data');
        }

        // 数据库查询功能
        if ($appInfo->is_table && array_filter($appInfo->keyword, function ($keyword) use ($message) {
            return str_contains($message, $keyword);
        })
        ) {
            $userName = app()->make(AdminService::class)->value($userId, 'name');

            [$recordData, $runFun, $listData] = $this->runSqlV2($appInfo, $option, $modelsInfo->key, $message, $recordData, false, $saveData, ['name' => $userName, 'uid' => $userId]);
            if ($runFun) {
                if ($listData['list'] || $listData['totalNum']) {
                    $option->messages = [];
                    $option->setMessage($systemMessage, BaseOption::RULE_SYSTEM);
                    $option->setMessage($message);
                    $option->setMessage(json_encode($listData, JSON_UNESCAPED_UNICODE), BaseOption::RULE_ASSISTANT);
                    $option->setMessage($appInfo->data_arrange_text);
                } else {
                    return $this->sendMessage('没有查询到数据相关数据,请换一个其他问题', 'error', $saveData);
                }
            } else {
                $option->setMessage($message);
            }
        } else {
            $option->setMessage($message);
        }

        $this->sendMessage($option->messages, 'info');

        [$response, $recordData] = $this->streamRequest($option, $appInfo, $modelsInfo, $message, $recordData, $saveData);

        return $this->saveRecord(userId: $userId, historyId: $historyId, message: $message, chatRecordUuid: $chatRecordUuid, recordData: $recordData, startTime: $startTime, response: (string) $response);
    }

    /**
     * 对话 优点一次性直接请求模型，不会过多消耗一号通的token，缺点用户提示词较多
     * 流程：用户提问的内容直接交给模型-》模型函数决定是否需要执行函数-》执行函数-》一号通模型返回结果-》是否存在数据 -》是 -》查询的数据结果二次请求模型-》模型返回结果.
     * @return bool
     * @throws BindingResolutionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function dialog(string $message, int $historyId, int $userId, ?string $chatRecordUuid = null, int $isShow = 1)
    {
        $chatRecordService = app()->make(ChatRecordService::class);
        $startTime         = microtime(true);

        if ($chatRecordUuid) {
            $record['chat_record_uuid'] = $chatRecordUuid;
        } else {
            $record = [];
        }

        $saveData = [
            'userId'         => $userId,
            'historyId'      => $historyId,
            'chatRecordUuid' => $chatRecordUuid,
            'recordData'     => $record,
            'startTime'      => $startTime,
            'message'        => $message,
        ];

        $chatApplicationId = $this->dao->value(['id' => $historyId], 'chat_application_id');
        if (! $chatApplicationId) {
            return $this->sendMessage('没有查询到应用ID', 'error', $saveData);
        }

        $appInfo = app()->make(ChatApplicationsService::class)->get($chatApplicationId);
        if (! $appInfo) {
            return $this->sendMessage('没有查询到应用信息', 'error', $saveData);
        }
        if ($appInfo->member_id && ! in_array($userId, $appInfo->member_id)) {
            return $this->sendMessage('暂无权限访问此应用', 'error', $saveData);
        }
        if ($chatRecordService->getDialogNum($userId, $appInfo->id, $appInfo->use_limit)) {
            return $this->sendMessage('今日使用已达到上限', 'error', $saveData);
        }

        $modelsInfo = app()->make(ChatModelsService::class)->get($appInfo['models_id']);
        if (! $modelsInfo) {
            return $this->sendMessage('没有查询到模型信息', 'error', $saveData);
        }
        if (! $modelsInfo->key) {
            return $this->sendMessage('模型配置有问题，请检查模型配置', 'error', $saveData);
        }

        $option = $this->option($modelsInfo->provider);

        // 放入其他参数
        if ($appInfo->json) {
            $other = [];
            foreach ((array) $appInfo->json as $item) {
                if (isset($item['field'], $item['value'])) {
                    $other[$item['field']] = $item['value'];
                }
            }
            if ($other) {
                $option->options($other);
            }
        }

        $systemMessage = $appInfo->tooltip_text;

        // 数据库查询功能
        if ($appInfo->tables) {
            $option->setTool([
                'name'        => 'run_sql',
                'description' => '用来执行数据库查询',
                'parameters'  => [
                    'data' => [
                        'type'        => 'array',
                        'description' => '查询后的数据，二维数组，键值对形式',
                    ],
                ],
                'required' => ['data'],
            ]);

            $systemMessage = ($systemMessage ? $systemMessage . "\n" : '') . implode(',', $appInfo->tables) . "\n" . $appInfo->content;
        }

        if ($systemMessage) {
            $option->setMessage($systemMessage, 'system');
        }

        // 加入历史对话内容
        if ($appInfo->count_number) {
            $chatRecordList = $chatRecordService->getDialogRecord($historyId, $appInfo->count_number);
            if ($chatRecordList) {
                foreach ($chatRecordList as $item) {
                    if (! $item['problem_text'] || ! $item['answer_text']) {
                        continue;
                    }
                    $option->setMessage($item['problem_text']);
                    $option->setMessage($item['answer_text'], BaseOption::RULE_ASSISTANT);
                }
            }
        }

        $option->setMessage($message);

        if ($chatRecordUuid) {
            $recordData = $chatRecordService->get(['chat_record_uuid' => $chatRecordUuid])?->toArray();
            if (! $recordData) {
                $recordData['chat_applications_id'] = $appInfo->id;
                $saveData['recordData']             = $recordData;
                return $this->sendMessage('没查询到记录，无法从新生成', 'error', $saveData);
            }
        } else {
            $recordData = [
                'chat_record_uuid'     => v4(),
                'is_show'              => $isShow,
                'chat_applications_id' => $appInfo->id,
            ];
            $this->sendMessage($recordData, 'data');
        }

        $this->sendMessage($option->messages, 'info');

        [$response, $recordData] = $this->streamRequest($option, $appInfo, $modelsInfo, $message, $recordData, $saveData);

        return $this->saveRecord(userId: $userId, historyId: $historyId, message: $message, chatRecordUuid: $chatRecordUuid, recordData: $recordData, startTime: $startTime, response: (string) $response);
    }

    /**
     * @return true
     * @throws BindingResolutionException
     */
    public function saveRecord(int $userId, int $historyId, string $message, string $chatRecordUuid, array $recordData, float|int $startTime = 0, string $response = '')
    {
        $chatRecordService = app()->make(ChatRecordService::class);

        $array = explode('data:', str_replace(["\r\n", "\n", "\r", '[DONE]'], '', $response));

        $content = '';
        foreach ($array as $value) {
            if (! trim($value)) {
                continue;
            }
            $res = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                if (! empty($res['choices'][0]['delta']['content'])) {
                    $content .= $res['choices'][0]['delta']['content'];
                }
                if (! empty($res['error']['message'])) {
                    $content .= $res['error']['message'];
                    continue;
                }
                if (! empty($res['type']) && ! empty($res['message']) && $res['type'] == 'error') {
                    $content .= $res['message'];
                    continue;
                }
                if (empty($res['usage'])) {
                    continue;
                }
                $recordData['tokens']            = $res['usage']['total_tokens'] ?? 0;
                $recordData['prompt_tokens']     = $res['usage']['prompt_tokens'] ?? 0;
                $recordData['completion_tokens'] = $res['usage']['completion_tokens'] ?? 0;
            } else {
                $content .= $value;
            }
        }
        $recordData['answer_text']     = $content;
        $recordData['chat_history_id'] = $historyId;
        $recordData['problem_text']    = $message;
        $recordData['details']         = $response;
        $endTime                       = microtime(true);
        $executionTime                 = $startTime ? $endTime - $startTime : 0;
        $recordData['run_time']        = $executionTime;

        if ($chatRecordUuid) {
            unset($recordData['id'], $recordData['created_at'], $recordData['updated_at']);
            $chatRecordService->update(['chat_record_uuid' => $chatRecordUuid], $recordData);
        } else {
            $recordData['uid'] = $userId;
            $chatRecordService->create($recordData);
        }
        return true;
    }

    /**
     * @return array []
     * @throws BindingResolutionException
     */
    public function streamRequest(BaseOption $option, ChatApplications $appInfo, ChatModels $modelsInfo, string $message, array $recordData = [], array $saveData = [])
    {
        // 一次请求
        $res = $this->stream($modelsInfo->key, $option, $recordData['chat_record_uuid']);

        $runFun   = $res['runFun'];
        $response = $res['response'];
        $toolCall = $res['toolCall'];

        $jsonString = trim(str_replace(["\n", 'data:'], '', (string) $response));
        if ($jsonString) {
            $json = json_decode($jsonString, true, 512, JSON_BIGINT_AS_STRING);

            if (json_last_error() === JSON_ERROR_NONE && ! empty($json['error']['message'])) {
                $this->sendMessage($json['error']['message'], 'error');
                return [$response, $recordData];
            }
        }
        // 二次请求
        if ($runFun) {
            [$recordData, $runFun, $listData] = $this->runSql($appInfo, $message, $recordData, true, $saveData);
            if ($runFun) {
                if (! $listData) {
                    $errorMessage = "\n##您查询的数据为空";
                    $this->sendMessage($errorMessage, 'list');

                    $response .= $errorMessage;
                    return [$response, $recordData];
                }

                $this->sendMessage($listData, 'info');

                $content = json_encode($listData);

                $responseData = trim(str_replace(["\n", 'data:', '[DONE]'], '', (string) $response));
                $responseJson = json_decode($responseData, true);

                $option->setMessage(content: '', role: BaseOption::RULE_ASSISTANT, toolCalls: $responseJson['choices'][0]['delta']['tool_calls'] ?? []);
                $option->setMessage(content: $content, role: BaseOption::RULE_TOOL, toolCallId: $toolCall['id'], name: $toolCall['function']['name']);

                $this->sendMessage($option->messages, 'info');

                $res2 = $this->stream($modelsInfo->key, $option, $recordData['chat_record_uuid']);

                $response .= $res2['response'];

                $jsonString = trim(str_replace(["\n", 'data:'], '', $res2['response']));
                if ($jsonString) {
                    $json = json_decode($jsonString, true, 512, JSON_BIGINT_AS_STRING);

                    if (json_last_error() === JSON_ERROR_NONE && ! empty($json['error']['message'])) {
                        $this->sendMessage($json['error']['message'], 'error');
                        return [$response, $recordData];
                    }
                }

                $this->sendMessage($res2, 'info');
            }
        }

        return [$response, $recordData];
    }

    /**
     * 获取请求类型.
     * @return BaidubceOption|DeepseekOption
     */
    public function option(int $modelsType)
    {
        if ($modelsType) {
            $option = new BaidubceOption();
        } else {
            $option = new DeepseekOption();
        }
        return $option;
    }

    /**
     * 发送消息.
     * @return mixed|true
     * @throws BindingResolutionException
     */
    public function send(string $message)
    {
        if (extension_loaded('swoole') && php_sapi_name() === 'cli') {
            return app()->make(SwooleResponse::class)->write($message);
        }
        echo $message;
        ob_flush();
        flush();
        return true;
    }

    /**
     * 获取Embedding.
     * @return array|false|string
     * @throws BindingResolutionException
     */
    public function getEmbedding(string $message, string $key = '')
    {
        $key = $key ?: app()->make(ChatModelsService::class)->value(['models_type' => 1], 'key');
        if (! $key) {
            return false;
        }
        $curl = new BaseCurl($key);

        $body = [
            'model' => 'tao-8k',
            'input' => [$message],
        ];
        $response = $curl->setBaeUrl((new BaidubceOption())->baseUrl)->send(url: '/v2/embeddings', body: $body);
        return $response['data'][0]['embedding'] ?? [];
    }

    /**
     * 请求
     * @return array
     */
    protected function stream(string $key, BaseOption $option, string $uuid)
    {
        $curl = new BaseCurl($key);

        $runFun        = false;
        $toolCall      = [];
        $functionNames = [];
        foreach ($option->tools as $tool) {
            $functionNames[] = $tool['function']['name'];
        }

        $response = $curl->setBody($option)->stream(url: $option->url, stream: function ($data) use (&$runFun, &$toolCall, $functionNames) {
            $message = trim(str_replace(["\n", 'data:'], '', $data));
            if ($message) {
                $json = json_decode($message, true, 512, JSON_BIGINT_AS_STRING);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $toolCall = $json['choices'][0]['delta']['tool_calls'][0] ?? [];
                    $runFun   = in_array($json['choices'][0]['delta']['tool_calls'][0]['function']['name'] ?? null, $functionNames);
                }
            }
            if (! $runFun && empty($json['error']['message'])) {
                $this->sendMessage($data);
            } elseif ($runFun) {
                $this->sendMessage('正在执行sql语句', 'info');
            }
        }, uuid: $uuid, tagName: self::CHAT_HISTORY_TABLE);

        return [
            'response' => $response,
            'toolCall' => $toolCall,
            'runFun'   => $runFun,
        ];
    }

    /**
     * 根据类型发送消息.
     * @param mixed $message
     * @return bool
     * @throws BindingResolutionException
     */
    protected function sendMessage($message, string $type = 'success', array $saveData = [])
    {
        if ($type === 'error') {
            $data = 'data: ' . json_encode(['message' => $message, 'type' => $type], JSON_UNESCAPED_UNICODE) . "\n\n";
            $this->send($data);
            $res = $this->send("data: [DONE]\n\n");

            if ($saveData) {
                $this->saveRecord($saveData['userId'], $saveData['historyId'], $saveData['message'], $saveData['chatRecordUuid'], $saveData['recordData'], $saveData['startTime'], (string) $message);
            }
        } elseif ($type === 'list') {
            $data = 'data: ' . json_encode(['choices' => [
                [
                    'index' => 0,
                    'delta' => [
                        'content' => $message,
                    ],
                    'finish_reason' => 'stop',
                ],
            ], 'type' => $type], JSON_UNESCAPED_UNICODE) . "\n\n";
            $this->send($data);
            $res = $this->send("data: [DONE]\n\n");
        } elseif ($type == 'info') {
            // 调试信息
            $data = 'data: ' . json_encode(['message' => $message, 'type' => $type], JSON_UNESCAPED_UNICODE) . "\n\n";
            $res  = $this->send($data);
        } elseif ($type == 'data') {
            $data = 'data: ' . json_encode(['choices' => [
                [
                    'index' => 0,
                    'delta' => [
                        'content' => $message,
                    ],
                ],
            ], 'type' => $type], JSON_UNESCAPED_UNICODE) . "\n\n";
            $res = $this->send($data);
        } else {
            $res = $this->send($message);
        }
        return $res;
    }
}
