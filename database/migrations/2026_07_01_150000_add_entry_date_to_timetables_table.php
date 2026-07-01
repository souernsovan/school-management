<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('timetables', function (Blueprint $table) {
            $table->date('entry_date')->nullable()->after('day');
        });
    }
    public function down(): void {
        Schema::table('timetables', function (Blueprint $table) {
            $table->dropColumn('entry_date');
        });
    }
};
