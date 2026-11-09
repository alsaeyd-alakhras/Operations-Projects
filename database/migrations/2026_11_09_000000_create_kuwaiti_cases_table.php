<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('kuwaiti_cases')) {
            return;
        }

        Schema::create('kuwaiti_cases', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('name');
            $table->string('gender', 10)->nullable();
            $table->string('patient_ID', 20)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('marital_status', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('displacement_address')->nullable();
            $table->string('phone_number', 30)->nullable();
            $table->string('exam')->nullable();
            $table->decimal('exam_cost', 10, 2)->nullable();
            $table->string('exam_need', 20)->nullable();
            $table->string('housing_status', 30)->nullable();
            $table->string('income_status', 20)->nullable();
            $table->string('family_burden', 20)->nullable();
            $table->unsignedTinyInteger('total_score')->default(0);
            $table->string('data_entry')->nullable();
            $table->text('case_description')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->boolean('eligible')->nullable();
            $table->unsignedTinyInteger('need_percentage')->nullable();
            $table->text('researcher_opinion')->nullable();
            $table->string('management_status')->nullable();
            $table->text('management_notes')->nullable();
            $table->boolean('archived')->default(0);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kuwaiti_cases');
    }
};
