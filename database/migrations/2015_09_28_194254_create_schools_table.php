<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSchoolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary()->unique();
            $table->string('name');
            $table->text('address');
            $table->string('city');
            $table->string('state');
            $table->string('postal_code');
            $table->string('country');
            $table->tinyInteger('stripe_active')->default(0);
            $table->string('stripe_id')->nullable();
            $table->string('stripe_subscription')->nullable();
            $table->string('stripe_plan', 100)->nullable();
            $table->string('last_four', 4)->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('school_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('schools');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('school_id');
        });
    }
}
