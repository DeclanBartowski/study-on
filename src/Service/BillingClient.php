<?php

namespace App\Service;

use App\Exception\BillingUnavailableException;

class BillingClient
{
    private string $apiUrl;

    public array $headers = [];

    public function __construct(string $apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }

    public function setHeaders(array $headers): void
    {
        $this->headers = array_merge($headers, $this->headers);
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function get(string $endpoint, array $queryParams = []): array
    {
        return $this->request('GET', $endpoint, [], $queryParams);
    }

    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, $data);
    }

    private function request(string $method, string $endpoint, array $data = [], array $queryParams = []): array
    {
        $url = $this->buildUrl($endpoint, $queryParams);

        $ch = curl_init();

        // Настройка cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $this->setHeaders([
                'Content-Type: application/json',
                'Content-Length: ' . strlen(json_encode($data)),
            ]);
        }

        if (!empty($this->getHeaders())) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders());
        }

        // Выполнение запроса
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Закрытие соединения
        curl_close($ch);

        // Обработка ошибок
        if ($response === false) {
            throw new BillingUnavailableException('Ошибка данных.');
        }

        /*if ($httpCode >= 400 || json_last_error() !== JSON_ERROR_NONE) {
            throw new BillingUnavailableException('Ошибка подключения к сервису.');
        }*/

        return json_decode($response, true);
    }

    /**
     * Строит полный URL для запроса.
     *
     * @param string $endpoint Эндпоинт API
     * @param array $queryParams Параметры запроса
     * @return string Полный URL
     */
    private function buildUrl(string $endpoint, array $queryParams = []): string
    {
        $url = rtrim($this->apiUrl, '/') . '/' . ltrim($endpoint, '/');

        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }

        return $url;
    }
}
