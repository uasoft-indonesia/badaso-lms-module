<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Uasoft\Badaso\Module\LMSModule\Helpers\DatabaseHelper;

class CreateQuizTable extends Migration
{
    private $tableName;

    public function __construct()
    {
        $this->tableName = DatabaseHelper::getBadasoTableName('quizzes');
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
            $table->foreignId('course_id');
            $table->foreignId('topic_id')->nullable();
            $table->string('name', 255)->nullable();
            $table->text('description')->nullable();
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->integer('duration')->nullable();
            $table->integer('point')->nullable();
            $table->text('link_url')->nullable();
            $table->foreignId('created_by');
            $table->timestamps();

            $table->foreign('course_id')
                ->references('id')
                ->on(DatabaseHelper::getBadasoTableName('courses'))
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('topic_id')
                ->references('id')
                ->on(DatabaseHelper::getBadasoTableName('topics'))
                ->onDelete('set null')
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
