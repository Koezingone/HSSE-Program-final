<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // LANGKAH 1: Bersihkan data lama. Atur semua nilai ke 0.
        // Ini akan menghapus data "Contoh: 1" yang menyebabkan error.
        DB::table('risk_controls')->update([
            'eng' => 0,
            'proc' => 0,
            'cons' => 0,
            'comm' => 0,
        ]);

        // LANGKAH 2: Sekarang aman untuk mengubah tipe kolom
        Schema::table('risk_controls', function (Blueprint $table) {
            // Ubah tipe kolom menjadi boolean (akan tersimpan sbg 1 atau 0)
            $table->boolean('eng')->default(0)->change();
            $table->boolean('proc')->default(0)->change();
            $table->boolean('cons')->default(0)->change();
            $table->boolean('comm')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risk_controls', function (Blueprint $table) {
            // Jika migrasi di-rollback, kembalikan ke string (nullable)
            $table->string('eng')->nullable()->change();
            $table->string('proc')->nullable()->change();
            $table->string('cons')->nullable()->change();
            $table->string('comm')->nullable()->change();
        });
    }
};
