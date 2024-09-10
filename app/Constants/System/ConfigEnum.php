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

namespace App\Constants\System;

use App\Constants\StorageEnum;
use MyCLabs\Enum\Enum;

/**
 * 系统：配置项枚举.
 */
final class ConfigEnum extends Enum
{
    public const LOGIN_PASSWORD_LENGTH = [
        'title'      => '密码长度',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'login_password_length',
        'value'      => '8',
        'parameter'  => [],
        'category'   => CategoryEnum::SYSTEM_CONFIG['key'],
    ];

    public const LOGIN_PASSWORD_TYPE = [
        'title'      => '密码类型',
        'type'       => 'checkbox',
        'input_type' => '',
        'key'        => 'login_password_type',
        'value'      => [0],
        'parameter'  => [
            '数字',
            '大写字母',
            '小写字母',
            '特殊符号',
        ],
        'category' => CategoryEnum::SYSTEM_CONFIG['key'],
    ];

    public const LOGINT_TIME_OUT = [
        'title'      => '登录超时退出时间',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'login_time_out',
        'value'      => '24',
        'parameter'  => [],
        'category'   => CategoryEnum::SYSTEM_CONFIG['key'],
    ];

    public const LOGIN_ERROR_COUNT = [
        'title'      => '登录错误次数',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'login_error_count',
        'value'      => '15',
        'parameter'  => [],
        'category'   => CategoryEnum::SYSTEM_CONFIG['key'],
    ];

    public const LOGIN_LOCK = [
        'title'      => '密码错误锁定时间',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'login_lock',
        'value'      => '1',
        'parameter'  => [],
        'category'   => CategoryEnum::SYSTEM_CONFIG['key'],
    ];

    public const SITE_OPEN = [
        'title'      => '站点开启',
        'type'       => 'radio',
        'input_type' => '',
        'key'        => 'site_open',
        'value'      => 1,
        'parameter'  => [
            '关闭',
            '开启',
        ],
        'category' => CategoryEnum::SYSTEM_CONFIG['key'],
    ];

    public const SITE_URL = [
        'title'      => '网站地址',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'site_url',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::SYSTEM_CONFIG['key'],
    ];

    public const SITE_RECORD_NUMBER = [
        'title'      => '网站备案号',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'site_record_number',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::SYSTEM_CONFIG['key'],
    ];

    public const SITE_NAME = [
        'title'      => '网站名称',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'site_name',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::SYSTEM_CONFIG['key'],
    ];

    public const SITE_TEL = [
        'title'      => '网站联系电话',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'site_tel',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::SYSTEM_CONFIG['key'],
    ];

    public const ENTERPRISE_CULTURE = [
        'title'      => '企业文化语',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'enterprise_culture',
        'value'      => '高效团队铸就一流企业！！！',
        'parameter'  => [],
        'category'   => CategoryEnum::SYSTEM_CONFIG['key'],
    ];

    public const UPLOAD_TYPE = [
        'title'      => '云存储类型',
        'type'       => 'radio',
        'input_type' => '',
        'key'        => 'upload_type',
        'value'      => StorageEnum::UPLOAD_LOCAL,
        'parameter'  => [
            StorageEnum::UPLOAD_LOCAL => '本地存储',
            StorageEnum::UPLOAD_QINIU => '七牛云存储',
            StorageEnum::UPLOAD_ALI   => '阿里云存储',
            StorageEnum::UPLOAD_TX    => '腾讯云存储',
            StorageEnum::UPLOAD_JD    => '京东云存储',
            StorageEnum::UPLOAD_HW    => '华为云存储',
            StorageEnum::UPLOAD_TY    => '天翼云存储',
        ],
        'category' => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const WPS_TYPE = [
        'title'      => '云文件预览类型',
        'type'       => 'radio',
        'input_type' => '',
        'key'        => 'wps_type',
        'value'      => '1',
        'parameter'  => [
            '0' => 'WPS',
            '1' => 'PDF',
        ],
        'category' => CategoryEnum::WPS_CONFIG['key'],
    ];

    public const WPS_APPID = [
        'title'      => 'WPS AppId',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'wps_appid',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::WPS_CONFIG['key'],
    ];

    public const WPS_APPKEY = [
        'title'      => 'WPS AppKey',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'wps_appkey',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::WPS_CONFIG['key'],
    ];

    public const ASSESS_COMPUTE_MODE = [
        'title'      => '评分方式',
        'type'       => 'radio',
        'input_type' => 'input',
        'key'        => 'assess_compute_mode',
        'value'      => 1,
        'parameter'  => ['加权评分', '加和评分'],
        'category'   => CategoryEnum::ASSESS_CONFIG['key'],
    ];

    public const ASSESS_SCORE_MARK = [
        'title'      => '评分说明',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'assess_score_mark',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::ASSESS_CONFIG['key'],
    ];

    public const SCHEDULE_SYNC = [
        'title'      => '同步日程',
        'type'       => 'radio',
        'input_type' => 'input',
        'key'        => 'schedule_sync',
        'value'      => 1,
        'parameter'  => ['关闭', '开启'],
        'category'   => CategoryEnum::SYSTEM_CONFIG['key'],
    ];

    public const FOLLOW_UP_SWITCH = [
        'title'      => '客户跟进提醒',
        'type'       => 'radio',
        'input_type' => '',
        'key'        => 'follow_up_switch',
        'value'      => 0,
        'parameter'  => ['关闭', '开启'],
        'category'   => CategoryEnum::CUSTOMER_FOLLOW_CONFIG['key'],
    ];

    public const FOLLOW_UP_STATUS = [
        'title'      => '客户状态',
        'type'       => 'checkbox',
        'input_type' => '',
        'key'        => 'follow_up_status',
        'value'      => [],
        'parameter'  => ['未成交', '已成交'],
        'category'   => CategoryEnum::CUSTOMER_FOLLOW_CONFIG['key'],
    ];

    public const FOLLOW_UP_TRADED = [
        'title'      => '客户状态已成交提醒周期',
        'type'       => 'text',
        'input_type' => 'number',
        'key'        => 'follow_up_traded',
        'value'      => 0,
        'parameter'  => [],
        'category'   => CategoryEnum::CUSTOMER_FOLLOW_CONFIG['key'],
    ];

    public const FOLLOW_UP_UNSETTLED = [
        'title'      => '客户状态暂未成交提醒周期',
        'type'       => 'text',
        'input_type' => 'number',
        'key'        => 'follow_up_unsettled',
        'value'      => 0,
        'parameter'  => [],
        'category'   => CategoryEnum::CUSTOMER_FOLLOW_CONFIG['key'],
    ];

    public const RETURN_HIGH_SEAS_SWITCH = [
        'title'      => '自动退回公海规则',
        'type'       => 'radio',
        'input_type' => '',
        'key'        => 'return_high_seas_switch',
        'value'      => 0,
        'parameter'  => ['关闭', '开启'],
        'category'   => CategoryEnum::CUSTOMER_SEA_CONFIG['key'],
    ];

    public const UNSETTLED_CYCLE = [
        'title'      => '退回客户公海周期(暂未成交)',
        'type'       => 'text',
        'input_type' => 'number',
        'key'        => 'unsettled_cycle',
        'value'      => 30,
        'parameter'  => [],
        'category'   => CategoryEnum::CUSTOMER_SEA_CONFIG['key'],
    ];

    public const UNFOLLOWED_CYCLE = [
        'title'      => '未跟进退回公海(暂未成交)',
        'type'       => 'text',
        'input_type' => 'number',
        'key'        => 'unfollowed_cycle',
        'value'      => 30,
        'parameter'  => [],
        'category'   => CategoryEnum::CUSTOMER_SEA_CONFIG['key'],
    ];

    public const ADVANCE_CYCLE = [
        'title'      => '客户退回公海提醒提前',
        'type'       => 'text',
        'input_type' => 'number',
        'key'        => 'advance_cycle',
        'value'      => 5,
        'parameter'  => [],
        'category'   => CategoryEnum::CUSTOMER_SEA_CONFIG['key'],
    ];

    public const CLIENT_POLICY_SWITCH = [
        'title'      => '客户保单规则',
        'type'       => 'radio',
        'input_type' => '',
        'key'        => 'client_policy_switch',
        'value'      => 0,
        'parameter'  => ['关闭', '开启'],
        'category'   => CategoryEnum::CUSTOMER_SEA_CONFIG['key'],
    ];

    public const UNSETTLED_CLIENT_NUMBER = [
        'title'      => '暂未成交客户数量设置',
        'type'       => 'text',
        'input_type' => 'number',
        'key'        => 'unsettled_client_number',
        'value'      => 999,
        'parameter'  => [],
        'category'   => CategoryEnum::CUSTOMER_SEA_CONFIG['key'],
    ];

    public const CONTRACT_REFUND_SWITCH = [
        'title'      => '合同回款',
        'type'       => 'radio',
        'input_type' => '',
        'key'        => 'contract_refund_switch',
        'value'      => 0,
        'parameter'  => [],
        'category'   => CategoryEnum::CUSTOMER_APPROVE_CONFIG['key'],
    ];

    public const CONTRACT_RENEW_SWITCH = [
        'title'      => '合同续费',
        'type'       => 'radio',
        'input_type' => '',
        'key'        => 'contract_renew_switch',
        'value'      => 0,
        'parameter'  => [],
        'category'   => CategoryEnum::CUSTOMER_APPROVE_CONFIG['key'],
    ];

    public const CONTRACT_DISBURSE_SWITCH = [
        'title'      => '合同支出',
        'type'       => 'radio',
        'input_type' => '',
        'key'        => 'contract_disburse_switch',
        'value'      => 0,
        'parameter'  => [],
        'category'   => CategoryEnum::CUSTOMER_APPROVE_CONFIG['key'],
    ];

    public const INVOICING_SWITCH = [
        'title'      => '开具发票',
        'type'       => 'radio',
        'input_type' => '',
        'key'        => 'invoicing_switch',
        'value'      => 0,
        'parameter'  => [],
        'category'   => CategoryEnum::CUSTOMER_APPROVE_CONFIG['key'],
    ];

    public const VOID_INVOICE_SWITCH = [
        'title'      => '作废发票',
        'type'       => 'radio',
        'input_type' => '',
        'key'        => 'void_invoice_switch',
        'value'      => 0,
        'parameter'  => [],
        'category'   => CategoryEnum::CUSTOMER_APPROVE_CONFIG['key'],
    ];

    public const YIHAOTONG_APPID = [
        'title'      => '一号通AppId',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'yihaotong_appid',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::YIHT_CONFIG['key'],
    ];

    public const YIHAOTONG_APPSECRET = [
        'title'      => '一号通AppSecret',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'yihaotong_appsecret',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::YIHT_CONFIG['key'],
    ];

    public const UNI_PACKAGE_ID = [
        'title'      => '应用包名',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'uni_package_id',
        'desc'       => 'App包名',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::PUSH_CONFIG['key'],
    ];

    public const UNI_PUSH_APPID = [
        'title'      => '应用appId',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'uni_push_appid',
        'desc'       => 'UniPush应用appId',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::PUSH_CONFIG['key'],
    ];

    public const UNI_PUSH_APPKEY = [
        'title'      => '应用appKey',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'uni_push_appkey',
        'desc'       => 'UniPush应用appKey',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::PUSH_CONFIG['key'],
    ];

    public const UNI_PUSH_SECRET = [
        'title'      => '应用pushSecret',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'uni_push_secret',
        'desc'       => 'UniPush应用pushSecret',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::PUSH_CONFIG['key'],
    ];

    public const UNI_PUSH_MASTER_SECRET = [
        'title'      => '应用MasterSecret',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'uni_push_master_secret',
        'desc'       => 'UniPush应用MasterSecret',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::PUSH_CONFIG['key'],
    ];

    public const TL_CODE = [
        'title'      => '授权密钥',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'tl_code',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::OTHER_CONFIG['key'],
    ];

    public const SYSTEM_CACHE_TTL = [
        'title'      => '缓存时间',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'system_cache_ttl',
        'value'      => 3600,
        'parameter'  => [],
        'category'   => CategoryEnum::SYSTEM_CONFIG['key'],
    ];

    public const THUMB_BIG_WIDTH = [
        'title'      => '缩略大图（单位：px）：宽',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'thumb_big_width',
        'value'      => '800',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const THUMB_BIG_HEIGHT = [
        'title'      => '缩略大图（单位：px）：高',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'thumb_big_height',
        'value'      => '800',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const THUMB_MID_WIDTH = [
        'title'      => '缩略中图（单位：px）：宽',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'thumb_mid_width',
        'value'      => '300',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const THUMB_MID_HEIGHT = [
        'title'      => '缩略中图（单位：px）：高',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'thumb_mid_height',
        'value'      => '300',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const THUMB_SMALL_WIDTH = [
        'title'      => '缩略小图（单位：px）： 宽',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'thumb_small_width',
        'value'      => '150',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const THUMB_SMALL_HEIGHT = [
        'title'      => '缩略小图（单位：px）： 高',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'thumb_small_height',
        'value'      => '150',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const IMAGE_WATERMARK_STATUS = [
        'title'      => '是否开启水印',
        'type'       => 'radio',
        'input_type' => '',
        'key'        => 'image_watermark_status',
        'value'      => '0',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const WATERMARK_TYPE = [
        'title'      => '水印类型',
        'type'       => 'radio',
        'input_type' => '',
        'key'        => 'watermark_type',
        'value'      => '1',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const WATERMARK_IMAGE = [
        'title'      => '水印图片',
        'type'       => 'upload',
        'input_type' => 'input',
        'key'        => 'watermark_image',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const WATERMARK_POSITION = [
        'title'      => '水印位置',
        'type'       => 'radio',
        'input_type' => '',
        'key'        => 'watermark_position',
        'value'      => '5',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const WATERMARK_OPACITY = [
        'title'      => '水印图片透明度',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'watermark_opacity',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const WATERMARK_ROTATE = [
        'title'      => '水印图片倾斜度',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'watermark_rotate',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const WATERMARK_TEXT = [
        'title'      => '水印文字',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'watermark_text',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const WATERMARK_TEXT_SIZE = [
        'title'      => '水印文字大小（单位：px）',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'watermark_text_size',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const WATERMARK_TEXT_COLOR = [
        'title'      => '水印字体颜色',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'watermark_text_color',
        'value'      => '#666666',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const WATERMARK_TEXT_ANGLE = [
        'title'      => '水印字体旋转角度',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'watermark_text_angle',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const WATERMARK_X = [
        'title'      => '水印横坐标偏移量（单位：px）',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'watermark_x',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const WATERMARK_Y = [
        'title'      => '水印纵坐标偏移量（单位：px）',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'watermark_y',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const ACCESSKEY = [
        'title'      => '阿里云云存储accessKey',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'accessKey',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const SECRETKEY = [
        'title'      => '阿里云存储secretKey',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'secretKey',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const QINIU_ACCESSKEY = [
        'title'      => '七牛云存储accessKey',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'qiniu_accessKey',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const QINIU_SECRETKEY = [
        'title'      => '七牛云存储secretKey',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'qiniu_secretKey',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const TENGXUN_ACCESSKEY = [
        'title'      => '腾讯云存储accessKey',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'tengxun_accessKey',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const TENGXUN_SECRETKEY = [
        'title'      => '腾讯云存储secretKey',
        'type'       => 'text',
        'input_type' => '',
        'key'        => 'tengxun_secretKey',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const JD_ACCESSKEY = [
        'title'      => '京东云accessKey',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'jd_accessKey',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const JD_SECRETKEY = [
        'title'      => '京东云secretKey',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'jd_secretKey',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const JD_STORAGE_REGION = [
        'title'      => '京东云StorageRegion',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'jd_storage_region',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const HW_ACCESSKEY = [
        'title'      => '华为云accessKey',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'hw_accessKey',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const HW_SECRETKEY = [
        'title'      => '华为云secretKey',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'hw_secretKey',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const TY_SECRETKEY = [
        'title'      => '天翼云secretKey',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'ty_secretKey',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const TY_ACCESSKEY = [
        'title'      => '天翼云accessKey',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'ty_accessKey',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const TENGXUN_APPID = [
        'title'      => '腾讯云APPID',
        'type'       => 'text',
        'input_type' => 'input',
        'key'        => 'tengxun_appid',
        'value'      => '',
        'parameter'  => [],
        'category'   => CategoryEnum::STORAGE_CONFIG['key'],
    ];

    public const REGISTRATION_OPEN = [
        'title'      => '开启用户注册',
        'type'       => 'radio',
        'input_type' => '',
        'key'        => 'registration_open',
        'value'      => 0,
        'parameter'  => ['关闭', '开启'],
        'category'   => CategoryEnum::SYSTEM_CONFIG['key'],
    ];

    public const GLOBAL_WATERMARK_STATUS = [
        'title'      => '全局水印后台开关',
        'type'       => 'radio',
        'input_type' => '',
        'key'        => 'global_watermark_status',
        'value'      => 1,
        'parameter'  => ['关闭', '开启'],
        'category'   => CategoryEnum::SYSTEM_CONFIG['key'],
    ];
}
