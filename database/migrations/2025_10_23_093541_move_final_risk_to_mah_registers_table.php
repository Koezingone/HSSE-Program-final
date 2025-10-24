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
        // 1. Tambahkan kolom final_risk (integer, nullable) ke mah_registers
        Schema::table('mah_registers', function (Blueprint $table) {
            $table->integer('final_risk')->nullable()->after('residual_risk'); // Setelah residual_risk
        });

        // 2. Hapus kolom final_risk dari risk_controls
        Schema::table('risk_controls', function (Blueprint $table) {
            $table->dropColumn('final_risk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Lakukan kebalikannya jika di-rollback
        Schema::table('mah_registers', function (Blueprint $table) {
            $table->dropColumn('final_risk');
        });

        Schema::table('risk_controls', function (Blueprint $table) {
            // Buat kembali kolomnya sbg integer (sesuai migrasi sebelumnya)
            $table->integer('final_risk')->nullable()->after('persentase');
        });
    }
};
