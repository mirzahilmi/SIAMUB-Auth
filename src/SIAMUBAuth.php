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
		$content = trim($res->getBody()->getContents());

		// Verify Authentication Attempt
		$status = $this->extractValue($content, STATUS_XPATH);
		if (!empty($status)) throw new Exception('Invalid NIM or Password Credentials!');

		$this->populate($content);
	}

	// $keyNames = ['nim', 'nama', 'jenjang', 'fakultas', 'jurusan', 'program_studi', 'seleksi', 'nomor_ujian', 'status'];
	private function populate(string $body): void
	{
		$datas = $this->extractValue($body, CONTENT_XPATH);

		$this->information = [
			'nim' => $datas[1],
			'nama' => $datas[2],
			'jenjang' => $datas[3],
			'fakultas' => $datas[4],
			'jurusan' => $datas[5],
			// 'program_studi' => $datas[6],
			// 'seleksi' => $datas[7],
			// 'nomor_ujian' => $datas[8],
			// 'status' => $datas[9],
		];
	}

	private function extractValue(string $content, string $pattern): string|array
	{
		$matches = '';
		preg_match($pattern, $content, $matches);
		return $matches;
	}
}
