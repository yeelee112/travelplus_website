<?php
require 'vendor/autoload.php';
require 'app/Data/VisaPageContent.php';
$d = App\Data\VisaPageContent::get('vi');
echo $d['hero_title'], PHP_EOL, $d['hero_desc'], PHP_EOL;
