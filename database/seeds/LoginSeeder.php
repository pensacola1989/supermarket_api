<?php

use Illuminate\Database\Seeder;

class LoginSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('logins')->insert([
            'external_system' => 1,
            'external_id' => 'sdf212sdfsdfsdf',
            'user_id' => 1,
            'avatar_url' => ''
        ]);
        DB::table('logins')->insert([
            'external_system' => 1,
            'external_id' => 'sdf212sdfsdfsdf21131',
            'user_id' => 2,
            'avatar_url' => ''
        ]);
        DB::table('logins')->insert([
            'external_system' => 1,
            'external_id' => 'sdf212sdfsdfsd2234f',
            'user_id' => 3,
            'avatar_url' => ''
        ]);
        DB::table('logins')->insert([
            'external_system' => 1,
            'external_id' => 'sdf212sdfsd324fsdf',
            'user_id' => 4,
            'avatar_url' => ''
        ]);
    }
}
