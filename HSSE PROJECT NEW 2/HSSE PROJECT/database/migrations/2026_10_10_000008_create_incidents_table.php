
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();

            $table->string('incident_code')->unique();
            $table->string('title');
            $table->string('incident_type', 100);
            $table->string('unit', 50);

            $table->dateTime('incident_date');
            $table->string('location');
            $table->string('severity', 50)->default('Rendah');

            $table->text('description');
            $table->text('immediate_action')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('corrective_action')->nullable();

            $table->enum('status', [
                'Dilaporkan',
                'Investigasi',
                'Dalam Penanganan',
                'Selesai',
            ])->default('Dilaporkan');

            $table->string('reported_by');
            $table->date('closed_at')->nullable();

            $table->timestamps();

            $table->index(['incident_date', 'status']);
            $table->index(['unit', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
