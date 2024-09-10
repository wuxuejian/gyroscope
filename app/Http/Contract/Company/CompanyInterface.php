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

namespace App\Http\Contract\Company;

interface CompanyInterface
{
    public function companyList(array $where, array $field, $sort, array $with): array;

    public function createCompanyForm(): array;

    public function createCompanySave(array $data): array;

    public function updateCompanyForm(int $id): array;

    public function updateCompanySave($id, array $data): array;
}
