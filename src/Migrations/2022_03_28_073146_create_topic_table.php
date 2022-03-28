<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Uasoft\Badaso\Module\LMSModule\Helpers\DatabaseHelper;

class CreateTopicTable extends Migration
{
    private $tableName;

    public function __construct()
    {
        $this->tableName = DatabaseHelper::getBadasoTableName('topic');
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
            $table->text('title');
            $table->foreignId('course_id');
            $table->foreignId('created_by');
            $table->timestamps();

            $table->foreign('course_id')
                ->references('id')
                ->on(DatabaseHelper::getBadasoTableName('courses'))
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('created_by')
                ->references('id')
                ->on(DatabaseHelper::getBadasoTableName('users'))
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
        Schema::dropIfExists('topic');
    }
}
