<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Service::create([
            'name' => 'Cleaning',
            'icon' => 'icon_url',
            'slug' => 'cleaning',
            'is_primary' => true
        ]);

        Service::create([
            'name' => 'Electricity',
            'icon' => 'icon_url',
            'slug' => 'electricity',
            'is_primary' => true
        ]);

        Service::create([
            'name' => 'Engineering',
            'icon' => 'icon_url',
            'slug' => 'engineering',
            'is_primary' => true
        ]);

        Service::create([
            'name' => 'Plumbing',
            'icon' => 'icon_url',
            'slug' => 'plumbing',
            'is_primary' => true
        ]);

        Service::create([
            'name' => 'Gardening',
            'icon' => 'icon_url',
            'slug' => 'gardening',
            'is_primary' => true
        ]);
    }
}
