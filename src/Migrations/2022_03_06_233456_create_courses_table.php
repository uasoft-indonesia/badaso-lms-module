<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoursesTable extends Migration
{
    private $tableNamePrefix;
    private $tableName;

    public function __construct()
    {
        $this->tableNamePrefix = config('badaso.database.prefix');
        $this->tableName = $this->tableNamePrefix.'courses';
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->nullable();
            $table->string('subject', 255)->nullable();
            $table->string('room', 255)->nullable();
            $table->string('join_code', 63)->unique();
            $table->foreignId('created_by');
            $table->timestamps();

            $table->foreign('created_by')
                ->references('id')
                ->on($this->tableNamePrefix.'users')
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
