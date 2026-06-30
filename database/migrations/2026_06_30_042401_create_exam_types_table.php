<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed existing types so nothing breaks
        $types = ['Class Test', 'Monthly Test', 'Mid-Year Exam', 'Final Exam', 'Quiz'];
        foreach ($types as $i => $name) {
            DB::table('exam_types')->insert([
                'name'       => $name,
                'sort_order' => $i + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_types');
    }
};
