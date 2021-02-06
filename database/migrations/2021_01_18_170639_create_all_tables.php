<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAllTables extends Migration
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
            $table->uuid('public_id')->unique();
            $table->string('name', 50);
            $table->string('avatar', 100)->default('default.png');
            $table->string('email', 50)->unique();
            $table->string('telephone', 25)->unique()->nullable();
            $table->boolean('verified')->default(0);
            $table->string('password', 255);
        });

        Schema::create('user_favorites', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->uuid('id_user');
            $table->uuid('id_barber');
        });

        Schema::create('user_appointments', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->uuid('id_user');
            $table->uuid('id_barber');
            $table->uuid('id_service');
            $table->dateTime('ap_datetime');
            $table->boolean('confirmed')->default(0);
        });

        Schema::create('barbers', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('name', 50);
            $table->string('avatar', 100)->default('default.png');
            $table->decimal('stars', 2, 1)->default(0.0);
            $table->decimal('latitude', 8, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->string('email', 50)->unique()->nullable();
            $table->string('telephone', 25)->unique()->nullable();
            $table->boolean('verified')->default(0);
            $table->string('password', 255);
        });

        Schema::create('barber_photos', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->uuid('id_barber');
            $table->string('url', 100);
        });

        Schema::create('barber_services', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->uuid('id_barber');
            $table->string('name', 50);
            $table->text('desc')->nullable();
            $table->string('photo', 100)->nullable();
            $table->string('price', 10);
        });

        Schema::create('barber_testimonials', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->uuid('id_barber');
            $table->uuid('id_user');
            $table->string('name', 50);
            $table->decimal('rate', 2, 1);
            $table->text('body')->nullable();
        });

        Schema::create('barber_availabilities', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->uuid('id_barber');
            $table->tinyInteger('weekday');
            $table->text('hours');
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
        Schema::dropIfExists('user_favorites');
        Schema::dropIfExists('user_appointments');
        Schema::dropIfExists('barbers');
        Schema::dropIfExists('barber_photos');
        Schema::dropIfExists('barber_services');
        Schema::dropIfExists('barber_testimonials');
        Schema::dropIfExists('barber_availabilities');
    }
}
