<?php

use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('languages')->insert([
            'name' => 'en',
            'status' => '1',
        ]);
        DB::table('languages')->insert([
            'name' => 'fr',
            'status' => '1',
        ]);
        DB::table('languages')->insert([
            'name' => 'es',
            'status' => '16',
        ]);
    }
}
