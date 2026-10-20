
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issue_follow_ups', function (Blueprint $table) {
            $table->id();

            $table->string('issue_code')->unique();
            $table->string('title');
            $table->string('category', 100);
            $table->string('unit', 50);

            $table->date('reported_date');
            $table->date('due_date')->nullable();
            $table->string('location')->nullable();

            $table->text('description');
            $table->text('action_plan')->nullable();
            $table->string('pic');

            $table->enum('status', [
                'Open',
                'In Progress',
                'Close',
            ])->default('Open');

            $table->date('closed_date')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['status', 'reported_date']);
            $table->index(['unit', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_follow_ups');
    }
};
