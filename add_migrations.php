<?php

include_once __DIR__ . "/../updater/FileUploader.php";

$uploader = new \Phphleb\Updater\FileUploader(__DIR__ . DIRECTORY_SEPARATOR . "embedded_files");

$uploader->setDesign(['base']);

$uploader->setPluginNamespace(__DIR__, 'Migration');

$uploader->setSpecialNames('migration', 'Migration');

$uploader->run();



