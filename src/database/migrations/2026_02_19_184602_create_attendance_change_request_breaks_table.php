<?php

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
        Schema::create('attendance_change_request_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')
                ->constrained('attendance_change_requests')
                ->onDelete('cascade');
            $table->dateTime('requested_break_start');
            $table->dateTime('requested_break_end');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_change_request_breaks');
    }
};
