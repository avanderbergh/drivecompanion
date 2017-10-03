<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveStripeFromSchools extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('stripe_active');
            $table->dropColumn('stripe_id');
            $table->dropColumn('stripe_subscription');
            $table->dropColumn('stripe_plan');
            $table->dropColumn('last_four');
            $table->dropColumn('trial_ends_at');
            $table->dropColumn('subscription_ends_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->tinyInteger('stripe_active')->default(0);
            $table->string('stripe_id')->nullable();
            $table->string('stripe_subscription')->nullable();
            $table->string('stripe_plan', 100)->nullable();
            $table->string('last_four', 4)->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
        });
    }
}
