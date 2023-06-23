<?php

namespace MirzaHilmi;

require __DIR__ . '/../vendor/autoload.php';
require 'config.php';

use DOMDocument;
use DOMXPath;
use Exception;
use GuzzleHttp\Client;

class SIAMUBAuth
{
	private static Client $client;
	private string $token;

	public $information;

	private function __construct()
	{
	}

	public static function authenticate(string $nim, string $password): ?SIAMUBAuth
	{
		self::$client = new Client();

		try {
			$user = new self();

			$user->token = $user->getCookieToken();
			$user->auth($nim, $password);

			return $user;
		} catch (Exception $e) {
			error_log('Error: ', $e->getMessage());
			return null;
		}
	}

	private function getCookieToken(): string
	{
		$res = self::$client->head(WEB_URL);

		if (!isset($res->getHeader('Set-Cookie')[0])) throw new Exception('Failed to retrieve Token from Cookie. The "Set-Cookie" header is not present.');

		return $res->getHeader('Set-Cookie')[0];
	}

	public function auth(string $nim, string $password)
	{
		if (empty($nim) || empty($password)) throw new Exception('Could not authenticate. Empty NIM or Password!');

		$headers = [
			'Cookie' => 'PHPSESSID=riqd29ioeelai8vbs3h8ek9ta2',
			'Content-Type' => 'application/x-www-form-urlencoded'
		];

		$res = self::$client->post(WEB_INDEX, [
			'form_params' => [
				'username' => $nim,
				'password' => $password,
				'login' => 'Masuk'
			]
		]);

		$html = $res->getBody()->getContents();

		// HTML is often wonky, this suppresses a lot of warnings
		libxml_use_internal_errors(true);

		$doc = new DOMDocument();
		$doc->loadHTML($html);

		$xpath = new DOMXPath($doc);

		$status = $xpath->evaluate("//small[contains(@class, 'error-code')]")[0];
		if (!empty($status)) throw new Exception('Invalid NIM or Password Credentials!');
	}
}
