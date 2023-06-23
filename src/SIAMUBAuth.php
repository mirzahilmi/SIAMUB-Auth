<?php

namespace MirzaHilmi;

require __DIR__ . '/../vendor/autoload.php';
require 'config.php';

use DOMDocument;
use DOMNode;
use DOMXPath;
use Exception;
use GuzzleHttp\Client;

class SIAMUBAuth
{
	private static Client $client;
	private string $token;
	private string $bodyContent;

	public array $information;

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
			$user->populate();

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
		$content = $res->getBody()->getContents();

		// Verify Authentication Attempt
		$status = $this->extractValue($content, STATUS_XPATH);
		if (!empty($status)) throw new Exception('Invalid NIM or Password Credentials!');

		$this->bodyContent = $content;
	}

	// $keyNames = ['nim', 'nama', 'jenjang', 'fakultas', 'jurusan', 'program_studi', 'seleksi', 'nomor_ujian', 'status'];
	private function populate(): void
	{
		$values = $this->extractValue($this->bodyContent, [CONTENT_XPATH[0], CONTENT_XPATH[1], CONTENT_XPATH[2]]);

		$this->information = ['nim' => $values[0], 'nama' => $values[1]];

		$elementStr = $this->extractValue($this->bodyContent, PARENT);
		$inner = explode('<br>', $elementStr);

		echo $inner;
	}

	private function extractValue(string $content, string|array $patterns): string|array
	{
		// HTML is often wonky, this suppresses a lot of warnings
		libxml_use_internal_errors(true);

		$doc = new DOMDocument();
		$doc->loadHTML($content);

		$xpath = new DOMXPath($doc);

		if (!is_array($patterns)) {
			return $this->innerHTML($xpath->query($patterns[0])[0]);
		}

		$arr = [];
		$i = 0;
		foreach ($patterns as $pattern) {
			$arr[] = $this->innerHTML($xpath->query($pattern)[$i]);
		}

		return $arr;
	}

	private function innerHTML(DOMNode $element): string
	{
		$innerHTML = "";
		$children  = $element->childNodes;

		foreach ($children as $child) {
			$innerHTML .= $element->ownerDocument->saveHTML($child);
		}

		return $innerHTML;
	}
}
