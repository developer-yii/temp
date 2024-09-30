<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if(Setting::where('param_name', 'register')->first() == null){
            DB::table('settings')->insert([
                'param_name' => 'register',
                'param_value' => 0,
            ]);
        }
    }
}
