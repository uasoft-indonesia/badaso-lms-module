<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseUserTable extends Migration
{
    private $tableNamePrefix;
    private $tableName;

    public function __construct()
    {
        $this->tableNamePrefix = config('badaso.database.prefix');
        $this->tableName = $this->tableNamePrefix . 'course_user';
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->foreignId('course_id');
            $table->foreignId('user_id');
            $table->enum('role', ['student', 'teacher']);
            $table->timestamps();

            $table->primary(['course_id', 'user_id']);
            $table->foreign('course_id')
                ->references('id')
                ->on($this->tableNamePrefix . 'courses')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('user_id')
                ->references('id')
                ->on($this->tableNamePrefix . 'users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tableName);
    }
}