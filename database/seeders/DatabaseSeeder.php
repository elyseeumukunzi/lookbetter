<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);
        $admin = Role::create(['name' => 'Admin']);
        $user -> assignRole($admin); 

        //create another user
        $user2 = User::factory()->create([
            'name' => 'elysee',
            'email' => 'elyseeumukunzi@gmail.com',
        ]);
        $receptionist = Role::create(['name' => 'Receptionist']);
        $user2 -> assignRole($receptionist); 

    }
}
