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

namespace App\Task;

use App\Constants\ApproveEnum;
use App\Http\Model\Admin\Admin;
use App\Http\Model\Approve\Approve;
use App\Http\Model\Approve\ApproveForm;
use App\Http\Model\Approve\ApproveHolidayType;
use App\Http\Model\Approve\ApproveRule;
use App\Http\Model\Assess\AssessScore;
use App\Http\Model\Attendance\AttendanceShift;
use App\Http\Model\Attendance\AttendanceShiftRule;
use App\Http\Model\Category\Category;
use App\Http\Model\Cloud\CloudFile;
use App\Http\Model\Company\Company;
use App\Http\Model\Config\Agreement;
use App\Http\Model\Config\DictData;
use App\Http\Model\Config\DictType;
use App\Http\Model\Config\FormCate;
use App\Http\Model\Config\FormData;
use App\Http\Model\Crud\SystemCrud;
use App\Http\Model\Crud\SystemCrudCate;
use App\Http\Model\Crud\SystemCrudField;
use App\Http\Model\Crud\SystemCrudForm;
use App\Http\Model\Finance\BillCategory;
use App\Http\Model\Finance\Paytype;
use App\Http\Model\Frame\Frame;
use App\Http\Model\Frame\FrameAssist;
use App\Http\Model\Open\OpenapiRule;
use App\Http\Model\Position\Job;
use App\Http\Model\Position\Level;
use App\Http\Model\Position\Position;
use App\Http\Model\Position\Relation;
use App\Http\Model\Schedule\ScheduleType;
use App\Http\Model\System\Menus;
use App\Http\Model\System\Quick;
use App\Http\Model\System\Role;
use App\Http\Model\User\User;
use App\Http\Service\Company\CompanyService;
use App\Http\Service\Message\MessageService;
use Carbon\Carbon;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Webpatser\Uuid\Uuid;

class CompanyInitTask extends Task
{
    private Company $company;

    public function __construct(protected int $entId)
    {
        $this->company = Company::find($this->entId);
    }

    public function handle()
    {
        try {
            $this->initCompany();
            $this->initQuick();
            $this->initScore();
            $this->initApprove();
            $this->initHoliday();
            $this->initPosition();
            $this->initMenus();
            $this->initFrame();
            $this->initAgreement();
            $this->initDict();
            $this->initPaytype();
            $this->initScheduleType();
            $this->initBillCategory();
            $this->initFormCate();
            $this->initCrud();
            $this->initCustomer();
            $this->initContract();
            $this->initLiaison();
            $this->initApiDoc();
            $this->initAttendance();
            // 同步系统消息
            app()->get(MessageService::class)->syncMessage($this->entId);
        } catch (\Exception $e) {
            Log::error(__CLASS__ . ':' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine(), 'trace' => $e->getTrace()]);
        }
    }

    private function initCompany(): void
    {
        if ($this->entId) {
            try {
                $company             = Company::find($this->entId);
                $user                = User::find($company->uid);
                $company->phone      = $user->phone;
                $company->short_name = substr($company->enterprise_name, 0, 4);
                $company->save();
            } catch (\Exception $e) {
            }
        }
        dump('initCompany');
    }

    private function initMenus(): void
    {
        if ($this->entId && ! CompanyMenus::where('entid', $this->entId)->exists()) {
            DB::transaction(function () {
                $menus = Menus::whereIn('entid', [$this->entId, 1, 0])->where('status', 1)->select(['id', 'type', 'is_show', 'type', 'status'])->get()?->toArray();
                $save  = $rules = $apis = [];
                foreach ($menus as $menu) {
                    $save[] = [
                        'menu_id' => $menu['id'],
                        'entid'   => $this->entId,
                        'status'  => $menu['status'],
                        'is_show' => $menu['is_show'],
                        'type'    => (int) ($menu['type'] != 'M'),
                    ];
                    if ($menu['type'] == 'M') {
                        $rules[] = $menu['id'];
                    } else {
                        $apis[] = $menu['id'];
                    }
                }
                Role::updateOrCreate(['entid' => $this->entId], [
                    'role_name' => '企业超级管理员',
                    'entid'     => $this->entId,
                    'status'    => 1,
                    'rules'     => $rules,
                    'apis'      => $apis,
                    'type'      => 'enterprise',
                ]);
                $save && CompanyMenus::insert($save);
                return true;
            });
        }
        dump('initMenus');
    }

    private function initFrame()
    {
        if ($this->entId && ! Frame::where('entid', $this->entId)->exists()) {
            $frameId = Frame::create([
                'entid' => $this->entId,
                'name'  => $this->company->enterprise_name,
            ])->id;
            CloudFile::create([
                'entid' => $this->entId,
                'name'  => $this->company->enterprise_name,
            ]);
            $user = User::find($this->company->uid);
            if (! $user) {
                throw new \Exception('用户不存在');
            }
            $user->entid = $this->entId;
            $user->save();
            $user->name     = $user->real_name;
            $user->is_admin = 1;
            $admin          = Admin::create($user->toArray());
            FrameAssist::updateOrCreate(['frame_id' => $frameId, 'user_id' => $admin->id, 'entid' => $this->entId], [
                'frame_id'     => $frameId,
                'user_id'      => $admin->id,
                'is_mastart'   => 1,
                'is_admin'     => 1,
                'superior_uid' => 0,
                'entid'        => $this->entId,
                'uniqued'      => str_replace('-', '', (string) Uuid::generate(4)),
            ]);
        }
        dump('initFrame');
    }

    private function initQuick()
    {
        if ($this->entId && ! Category::where('entid', $this->entId)->exists()) {
            DB::transaction(function () {
                $types = Category::where('entid', 0)->where('type', 'quickConfig')->select(['id', 'cate_name', 'sort', 'is_show', 'type'])->get()?->toArray();
                if ($types) {
                    foreach ($types as $type) {
                        $type['entid'] = $this->entId;
                        $infos         = Quick::where('cid', $type['id'])->get()?->toArray();
                        unset($type['id']);
                        $cate  = Category::create($type);
                        $quick = [];
                        foreach ($infos as $info) {
                            $info['cid']   = $cate->id;
                            $info['entid'] = $this->entId;
                            unset($info['id']);
                            $quick[] = $info;
                        }
                        $quick && Quick::insert($quick);
                    }
                }
                return true;
            });
        }
        dump('initQuick');
    }

    private function initScore()
    {
        if ($this->entId && ! AssessScore::where('entid', $this->entId)->exists()) {
            $cate = AssessScore::where('entid', 0)->select(['id', 'name', 'min', 'max', 'level', 'mark'])->get()?->toArray();
            if ($cate) {
                DB::transaction(function () use ($cate) {
                    $data = [];
                    foreach ($cate as $item) {
                        unset($item['id']);
                        $item['entid'] = $this->entId;
                        $data[]        = $item;
                    }
                    $data && AssessScore::insert($data);
                    return true;
                });
            }
        }
        dump('initScore');
        return true;
    }

    private function initApprove()
    {
        if ($this->entId && ! Approve::where('entid', $this->entId)->exists()) {
            $approves   = Approve::where('entid', 0)->where('examine', 0)->select(['id', 'name', 'icon', 'color', 'info', 'types', 'examine', 'status'])->get()?->toArray();
            $approves[] = Approve::where('entid', 0)->where('types', ApproveEnum::APPROVE_REVOKE)->select(['*'])->first()?->toArray();
            if ($approves) {
                DB::transaction(function () use ($approves) {
                    $forms = [];
                    foreach ($approves as $item) {
                        $form = ApproveForm::where('approve_id', $item['id'])->where('entid', 0)->select(['*'])->get()?->toArray();
                        $rule = ApproveRule::where('approve_id', $item['id'])->select(['*'])->first()?->toArray();
                        unset($item['id']);
                        $item['entid'] = $this->entId;
                        $newCate       = Approve::create($item);
                        if ($form) {
                            foreach ($form as $formItem) {
                                $formItem['approve_id'] = $newCate->id;
                                $formItem['entid']      = $this->entId;
                                $formItem['content']    = $formItem['content'] ? json_encode($formItem['content']) : '';
                                $formItem['props']      = $formItem['props'] ? json_encode($formItem['props']) : '';
                                $formItem['options']    = $formItem['options'] ? json_encode($formItem['options']) : '';
                                $formItem['config']     = $formItem['config'] ? json_encode($formItem['config']) : '';
                                unset($formItem['id']);
                                $forms[] = $formItem;
                            }
                        }
                        if ($rule && $item['types'] == ApproveEnum::APPROVE_REVOKE) {
                            unset($rule['id']);
                            $rule['approve_id'] = $newCate->id;
                            $rule['entid']      = $this->entId;
                            $rule['edit']       = json_encode($rule['edit']);
                            ApproveRule::create($rule);
                        }
                    }
                    $forms && ApproveForm::insert($forms);
                    return true;
                });
            }
        }
        dump('initApprove');
        return true;
    }

    private function initHoliday()
    {
        if ($this->entId && ! ApproveHolidayType::where('entid', $this->entId)->exists()) {
            $holiday = ApproveHolidayType::where('entid', 0)->select(['id', 'name', 'new_employee_limit', 'new_employee_limit_month', 'duration_type', 'duration_calc_type', 'sort'])->get()?->toArray();
            if ($holiday) {
                DB::transaction(function () use ($holiday) {
                    $save = [];
                    foreach ($holiday as $item) {
                        unset($item['id']);
                        $item['entid'] = $this->entId;
                        $save[]        = $item;
                    }
                    $save && ApproveHolidayType::insert($save);
                    return true;
                });
            }
        }
        dump('initHoliday');
        return true;
    }

    private function initPosition()
    {
        if ($this->entId && ! Position::where('entid', $this->entId)->exists()) {
            DB::transaction(function () { // 职级分类
                $now       = Carbon::now()->toDateTimeString();
                $cardId    = Admin::where('entid', $this->entId)->where('is_admin', 1)->select(['id'])->first()?->id ?? 0;
                $rankCates = \App\Http\Model\Position\Category::where('entid', 0)->select(['id', 'name', 'number'])->get()?->toArray();
                // 职级列表
                $ranks = Position::where('entid', 0)->select(['*'])->get()?->toArray();
                // 职级列表
                $rankJobs = Job::where('entid', 0)->select(['*'])->get()?->toArray();
                // 职级列表
                $rankLevels = Level::where('entid', 0)->select(['*'])->get()?->toArray();
                if (! $rankCates || ! $ranks) {
                    return true;
                }
                $levelIds = $cateIds = $rankIds = [];
                foreach ($rankCates as $cate) {
                    $cateId = \App\Http\Model\Position\Category::create([
                        'entid' => $this->entId,
                        'name'  => $cate['name'],
                    ])->id;
                    $cateIds[$cate['id']] = $cateId;
                    foreach ($ranks as $rank) {
                        if ($rank['cate_id'] == $cate['id']) {
                            array_walk($rank, function (&$item, $key) use ($cateId, $cardId, $now) {
                                if ($key == 'entid') {
                                    $item = $this->entId;
                                }
                                if ($key == 'cate_id') {
                                    $item = $cateId;
                                }
                                if ($key == 'card_id') {
                                    $item = $cardId;
                                }
                                if (in_array($key, ['created_at', 'updated_at'])) {
                                    $item = $now;
                                }
                            });
                            $saveRank = $rank;
                            if (isset($saveRank['id'])) {
                                unset($saveRank['id']);
                            }
                            $rankId               = Position::create($saveRank)->id;
                            $rankIds[$rank['id']] = $rankId;
                            foreach ($rankJobs as $job) {
                                if ($job['rank_id'] == $rank['id']) {
                                    array_walk($job, function (&$item, $key) use ($cateId, $cardId, $rankId, $now) {
                                        if ($key == 'entid') {
                                            $item = $this->entId;
                                        }
                                        if ($key == 'cate_id') {
                                            $item = $cateId;
                                        }
                                        if ($key == 'card_id') {
                                            $item = $cardId;
                                        }
                                        if ($key == 'rank_id') {
                                            $item = $rankId;
                                        }
                                        if (in_array($key, ['created_at', 'updated_at'])) {
                                            $item = $now;
                                        }
                                    });
                                    $saveJob = $job;
                                    if (isset($saveJob['id'])) {
                                        unset($saveJob['id']);
                                    }
                                    $jobId              = Job::create($saveJob)->id;
                                    $jobIds[$job['id']] = $jobId;
                                }
                            }
                        }
                    }
                }
                foreach ($rankLevels as $level) {
                    array_walk($level, function (&$item, $key) use ($now) {
                        if ($key == 'entid') {
                            $item = $this->entId;
                        }
                        if (in_array($key, ['created_at', 'updated_at'])) {
                            $item = $now;
                        }
                    });
                    $saveLevel = $level;
                    if (isset($saveLevel['id'])) {
                        unset($saveLevel['id']);
                    }
                    $levelId                = Level::create($saveLevel)->id;
                    $levelIds[$level['id']] = $levelId;
                }
                $relations = Relation::where('entid', 0)->select(['*'])->get()?->toArray();
                if (! empty($relations) && $levelIds && $cateIds && $rankIds) {
                    foreach ($relations as $relation) {
                        try {
                            Relation::create([
                                'entid'    => $this->entId,
                                'level_id' => $levelIds[$relation['level_id']],
                                'cate_id'  => $cateIds[$relation['cate_id']],
                                'rank_id'  => $rankIds[$relation['rank_id']],
                            ]);
                        } catch (\Exception $e) {
                        }
                    }
                }
                return true;
            });
        }
        dump('initPosition');
        return true;
    }

    private function initAgreement()
    {
        if ($this->entId && ! Agreement::where('entid', $this->entId)->exists()) {
            $types = Agreement::where('entid', 0)->select(['id', 'ident', 'title', 'content'])->get()?->toArray();
            if ($types) {
                $save = [];
                foreach ($types as $type) {
                    $type['entid'] = $this->entId;
                    unset($type['id']);
                    $save[] = $type;
                }
                $save && Agreement::insert($save);
            }
        }
        dump('initAgreement');
    }

    private function initDict()
    {
        if ($this->entId && ! DictType::where('entid', $this->entId)->exists()) {
            $cate = DictType::where('entid', 0)->select(['id', 'name', 'ident', 'link_type', 'level', 'status', 'is_default', 'mark'])->get()?->toArray();
            if ($cate) {
                DB::transaction(function () use ($cate) {
                    $data = [];
                    foreach ($cate as $item) {
                        $dataItem = DictData::where('type_id', $item['id'])->where('entid', 0)->select(['*'])->get()?->toArray();
                        unset($item['id']);
                        $item['entid'] = $this->entId;
                        $newCate       = DictType::create($item);
                        if ($dataItem) {
                            foreach ($dataItem as $value) {
                                $value['type_id'] = $newCate->id;
                                $value['entid']   = $this->entId;
                                unset($value['id']);
                                $data[] = $value;
                            }
                        }
                    }
                    $data && DictData::insert($data);
                    return true;
                });
            }
        }
        dump('initDict');
    }

    private function initPaytype()
    {
        if ($this->entId && ! Paytype::where('entid', $this->entId)->exists()) {
            $cate = Paytype::where('entid', 0)->select(['id', 'name', 'ident', 'info', 'status'])->get()?->toArray();
            if ($cate) {
                DB::transaction(function () use ($cate) {
                    $data = [];
                    foreach ($cate as $item) {
                        unset($item['id']);
                        $item['entid'] = $this->entId;
                        $data[]        = $item;
                    }
                    $data && Paytype::insert($data);
                    return true;
                });
            }
        }
        dump('initPaytype');
    }

    private function initScheduleType()
    {
        if ($this->entId && ! ScheduleType::where('entid', $this->entId)->exists()) {
            $types = ScheduleType::where('entid', 0)->select(['id', 'name', 'sort', 'color', 'is_public', 'types'])->get()?->toArray();
            if ($types) {
                $save = [];
                foreach ($types as $type) {
                    $type['entid'] = $this->entId;
                    unset($type['id']);
                    $save[] = $type;
                }
                $save && ScheduleType::insert($save);
            }
        }
        dump('initScheduleType');
    }

    private function initBillCategory()
    {
        if ($this->entId && ! BillCategory::where('entid', $this->entId)->exists()) {
            $cate = BillCategory::where('entid', 0)->select(['id', 'name', 'cate_no', 'pid', 'types'])->get()?->toArray();
            if ($cate) {
                DB::transaction(function () use ($cate) {
                    $data = [];
                    foreach ($cate as $item) {
                        unset($item['id']);
                        $item['entid'] = $this->entId;
                        $data[]        = $item;
                    }
                    $data && BillCategory::insert($data);
                    return true;
                });
            }
        }
        dump('initBillCategory');
    }

    private function initFormCate()
    {
        if ($this->entId) {
            for ($i = 1; $i <= 3; ++$i) {
                if (FormCate::where('types', $i)->where('entid', $this->entId)->exists()) {
                    continue;
                }
                $cate = FormCate::where('types', $i)->where('entid', 0)->select(['*'])->get()?->toArray();
                if ($cate) {
                    DB::transaction(function () use ($cate) {
                        $data = [];
                        foreach ($cate as $item) {
                            $dataItem = FormData::where('cate_id', $item['id'])->where('entid', 0)->select(['*'])->get()?->toArray();
                            unset($item['id']);
                            $item['entid'] = $this->entId;
                            $newCate       = FormCate::create($item);
                            if ($dataItem) {
                                foreach ($dataItem as $value) {
                                    $value['cate_id'] = $newCate->id;
                                    $value['entid']   = $this->entId;
                                    unset($value['id']);
                                    is_array($value['value']) && $value['value'] = json_encode($value['value']);
                                    $data[]                                      = $value;
                                }
                            }
                        }
                        $data && FormData::insert($data);
                        return true;
                    });
                }
            }
        }
        dump('initFormCate');
    }

    private function initCrud()
    {
        if ($this->entId && ! SystemCrud::where('entid', $this->entId)->exists()) {
            DB::transaction(function () {
                $crud = SystemCrud::where('cate_ids', '')->where('entid', 0)->select(['*'])->get()?->toArray();
                foreach ($crud as $item) {
                    $crudId = $item['id'];
                    unset($item['id']);
                    $item['entid'] = $this->entId;
                    if (in_array($item['table_name_en'], ['customer', 'contract', 'customer_liaison'])) {
                        $item['table_name_en'] = $item['table_name_en'] . '_' . $this->entId;
                    }
                    $newCrud = SystemCrud::create($item);
                    $field   = SystemCrudField::where('crud_id', $crudId)->where('entid', 0)->select(['*'])->get()?->toArray();
                    foreach ($field as $fieldItem) {
                        unset($fieldItem['id']);
                        $fieldItem['entid']   = $this->entId;
                        $fieldItem['crud_id'] = $newCrud->id;
                        SystemCrudField::create($fieldItem);
                    }
                    $form = SystemCrudForm::where('crud_id', $crudId)->where('entid', 0)->select(['*'])->get()?->toArray();
                    foreach ($form as $formItem) {
                        unset($formItem['id']);
                        $formItem['entid']   = $this->entId;
                        $formItem['crud_id'] = $newCrud->id;
                        SystemCrudForm::create($formItem);
                    }
                }
            });
            $crudCate = SystemCrudCate::where('entid', 0)->select(['*'])->get()?->toArray();
            DB::transaction(function () use ($crudCate) {
                foreach ($crudCate as $cate) {
                    $cateId = $cate['id'];
                    unset($cate['id']);
                    $cate['entid'] = $this->entId;
                    $newCate       = SystemCrudCate::create($cate);
                    $crud          = SystemCrud::where('cate_ids', 'like', "%{$cateId}%")->where('entid', 0)->select(['*'])->get()?->toArray();
                    foreach ($crud as $item) {
                        $crudId = $item['id'];
                        unset($item['id']);
                        $item['entid']    = $this->entId;
                        $item['cate_ids'] = $newCate->id;
                        if (in_array($item['table_name_en'], ['customer', 'contract', 'customer_liaison'])) {
                            $item['table_name_en'] = $item['table_name_en'] . '_' . $this->entId;
                        }
                        $newCrud = SystemCrud::create($item);
                        $field   = SystemCrudField::where('crud_id', $crudId)->where('entid', 0)->select(['*'])->get()?->toArray();
                        foreach ($field as $fieldItem) {
                            unset($fieldItem['id']);
                            $fieldItem['entid']   = $this->entId;
                            $fieldItem['crud_id'] = $newCrud->id;
                            SystemCrudField::create($fieldItem);
                        }
                        $form = SystemCrudForm::where('crud_id', $crudId)->where('entid', 0)->select(['*'])->get()?->toArray();
                        foreach ($form as $formItem) {
                            unset($formItem['id']);
                            $formItem['entid']   = $this->entId;
                            $formItem['crud_id'] = $newCrud->id;
                            SystemCrudForm::create($formItem);
                        }
                    }
                }
            });
            DB::transaction(function () {
                $associationFields = SystemCrudField::where('association_crud_id', '>', 0)->where('entid', $this->entId)->select(['*'])->get()?->toArray();
                if ($associationFields) {
                    $defaultCrud = SystemCrud::where('entid', 0)->select(['*'])->get()?->toArray();
                    $crudInfo    = [];
                    foreach ($defaultCrud as $item) {
                        $crudInfo[$item['id']] = $item['table_name_en'];
                    }
                    foreach ($associationFields as $item) {
                        if (isset($crudInfo[$item['association_crud_id']])) {
                            $crudId = SystemCrud::where('entid', $this->entId)->where(function ($q) use ($crudInfo, $item) {
                                $q->where('table_name_en', $crudInfo[$item['association_crud_id']])->orWhere('table_name_en', $crudInfo[$item['association_crud_id']] . '_' . $this->entId);
                            })->first()?->id;
                            $crudId && SystemCrudField::where('id', $item['id'])->update(['association_crud_id' => $crudId]);
                            unset($crudId);
                        }
                    }
                }
            });
            DB::transaction(function () {
                $dictFields = SystemCrudField::where('data_dict_id', '>', 0)->where('entid', $this->entId)->select(['*'])->get()?->toArray();
                if ($dictFields) {
                    $defaultDict = DictType::where('entid', 0)->select(['id', 'ident'])->get()?->toArray();
                    $dictInfo    = [];
                    foreach ($defaultDict as $item) {
                        $dictInfo[$item['id']] = $item['ident'];
                    }
                    foreach ($dictFields as $item) {
                        isset($dictInfo[$item['data_dict_id']]) && SystemCrudField::where('id', $item['id'])->update(['data_dict_id' => DictType::where('entid', $this->entId)->where('ident', $dictInfo[$item['data_dict_id']])->first()?->id]);
                    }
                }
            });
        }
        dump('initCrud');
    }

    private function initCustomer()
    {
        $tableName = 'customer_' . $this->entId;
        if (! Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('uid')->default(0)->comment('业务员ID');
                $table->unsignedInteger('entid')->default($this->entId)->index()->comment('企业ID');
                $table->integer('before_uid')->default(0)->comment('前业务员ID');
                $table->integer('creator_uid')->default(0)->comment('创建人ID');
                $table->string('customer_name')->default('')->comment('客户名称');
                $table->string('customer_label')->default('""')->comment('客户标签');
                $table->string('customer_no', 15)->default('')->comment('客户编号');
                $table->string('customer_way')->default('""')->comment('客户来源');
                $table->integer('un_followed_days')->default(0)->comment('未跟进天数');
                $table->decimal('amount_recorded', 10)->default(0)->comment('已入账金额');
                $table->decimal('amount_expend', 10)->default(0)->comment('已支出+金额');
                $table->decimal('invoiced_amount', 10)->default(0)->comment('已开票金额');
                $table->integer('contract_num')->default(0)->comment('合同数量');
                $table->integer('invoice_num')->default(0)->comment('发票数量');
                $table->integer('attachment_num')->default(0)->comment('附件数量');
                $table->integer('return_num')->default(0)->comment('退回次数');
                $table->string('customer_followed')->default('1')->comment('是否关注');
                $table->string('customer_status')->default('0')->comment('客户状态');
                $table->string('area_cascade')->default('""')->comment('省市区');
                $table->string('b37a3f36')->default('')->comment('备注');
                $table->string('b37a3f16')->default('')->comment('企业电话');
                $table->string('9bfe77e4')->default('')->comment('详细地址');
                $table->string('7763f80f')->default('')->comment('客户附件');
                $table->string('c839a357')->default('')->comment('备注');
                $table->timestamp('last_follow_up_time')->nullable()->comment('最后跟进时间');
                $table->timestamp('collect_time')->nullable()->comment('领取时间');
                $table->timestamps();
                $table->softDeletes();
            });
        }
        dump('initCustomer');
    }

    private function initLiaison()
    {
        $tableName = 'customer_liaison_' . $this->entId;
        if (! Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('entid')->default($this->entId)->index()->comment('企业ID');
                $table->integer('uid')->default(0)->comment('业务员ID');
                $table->integer('eid')->default(0)->comment('客户ID');
                $table->integer('creator_uid')->default(0)->comment('创建人ID');
                $table->string('liaison_name')->default('')->comment('联系人姓名');
                $table->string('liaison_tel')->default('')->comment('联系电话');
                $table->string('liaison_job')->default('公司职员')->comment('联系人职位');
                $table->string('e06d7153')->default('')->comment('性别');
                $table->string('e06d7152')->default('')->comment('联系人邮箱');
                $table->string('e06d7159')->default('')->comment('联系人微信');
                $table->string('l753bf282')->default('')->comment('备注');
                $table->string('cdc4d06a')->default('')->comment('联系人QQ');
                $table->timestamps();
                $table->softDeletes();
            });
        }
        dump('initLiaison');
    }

    private function initContract()
    {
        $tableName = 'contract_' . $this->entId;
        if (! Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('entid')->default($this->entId)->index()->comment('企业ID');
                $table->integer('uid')->default(0)->comment('业务员ID');
                $table->integer('eid')->default(0)->comment('客户ID');
                $table->integer('creator_uid')->default(0)->comment('创建人ID');
                $table->string('contract_name')->default('')->comment('合同名称');
                $table->string('contract_no')->default('')->comment('合同编号');
                $table->decimal('contract_price', 10)->default(0)->comment('合同金额(元)');
                $table->decimal('received', 10)->default(0)->comment('回款金额');
                $table->decimal('surplus', 10)->default(0)->comment('尾款金额');
                $table->string('contract_followed')->default('1')->comment('是否关注');
                $table->string('contract_status')->default('')->comment('合同状态');
                $table->tinyInteger('renew')->default(0)->comment('是否有续费：0、否；1、是；');
                $table->date('start_date')->nullable()->comment('开始时间');
                $table->date('end_date')->nullable()->comment('结束时间');
                $table->string('signing_status')->default('')->comment('签约状态');
                $table->string('b3733f36')->default('')->comment('备注');
                $table->string('contract_category')->default('""')->comment('合同分类');
                $table->string('contract_cate')->default('""')->comment('合同分类copy');
                $table->tinyInteger('is_abnormal')->default(0)->comment('是否异常：1、是；0、否；');
                $table->timestamps();
                $table->softDeletes();
            });
        }
        dump('initContract');
    }

    private function initApiDoc()
    {
        if ($this->entId && ! OpenapiRule::where('entid', $this->entId)->exists()) {
            $rules = OpenapiRule::where('pid', 0)->where('entid', 0)->select(['*'])->get()?->toArray();
            if ($rules) {
                DB::transaction(function () use ($rules) {
                    $data = [];
                    foreach ($rules as $item) {
                        $dataItem = OpenapiRule::where('pid', $item['id'])->where('entid', 0)->select(['*'])->get()?->toArray();
                        unset($item['id']);
                        $item['entid'] = $this->entId;
                        $newRule       = OpenapiRule::create($item);
                        if ($dataItem) {
                            foreach ($dataItem as $value) {
                                $value['pid']   = $newRule->id;
                                $value['entid'] = $this->entId;
                                unset($value['id']);
                                $data[] = $value;
                            }
                        }
                    }
                    $data && OpenapiRule::insert($data);
                    return true;
                });
            }
        }
        dump('initApiDoc');
    }

    private function initAttendance()
    {
        if ($this->entId && ! AttendanceShift::where('entid', $this->entId)->exists()) {
            $uid  = app()->get(CompanyService::class)->setEntValue($this->entId)->value([], 'uid');
            $data = AttendanceShift::where('entid', 0)->where('types', 2)->first()?->toArray();
            $rule = AttendanceShiftRule::where('entid', 0)->first()?->toArray();
            if ($data) {
                DB::transaction(function () use ($data, $uid, $rule) {
                    $data['entid'] = $this->entId;
                    $data['uid']   = $uid;
                    unset($data['id']);
                    $newId            = AttendanceShift::create($data);
                    $rule['shift_id'] = $newId->id;
                    $rule['entid']    = $this->entId;
                    unset($rule['id']);
                    AttendanceShiftRule::create($rule);
                    return true;
                });
            }
        }
        dump('initAttendance');
    }
}
