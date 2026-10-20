<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('issue_follow_ups')) {
            // Ubah kolom status dari enum('Open','In Progress','Close') menjadi VARCHAR(30) agar mendukung 'Closed' secara konsisten tanpa merusak data lama
            DB::statement("ALTER TABLE issue_follow_ups MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'Open'");

            // Normalisasi nilai 'Close' lama (jika ada) menjadi 'Closed'
            DB::table('issue_follow_ups')->where('status', 'Close')->update(['status' => 'Closed']);

            Schema::table('issue_follow_ups', function (Blueprint $table) {
                if (!Schema::hasColumn('issue_follow_ups', 'priority')) {
                    $table->string('priority', 30)->default('Sedang')->after('pic');
                }
                if (!Schema::hasColumn('issue_follow_ups', 'attachment_path')) {
                    $table->string('attachment_path')->nullable()->after('notes');
                }
                if (!Schema::hasColumn('issue_follow_ups', 'closed_by')) {
                    $table->string('closed_by', 150)->nullable()->after('closed_date');
                }
            });
        }

        if (!Schema::hasTable('issue_follow_up_logs')) {
            Schema::create('issue_follow_up_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('issue_follow_up_id')
                    ->constrained('issue_follow_ups')
                    ->cascadeOnDelete();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('user_name', 150)->nullable();
                $table->string('action_type', 50)->default('FOLLOW_UP');
                $table->string('old_status', 30)->nullable();
                $table->string('new_status', 30)->nullable();
                $table->text('note');
                $table->string('attachment_path')->nullable();
                $table->timestamp('followed_up_at')->nullable();
                $table->timestamps();

                $table->index(['issue_follow_up_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_follow_up_logs');

        if (Schema::hasTable('issue_follow_ups')) {
            Schema::table('issue_follow_ups', function (Blueprint $table) {
                $cols = [];
                foreach (['priority', 'attachment_path', 'closed_by'] as $col) {
                    if (Schema::hasColumn('issue_follow_ups', $col)) {
                        $cols[] = $col;
                    }
                }
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};

