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

namespace App\Http\Requests\enterprise\attendance;

use App\Http\Requests\ApiValidate;

/**
 * 考勤组
 * Class AttendanceGroupRequest.
 */
class AttendanceGroupRequest extends ApiValidate
{
    /**
     * 场景.
     * @var \string[][]
     */
    protected $scene = [
        'step_one'   => ['name', 'shifts', 'members', 'admins', 'filters', 'other_filters'],
        'step_two'   => ['address', 'lat', 'lng', 'effective_range', 'location_name'],
        'step_three' => [
            'repair_allowed', 'repair_type', 'is_limit_time', 'limit_time', 'is_limit_number', 'limit_number',
            'is_photo', 'is_external', 'is_external_note', 'is_external_photo',
        ],
    ];
    /**
     * 自动.
     * @var bool
     */
    // public $authValidate = true;

    /**
     * @return array|string[]
     */
    public function rules(): array
    {
        return [
            'name'          => 'required|max:50',
            'members'       => 'required|array',
            'shifts'        => 'required|array',
            'admins'        => 'required|array',
            'filters'       => 'array',
            'other_filters' => 'array',

            'address'         => 'required|max:100',
            'lat'             => 'required',
            'lng'             => 'required',
            'effective_range' => 'required',
            'location_name'   => 'required',

            'repair_allowed'    => 'required|boolean',
            'repair_type'       => 'required|array',
            'is_limit_time'     => 'required|boolean',
            'limit_time'        => 'required|integer|max:365',
            'is_limit_number'   => 'required|boolean',
            'limit_number'      => 'required|integer|max:30',
            'is_photo'          => 'required|boolean',
            'is_external'       => 'required|boolean',
            'is_external_note'  => 'required|boolean',
            'is_external_photo' => 'required|boolean',
        ];
    }

    /**
     * @return string[]
     */
    public function message(): array
    {
        return [
            'name.required'       => '请填写考勤组名称',
            'name.max'            => '考勤组名称长度超出限制',
            'members.required'    => '请选择考勤成员',
            'members.array'       => '请选择正确的考勤成员',
            'shifts.required'     => '请选择考勤班次',
            'shifts.array'        => '请选择正确的考勤班次',
            'admins.required'     => '请选择管理员',
            'admins.array'        => '请选择正确的管理员',
            'filters.array'       => '请选择正确的排除考勤成员',
            'other_filters.array' => '请选择正确的考勤组排除成员',

            'address.required'         => '请填写考勤详细地址',
            'address.max'              => '考勤详细地址长度超出限制',
            'lat.required'             => '请填写纬度坐标',
            'lng.required'             => '请填写经度坐标',
            'effective_range.required' => '请填写有效范围',
            'location_name.required'   => '请填写考勤地点名称',

            'repair_allowed.required'    => '请选择是否允许补卡',
            'repair_allowed.boolean'     => '请选择正确的是否允许补卡',
            'repair_type.required'       => '请选择补卡类型',
            'repair_type.array'          => '请选择正确的补卡类',
            'is_limit_time.required'     => '请选择是否限制补卡时间',
            'is_limit_time.boolean'      => '请选择正确的限制补卡时间',
            'limit_time.required'        => '请输入补卡时间',
            'limit_time.integer'         => '请输入正确的补卡时间',
            'limit_time.max'             => '补卡时间超出限制',
            'is_limit_number.required'   => '请选择是否限制补卡次数',
            'is_limit_number.boolean'    => '请选择正确的是否限制补卡次数',
            'limit_number.required'      => '请输入补卡次数',
            'limit_number.integer'       => '请输入正确的补卡次数',
            'limit_number.max'           => '补卡次数超出限制',
            'is_photo.required'          => '请选择是否拍照打卡',
            'is_photo.boolean'           => '请选择正确的是否拍照打卡',
            'is_external.required'       => '请选择是否允许外勤打卡',
            'is_external.boolean'        => '请选择正确的是否允许外勤打卡',
            'is_external_note.required'  => '请选择外勤打卡备注',
            'is_external_note.boolean'   => '请选择正确的外勤打卡备注',
            'is_external_photo.required' => '请选择外勤打卡必须拍照',
            'is_external_photo.boolean'  => '请选择正确的外勤打卡必须拍照',
        ];
    }
}
