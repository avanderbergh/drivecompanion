<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary()->unique();
            $table->string('name');
            $table->string('folder_id')->nullable();
            $table->string('students_folder_id')->nullable();
            $table->string('assignments_folder_id')->nullable();
            $table->string('templates_folder_id')->nullable();
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
        Schema::drop('sections');
    }
}
