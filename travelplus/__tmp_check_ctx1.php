<?php
require 'vendor/autoload.php';
require 'app/Services/WebsiteKnowledgeService.php';
$s = new App\Services\WebsiteKnowledgeService();
$r = $s->getRelevantContext('vi', 'Travel Plus có hỗ trợ visa không?', 8);
echo $r['summary'];
