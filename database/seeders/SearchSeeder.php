<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SearchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('username', 'test')->first();
        for ($i = 0; $i < 20; $i++) {
            Contact::create([
                'firstname' => 'First' . $i,
                'lastname' => 'Last' . $i,
                'email' => "test$i@mail.com",
                "phone" => "0889$i$i$i$i$i$i$i",
                "user_id" => $user->id,
            ]);
        }
    }
}
