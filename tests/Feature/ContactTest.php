<?php

namespace Tests\Feature;

use App\Models\Contact;
use Database\Seeders\ContactSeeder;
use Database\Seeders\SearchSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ContactTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testCreateSuccess()
    {
        $this->seed([UserSeeder::class]);

        $response = $this->post('/api/contacts', [
            'firstname' => "Dimas",
            'lastname' => "Aldi Sallam",
            'email' => "dimas@gmail.com",
            'phone' => '08879325242'
        ], [
            'authorization' => 'test'
        ]);

        $response->assertStatus(201)->assertJson([
            "data" => [
                'firstname' => "Dimas",
                'lastname' => "Aldi Sallam",
                'email' => "dimas@gmail.com",
                'phone' => '08879325242'
            ],
        ]);
    }

    public function testCreateFailed()
    {
        $this->seed([UserSeeder::class]);

        $response = $this->post('/api/contacts', [
            'firstname' => "",
            'lastname' => "Aldi Sallam",
            'email' => "dimas",
            'phone' => '08879325242'
        ], [
            'authorization' => 'test'
        ]);

        $response->assertStatus(400)->assertJson([
            "errors" => [
                'firstname' => [
                    "The firstname field is required."
                ],
                'email' => [
                    "The email field must be a valid email address."
                ],
            ],
        ]);
    }

    public function testCreateUnauthorized()
    {
        $this->seed([UserSeeder::class]);

        $response = $this->post('/api/contacts', [
            'firstname' => "",
            'lastname' => "Aldi Sallam",
            'email' => "dimas",
            'phone' => '08879325242'
        ], [
            'authorization' => 'salah'
        ]);

        $response->assertStatus(401)->assertJson([
            "errors" => [
                'message' => [
                    "unauthorized"
                ],
            ],
        ]);
    }

    public function testGetSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->get('/api/contacts/' . $contact->id, [
            "Authorization" => 'test'
        ])->assertStatus(200)->assertJson([
            "data" => [
                'firstname' => 'test',
                'lastname' => 'test',
                'email' => 'test@mail.com',
                'phone' => '111111',
            ]
        ]);
    }
    public function testGetNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->get('/api/contacts/' . ($contact->id + 1), [
            "Authorization" => 'test'
        ])->assertStatus(404)->assertJson([
            "errors" => [
                'message' => [
                    "not found"
                ]
            ]
        ]);
    }
    public function testGetOtherUserContact()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $contact = Contact::query()->limit(1)->first();

        $this->get('/api/contacts/' . $contact->id, [
            "Authorization" => 'test2'
        ])->assertStatus(404)->assertJson([
            "errors" => [
                'message' => [
                    "not found"
                ]
            ]
        ]);
    }

    public function testUpdateSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $contact = Contact::query()->limit(1)->first();

        $this->put('/api/contacts/' . $contact->id, [
            'firstname' => 'test2',
            'lastname' => 'test2',
            'email' => 'test2@mail.com',
            'phone' => '222222',
        ], [
            "Authorization" => 'test'
        ])->assertStatus(200)->assertJson([
            "data" => [
                'firstname' => 'test2',
                'lastname' => 'test2',
                'email' => 'test2@mail.com',
                'phone' => '222222',
            ]
        ]);
    }

    public function testUpdateFailed()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $contact = Contact::query()->limit(1)->first();

        $this->put('/api/contacts/' . $contact->id, [
            'firstname' => '',
            'lastname' => 'test2',
            'email' => 'test2@mail.com',
            'phone' => '222222',
        ], [
            "Authorization" => 'test'
        ])->assertStatus(400)->assertJson([
            "errors" => [
                'firstname' => [
                    "The firstname field is required."
                ],
            ],
        ]);
    }

    public function testDeleteSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $contact = Contact::query()->limit(1)->first();

        $this->delete('/api/contacts/' . $contact->id, [], [
            "Authorization" => 'test'
        ])->assertStatus(200)->assertJson([
            "data" => true
        ]);
    }

    public function testDeleteNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $contact = Contact::query()->limit(1)->first();

        $this->delete('/api/contacts/' . ($contact->id + 1), [], [
            "Authorization" => 'test'
        ])->assertStatus(404)->assertJson([
            "errors" => [
                "message" => [
                    "not found"
                ]
            ]
        ]);
    }

    public function testSearchByFirstName()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get('/api/contacts/?name=first', [
            "Authorization" => 'test',
        ])->assertStatus(200)->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(10, count($response['data']));
        self::assertEquals(20, $response['meta']['total']);
    }

    public function testSearchByLastName()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get('/api/contacts/?name=last', [
            "Authorization" => 'test',
        ])->assertStatus(200)->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(10, count($response['data']));
        self::assertEquals(20, $response['meta']['total']);
    }

    public function testSearchByEmail()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get('/api/contacts/?email=test', [
            "Authorization" => 'test',
        ])->assertStatus(200)->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(10, count($response['data']));
        self::assertEquals(20, $response['meta']['total']);
    }
    public function testSearchByPhone()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get('/api/contacts/?phone=0889', [
            "Authorization" => 'test',
        ])->assertStatus(200)->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(10, count($response['data']));
        self::assertEquals(20, $response['meta']['total']);
    }

    public function testSearchNotFound()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get('/api/contacts/?name=tidakada', [
            "Authorization" => 'test',
        ])->assertStatus(200)->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(0, count($response['data']));
        self::assertEquals(0, $response['meta']['total']);
    }
    public function testSearchwithPage()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);

        $response = $this->get('/api/contacts/?size=5&page=2', [
            "Authorization" => 'test',
        ])->assertStatus(200)->json();

        Log::info(json_encode($response, JSON_PRETTY_PRINT));

        self::assertEquals(5, count($response['data']));
        self::assertEquals(20, $response['meta']['total']);
        self::assertEquals(2, $response['meta']['current_page']);
    }
}
