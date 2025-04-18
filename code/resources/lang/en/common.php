<?php

//英文语言包
return [
    'empty'     => [
        'method'   => 'Method :class:::method does not exist.',
        'attrs'    => 'Missing required parameters！',
        'attr'     => 'Missing required parameter :attr。',
        'property' => ':name Property does not exist'
    ],
    'request'   => [
        'error' => 'Wrong request method'
    ],
    'data'      => [
        'typeError' => 'Wrong data type. It must be a closure or an array'
    ],
    'route'     => [
        'miss' => 'The address you visited does not exist'
    ],
    'upload'    => [
        'filesizeRrror' => 'The uploaded file size exceeds the system limit',
        'fileExtError'  => 'This type of file is not supported at the moment，Please upload .jpeg, .gif, .png, .jpg, .doc, .docx, .xls, .xlsx, .xlsm, .ppt, .pptx file',
        'fileMineError' => 'This type of file is not supported at the moment，Please upload .jpeg, .gif, .png, .jpg, .doc, .docx, .xls, .xlsx, .xlsm, .ppt, .pptx file'
    ],
    'insert'    => [
        'succ'   => 'Added successful！',
        'fail'   => 'Added failed！',
        'exists' => 'The same data already exists, please do not add it again！',
    ],
    'delete'    => [
        'succ' => 'Delete successful！',
        'fail' => 'Delete failed！',
    ],
    'update'    => [
        'succ' => 'Modified successful！',
        'fail' => 'Modified failed！',
    ],
    'query'     => [
        'succ' => 'Query was successful！',
        'fail' => 'Query was failed！',
    ],
    'operation' => [
        'succ'         => 'Operation successful！',
        'fail'         => 'Operation failed！',
        'noPermission' => '您暂无权限操作该内容！',
    ],
];
