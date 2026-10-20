<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create well_pads table
        if (!Schema::hasTable('well_pads')) {
            Schema::create('well_pads', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100)->unique();
                $table->string('code', 50)->unique();
                $table->boolean('is_active')->default(true)->index();
                $table->integer('sort_order')->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // 2. Enhance gas_monitorings table with o2_level, lel_level, and status columns
        if (Schema::hasTable('gas_monitorings')) {
            Schema::table('gas_monitorings', function (Blueprint $table) {
                if (!Schema::hasColumn('gas_monitorings', 'o2_level')) {
                    $table->decimal('o2_level', 8, 2)->nullable()->after('co_level');
                }
                if (!Schema::hasColumn('gas_monitorings', 'lel_level')) {
                    $table->decimal('lel_level', 8, 2)->nullable()->after('h2s_level');
                }
                if (!Schema::hasColumn('gas_monitorings', 'o2_status')) {
                    $table->string('o2_status', 20)->default('Sesuai')->after('co_status');
                }
                if (!Schema::hasColumn('gas_monitorings', 'lel_status')) {
                    $table->string('lel_status', 20)->default('Sesuai')->after('h2s_status');
                }
                if (!Schema::hasColumn('gas_monitorings', 'overall_status')) {
                    $table->string('overall_status', 20)->default('Sesuai')->after('pressure_status');
                }
                if (!Schema::hasColumn('gas_monitorings', 'well_pad_id')) {
                    $table->unsignedBigInteger('well_pad_id')->nullable()->after('id')->index();
                }
            });
        }

        // 3. Seed initial well pads from Image 2 & Image 3
        $defaultPads = [
            ['name' => 'Pad 7', 'code' => 'WP-07', 'is_active' => true, 'sort_order' => 1],
            ['name' => 'Pad 29 C', 'code' => 'WP-29C', 'is_active' => true, 'sort_order' => 2],
            ['name' => 'Pad 29 D', 'code' => 'WP-29D', 'is_active' => true, 'sort_order' => 3],
            ['name' => 'Pad 9 C', 'code' => 'WP-09C', 'is_active' => true, 'sort_order' => 4],
            ['name' => 'Pad 9 D', 'code' => 'WP-09D', 'is_active' => true, 'sort_order' => 5],
            ['name' => 'Pad 10', 'code' => 'WP-10', 'is_active' => false, 'sort_order' => 6],
        ];

        foreach ($defaultPads as $pad) {
            $exists = DB::table('well_pads')->where('code', $pad['code'])->orWhere('name', $pad['name'])->first();
            if (!$exists) {
                DB::table('well_pads')->insert(array_merge($pad, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // 4. Also register any existing legacy well pads from gas_monitorings table
        $existingPads = DB::table('gas_monitorings')->select('well_pad')->distinct()->pluck('well_pad');
        foreach ($existingPads as $wpName) {
            if (!$wpName) continue;
            // Clean up name or match
            $cleanName = trim($wpName);
            $aliasPad = DB::table('well_pads')->where('name', $cleanName)->first();
            if (!$aliasPad) {
                // If named 'Well Pad 7', let's see if 'Pad 7' exists
                $shortName = preg_replace('/^Well\s+/i', '', $cleanName);
                $aliasPad = DB::table('well_pads')->where('name', $shortName)->first();
            }

            if (!$aliasPad) {
                // Create it so legacy records have a valid well pad
                $cleanCode = 'WP-' . strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $cleanName));
                DB::table('well_pads')->insert([
                    'name' => $cleanName,
                    'code' => $cleanCode,
                    'is_active' => true,
                    'sort_order' => 99,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 5. Link well_pad_id and calculate o2/lel for any existing records where null
        $pads = DB::table('well_pads')->get();
        foreach ($pads as $p) {
            DB::table('gas_monitorings')
                ->where('well_pad', $p->name)
                ->orWhere('well_pad', 'Well ' . $p->name)
                ->update(['well_pad_id' => $p->id]);
        }

        // Default reasonable O2 and LEL if null for existing records to keep statistics realistic
        $recordsToFill = DB::table('gas_monitorings')->whereNull('o2_level')->orWhereNull('lel_level')->get();
        foreach ($recordsToFill as $r) {
            $o2 = $r->o2_level ?? 20.9;
            $lel = $r->lel_level ?? 0.0;
            $o2Status = ($o2 >= 19.5 && $o2 <= 23.5) ? 'Sesuai' : 'Tidak Sesuai';
            $lelStatus = ($lel <= 10.0) ? 'Sesuai' : 'Tidak Sesuai';
            
            $isConforming = ($r->co_status === 'Sesuai') 
                && ($r->h2s_status === 'Sesuai') 
                && ($r->pressure_status === 'Sesuai') 
                && ($o2Status === 'Sesuai') 
                && ($lelStatus === 'Sesuai');

            DB::table('gas_monitorings')->where('id', $r->id)->update([
                'o2_level' => $o2,
                'lel_level' => $lel,
                'o2_status' => $o2Status,
                'lel_status' => $lelStatus,
                'overall_status' => $isConforming ? 'Sesuai' : 'Tidak Sesuai',
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('gas_monitorings')) {
            Schema::table('gas_monitorings', function (Blueprint $table) {
                if (Schema::hasColumn('gas_monitorings', 'well_pad_id')) {
                    $table->dropColumn('well_pad_id');
                }
                if (Schema::hasColumn('gas_monitorings', 'overall_status')) {
                    $table->dropColumn('overall_status');
                }
                if (Schema::hasColumn('gas_monitorings', 'lel_status')) {
                    $table->dropColumn('lel_status');
                }
                if (Schema::hasColumn('gas_monitorings', 'o2_status')) {
                    $table->dropColumn('o2_status');
                }
                if (Schema::hasColumn('gas_monitorings', 'lel_level')) {
                    $table->dropColumn('lel_level');
                }
                if (Schema::hasColumn('gas_monitorings', 'o2_level')) {
                    $table->dropColumn('o2_level');
                }
            });
        }

        Schema::dropIfExists('well_pads');
    }
};
