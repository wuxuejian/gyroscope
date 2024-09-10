<?php

//中文语言包

return [
    'empty'     => [
        'method'   => '方法 :class:::method 不存在。',
        'attrs'    => '缺少必要参数。',
        'attr'     => '缺少必要参数 :attr。',
        'property' => ':name属性不存在'
    ],
    'request'   => [
        'error'        => '请求错误',
        'noPermission' => '暂无权限访问',
    ],
    'data'      => [
        'typeError' => '错误的数据类型。它必须是闭包或数组'
    ],
    'route'     => [
        'miss' => '无效的请求地址！'
    ],
    'upload'    => [
        'filesizeRrror' => '上传文件大小类型超出系统限制',
        'fileExtError'  => '暂不支持上传该文件格式',
        'fileMineError' => '暂不支持上传该文件格式！',
        'succ'          => '上传成功！',
        'fail'          => '上传失败！',
        'noPermission'  => '暂无权限操作！',
    ],
    'insert'    => [
        'succ'   => '添加成功！',
        'fail'   => '添加失败！',
        'exists' => '已存在相同数据，请勿重复添加！',
    ],
    'delete'    => [
        'succ' => '删除成功！',
        'fail' => '删除失败！',
    ],
    'update'    => [
        'succ' => '修改成功！',
        'fail' => '修改失败！',
    ],
    'query'     => [
        'succ' => '查询成功！',
        'fail' => '查询失败！',
    ],
    'operation' => [
        'succ'         => '操作成功！',
        'fail'         => '操作失败！',
        'noPermission' => '您暂无权限操作该内容！',
        'exists'       => '请勿重复操作！',
        'noExists'     => '操作的记录不存在！',
    ],
];
