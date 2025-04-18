<?php

//中文语言包

return [
    'empty'     => [
        'method'   => '方法 :class:::method 不存在。',
        'attrs'    => 'Paramètres nécessaires manquants。',
        'attr'     => 'Paramètres nécessaires manquants :attr。',
        'property' => ":name La propriété n'existe pas"
    ],
    'request'   => [
        'error'        => 'Erreur de demande',
        'noPermission' => "Pas d'accès autorisé pour le moment",
    ],
    'data'      => [
        'typeError' => "Mauvais type de données,Il doit s'agir d'une fermeture ou d'un tableau"
    ],
    'route'     => [
        'miss' => "L'adresse que vous avez consultée n'existe pas"
    ],
    'upload'    => [
        'filesizeRrror' => 'La taille du fichier téléchargé dépasse la limite du système',
        'fileExtError'  => 'Ce type de fichier n`est pas pris en charge pour le moment，Veuillez télécharger.jpeg, .gif, .bmp, .png, .jpg, .doc, .docx, .xls, .xlsx, .xlsm, .ppt, .pptx Documentation',
        'fileMineError' => 'Ce type de fichier n`est pas pris en charge pour le moment，Veuillez télécharger.jpeg, .gif, .bmp, .png, .jpg, .doc, .docx, .xls, .xlsx, .xlsm, .ppt, .pptx Documentation'
    ],
    'insert'    => [
        'succ'   => 'Ajouté avec succès！',
        'fail'   => "Impossible d'ajouter！",
        'exists' => 'Les mêmes données existent déjà, ne les ajoutez pas à plusieurs reprises！',
    ],
    'delete'    => [
        'succ' => 'Suppression réussie！',
        'fail' => 'Échec de la suppression！',
    ],
    'update'    => [
        'succ' => 'Modification réussie！',
        'fail' => 'Échec de la modification！',
    ],
    'query'     => [
        'succ' => 'Requête réussie！',
        'fail' => 'La requête a échoué！',
    ],
    'operation' => [
        'succ' => 'Opération réussie！',
        'fail' => "L'opération a échoué！",
    ],
];
