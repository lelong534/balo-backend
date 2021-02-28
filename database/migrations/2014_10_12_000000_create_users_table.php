<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->char('phone_number', 30)->unique();
            $table->string('password');
            $table->boolean('is_blocked')->default(false);
            $table->string("avatar")->nullable();
            $table->string("description")->nullable();
            $table->string("address")->nullable();
            $table->string("city")->nullable();
            $table->string("country")->nullable();
            $table->string("cover_image")->nullable();
            $table->string("link")->nullable();
            $table->integer("is_online")->nullable();
            $table->integer("device_id")->nullable();
            $table->dateTime("verified_email_at")->nullable();
            $table->string('uuid')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
