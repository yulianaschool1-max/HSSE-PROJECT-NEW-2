<?php

namespace Tests\Feature\Inventory;

use App\Models\InventoryBorrowing;
use App\Models\InventoryItem;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class InventoryBorrowingTest extends TestCase
{
    protected function getAdmin(): User
    {
        return User::first() ?: User::factory()->create([
            'role' => 'Admin',
            'status' => 'Aktif',
        ]);
    }

    public function test_inventory_index_displays_three_tabs_and_borrowing_metrics(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get(route('inventory.index'));
        $response->assertStatus(200);
        $response->assertSee('Master Barang & Stok Real-Time', false);
        $response->assertSee('Riwayat Transaksi (History)', false);
        $response->assertSee('Barang Terpinjam', false);
        $response->assertSee('Stok Fisik Tersedia', false);

        // Pastikan tombol + Pinjam Barang dan modal peminjaman ada & terhubung
        $response->assertSee('id="modalPinjamBarang"', false);
        $response->assertSee('data-bs-target="#modalPinjamBarang"', false);
        $response->assertSee('openBorrowModal()', false);
        $response->assertSee('id="formPinjamBarang"', false);
        $response->assertSee('id="borrowItemSelect"', false);
        $response->assertSee('id="btnSubmitBorrow"', false);
    }

    public function test_can_borrow_non_consumable_item_successfully(): void
    {
        $admin = $this->getAdmin();

        $item = InventoryItem::create([
            'item_code' => 'TEST-TOOL-' . time(),
            'item_name' => 'Gas Detector Portable Multi',
            'category' => 'Peralatan Emergency & Rescue',
            'stock' => 10,
            'unit_measure' => 'Unit',
            'minimum_stock' => 2,
        ]);

        $response = $this->actingAs($admin)->post(route('inventory.borrowings.store'), [
            'item_id' => $item->id,
            'borrower_name' => 'Budi Santoso',
            'borrower_division' => 'Operasi Dieng 2',
            'quantity_borrowed' => 3,
            'borrow_date' => Carbon::now()->toDateString(),
            'expected_return_date' => Carbon::now()->addDays(5)->toDateString(),
            'purpose' => 'Pemeriksaan sumur H2S',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('inventory_borrowings', [
            'inventory_item_id' => $item->id,
            'borrower_name' => 'Budi Santoso',
            'quantity_borrowed' => 3,
            'quantity_returned' => 0,
            'status' => 'Dipinjam',
        ]);

        // Stok fisik tetap 10, tetapi stok tersedia adalah 7
        $freshItem = $item->fresh();
        $this->assertEquals(10, $freshItem->stock);
        $this->assertEquals(3, $freshItem->borrowed_stock);
        $this->assertEquals(7, $freshItem->available_stock);
    }

    public function test_cannot_borrow_consumable_item(): void
    {
        $admin = $this->getAdmin();

        $consumableItem = InventoryItem::create([
            'item_code' => 'TEST-CONS-' . time(),
            'item_name' => 'Sarung Tangan Nitril Sekali Pakai',
            'category' => 'APD Habis Pakai',
            'stock' => 50,
            'unit_measure' => 'Pasang',
            'minimum_stock' => 10,
        ]);

        $response = $this->actingAs($admin)->post(route('inventory.borrowings.store'), [
            'item_id' => $consumableItem->id,
            'borrower_name' => 'Joko Anwar',
            'borrower_division' => 'Pemeliharaan',
            'quantity_borrowed' => 5,
            'borrow_date' => Carbon::now()->toDateString(),
            'expected_return_date' => Carbon::now()->addDays(2)->toDateString(),
            'purpose' => 'Pembersihan pipa',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('inventory_borrowings', [
            'inventory_item_id' => $consumableItem->id,
            'borrower_name' => 'Joko Anwar',
        ]);
    }

    public function test_cannot_borrow_more_than_available_stock(): void
    {
        $admin = $this->getAdmin();

        $item = InventoryItem::create([
            'item_code' => 'TEST-LIMITED-' . time(),
            'item_name' => 'Handy Talkie Motorola',
            'category' => 'Detektor & Instrumen Lingkungan',
            'stock' => 5,
            'unit_measure' => 'Unit',
            'minimum_stock' => 1,
        ]);

        // Buat peminjaman 4 unit
        InventoryBorrowing::create([
            'borrow_code' => 'BRW-TEST-' . time() . '-' . rand(100, 999),
            'inventory_item_id' => $item->id,
            'borrower_name' => 'Operator A',
            'borrower_division' => 'Security',
            'quantity_borrowed' => 4,
            'quantity_returned' => 0,
            'borrow_date' => Carbon::now()->toDateString(),
            'status' => 'Dipinjam',
        ]);

        // Coba pinjam 2 unit lagi (tersisa hanya 1)
        $response = $this->actingAs($admin)->post(route('inventory.borrowings.store'), [
            'item_id' => $item->id,
            'borrower_name' => 'Operator B',
            'borrower_division' => 'Security',
            'quantity_borrowed' => 2,
            'borrow_date' => Carbon::now()->toDateString(),
            'expected_return_date' => Carbon::now()->addDays(1)->toDateString(),
            'purpose' => 'Patroli malam',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_can_return_borrowed_item_partially_and_fully(): void
    {
        $admin = $this->getAdmin();

        $item = InventoryItem::create([
            'item_code' => 'TEST-RETURN-' . time(),
            'item_name' => 'Harness Keselamatan Full Body',
            'category' => 'Perlengkapan Kerja di Ketinggian',
            'stock' => 8,
            'unit_measure' => 'Set',
            'minimum_stock' => 2,
        ]);

        $borrowing = InventoryBorrowing::create([
            'borrow_code' => 'BRW-TEST-RET-' . time(),
            'inventory_item_id' => $item->id,
            'borrower_name' => 'Teknisi Proyek',
            'borrower_division' => 'Konstruksi',
            'quantity_borrowed' => 5,
            'quantity_returned' => 0,
            'borrow_date' => Carbon::now()->subDays(2)->toDateString(),
            'expected_return_date' => Carbon::now()->addDays(3)->toDateString(),
            'status' => 'Dipinjam',
        ]);

        // 1. Pengembalian parsial (2 dari 5 unit)
        $respPartial = $this->actingAs($admin)->post(route('inventory.borrowings.return', $borrowing), [
            'quantity_returned' => 2,
            'actual_return_date' => Carbon::now()->toDateString(),
            'item_condition' => 'Baik',
            'notes' => '2 harness selesai dipakai',
        ]);

        $respPartial->assertRedirect();
        $respPartial->assertSessionHas('success');

        $borrowing->refresh();
        $this->assertEquals(2, $borrowing->quantity_returned);
        $this->assertEquals(3, $borrowing->remaining_quantity);
        $this->assertEquals(InventoryBorrowing::STATUS_PARTIAL, $borrowing->status);

        // 2. Pengembalian sisa secara penuh (3 unit)
        $respFull = $this->actingAs($admin)->post(route('inventory.borrowings.return', $borrowing), [
            'quantity_returned' => 3,
            'actual_return_date' => Carbon::now()->toDateString(),
            'item_condition' => 'Baik',
            'notes' => 'Sisa 3 harness dikembalikan lengkap',
        ]);

        $respFull->assertRedirect();
        $respFull->assertSessionHas('success');

        $borrowing->refresh();
        $this->assertEquals(5, $borrowing->quantity_returned);
        $this->assertEquals(0, $borrowing->remaining_quantity);
        $this->assertEquals('Dikembalikan', $borrowing->status);
        $this->assertNotNull($borrowing->actual_return_date);

        // Stok barang tersedia kembali penuh 8 unit
        $freshItem = $item->fresh();
        $this->assertEquals(8, $freshItem->stock);
        $this->assertEquals(0, $freshItem->borrowed_stock);
        $this->assertEquals(8, $freshItem->available_stock);
    }

    public function test_inventory_export_csv_defaults_to_transaction_history(): void
    {
        $admin = $this->getAdmin();

        $response = $this->actingAs($admin)->get(route('inventory.export'));
        $response->assertStatus(200);

        // Content-Disposition harus menunjukkan Inventory_Mutasi_Transaksi
        $disposition = (string) $response->headers->get('content-disposition');
        $this->assertStringContainsString('Inventory_Mutasi_Transaksi', $disposition);
        $this->assertStringContainsString('.csv', $disposition);
    }

    public function test_cannot_borrow_zero_or_negative_quantity(): void
    {
        $admin = $this->getAdmin();

        $item = InventoryItem::create([
            'item_code' => 'TEST-QTY-' . time(),
            'item_name' => 'Alat Uji Gas',
            'category' => 'Detektor & Instrumen Lingkungan',
            'stock' => 10,
            'unit_measure' => 'Unit',
            'minimum_stock' => 1,
        ]);

        // Coba jumlah nol
        $respZero = $this->actingAs($admin)->post(route('inventory.borrowings.store'), [
            'item_id' => $item->id,
            'borrower_name' => 'Tester',
            'quantity_borrowed' => 0,
            'borrow_date' => Carbon::now()->toDateString(),
        ]);
        $respZero->assertSessionHasErrors('quantity_borrowed');

        // Coba jumlah negatif
        $respNeg = $this->actingAs($admin)->post(route('inventory.borrowings.store'), [
            'item_id' => $item->id,
            'borrower_name' => 'Tester',
            'quantity_borrowed' => -5,
            'borrow_date' => Carbon::now()->toDateString(),
        ]);
        $respNeg->assertSessionHasErrors('quantity_borrowed');
    }

    public function test_borrowed_item_appears_in_borrowed_tab_list(): void
    {
        $admin = $this->getAdmin();

        $item = InventoryItem::create([
            'item_code' => 'TEST-TAB-' . time(),
            'item_name' => 'Radio Komunikasi HT Lapangan',
            'category' => 'Peralatan Emergency & Rescue',
            'stock' => 5,
            'unit_measure' => 'Unit',
            'minimum_stock' => 1,
        ]);

        $borrowing = InventoryBorrowing::create([
            'borrow_code' => 'BRW-TAB-' . time(),
            'inventory_item_id' => $item->id,
            'borrower_name' => 'Ahmad Teknisi',
            'department' => 'Operasi Dieng 2',
            'quantity_borrowed' => 2,
            'quantity_returned' => 0,
            'borrow_date' => Carbon::now()->toDateString(),
            'status' => InventoryBorrowing::STATUS_BORROWED,
        ]);

        $response = $this->actingAs($admin)->get(route('inventory.index', ['tab' => 'borrowed']));
        $response->assertStatus(200);
        $response->assertSee('Ahmad Teknisi', false);
        $response->assertSee('Radio Komunikasi HT Lapangan', false);
        $response->assertSee('Kembalikan', false);
    }
}
