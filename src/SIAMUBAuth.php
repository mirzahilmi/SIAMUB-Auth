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

	// Private constructor to prevent object creation with "new"
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

		return strstr($res->getHeader('Set-Cookie')[0], ';', true);
	}

	public function auth(string $nim, string $password): void
	{
		if (empty($nim) || empty($password)) throw new Exception('Could not authenticate. Empty NIM or Password!');

		$headers = [
			'Cookie' => $this->token,
			'Content-Type' => 'application/x-www-form-urlencoded'
		];

		$res = self::$client->post(WEB_INDEX, [
			'headers' => [
				'Cookie' => $this->getCookieToken(),
				'Content-Type' => 'application/x-www-form-urlencoded'
			],
			'form_params' => [
				'username' => $nim,
				'password' => $password,
				'login' => 'Masuk'
			]
		]);
		
		// Verify Authentication Attempt
		$status = $this->extractValue($res->getBody()->getContents(), STATUS_XPATH);
		if (!empty($status)) throw new Exception('Invalid NIM or Password Credentials!');
	}
	
	private function extractValue(string $content, string $pattern): string
	{
		$doc = new DOMDocument();
		$doc->loadHTML($content);
		
		$xpath = new DOMXPath($doc);

		// HTML is often wonky, this suppresses a lot of warnings
		libxml_use_internal_errors(true);

		return $xpath->query($pattern);
	}
}
