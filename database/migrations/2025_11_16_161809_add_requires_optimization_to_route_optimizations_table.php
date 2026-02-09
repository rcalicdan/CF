<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('route_optimizations', function (Blueprint $table) {
            $table->boolean('requires_optimization')->default(false)->after('is_manual_edit');
        });
    }

    public function down(): void
    {
        Schema::table('route_optimizations', function (Blueprint $table) {
            $table->dropColumn('requires_optimization');
        });
    }
};