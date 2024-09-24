<?php

use App\Enums\PaymentMethodeStatus;
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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->enum('methode',PaymentMethodeStatus::getValues())->nullable()->default(PaymentMethodeStatus::cash);
            $table->enum('status', ['payed', 'unpaid'])->default('unpaid')->nullable();
            $table->decimal('total_price',10,2)->default(0)->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
