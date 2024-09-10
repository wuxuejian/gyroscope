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

namespace crmeb\utils;

/**
 * 统计
 * Class Statistics.
 */
class Statistics
{
    /**
     * 百分比计算.
     * @param mixed $rateList
     * @param mixed $statisticKey
     * @param mixed $calcKey
     * @param mixed $ratioKey
     */
    public static function calcRatio($rateList, $statisticKey, $calcKey, $ratioKey, float $total = 0): array
    {
        if (count($rateList) === 0) {
            return [];
        }
        $sum    = $total ?: array_sum(array_column($rateList, $statisticKey));
        $result = [];
        foreach ($rateList as $item) {
            $value = '0.00';
            if ($item[$statisticKey] > 0) {
                $value = strval(($item[$statisticKey] / $sum) * 10000);
            }
            $tmp                        = explode('.', $value);
            $result[$value]['integer']  = $tmp[1] ?? 0;
            $result[$value][$ratioKey]  = $tmp[0] ?? 0;
            $result[$value]['calc_key'] = $item[$calcKey];
        }

        $count = 10000 - array_sum(array_column($result, $ratioKey));

        $rateKey = [];
        foreach ($result as $key => $value) {
            $rateKey[$value['calc_key']] = $key < $count ? ($value[$ratioKey] + 1) / 100 : $value[$ratioKey] / 100;
        }

        foreach ($rateList as &$item) {
            $item[$ratioKey] = $item[$statisticKey] > 0 ? $rateKey[$item[$calcKey]] ?? 0 : 0;
        }
        return $rateList;
    }

    /**
     * 环比计算.
     * @param mixed $first
     * @param mixed $second
     */
    public static function ringRatio($first, $second): int
    {
        $first  = (string) $first;
        $second = (string) $second;
        if ($first == $second) {
            return 0;
        }

        if ($second == 0) {
            $ratio = 100;
        } else {
            $ratio = (int) bcmul(bcdiv(bcsub($first, $second, 2), $second, 2), '100');
        }
        return $ratio;
    }
}
