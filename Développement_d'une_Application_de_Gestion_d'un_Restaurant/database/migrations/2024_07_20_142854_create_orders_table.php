<?php

use App\Enums\OrderStatus;
use App\Enums\OrderTypeStatus;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('orderable_id')->nullable()->default(null);
            $table->string('orderable_type')->nullable()->default(null);
            $table->dateTime('order_date', 0)->default(now());
            $table->enum('order_type',OrderTypeStatus::getValues())->nullable()->default(OrderTypeStatus::on_site);
            $table->enum('status',OrderStatus::getValues())->default(OrderStatus::processing);
            $table->decimal('total_price', 10, 2)->default(0)->unsigned();
            $table->unsignedBigInteger('admin_id')->nullable()->default(null); // Updated to unsignedBigInteger
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
