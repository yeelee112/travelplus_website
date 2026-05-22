<?php
require 'vendor/autoload.php';
require 'app/Services/WebsiteKnowledgeService.php';
$s = new App\Services\WebsiteKnowledgeService();
$r = $s->getRelevantContext('vi', 'Website đang có những phương thức thanh toán nào?', 8);
echo $r['summary'];
