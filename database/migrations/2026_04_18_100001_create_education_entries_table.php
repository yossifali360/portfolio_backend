<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('education_entries', function (Blueprint $table) {
            $table->id();
            $table->string('institution_en');
            $table->string('institution_ar')->nullable();
            $table->string('degree_en');
            $table->string('degree_ar')->nullable();
            $table->string('period');
            $table->text('detail_en')->nullable();
            $table->text('detail_ar')->nullable();
            $table->text('overall_grade_en')->nullable();
            $table->text('overall_grade_ar')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education_entries');
    }
};
