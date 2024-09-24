<?php

use App\Enums\BookingStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->default(null);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->bigInteger('table_id')->unsigned()->nullable()->default(null);
            $table->foreign('table_id')->references('id')->on('tables')->onDelete('set null');
            $table->integer('number_people')->default(0)->unsigned();
            $table->dateTime('date_hour_booking', 0);
            $table->enum('status',BookingStatus::getValues())->default(BookingStatus::pending)->nullable();
            $table->String('reason')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
