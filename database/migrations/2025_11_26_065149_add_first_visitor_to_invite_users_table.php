<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFirstVisitorToInviteUsersTable extends Migration
{
    public function up()
    {
        Schema::table('invite_users', function (Blueprint $table) {
            $table->boolean('first_visitor')->default(0)->after('user_id');
        });
    }

    public function down()
    {
        Schema::table('invite_users', function (Blueprint $table) {
            $table->dropColumn('first_visitor');
        });
    }
}
