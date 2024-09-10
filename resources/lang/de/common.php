<?php

//德语语言包
return [
    'empty'     => [
        'method'   => 'Method :class:::method does not exist.',
        'attrs'    => 'Fehlende Parameter。',
        'attr'     => 'Fehlende Parameter:attr。',
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
        'filesizeRrror' => 'Dateigröße hochladen übertrifft Systembegrenzung',
        'fileExtError'  => 'Diese Art von Datei wird im Moment nicht unterstützt，Bitte laden.jpeg, .gif, .png, .jpg, .doc, .docx, .xls, .xlsx, .xlsm, .ppt, .pptx Datei',
        'fileMineError' => 'Diese Art von Datei wird im Moment nicht unterstützt，Bitte laden.jpeg, .gif, .png, .jpg, .doc, .docx, .xls, .xlsx, .xlsm, .ppt, .pptx Datei'
    ],
    'insert'    => [
        'succ'   => 'Erfolgreich hinzugefügt！',
        'fail'   => 'Hinzufügen fehlgeschlagen！',
        'exists' => 'Die gleichen Daten gibt es bereits, bitte fügen Sie sie nicht wieder hinzu！',
    ],
    'delete'    => [
        'succ' => 'Löschen erfolgreich！',
        'fail' => 'Löschen fehlgeschlagen！',
    ],
    'update'    => [
        'succ' => 'Geändert erfolgreich！',
        'fail' => 'Änderung fehlgeschlagen！',
    ],
    'query'     => [
        'succ' => 'Abfrage erfolgreich！',
        'fail' => 'Abfrage fehlgeschlagen！',
    ],
    'operation' => [
        'succ' => 'Operation erfolgreich！',
        'fail' => 'Operation fehlgeschlagen！',
    ],
];
