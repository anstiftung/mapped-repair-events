<?php

$explodedName = explode('.', $name);
$convertedName = strtolower($explodedName[0]).'-'.$explodedName[1].'';

if ($uid === null) {
    echo '<p style="margin-bottom: 10px;clear: both;">Um Bilder und Dateien hochzuladen, bitte zuerst speichern.</p>';
    $this->element('addScript', ['script' => 
        JS_NAMESPACE . ".Helper.initCkeditorWithoutElfinder('".$convertedName."', ".$this->request->getSession()->read('isMobile').");"
	]);
} else {
    $this->element('addScript', ['script' =>
        JS_NAMESPACE . ".Helper.initCkeditor('".$convertedName."', ".$this->request->getSession()->read('isMobile').");"
    ]);
}

if (isset($objectType) && isset($uid)) {
    $_SESSION['ELFINDER'] = [];
    $_SESSION['ELFINDER']['uploadUrl'] = "/files/kcfinder/".$objectType."/".$uid;
    $uploadDir = str_replace('//', '/', $_SERVER['DOCUMENT_ROOT']."files/kcfinder/".$objectType."/".$uid);
    $_SESSION['ELFINDER']['uploadPath'] = $uploadDir;
}

echo $this->Form->control($name,
    [
        'type' => 'textarea',
        'value' => $value,
        'label' => '',
        'style' => 'width:610px;',
        'class' => 'ckeditor'
    ]
);

?>
