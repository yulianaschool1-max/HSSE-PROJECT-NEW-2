
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workforces', function (Blueprint $table) {
            $table->id();

            $table->string('employee_code')->unique();
            $table->string('name');
            $table->string('unit', 50);
            $table->string('department')->nullable();
            $table->string('position')->nullable();

            $table->enum('employment_type', [
                'Karyawan',
                'Kontraktor',
                'Vendor',
                'Magang',
                'Lainnya',
            ])->default('Karyawan');

            $table->date('join_date')->nullable();
            $table->enum('status', [
                'Aktif',
                'Tidak Aktif',
            ])->default('Aktif');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['unit', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workforces');
    }
};
