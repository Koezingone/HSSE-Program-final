<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::create('barrier_assessments', function (Blueprint $table) {
            $table->id();
            $table->string('barrier_category'); // Kategori Utama (Prevention, dll.)
            $table->string('specific_barrier'); // Nama Barrier Spesifik (SS-01, dll.)
            $table->string('barrier_type')->nullable(); // Tipe: Hardware / Human
            $table->decimal('percentage', 5, 2)->nullable(); // Persentase (misal 95.50)
            $table->date('assessment_date')->nullable(); // Tanggal Penilaian
            $table->text('notes')->nullable(); // Catatan Tambahan
            $table->timestamps(); // Waktu pembuatan & update
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('barrier_assessments');
    }
};
