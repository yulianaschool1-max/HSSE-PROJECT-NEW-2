<?php

namespace Tests\Feature\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\User;
use Tests\TestCase;

class InventoryManagementTest extends TestCase
{
    protected function getAdmin(): User
    {
        return User::first() ?: User::factory()->create([
            'role' => 'Admin',
            'status' => 'Aktif',
        ]);
    }

    public function test_inventory_index_renders_successfully(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get('/inventory');
        $response->assertStatus(200);
        $response->assertSee('Manajemen Logistik & Inventaris HSSE', false);
        $response->assertSee('Master Barang & Stok Real-Time', false);
        $response->assertSee('Riwayat Transaksi (History)', false);
    }

    public function test_inventory_ajax_json_endpoint(): void
    {
        $admin = $this->getAdmin();

        $item = InventoryItem::firstOrCreate(
            ['item_code' => 'TEST-INV-001'],
            [
                'item_name' => 'Safety Helmet Test',
                'category' => 'APD (Alat Pelindung Diri)',
                'unit_measure' => 'Unit',
                'stock' => 50,
                'minimum_stock' => 10,
                'location' => 'Gudang Utama',
            ]
        );

        $response = $this->actingAs($admin)->getJson('/inventory');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'items',
            'transactions',
            'totalMasuk',
            'totalKeluar',
            'stokAmanCount',
            'perluPengadaanCount',
        ]);
        $response->assertJson(['success' => true]);
    }

    public function test_admin_can_create_inventory_item(): void
    {
        $admin = $this->getAdmin();
        $code = 'TEST-ITEM-' . time();

        $response = $this->actingAs($admin)->post(route('inventory.store'), [
            'barang_id' => $code,
            'nama_barang' => 'Kacamata Safety Test',
            'kategori' => 'APD (Alat Pelindung Diri)',
            'stok_sekarang' => 30,
            'stok_minimum' => 5,
            'satuan' => 'Pcs',
            'lokasi_default' => 'Gudang 1',
            'keterangan' => 'Uji Coba Otomatis',
        ]);

        $response->assertRedirect(route('inventory.index', ['tab' => 'master']));
        $this->assertDatabaseHas('inventory_items', [
            'item_code' => $code,
            'item_name' => 'Kacamata Safety Test',
        ]);
    }

    public function test_admin_can_update_inventory_item(): void
    {
        $admin = $this->getAdmin();
        $item = InventoryItem::firstOrCreate(
            ['item_code' => 'TEST-UPD-001'],
            [
                'item_name' => 'Sarung Tangan Uji',
                'category' => 'APD (Alat Pelindung Diri)',
                'unit_measure' => 'Pasang',
                'stock' => 20,
                'minimum_stock' => 5,
                'location' => 'Gudang HSSE',
            ]
        );

        $response = $this->actingAs($admin)->put(route('inventory.update', $item->id), [
            'nama_barang' => 'Sarung Tangan Uji Updated',
            'kategori' => 'APD (Alat Pelindung Diri)',
            'stok_sekarang' => 25,
            'stok_minimum' => 8,
            'satuan' => 'Pasang',
            'lokasi_default' => 'Gudang Rak 2',
            'keterangan' => 'Diperbarui',
        ]);

        $response->assertRedirect(route('inventory.index', ['tab' => 'master']));
        $item->refresh();
        $this->assertEquals('Sarung Tangan Uji Updated', $item->item_name);
        $this->assertEquals(25, $item->stock);
    }

    public function test_can_record_incoming_and_outgoing_transactions(): void
    {
        $admin = $this->getAdmin();
        $item = InventoryItem::firstOrCreate(
            ['item_code' => 'TEST-TRX-001'],
            [
                'item_name' => 'Earplug Safety Test',
                'category' => 'APD (Alat Pelindung Diri)',
                'unit_measure' => 'Box',
                'stock' => 10,
                'minimum_stock' => 5,
            ]
        );

        $initialStock = $item->stock;

        // Transaksi Masuk
        $responseMasuk = $this->actingAs($admin)->post(route('inventory.transactions.store'), [
            'inventory_id' => $item->id,
            'jenis_transaksi' => 'masuk',
            'jumlah' => 15,
            'petugas' => 'Admin Tester',
            'keterangan' => 'Restock Masuk',
        ]);
        $responseMasuk->assertRedirect(route('inventory.index', ['tab' => 'history']));

        $item->refresh();
        $this->assertEquals($initialStock + 15, $item->stock);

        // Transaksi Keluar
        $responseKeluar = $this->actingAs($admin)->post(route('inventory.transactions.store'), [
            'inventory_id' => $item->id,
            'jenis_transaksi' => 'keluar',
            'jumlah' => 5,
            'petugas' => 'Admin Tester',
            'keterangan' => 'Pengambilan untuk Pekerja',
        ]);
        $responseKeluar->assertRedirect(route('inventory.index', ['tab' => 'history']));

        $item->refresh();
        $this->assertEquals($initialStock + 10, $item->stock);
    }

    public function test_rejects_outgoing_transaction_exceeding_stock(): void
    {
        $admin = $this->getAdmin();
        $item = InventoryItem::firstOrCreate(
            ['item_code' => 'TEST-STOCK-LIMIT'],
            [
                'item_name' => 'Full Body Harness Limited',
                'category' => 'Perlengkapan Kerja di Ketinggian',
                'unit_measure' => 'Set',
                'stock' => 2,
                'minimum_stock' => 1,
            ]
        );

        $response = $this->actingAs($admin)->post(route('inventory.transactions.store'), [
            'inventory_id' => $item->id,
            'jenis_transaksi' => 'keluar',
            'jumlah' => 50,
            'petugas' => 'Admin Tester',
        ]);

        $response->assertSessionHasErrors(['jumlah']);
        $item->refresh();
        $this->assertEquals(2, $item->stock);
    }

    public function test_admin_can_delete_inventory_item(): void
    {
        $admin = $this->getAdmin();
        $item = InventoryItem::create([
            'item_code' => 'TEST-DEL-' . time(),
            'item_name' => 'Barang Untuk Dihapus',
            'category' => 'APD (Alat Pelindung Diri)',
            'unit_measure' => 'Pcs',
            'stock' => 1,
            'minimum_stock' => 1,
        ]);

        $response = $this->actingAs($admin)->delete(route('inventory.destroy', $item->id));
        $response->assertRedirect(route('inventory.index', ['tab' => 'master']));
        $this->assertDatabaseMissing('inventory_items', ['id' => $item->id]);
    }

    public function test_inventory_history_filters_work_properly(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get('/inventory?' . http_build_query([
            'tab' => 'history',
            'trx_jenis' => 'masuk',
            'trx_kategori' => 'APD (Alat Pelindung Diri)',
            'trx_start' => date('Y-01-01'),
            'trx_end' => date('Y-12-31'),
        ]));

        $response->assertStatus(200);
        $response->assertSee('Riwayat Transaksi (History)', false);
    }

    public function test_inventory_export_csv_master_and_history(): void
    {
        $admin = $this->getAdmin();

        // 1. Export Master Barang
        $resMaster = $this->actingAs($admin)->get(route('inventory.export', ['tab' => 'master']));
        $resMaster->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $resMaster->headers->get('content-type'));
        $this->assertStringContainsString('Inventory_Master_Barang', (string) $resMaster->headers->get('content-disposition'));

        // 2. Export Mutasi Transaksi
        $resHistory = $this->actingAs($admin)->get(route('inventory.export', ['tab' => 'history']));
        $resHistory->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $resHistory->headers->get('content-type'));
        $this->assertStringContainsString('Inventory_Mutasi_Transaksi', (string) $resHistory->headers->get('content-disposition'));
    }

    public function test_inventory_cards_calculate_physical_quantities(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get(route('inventory.index'));
        $response->assertStatus(200);
        $response->assertViewHas('totalMasuk');
        $response->assertViewHas('totalKeluar');
        $response->assertViewHas('totalStokTersedia');
        $response->assertViewHas('totalItemJenis');

        $this->assertIsInt($response->viewData('totalMasuk'));
        $this->assertIsInt($response->viewData('totalKeluar'));
        $this->assertIsInt($response->viewData('totalStokTersedia'));
        $this->assertIsInt($response->viewData('totalItemJenis'));
    }
}
