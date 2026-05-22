<?php
require 'vendor/autoload.php';
require 'app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/Boot.php';
CodeIgniter\Boot::bootWeb($paths);
$svc = new App\Services\TourCatalogService();
$tour = $svc->findTourBySlug('vi', 'kham-pha-tay-au-phap-thuy-si-y-10n9d', 'outbound');
if (!$tour) {
    echo 'NO TOUR';
    exit;
}
echo "TITLE: ", $tour['title'], PHP_EOL;
foreach (($tour['itinerary_days'] ?? []) as $day) {
    echo 'DAY ', $day['day_number'] ?? '', ' | ', ($day['title'] ?? ''), PHP_EOL;
    echo ($day['description'] ?? ''), PHP_EOL, '---', PHP_EOL;
}
