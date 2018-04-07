<?php
include_once('../src/ImageProcess.php');
include_once('../src/ImageObject.php');
include_once('../src/Filter.php');


$imageProcess = new \ImageProcess\ImageProcess(__DIR__ . '\\');
$imageProcess->setImagesCaching(false);
$imageProcess->registerImage('main', __DIR__ . '\test.jpg');

$imageProcess->registerFilter('crop', 'width=100,height=50, valign=top, halign=center');
$imageProcess->registerExport('main', 'png', __DIR__ . '\test.png');
$imageProcess->executeProcess();

echo 'done';