<?php
include_once('../src/ImageProcess.php');
include_once('../src/ImageObject.php');
include_once('../src/Filter.php');


$imageProcess = new \ImageProcess\ImageProcess(__DIR__ . '\\');
$imageProcess->setImagesCaching(false);
$imageProcess->registerImage('main', __DIR__ . '\test2.png');

$imageProcess->registerFilter('crop', 'width=100,height=50, valign=top, halign=center');
$imageProcess->registerExport('main', 'webp', __DIR__ . '\test.webp', 100);
$imageProcess->executeProcess();

echo 'done';