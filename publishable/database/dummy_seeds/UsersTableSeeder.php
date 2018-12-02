<?php

use Illuminate\Database\Seeder;
use Afaneh262\Iwan\Models\Role;
use Afaneh262\Iwan\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     *
     * @return void
     */
    public function run()
    {
        if (User::count() == 0) {
            $role = Role::where('name', 'admin')->firstOrFail();

            $user = User::create([
                'name'           => 'Admin',
                'email'          => 'admin@admin.com',
                'password'       => bcrypt('password'),
                'remember_token' => str_random(60),
            ]);

            $user->roles()->attach($role->id);
        }
    }
}
