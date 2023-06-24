<?php
// require_once 'SIAMUBAuth.php';
require 'config.php';

// use MirzaHilmi\SIAMUBAuth;

libxml_use_internal_errors(true);

$dom = new DOMDocument();
// $dom->loadHTMLFile(__DIR__ . '\failed-auth-sample.html');

$xpath = new DOMXPath($dom);
// $elements = $xpath->query('/html/body/div[1]/div/div[1]/div[2]/div/form/div/small[1]');

// echo count($elements);
// echo trim($elements[0]->nodeValue);

$dom->loadHTMLFile(__DIR__ . '\success-auth-sample.html');
$xpath = new DOMXPath($dom);
$elements = $xpath->query('//td[contains(@class, \'text\') and contains(@width, \'363\') and contains(@valign, \'top\')]');

// echo $dom->saveHTML();\

// 

$nodeValue = trim($elements[0]->nodeValue);
$datas = preg_split('/\s{2,}/', $nodeValue);
echo $datas[4];
// foreach ($nodes as $node) {
//   echo $node->nodeValue. "\n";
// }