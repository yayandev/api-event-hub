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
        Schema::create('ticket_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->string('name'); // VIP, Regular, Early Bird
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->integer('quantity');
            $table->integer('sold_quantity')->default(0);
            $table->integer('reserved_quantity')->default(0); // untuk temporary hold
            $table->integer('min_purchase')->default(1);
            $table->integer('max_purchase')->default(10);
            $table->dateTime('sale_start_date');
            $table->dateTime('sale_end_date');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('benefits')->nullable(); // list keuntungan tiket
            $table->timestamps();

            $table->index(['event_id', 'is_active']);
            $table->index(['sale_start_date', 'sale_end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_types');
    }
};
