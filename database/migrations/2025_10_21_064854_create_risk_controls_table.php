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
        Schema::create('risk_controls', function (Blueprint $table) {
            $table->id(); // ID unik untuk setiap action plan

            // Ini adalah PENGHUBUNGNYA
            // Menghubungkan ke 'id' di tabel 'mah_registers'
            $table->foreignId('mah_register_id')
                ->constrained('mah_registers') // merujuk ke tabel mah_registers
                ->onDelete('cascade'); // Jika MAH dihapus, action plannya ikut terhapus

            $table->text('action_plan'); // Dari "Action Plan"
            $table->text('action_progress')->nullable(); // Dari "Action Progress"
            $table->string('location')->nullable(); // Dari "Location"
            $table->string('plan_complete_date')->nullable(); // Dari "Plan Complete Date" (pakai string, krn data Anda ada "2027")
            $table->string('actual_complete_date')->nullable(); // Dari "Actual Complete Date" (pakai string, krn data Anda ada "continue")
            $table->string('action_status')->nullable(); // Dari "Action Status" (OPEN, CLOSE, etc)
            $table->string('final_risk')->nullable(); // Dari "Final Risk" (on progress, etc)
            $table->string('referensi_sudi')->nullable(); // Dari "Referensi Sudi"

            // Kolom Eng, Proc, Cons, Comm, Persentase (saya buat nullable jika tidak selalu diisi)
            $table->string('eng')->nullable();
            $table->string('proc')->nullable();
            $table->string('cons')->nullable();
            $table->string('comm')->nullable();
            $table->string('persentase')->nullable();

            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_controls');
    }
};
