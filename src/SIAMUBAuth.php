<?php
namespace MirzaHilmi;

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'Config.php';

use DOMDocument;
use DOMXPath;
use Exception;
use GuzzleHttp\Client;

/**
 * Class SIAMUBAuth
 *
 * The SIAMUBAuth class provides authentication and information retrieval functionalities for SIAM UB (Sistem Informasi Akademik Mahasiswa Universitas Brawijaya).
 *
 * @package MirzaHilmi
 */
class SIAMUBAuth
{
    /**
     * @var Client The GuzzleHttp client instance.
     */
    private static Client $client;

    /**
     * @var string The authentication token.
     */
    private string $token;

    /**
     * @var array The user information.
     */
    private array $information;

    /**
     * SIAMUBAuth constructor.
     *
     * Private constructor to prevent object creation with the "new" keyword.
     */
    private function __construct()
    {
    }

    /**
     * Authenticate the user with the given NIM and password.
     *
     * @param string $nim The user's NIM (Nomor Induk Mahasiswa).
     * @param string $password The user's password.
     * @param Client $client The GuzzleHttp client instance.
     * @return SIAMUBAuth|null The authenticated SIAMUBAuth instance or null on failure.
     */
    public static function authenticate(string $nim, string $password, Client $client): ?SIAMUBAuth
    {
        self::$client = $client;

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

    /**
     * Retrieve the cookie token from the client response.
     *
     * @return string The extracted cookie token.
     * @throws Exception If the token cannot be retrieved.
     */
    private function getCookieToken(): string
    {
        $res = self::$client->head(WEB_URL);

        if (!isset($res->getHeader('Set-Cookie')[0])) {
            throw new Exception('Failed to retrieve Token from Cookie. The "Set-Cookie" header is not present.');
        }

        return strstr($res->getHeader('Set-Cookie')[0], ';', true);
    }

    /**
     * Authenticate the user with the given NIM and password.
     *
     * @param string $nim The user's NIM (Nomor Induk Mahasiswa).
     * @param string $password The user's password.
     * @return string The authentication response body.
     * @throws Exception If the authentication fails.
     */
    public function auth(string $nim, string $password): string
    {
        if (empty($nim) || empty($password)) {
            throw new Exception('Could not authenticate. Empty NIM or Password!');
        }

        $res = self::$client->post(WEB_INDEX, [
            'headers' => [
                'Cookie' => $this->token,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'username' => $nim,
                'password' => $password,
                'login' => 'Masuk',
            ],
        ]);
        $content = trim($res->getBody()->getContents());

        // Verify Authentication Attempt
        $status = $this->extractContent($content, STATUS_XPATH);
        if (!empty($status)) {
            throw new Exception('Invalid NIM or Password Credentials!');
        }

        return $content;
    }

    /**
     * Populate the user information from the response body.
     *
     * @param string $body The response body.
     * @return void
     */
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

    /**
     * Extract the content from the response body using XPath.
     *
     * @param string $content The response body.
     * @param string $regex The XPath expression.
     * @return string The extracted content.
     */
    private function extractContent(string $content, string $regex): string
    {
        // Suppress document warnings
        libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        $dom->loadHTML($content);

        $xpath = new DOMXPath($dom);

        $elements = $xpath->query($regex);
        if (!$elements || $elements->length === 0) {
            return '';
        }

        return trim($elements[0]->nodeValue);
    }

    /**
     * Get the user information.
     *
     * @return array The user information.
     */
    public function getInformation(): array
    {
        return $this->information;
    }
}
