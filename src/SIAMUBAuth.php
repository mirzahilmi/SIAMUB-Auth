<?php

namespace MirzaHilmi;

require_once 'Config.php';

use DOMDocument;
use DOMNode;
use DOMXPath;
use Exception;
use GuzzleHttp\Client;
use MirzaHilmi\Models\Mahasiswa;

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
    private static $client;

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
     * @return Mahasiswa|null The authenticated SIAMUBAuth instance or null on failure.
     */
    public static function authenticate(string $nim, string $password, Client $client): ?Mahasiswa
    {
        self::$client = $client;

        try {
            $token = self::getCookieToken();
            $content = self::auth($nim, $password, $token);
            self::invalidate($token);
            $datas = self::process($content);

            return new Mahasiswa($datas);
        } catch (Exception $e) {

            self::invalidate($token);
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
    private static function getCookieToken(): string
    {
        $res = self::$client->head(BASE_URI, ['timeout' => 15]);

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
    private static function auth(string $nim, string $password, string $token): string
    {
        if (empty($nim) || empty($password)) {
            throw new Exception('Could not authenticate. Empty NIM or Password!');
        }

        $res = self::$client->post(BASE_URI . '/index.php', [
            'headers' => [
                'Cookie' => $token,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'username' => $nim,
                'password' => $password,
                'login' => 'Masuk',
            ],
            'timeout' => 15
        ]);
        $content = trim($res->getBody()->getContents());

        // Verify Authentication Attempt
        $status = self::extractContent($content, STATUS_XPATH);
        if ($status !== null) {
            throw new Exception('Invalid NIM or Password Credentials!');
        }

        return $content;
    }

    /**
     * Process the extracted content and get the data.
     *
     * @param string $body The response body.
     * @return array
     */
    private static function process(string $body): array
    {
        $contents = self::extractContent($body, CONTENT_XPATH);
        $datas = preg_split('/\s{2,}/', trim($contents->nodeValue));
        $photoNode = self::extractContent($body, PHOTO_XPATH);

        if ($photoNode === null) return $datas;

        $datas[] = $photoNode->attributes->getNamedItem('style')->nodeValue;
        return $datas;
    }

    /**
     * Extract the content from the response body using XPath.
     *
     * @param string $content The response body.
     * @param string $regex The XPath expression.
     * @return DOMNode|null The extracted content.
     */
    private static function extractContent(string $content, string $regex): ?DOMNode
    {
        // Suppress document warnings
        libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        $dom->loadHTML($content);

        $xpath = new DOMXPath($dom);

        $elements = $xpath->query($regex);
        if (!$elements || $elements->length === 0) {
            return null;
        }

        return $elements->item(0);
    }

    /**
     * Invalidate session key for security reason.
     *
     * @return void
     */
    private static function invalidate($token): void
    {
        self::$client->get(BASE_URI . '/logout.php', [
            'headers' => ['Cookie' => $token],
            'timeout' => 15
        ]);
    }
}
