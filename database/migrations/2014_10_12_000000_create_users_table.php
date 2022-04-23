<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date');
            $table->string('email')->unique();
            $table->string('phone_number')->unique();
            $table->string('country');
            $table->string('city');
            $table->string('gender')->default('male');
            $table->string('profession');
            $table->string('account_type')->default('client');
            $table->string('password');
            $table->float('funds')->default(0.00);
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('two_factor_authentication')->default(false);
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
