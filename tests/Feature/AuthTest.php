<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test successful login returns bearer token
     */
    public function test_user_can_login_with_valid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'admin@cargotrace.test',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'role'],
                    'token',
                    'token_type',
                ]
            ]);
    }

    /**
     * Test login failure with invalid password
     */
    public function test_user_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'admin@cargotrace.test',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test unauthenticated request to protected endpoint returns 401
     */
    public function test_unauthenticated_request_is_rejected()
    {
        $response = $this->getJson('/api/user');
        $response->assertStatus(401);
    }

    /**
     * Test new customer registration via API
     */
    public function test_customer_can_register()
    {
        $payload = [
            'name' => 'Test Importer',
            'email' => 'importer_' . uniqid() . '@example.com',
            'password' => 'secret12345',
            'password_confirmation' => 'secret12345',
            'phone' => '+628198765432',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.user.role', 'customer');
    }
}

