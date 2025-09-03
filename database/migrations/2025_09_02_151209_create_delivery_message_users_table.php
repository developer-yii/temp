<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryMessageUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_message_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delivery_message_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->tinyInteger('is_read')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_message_users');
    }
}
