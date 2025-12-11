<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recycling_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('category', ['Logam', 'Minyak', 'Kertas', 'Elektronik', 'Besi', 'Kaca', 'Plastik']);
            $table->decimal('weight', 8, 2); // berat dalam kg
            $table->decimal('price_per_kg', 10, 2)->nullable(); // harga per kg yang ditetapkan admin
            $table->decimal('total_price', 10, 2)->nullable(); // total harga
            $table->text('description')->nullable();
            $table->string('image')->nullable(); // gambar sampah
            $table->string('pickup_address');
            $table->enum('status', ['Pending', 'Approved', 'Berjalan', 'Selesai', 'Ditolak'])->default('Pending');
            $table->foreignId('petugas_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recycling_orders');
    }
};