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

namespace crmeb\services\expressionLanguage;

use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage as SymfonyExpressionLanguage;

/**
 * 表达式语言
 */
class ExpressionLanguage
{
    /**
     * @var SymfonyExpressionLanguage
     */
    private $language;

    public function __construct()
    {
        $this->language = new SymfonyExpressionLanguage();
        $this->language->registerProvider(new CustomBcDivExpressionFunctionProvider());
        $this->language->registerProvider(new CustomBcAddExpressionFunctionProvider());
        $this->language->registerProvider(new CustomBcmulExpressionFunctionProvider());
        $this->language->registerProvider(new CustomBcsubExpressionFunctionProvider());
    }

    /**
     * 执行表达式.
     * @return mixed
     */
    public function evaluate(Expression|string $expression, array $values = [])
    {
        return $this->language->evaluate($expression, $values);
    }
}
