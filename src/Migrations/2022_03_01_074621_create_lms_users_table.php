<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLMSUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('Badaso.database.prefix').'lms_users', function (Blueprint $table) {
            $table->id('id');
            $table->text('full_name');
            $table->string('username', 255)->unique();
            $table->string('email', 255);
            $table->text('password');
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
        Schema::dropIfExists(config('Badaso.database.prefix').'lms_users');
    }
}
