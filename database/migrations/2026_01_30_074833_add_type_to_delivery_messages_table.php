<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToDeliveryMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_messages', function (Blueprint $table) {
            $table->enum('type', ['delivery', 'notification'])->default('delivery')->after('delivery_message');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_messages', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
