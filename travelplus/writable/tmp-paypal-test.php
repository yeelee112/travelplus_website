<?php
$ch = curl_init('https://api-m.sandbox.paypal.com/v1/oauth2/token');
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
  CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
  CURLOPT_USERPWD => 'AXVC404DSGvCwd1JkBEayO3dCWaxFb__9U5G6nhblwdK1wAhI----NEhaGiHbV5rokQle7mk--znhL-i:EDj4lvXVQn3nXJnuj6UhxeGflnCUwibHFqPWuaR9X-6QvODM9HTBvZs1XER8KupVVyeVp31OH0LIO4ey',
  CURLOPT_HTTPHEADER => [
    'Accept: application/json',
    'Accept-Language: en_US',
    'Content-Type: application/x-www-form-urlencoded',
  ],
]);
$response = curl_exec($ch);
var_dump(curl_errno($ch));
var_dump(curl_error($ch));
var_dump(curl_getinfo($ch, CURLINFO_HTTP_CODE));
var_dump($response === false ? false : substr($response, 0, 300));
curl_close($ch);
?>
