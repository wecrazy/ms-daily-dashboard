<?php

declare(strict_types=1);

namespace MsDashboard\Http;

use MsDashboard\Config\Config;

/**
 * HTTP client for Odoo JSON-RPC API calls.
 * Replaces all duplicated post_req() functions across the codebase.
 */
final class OdooClient
{
    private readonly string $baseUrl;
    private readonly string $db;
    private readonly string $login;
    private readonly string $password;
    private readonly string $cookiesPath;
    private bool $authenticated = false;

    public function __construct(?Config $config = null)
    {
        $config = $config ?? Config::load();

        $this->baseUrl     = rtrim($config->get('ODOO_URL', 'https://www.gsa4u.net:13070'), '/');
        $this->db          = $config->get('ODOO_DB', 'gsa_db');
        $this->login       = $config->get('ODOO_LOGIN');
        $this->password    = $config->get('ODOO_PASSWORD');
        $this->cookiesPath = $config->cookiesPath();
    }

    /**
     * Authenticate with Odoo (session-based, via cookies).
     */
    public function authenticate(): void
    {
        if ($this->authenticated) {
            return;
        }

        $loginData = [
            'jsonrpc' => '2.0',
            'params'  => [
                'db'       => $this->db,
                'login'    => $this->login,
                'password' => $this->password,
            ],
        ];

        $this->postRequest(
            $this->baseUrl . '/web/session/authenticate',
            $loginData,
            saveCookie: true
        );

        $this->authenticated = true;
    }

    /**
     * Fetch data from Odoo API.
     *
     * @param array<string, mixed> $domain
     * @param string               $model
     * @param list<string>         $fields
     * @param string               $order
     * @param array<string, mixed> $extraParams
     *
     * @return array<int, array<string, mixed>>
     */
    public function getData(
        array $domain,
        string $model,
        array $fields,
        string $order = 'create_date asc',
        array $extraParams = [],
    ): array {
        $this->authenticate();

        $params = array_merge([
            'domain' => $domain,
            'model'  => $model,
            'fields' => $fields,
            'order'  => $order,
        ], $extraParams);

        $requestData = [
            'jsonrpc' => '2.0',
            'params'  => $params,
        ];

        $response = $this->postRequest(
            $this->baseUrl . '/api/getdata',
            $requestData,
            useCookie: true
        );

        $json = json_decode($response, true);

        if (!is_array($json) || !isset($json['result'])) {
            throw new \RuntimeException('Invalid Odoo API response');
        }

        return $json['result'];
    }

    /**
     * Low-level cURL POST request with JSON body and cookie handling.
     */
    private function postRequest(
        string $url,
        array $data,
        bool $saveCookie = false,
        bool $useCookie = false,
    ): string {
        $jsonBody = json_encode($data);
        $headers  = [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonBody),
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_POSTFIELDS     => $jsonBody,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30000,
            CURLOPT_CONNECTTIMEOUT => 6000,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        if ($saveCookie) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiesPath);
        }

        if ($useCookie) {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiesPath);
        }

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException('cURL Error: ' . $error);
        }

        curl_close($ch);

        return (string) $response;
    }
}
