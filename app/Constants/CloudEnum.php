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

namespace App\Constants;

final class CloudEnum
{
    public const FILE_READ = 0;

    public const FILE_DELETE = -1;

    public const FILE_NOTICE = [
        NoticeEnum::CLOUD_FILE_READ_TYPE,
        NoticeEnum::CLOUD_FILE_CREATE_TYPE,
        NoticeEnum::CLOUD_FILE_DELETE_TYPE,
    ];

    /**
     * 云文件权限：新建.
     */
    public const CREATE_AUTH = 'create';

    /**
     * 云文件权限：编辑.
     */
    public const UPDATE_AUTH = 'update';

    /**
     * 云文件权限：删除.
     */
    public const DELETE_AUTH = 'delete';

    /**
     * 云文件权限：查看.
     */
    public const READ_AUTH = 'read';

    /**
     * 云文件权限：下载.
     */
    public const DOWNLOAD_AUTH = 'download';
}
