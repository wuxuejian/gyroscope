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

namespace App\Http\Contract\User;

use App\Constants\CommonEnum;

/**
 * 基础用户.
 */
interface UserInterface
{
    /**
     * 通过uuid获取用户信息.
     * @param array $field 查询字段
     * @param array $with 关联查询
     */
    public function getInfo(int $uid, array $field = ['*'], array $with = []): array;

    /**
     * 用户登录.
     * @param string $account 账号
     * @param string $password 密码
     * @param string $client 客户端标识
     * @param string $clientId 客户端唯一ID
     */
    public function login(string $account, string $password, string $client = '', string $mac = '', string $clientId = '', string $origin = CommonEnum::ORIGIN_WEB): array;

    /**
     * 用户退出登录.
     */
    public function logout(): void;

    /**
     * 用户注册.
     * @param mixed $phone
     * @param mixed $password
     */
    public function register($phone, $password): array;

    /**
     * 用户修改密码
     * @return array
     */
    public function password(int $uid, string $phone, string $password): int;

    /**
     * 获取扫码登录Key.
     */
    public function scanCodeKey(): array;

    public function keyStatus($key): array;

    public function scanWithUser(string $key, int $uid): bool;

    public function scanLogin(int $uid, string $key, int|string $status): void;
}
