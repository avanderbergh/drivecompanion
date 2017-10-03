<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForgeignKeyConstraints extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->foreign('section_id')
                ->references('id')->on('sections')
                ->onDelete('cascade');
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
        Schema::table('assignments', function (Blueprint $table) {
            $table->foreign('section_id')
                ->references('id')->on('sections')
                ->onDelete('cascade');
        });
        Schema::table('assignment_files', function (Blueprint $table) {
            $table->foreign('enrollment_id')
                ->references('id')->on('enrollments')
                ->onDelete('cascade');
            $table->foreign('assignment_id')
                ->references('id')->on('assignments')
                ->onDelete('cascade');
        });
        Schema::table('sections', function (Blueprint $table) {
            $table->foreign('school_id')
                ->references('id')->on('schools')
                ->onDelete('cascade');
            $table->foreign('owner_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('school_id')
                ->references('id')->on('schools')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign('enrollments_section_id_foreign');
            $table->dropForeign('enrollments_user_id_foreign');
        });
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropForeign('assignments_section_id_foreign');
        });
        Schema::table('assignment_files', function (Blueprint $table) {
            $table->dropForeign('assignment_files_enrollment_id_foreign');
            $table->dropForeign('assignment_files_assignment_id_foreign');
        });
        Schema::table('sections', function (Blueprint $table) {
            $table->dropForeign('sections_school_id_foreign');
            $table->dropForeign('sections_owner_id_foreign');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_school_id_foreign');
        });
    }
}
