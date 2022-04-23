<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RoleSeeder::class);
        $this->call(ServiceSeeder::class);
        \App\Models\User::factory(100)->create();
        \App\Models\Service::factory(100)->create();
        \App\Models\SubService::factory(100)->create();
    }
}
