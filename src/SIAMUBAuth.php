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
	private string $bodyContent;

	// Accessible User Field
	public array $information;

	// Private constructor to prevent object creation with "new" keyword
	private function __construct()
	{
	}

	public static function authenticate(string $nim, string $password): ?SIAMUBAuth
	{
		self::$client = new Client();

		try {
			$user = new self();

			$user->token = $user->getCookieToken();
			$content = $user->auth($nim, $password);
			$user->populate($content);

			return $user;
		} catch (Exception $e) {
			error_log('Error: ' . $e->getMessage());
			return null;
		}
	}

	private function getCookieToken(): string
	{
		$res = self::$client->head(WEB_URL);

		if (!isset($res->getHeader('Set-Cookie')[0])) throw new Exception('Failed to retrieve Token from Cookie. The "Set-Cookie" header is not present.');

		return strstr($res->getHeader('Set-Cookie')[0], ';', true);
	}

	public function auth(string $nim, string $password): string
	{
		if (empty($nim) || empty($password)) throw new Exception('Could not authenticate. Empty NIM or Password!');

		$res = self::$client->post(WEB_INDEX, [
			'headers' => [
				'Cookie' => $this->token,
				'Content-Type' => 'application/x-www-form-urlencoded'
			],
			'form_params' => [
				'username' => $nim,
				'password' => $password,
				'login' => 'Masuk'
			]
		]);
		$content = trim($res->getBody()->getContents());

		// Verify Authentication Attempt
		$status = $this->extractContent($content, STATUS_XPATH);
		if (!empty($status)) throw new Exception('Invalid NIM or Password Credentials!');

		return $content;
	}

	private function populate(string $body): void
	{
		$contents = $this->extractContent($body, CONTENT_XPATH);
		$datas = preg_split('/\s{2,}/', $contents);

		$take = function (string $str): string {
			return str_replace(': ', '', strstr($str, ':'));
		};

		$jenjang = explode('/', $take($datas[3]));

		$this->information = [
			'nim' => $datas[0],
			'nama' => $datas[1],
			'jenjang' => $jenjang[0],
			'fakultas' => $jenjang[1],
			'jurusan' => $take($datas[4]),
			'program_studi' => $take($datas[5]),
			'seleksi' => $take($datas[6]),
			'nomor_ujian' => $take($datas[7]),
			'status' => $take($datas[8]) == 'Aktif' ? true : false,
		];
	}

	private function extractContent(string $content, string $regex): string
	{
		// Supress document warnings
		libxml_use_internal_errors(true);

		$dom = new DOMDocument();
		$dom->loadHTML($content);

		$xpath = new DOMXPath($dom);

		$elements = $xpath->query($regex);
		if (!$elements || $elements->length === 0) return '';

		return trim($elements[0]->nodeValue);
	}
}
