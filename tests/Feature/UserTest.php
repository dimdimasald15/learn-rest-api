<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testRegisterSuccess(): void
    {
        $response = $this->post('/api/users', [
            'username' => "dimas",
            'password' => "rahasia",
            'name' => "Dimas Aldi Sallam",
        ]);

        $response->assertStatus(201)->assertJson([
            "data" => [
                'username' => "dimas",
                'name' => "Dimas Aldi Sallam",
            ],
        ]);
    }

    public function testRegisterFailed(): void
    {
        $response = $this->post('/api/users', [
            'username' => "",
            'password' => "",
            'name' => "",
        ]);

        $response->assertStatus(400)->assertJson([
            "errors" => [
                'username' => [
                    "The username field is required."
                ],
                'password' => [
                    "The password field is required."
                ],
                'name' => [
                    "The name field is required."
                ],
            ],
        ]);
    }

    public function testRegisterUsernameAlreadyExists(): void
    {
        $this->testRegisterSuccess();
        $response = $this->post('/api/users', [
            'username' => "dimas",
            'password' => "rahasia",
            'name' => "Dimas Aldi Sallam",
        ]);

        $response->assertStatus(400)->assertJson([
            "errors" => [
                'username' => [
                    "Username already registered",
                ],
            ],
        ]);
    }

    public function testLoginSuccess()
    {
        $this->seed([UserSeeder::class]);
        $response = $this->post('/api/users/login', [
            'username' => "test",
            'password' => "test",
        ]);

        $response->assertStatus(200)->assertJsonStructure([
            'data' => [
                'id',
                'username',
                'name',
                'token',
            ],
        ]);

        $user = User::where('username', 'test')->first();
        self::assertNotNull($user->token);
    }

    public function testLoginFailedUsernameNotFound()
    {
        $response = $this->post('/api/users/login', [
            'username' => "test",
            'password' => "test",
        ]);

        $response->assertStatus(401)->assertJson([
            "errors" => [
                'message' => [
                    "username or password wrong"
                ],
            ],
        ]);
    }

    public function testLoginFailedPasswordWrong()
    {
        $this->seed([UserSeeder::class]);
        $response = $this->post('/api/users/login', [
            'username' => "test",
            'password' => "salah",
        ]);

        $response->assertStatus(401)->assertJson([
            "errors" => [
                'message' => [
                    "username or password wrong"
                ],
            ],
        ]);
    }

    public function testGetSuccess()
    {
        $this->seed([UserSeeder::class]);
    
        $response = $this->get('/api/users/current', [
            'Authorization' => "test",
        ]);
    
        $response->assertStatus(200)->assertJson([
            "data" => [
                'username' => 'test',
                'name' => 'test',
                'token' => 'test',
            ],
        ])->assertJsonMissing([
            "data" => [
                'password' => 'test',
            ],
        ]);
    }

    public function testGetUnauthorized()
    {
        $response = $this->get('/api/users/current');

        $response->assertStatus(401)->assertJson([
            "errors" => [
                'message' => [
                    "unauthorized"
                ],
            ],
        ]);
    }
    public function testGetInvalidToken()
    {
        $response = $this->get('/api/users/current',[
            'Authorization'=>'salah'
        ]);

        $response->assertStatus(401)->assertJson([
            "errors" => [
                'message' => [
                    "unauthorized"
                ],
            ],
        ]);
    }
}
