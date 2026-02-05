<?php

$countries = json_decode(
  file_get_contents(APPPATH . 'Views/data/countriesV3.1.json'),
  true
);

$data = [];

// country list
foreach ($countries as $c) {
  if (!isset($c['name']['common'])) continue;

  $data[] = [
    'type' => 'country',
    'name' => $c['name']['common'],
    'code' => $c['cca2'] ?? null
  ];
}

// destination hot (thêm tay)
// $data[] = [
//   'type' => 'city',
//   'name' => 'Bali',
//   'country' => 'Indonesia'
// ];

// $data[] = [
//   'type' => 'city',
//   'name' => 'Bangkok',
//   'country' => 'Thailand'
// ];

return $data;
