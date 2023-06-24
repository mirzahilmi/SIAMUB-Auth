<?php
require_once 'SIAMUBAuth.php';

use MirzaHilmi\SIAMUBAuth;

$user = SIAMUBAuth::authenticate('', '');

foreach ($user->information as $data) {
  echo $data . PHP_EOL;
}
