<?php

use App\Enums\ComplaintStatus;
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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_carpet_id')->constrained('order_carpets', 'id')->cascadeOnDelete();
            $table->text('complaint_details');
            $table->enum('status', array_column(ComplaintStatus::cases(), 'value'))
                ->default(ComplaintStatus::OPEN->value);
            $table->timestamps();

            $table->index('status', 'complaint_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
