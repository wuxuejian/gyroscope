<?php

/*
|--------------------------------------------------------------------------
| Validation Language Lines
|--------------------------------------------------------------------------
|
| The following language lines contain the default error messages used by
| the validator class. Some of these rules have multiple versions such
| as the size rules. Feel free to tweak each of these messages here.
|
*/

return [
    'accepted'             => '必須接受 :attribute。',
    'accepted_if'          => 'The :attribute must be accepted when :other is :value.',
    'active_url'           => ':attribute 並非一個有效的網址。',
    'after'                => ':attribute 必須要晚於 :date。',
    'after_or_equal'       => ':attribute 必須要等於 :date 或更晚。',
    'alpha'                => ':attribute 只能以字母組成。',
    'alpha_dash'           => ':attribute 只能以字母、數字、連接線(-)及底線(_)組成。',
    'alpha_num'            => ':attribute 只能以字母及數字組成。',
    'array'                => ':attribute 必須為陣列。',
    'attached'             => '此 :attribute 已附加。',
    'before'               => ':attribute 必須要早於 :date。',
    'before_or_equal'      => ':attribute 必須要等於 :date 或更早。',
    'between'              => [
        'array'   => ':attribute: 必須有 :min 至 :max 個項目。',
        'file'    => ':attribute 必須介乎 :min 至 :max KB 之間。',
        'numeric' => ':attribute 必須介乎 :min 至 :max 之間。',
        'string'  => ':attribute 必須介乎 :min 至 :max 個字符之間。',
    ],
    'boolean'              => ':attribute 必須是布爾值。',
    'confirmed'            => ':attribute 確認欄位的輸入並不相符。',
    'current_password'     => '密碼不正確.',
    'date'                 => ':attribute 並非一個有效的日期。',
    'date_equals'          => ':attribute 必須等於 :date。',
    'date_format'          => ':attribute 與 :format 格式不相符。',
    'different'            => ':attribute 與 :other 必須不同。',
    'digits'               => ':attribute 必須是 :digits 位數字。',
    'digits_between'       => ':attribute 必須介乎 :min 至 :max 位數字。',
    'dimensions'           => ':attribute 圖片尺寸不正確。',
    'distinct'             => ':attribute 已經存在。',
    'email'                => ':attribute 必須是有效的電郵地址。',
    'ends_with'            => ':attribute 結尾必須包含下列之一：:values。',
    'exists'               => ':attribute 不存在。',
    'file'                 => ':attribute 必須是文件。',
    'filled'               => ':attribute 不能留空。',
    'gt'                   => [
        'array'   => ':attribute 必須多於 :value 個項目。',
        'file'    => ':attribute 必須大於 :value KB。',
        'numeric' => ':attribute 必須大於 :value。',
        'string'  => ':attribute 必須多於 :value 個字符。',
    ],
    'gte'                  => [
        'array'   => ':attribute 必須多於或等於 :value 個項目。',
        'file'    => ':attribute 必須大於或等於 :value KB。',
        'numeric' => ':attribute 必須大於或等於 :value。',
        'string'  => ':attribute 必須多於或等於 :value 個字符。',
    ],
    'image'                => ':attribute 必須是一張圖片。',
    'in'                   => '所揀選的 :attribute 選項無效。',
    'in_array'             => ':attribute 沒有在 :other 中。',
    'integer'              => ':attribute 必須是一個整數。',
    'ip'                   => ':attribute 必須是一個有效的 IP 地址。',
    'ipv4'                 => ':attribute 必須是一個有效的 IPv4 地址。',
    'ipv6'                 => ':attribute 必須是一個有效的 IPv6 地址。',
    'json'                 => ':attribute 必須是正確的 JSON 格式。',
    'lt'                   => [
        'array'   => ':attribute 必須少於 :value 個項目。',
        'file'    => ':attribute 必須小於 :value KB。',
        'numeric' => ':attribute 必須小於 :value。',
        'string'  => ':attribute 必須少於 :value 個字符。',
    ],
    'lte'                  => [
        'array'   => ':attribute 必須少於或等於 :value 個項目。',
        'file'    => ':attribute 必須小於或等於 :value KB。',
        'numeric' => ':attribute 必須小於或等於 :value。',
        'string'  => ':attribute 必須少於或等於 :value 個字符。',
    ],
    'max'                  => [
        'array'   => ':attribute 不能多於 :max 個項目。',
        'file'    => ':attribute 不能大於 :max KB。',
        'numeric' => ':attribute 不能大於 :max。',
        'string'  => ':attribute 不能多於 :max 個字符。',
    ],
    'mimes'                => ':attribute 必須為 :values 的檔案。',
    'mimetypes'            => ':attribute 必須為 :values 的檔案。',
    'min'                  => [
        'array'   => ':attribute 不能小於 :min 個項目。',
        'file'    => ':attribute 不能小於 :min KB。',
        'numeric' => ':attribute 不能小於 :min。',
        'string'  => ':attribute 不能小於 :min 個字符。',
    ],
    'multiple_of'          => '所揀選的 :attribute 必須為 :value 中的多個。',
    'not_in'               => '所揀選的 :attribute 選項無效。',
    'not_regex'            => ':attribute 的格式錯誤。',
    'numeric'              => ':attribute 必須為一個數字。',
    'password'             => '密碼錯誤。',
    'present'              => ':attribute 必須存在。',
    'prohibited'           => ':attribute 字段被禁止。',
    'prohibited_if'        => '当 :other 为 :value 时，:attribute字段被禁止。',
    'prohibited_unless'    => ':attribute 字段被禁止，除非 :other 位于 :values 中。',
    'prohibits'            => 'The :attribute field prohibits :other from being present.',
    'regex'                => ':attribute 的格式錯誤。',
    'relatable'            => '此 :attribute 可能与此资源不相关联。',
    'required'             => ':attribute 不能留空。',
    'required_if'          => '當 :other 是 :value 時 :attribute 不能留空。',
    'required_unless'      => '當 :other 不是 :values 時 :attribute 不能留空。',
    'required_with'        => '當 :values 出現時 :attribute 不能留空。',
    'required_with_all'    => '當 :values 出現時 :attribute 不能留空。',
    'required_without'     => '當 :values 留空時 :attribute field 不能留空。',
    'required_without_all' => '當 :values 都不出現時 :attribute 不能留空。',
    'same'                 => ':attribute 與 :other 必須相同。',
    'size'                 => [
        'array'   => ':attribute 必須是 :size 個單元。',
        'file'    => ':attribute 的大小必須是 :size KB。',
        'numeric' => ':attribute 的大小必須是 :size。',
        'string'  => ':attribute 必須是 :size 個字符。',
    ],
    'starts_with'          => ':attribute 開頭必須包含下列之一：:values。',
    'string'               => ':attribute 必須是一個字符串',
    'timezone'             => ':attribute 必須是一個正確的時區值。',
    'unique'               => ':attribute 已經存在。',
    'uploaded'             => ':attribute 上傳失敗。',
    'url'                  => ':attribute 的格式錯誤。',
    'uuid'                 => ':attribute 必須是有效的 UUID。',
    'custom'               => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],
];
