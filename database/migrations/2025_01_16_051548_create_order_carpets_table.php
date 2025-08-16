<?php

use App\Enums\OrderCarpetStatus;
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
        Schema::create('order_carpets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders', 'id')->cascadeOnDelete();
            $table->text('qr_code')->unique()->nullable();
            $table->decimal('height', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('total_area', 10, 2)->nullable();
            $table->dateTime('measured_at')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', array_column(OrderCarpetStatus::cases(), 'value'))->default(OrderCarpetStatus::PICKED_UP->value);
            $table->timestamps();

            $table->index('status', 'order_carpet_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_carpets');
    }
};
