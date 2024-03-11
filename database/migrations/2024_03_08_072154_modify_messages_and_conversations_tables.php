<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyMessagesAndConversationsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['conversation_token', 'url', 'expiry', 'link_visit_count']);
            $table->unsignedBigInteger('conversation_id')->after('user_id');
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->timestamp('expiry')->nullable()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn('expiry');
        });

        // Rollback for `messages` table
        Schema::table('messages', function (Blueprint $table) {
            $table->string('conversation_token');
            $table->string('url')->nullable();
            $table->timestamp('expiry')->nullable();
            $table->unsignedBigInteger('link_visit_count')->default(0);

        });
    }
}
