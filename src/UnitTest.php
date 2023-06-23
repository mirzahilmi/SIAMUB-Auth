<?php
require_once 'SIAMUBAuth.php';

use MirzaHilmi\SIAMUBAuth;

$user = SIAMUBAuth::authenticate('2345667283578', 'abdultokum');

echo $auth->information;