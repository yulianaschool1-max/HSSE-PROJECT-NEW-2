<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('operational_units')) {
            Schema::create('operational_units', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100)->unique();
                $table->string('code', 50)->nullable();
                $table->string('description', 255)->nullable();
                $table->decimal('default_hours', 5, 2)->default(8.00);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_initial')->default(false);
                $table->unsignedInteger('sort_order')->default(0);
                $table->string('created_by', 150)->nullable();
                $table->timestamps();

                $table->index(['is_active', 'sort_order']);
            });
        }

        if (Schema::hasTable('workforces')) {
            Schema::table('workforces', function (Blueprint $table) {
                if (!Schema::hasColumn('workforces', 'operational_unit_id')) {
                    $table->foreignId('operational_unit_id')
                        ->nullable()
                        ->after('id')
                        ->constrained('operational_units')
                        ->nullOnDelete();
                }
            });

            DB::statement("ALTER TABLE workforces MODIFY COLUMN unit VARCHAR(100) NOT NULL DEFAULT 'PMU'");
        }

        if (Schema::hasTable('man_hours')) {
            Schema::table('man_hours', function (Blueprint $table) {
                if (!Schema::hasColumn('man_hours', 'operational_unit_id')) {
                    $table->foreignId('operational_unit_id')
                        ->nullable()
                        ->after('workforce_id')
                        ->constrained('operational_units')
                        ->nullOnDelete();
                }
            });

            DB::statement("ALTER TABLE man_hours MODIFY COLUMN unit VARCHAR(100) NOT NULL DEFAULT 'PMU'");
        }

        // Seed master unit awal secara aman tanpa merusak data yang sudah ada
        $initialUnits = [
            ['name' => 'PMU', 'code' => 'UNIT-PMU', 'description' => 'Project Management Unit Dieng', 'sort_order' => 1],
            ['name' => 'Dieng Unit 1', 'code' => 'UNIT-D1', 'description' => 'PLTP Dieng Unit 1', 'sort_order' => 2],
            ['name' => 'Dieng Unit 2', 'code' => 'UNIT-D2', 'description' => 'PLTP Dieng Unit 2', 'sort_order' => 3],
            ['name' => 'Utility', 'code' => 'UNIT-UTL', 'description' => 'Area Utility & Fasilitas Penunjang', 'sort_order' => 4],
            ['name' => 'JOTRE', 'code' => 'UNIT-JTR', 'description' => 'Area Operasi JOTRE', 'sort_order' => 5],
            ['name' => 'BISMO', 'code' => 'UNIT-BSM', 'description' => 'Area Operasi BISMO', 'sort_order' => 6],
        ];

        $now = now();
        foreach ($initialUnits as $u) {
            $exists = DB::table('operational_units')->where('name', $u['name'])->exists();
            if (!$exists) {
                DB::table('operational_units')->insert([
                    'name' => $u['name'],
                    'code' => $u['code'],
                    'description' => $u['description'],
                    'default_hours' => 8.00,
                    'is_active' => true,
                    'is_initial' => true,
                    'sort_order' => $u['sort_order'],
                    'created_by' => 'System Default',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // Pastikan seluruh unit yang sudah pernah tercatat di tabel workforces / man_hours juga masuk ke master unit
        if (Schema::hasTable('workforces')) {
            $existingWfUnits = DB::table('workforces')
                ->select('unit')
                ->whereNotNull('unit')
                ->where('unit', '!=', '')
                ->distinct()
                ->pluck('unit');

            $maxSort = (int) DB::table('operational_units')->max('sort_order');
            foreach ($existingWfUnits as $unitName) {
                $unitName = trim((string) $unitName);
                if ($unitName === '') {
                    continue;
                }
                $unitRow = DB::table('operational_units')->where('name', $unitName)->first();
                if (!$unitRow) {
                    $maxSort++;
                    $unitId = DB::table('operational_units')->insertGetId([
                        'name' => $unitName,
                        'code' => 'UNIT-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $unitName), 0, 6)),
                        'description' => 'Unit Operasi ' . $unitName,
                        'default_hours' => 8.00,
                        'is_active' => true,
                        'is_initial' => true,
                        'sort_order' => $maxSort,
                        'created_by' => 'System Migration',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                } else {
                    $unitId = $unitRow->id;
                }

                DB::table('workforces')
                    ->where('unit', $unitName)
                    ->whereNull('operational_unit_id')
                    ->update(['operational_unit_id' => $unitId]);

                if (Schema::hasTable('man_hours')) {
                    DB::table('man_hours')
                        ->where('unit', $unitName)
                        ->whereNull('operational_unit_id')
                        ->update(['operational_unit_id' => $unitId]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('man_hours') && Schema::hasColumn('man_hours', 'operational_unit_id')) {
            Schema::table('man_hours', function (Blueprint $table) {
                $table->dropConstrainedForeignId('operational_unit_id');
            });
        }

        if (Schema::hasTable('workforces') && Schema::hasColumn('workforces', 'operational_unit_id')) {
            Schema::table('workforces', function (Blueprint $table) {
                $table->dropConstrainedForeignId('operational_unit_id');
            });
        }

        Schema::dropIfExists('operational_units');
    }
};

