<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('restaurant_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('address');
            $table->string('phone_number', 20);
            $table->string('email', 255)->nullable();
            $table->json('opening_hours')->nullable();
            $table->text('reservation_policy')->nullable();
            $table->integer('max_capacity');
            $table->string('currency', 10);
            $table->timestamps();
        });

        DB::table('restaurant_settings')->insert([
            'name' => 'Gourmet Paradise',
            'address' => '123 Culinary Ave, Food City',
            'phone_number' => '+1234567890',
            'email' => 'info@gourmetparadise.com',
            'opening_hours' => json_encode([
                'monday' => '08:00-22:00',
                'tuesday' => '08:00-22:00',
                'wednesday' => '08:00-22:00',
                'thursday' => '08:00-22:00',
                'friday' => '08:00-23:00',
                'saturday' => '09:00-23:00',
                'sunday' => '09:00-21:00',
            ]),
            'reservation_policy' => 'Reservations must be made at least 24 hours in advance.',
            'max_capacity' => 150,
            'currency' => 'USD',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }




    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_settings');
    }
};
