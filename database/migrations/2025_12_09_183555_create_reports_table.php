<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location');
            $table->string('photo')->nullable(); // path file
            $table->enum('status', ['pending', 'verified', 'completed', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable(); // catatan dari admin
            $table->foreignId('assigned_petugas_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');
            $table->decimal('fee_amount', 10, 2)->nullable(); // biaya yang harus dibayar user

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
