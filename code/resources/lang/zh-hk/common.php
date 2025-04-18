<?php

//中文语言包

return [
    'empty'     => [
        'method'   => '方法 :class:::method 不存在。',
        'attrs'    => '缺少必要參數。',
        'attr'     => '缺少必要參數 :attr。',
        'property' => ':name内容不存在'
    ],
    'request'   => [
        'error'        => '請求錯誤',
        'noPermission' => '暫無許可權訪問',
    ],
    'data'      => [
        'typeError' => '錯誤的資料類型,它必須是閉包或數組.'
    ],
    'route'     => [
        'miss' => '您訪問的地址不存在'
    ],
    'upload'    => [
        'filesizeRrror' => '上傳文件大小超出系統限制',
        'fileExtError'  => '暫不支持該類型檔案，請上傳.jpeg, .gif, .bmp, .png, .jpg, .doc, .docx, .xls, .xlsx, .xlsm, .ppt, .pptx檔案',
        'fileMineError' => '暫不支持該類型檔案，請上傳.jpeg, .gif, .bmp, .png, .jpg, .doc, .docx, .xls, .xlsx, .xlsm, .ppt, .pptx檔案'
    ],
    'insert'    => [
        'succ'   => '添加成功！',
        'fail'   => '添加失敗！',
        'exists' => '已存在相同數據，請勿重複添加！',
    ],
    'delete'    => [
        'succ' => '删除成功！',
        'fail' => '删除失敗！',
    ],
    'update'    => [
        'succ' => '修改成功！',
        'fail' => '修改失敗！',
    ],
    'query'     => [
        'succ' => '査詢成功！',
        'fail' => '査詢失敗！',
    ],
    'operation' => [
        'succ' => '操作成功！',
        'fail' => '操作失敗！',
    ],
];
