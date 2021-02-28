<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->integer("user_id");
            $table->boolean("like_comment")->default(true);
            $table->boolean("from_friends")->default(true);
            $table->boolean("requested_friend")->default(true);
            $table->boolean("suggested_friend")->default(true);
            $table->boolean("birthday")->default(true);
            $table->boolean("video")->default(true);
            $table->boolean("report")->default(true);
            $table->boolean("sound_on")->default(true);
            $table->boolean("notification_on")->default(true);
            $table->boolean("vibrant_on")->default(true);
            $table->boolean("led_on")->default(true);
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
        Schema::dropIfExists('settings');
    }
}
