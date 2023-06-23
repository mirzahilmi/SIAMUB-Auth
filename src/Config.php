<?php
// Web URL
const WEB_URL = 'https://siam.ub.ac.id';
define('WEB_INDEX', WEB_URL . '/index.php');

// Element's XPath
const STATUS_XPATH = '/html/body/div[1]/div/div[1]/div[2]/div/form/div/small';
const PARENT = '/html/body/table[2]/tbody/tr[1]/td[2]/table[1]/tbody/tr/td[2]';
define('CONTENT_XPATH', [
	PARENT . '/span/b',
	PARENT . '/b',
	PARENT . '/strong/big'
]);
