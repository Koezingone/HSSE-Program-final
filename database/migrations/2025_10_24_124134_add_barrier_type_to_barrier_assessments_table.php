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
        Schema::table('barrier_assessments', function (Blueprint $table) {
            // Tambahkan kolom barrier_type setelah specific_barrier
            $table->string('barrier_type')->nullable()->after('specific_barrier');
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::table('barrier_assessments', function (Blueprint $table) {
            $table->dropColumn('barrier_type');
        });
    }
};
