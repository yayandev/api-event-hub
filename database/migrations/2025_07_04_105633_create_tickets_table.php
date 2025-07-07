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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('ticket_type_id')->constrained('ticket_types')->onDelete('cascade');
            $table->string('ticket_code', 50)->unique();
            $table->text('qr_code')->nullable(); // base64 encoded QR code image

            // Attendee Information
            $table->string('attendee_name');
            $table->string('attendee_email');
            $table->string('attendee_phone', 20)->nullable();

            $table->enum('status', ['active', 'used', 'cancelled', 'refunded'])->default('active');
            $table->timestamp('checked_in_at')->nullable();
            $table->foreignId('checked_in_by')->nullable()->constrained('users');
            $table->text('check_in_notes')->nullable();
            $table->timestamps();

            $table->index(['order_id']);
            $table->index(['ticket_type_id']);
            $table->index(['ticket_code']);
            $table->index(['status']);
            $table->index(['attendee_email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
