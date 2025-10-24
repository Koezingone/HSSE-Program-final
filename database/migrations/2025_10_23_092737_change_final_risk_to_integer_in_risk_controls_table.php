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
        // Set semua nilai string lama ke NULL agar bisa diubah tipenya
        DB::table('risk_controls')->update(['final_risk' => null]);

        Schema::table('risk_controls', function (Blueprint $table) {
            // Ubah tipe kolom menjadi integer, boleh kosong (nullable)
            $table->integer('final_risk')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risk_controls', function (Blueprint $table) {
            // Kembalikan ke string jika di-rollback
            $table->string('final_risk')->nullable()->change();
        });
    }
};
