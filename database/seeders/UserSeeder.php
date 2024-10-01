<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        User::create([
            'name'=>'John Doe',
            'address'=>'123 Main St',
            'email'=>'jhon.doe@gmail.com',
            'password' => Hash::make('12345678'),
        ]);

        User::create([
            'name'=>'Jane Smith',
            'address'=>'456 Elm St',
            'email'=>'jane.smith@gmail.com',
            'password' => Hash::make('12345678'),
        ]);

        User::create([
            'name'=>'Luis Claudio',
            'address'=>'Calle Loma',
            'email'=>'luis.claudio@gmail.com',
            'password' => Hash::make('12345678'),
        ]);
    }
}
