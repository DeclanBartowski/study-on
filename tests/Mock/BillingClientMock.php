<?php

namespace App\Tests\Mock;

use App\Service\BillingClient;

class BillingClientMock extends BillingClient
{
    public function __construct(string $apiUrl)
    {
        parent::__construct($apiUrl);
    }

    public function register(array $data): array
    {
        if ($data['email'] === 'test@example.com' && $data['password'] === 'password123') {
            return [
                'token' => 'mock_token',
                'refresh_token' => 'mock_refresh_token',
                'roles' => ['ROLE_USER'],
            ];
        }

        if ($data['email'] === 'admin@example.com' && $data['password'] === 'admin123') {
            return [
                'token' => 'mock_token_admin',
                'refresh_token' => 'mock_refresh_token_admin',
                'roles' => ['ROLE_USER', 'ROLE_SUPER_ADMIN'],
            ];
        }

        return ['error' => 'Invalid credentials'];
    }

    public function login(array $data): array
    {
        if ($data['email'] === 'test@example.com' && $data['password'] === 'password123') {
            return [
                'token' => 'mock_token',
                'refresh_token' => 'mock_refresh_token',
                'roles' => ['ROLE_USER'],
            ];
        }

        if ($data['email'] === 'admin@example.com' && $data['password'] === 'admin123') {
            return [
                'token' => 'mock_token_admin',
                'refresh_token' => 'mock_refresh_token_admin',
                'roles' => ['ROLE_USER', 'ROLE_SUPER_ADMIN'],
            ];
        }

        return ['error' => 'Invalid credentials'];
    }

    public function getCurrentUser(string $token): array
    {
        return [
            'email' => 'test@example.com',
            'roles' => ['ROLE_USER'],
        ];
    }
}
