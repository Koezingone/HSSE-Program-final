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
        Schema::create('mah_registers', function (Blueprint $table) {
            $table->id(); // Kolom ID otomatis
            $table->string('mah_id')->unique(); // Dari kolom "MAH ID", kita buat unik
            $table->string('hazard_category')->nullable(); // Dari "Hazard Category"
            $table->text('major_accident_hazard')->nullable(); // Dari "Major Accident Hazard"
            $table->string('cause')->nullable(); // Dari "Cause"
            $table->string('top_event')->nullable(); // Dari "Top Event"
            $table->text('consequences')->nullable(); // Dari "Consequences"
            $table->integer('initial_risk')->nullable(); // Dari "Initial Risk"
            $table->text('preventive_barriers')->nullable(); // Dari "Preventive Barriers"
            $table->text('mitigative_barriers')->nullable(); // Dari "Mitigative Barriers"
            $table->integer('residual_risk')->nullable(); // Dari "Residual Risk"
            $table->text('rekomendasi')->nullable(); // Dari "Rekomendasi"
            $table->string('referensi_sudi')->nullable(); // Dari "Referensi Sudi"
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mah_registers');
    }
};
