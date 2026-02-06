<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddColorToInviteUsersTable extends Migration
{
    private const COLOR_PALETTE = [
        '#f0f8ff', '#fff8f0', '#f0fff0', '#fff0f5', '#f5fffa',
        '#fffff0', '#f0ffff', '#fff5ee', '#f5f5dc', '#faf0e6',
        '#e6f2ff', '#fff0e6', '#e6ffe6', '#ffe6f0', '#f0e6ff',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invite_users', function (Blueprint $table) {
            $table->string('color', 7)->nullable()->after('user_id');
        });

        // Assign random colors to existing records
        $existingUsers = DB::table('invite_users')->whereNull('color')->get();
        foreach ($existingUsers as $user) {
            DB::table('invite_users')
                ->where('id', $user->id)
                ->update(['color' => self::COLOR_PALETTE[array_rand(self::COLOR_PALETTE)]]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invite_users', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
}
