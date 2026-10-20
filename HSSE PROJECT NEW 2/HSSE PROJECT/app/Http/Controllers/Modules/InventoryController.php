<?php

namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\InventoryBorrowing;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller
{
    /**
     * Tampilkan halaman utama Inventory sesuai referensi Apps/inventory/index.blade.php
     */
    public function index(Request $request): View|JsonResponse
    {
        $activeTab = $request->input('tab', 'master');

        // Query Master Barang
        $itemQuery = InventoryItem::query();
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $itemQuery->where(function ($q) use ($search) {
                $q->where('item_code', 'like', "%{$search}%")
                  ->orWhere('kode_barang', 'like', "%{$search}%")
                  ->orWhere('item_name', 'like', "%{$search}%")
                  ->orWhere('nama_barang', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('lokasi_default', 'like', "%{$search}%");
            });
        }
        if ($request->filled('kategori')) {
            $kat = $request->input('kategori');
            $itemQuery->where(function ($q) use ($kat) {
                $q->where('category', $kat)->orWhere('kategori', $kat);
            });
        }
        $items = $itemQuery->orderBy('item_name')->paginate(10)->withQueryString();

        // Query Transaksi
        $txQuery = InventoryTransaction::with(['item', 'inventory']);
        $searchH = trim($request->input('trx_search', $request->input('search_history', '')));
        if ($searchH !== '') {
            $txQuery->where(function ($q) use ($searchH) {
                $q->where('person_in_charge', 'like', "%{$searchH}%")
                  ->orWhere('petugas', 'like', "%{$searchH}%")
                  ->orWhere('notes', 'like', "%{$searchH}%")
                  ->orWhere('keterangan', 'like', "%{$searchH}%")
                  ->orWhereHas('item', function ($sub) use ($searchH) {
                      $sub->where('item_name', 'like', "%{$searchH}%")
                          ->orWhere('nama_barang', 'like', "%{$searchH}%")
                          ->orWhere('item_code', 'like', "%{$searchH}%")
                          ->orWhere('kode_barang', 'like', "%{$searchH}%")
                          ->orWhere('location', 'like', "%{$searchH}%")
                          ->orWhere('lokasi_default', 'like', "%{$searchH}%");
                  });
            });
        }

        $jenisVal = $request->input('trx_jenis', $request->input('jenis'));
        if (!empty($jenisVal)) {
            $txQuery->where(function ($q) use ($jenisVal) {
                $q->where('transaction_type', $jenisVal)
                  ->orWhere('jenis_transaksi', $jenisVal)
                  ->orWhere('transaction_type', ucfirst($jenisVal))
                  ->orWhere('jenis_transaksi', ucfirst($jenisVal));
            });
        }

        if ($request->filled('trx_kategori')) {
            $katVal = $request->input('trx_kategori');
            $txQuery->whereHas('item', function ($q) use ($katVal) {
                $q->where('category', $katVal)->orWhere('kategori', $katVal);
            });
        }

        if ($request->filled('trx_start') && $request->filled('trx_end')) {
            $txQuery->whereBetween('transaction_date', [
                \Carbon\Carbon::parse($request->input('trx_start'))->startOfDay(),
                \Carbon\Carbon::parse($request->input('trx_end'))->endOfDay(),
            ]);
        } elseif ($request->filled('trx_start')) {
            $txQuery->where('transaction_date', '>=', \Carbon\Carbon::parse($request->input('trx_start'))->startOfDay());
        } elseif ($request->filled('trx_end')) {
            $txQuery->where('transaction_date', '<=', \Carbon\Carbon::parse($request->input('trx_end'))->endOfDay());
        }

        $transactions = $txQuery->orderBy('transaction_date', 'desc')->paginate(10, ['*'], 'trx_page')->withQueryString();

        // Query Barang Terpinjam
        $borrowQuery = InventoryBorrowing::with(['item', 'user']);
        $searchB = trim($request->input('borrow_search', ''));
        if ($searchB !== '') {
            $borrowQuery->where(function ($q) use ($searchB) {
                $q->where('borrow_code', 'like', "%{$searchB}%")
                  ->orWhere('borrower_name', 'like', "%{$searchB}%")
                  ->orWhere('department', 'like', "%{$searchB}%")
                  ->orWhere('purpose', 'like', "%{$searchB}%")
                  ->orWhere('notes', 'like', "%{$searchB}%")
                  ->orWhereHas('item', function ($sub) use ($searchB) {
                      $sub->where('item_name', 'like', "%{$searchB}%")
                          ->orWhere('nama_barang', 'like', "%{$searchB}%")
                          ->orWhere('item_code', 'like', "%{$searchB}%")
                          ->orWhere('kode_barang', 'like', "%{$searchB}%");
                  });
            });
        }

        $borrowStatus = $request->input('borrow_status', 'active');
        if ($borrowStatus === 'active') {
            $borrowQuery->active();
        } elseif ($borrowStatus === 'returned') {
            $borrowQuery->returned();
        } elseif ($borrowStatus === 'overdue') {
            $borrowQuery->active()->whereNotNull('expected_return_date')->where('expected_return_date', '<', Carbon::today());
        } elseif ($borrowStatus !== 'all' && !empty($borrowStatus)) {
            $borrowQuery->where('status', $borrowStatus);
        }

        if ($request->filled('borrow_start') && $request->filled('borrow_end')) {
            $borrowQuery->whereBetween('borrow_date', [
                Carbon::parse($request->input('borrow_start'))->startOfDay(),
                Carbon::parse($request->input('borrow_end'))->endOfDay(),
            ]);
        } elseif ($request->filled('borrow_start')) {
            $borrowQuery->where('borrow_date', '>=', Carbon::parse($request->input('borrow_start'))->startOfDay());
        } elseif ($request->filled('borrow_end')) {
            $borrowQuery->where('borrow_date', '<=', Carbon::parse($request->input('borrow_end'))->endOfDay());
        }

        $borrowings = $borrowQuery->orderBy('borrow_date', 'desc')->orderBy('id', 'desc')->paginate(10, ['*'], 'borrow_page')->withQueryString();

        // Statistik Ringkasan Barang Terpinjam Aktif
        $allActiveBorrowings = InventoryBorrowing::active()->get();
        $totalPeminjamanAktif = (int) $allActiveBorrowings->count();
        $totalUnitTerpinjam = (int) $allActiveBorrowings->sum('remaining_quantity');
        $totalJenisBarangTerpinjam = (int) $allActiveBorrowings->pluck('inventory_item_id')->unique()->count();
        $totalPeminjamanOverdue = (int) $allActiveBorrowings->filter(fn ($b) => $b->is_overdue)->count();

        // 4 Kartu Statistik Ringkasan Stok
        $allItems = InventoryItem::all();
        $totalMasuk = (int) InventoryTransaction::whereIn('transaction_type', ['Masuk', 'masuk'])->sum('quantity');
        $totalKeluar = (int) InventoryTransaction::whereIn('transaction_type', ['Keluar', 'keluar'])->sum('quantity');
        $totalStokTersedia = (int) $allItems->sum(fn ($i) => $i->stock ?? $i->stok_sekarang ?? 0);
        $totalStokBersihTersedia = max(0, $totalStokTersedia - $totalUnitTerpinjam);
        $totalItemJenis = (int) $allItems->count();

        $stokAmanCount = $allItems->filter(fn ($i) => ($i->stock ?? $i->stok_sekarang ?? 0) > ($i->minimum_stock ?? $i->stok_minimum ?? 0))->count();
        $perluPengadaanCount = $allItems->filter(fn ($i) => ($i->stock ?? $i->stok_sekarang ?? 0) <= ($i->minimum_stock ?? $i->stok_minimum ?? 0))->count();

        // Modal Data Sources untuk Alpine.js 4 Kategori
        $modalMasukList = InventoryTransaction::with('item')
            ->whereIn('transaction_type', ['Masuk', 'masuk'])
            ->latest('transaction_date')
            ->take(50)
            ->get()
            ->map(fn ($t) => [
                'nama_barang' => $t->item?->nama_barang ?? $t->item?->item_name ?? 'Barang Dihapus',
                'barang_id' => $t->item?->kode_barang ?? $t->item?->item_code ?? '-',
                'jumlah' => $t->quantity ?? $t->jumlah ?? 0,
                'satuan' => $t->item?->satuan ?? $t->item?->unit_measure ?? 'pcs',
                'tanggal' => $t->transaction_date ? $t->transaction_date->format('d/m/Y H:i') : '-',
                'petugas' => $t->person_in_charge ?? $t->petugas ?? 'Petugas HSSE',
            ])->values();

        $modalKeluarList = InventoryTransaction::with('item')
            ->whereIn('transaction_type', ['Keluar', 'keluar'])
            ->latest('transaction_date')
            ->take(50)
            ->get()
            ->map(fn ($t) => [
                'nama_barang' => $t->item?->nama_barang ?? $t->item?->item_name ?? 'Barang Dihapus',
                'barang_id' => $t->item?->kode_barang ?? $t->item?->item_code ?? '-',
                'jumlah' => $t->quantity ?? $t->jumlah ?? 0,
                'satuan' => $t->item?->satuan ?? $t->item?->unit_measure ?? 'pcs',
                'tanggal' => $t->transaction_date ? $t->transaction_date->format('d/m/Y H:i') : '-',
                'petugas' => $t->person_in_charge ?? $t->petugas ?? 'Petugas HSSE',
            ])->values();

        $modalStokAmanList = $allItems
            ->filter(fn ($i) => ($i->stock ?? $i->stok_sekarang ?? 0) > ($i->minimum_stock ?? $i->stok_minimum ?? 0))
            ->values()
            ->map(fn ($i) => [
                'nama_barang' => $i->nama_barang ?? $i->item_name,
                'barang_id' => $i->kode_barang ?? $i->item_code,
                'kategori' => $i->kategori ?? $i->category,
                'stok_sekarang' => $i->stock ?? $i->stok_sekarang ?? 0,
                'stok_minimum' => $i->minimum_stock ?? $i->stok_minimum ?? 0,
                'satuan' => $i->satuan ?? $i->unit_measure ?? 'pcs',
                'lokasi' => $i->lokasi_default ?? $i->location ?? 'Gudang Utama',
            ]);

        $modalPerluPengadaanList = $allItems
            ->filter(fn ($i) => ($i->stock ?? $i->stok_sekarang ?? 0) <= ($i->minimum_stock ?? $i->stok_minimum ?? 0))
            ->values()
            ->map(fn ($i) => [
                'nama_barang' => $i->nama_barang ?? $i->item_name,
                'barang_id' => $i->kode_barang ?? $i->item_code,
                'kategori' => $i->kategori ?? $i->category,
                'stok_sekarang' => $i->stock ?? $i->stok_sekarang ?? 0,
                'stok_minimum' => $i->minimum_stock ?? $i->stok_minimum ?? 0,
                'satuan' => $i->satuan ?? $i->unit_measure ?? 'pcs',
                'lokasi' => $i->lokasi_default ?? $i->location ?? 'Gudang Utama',
            ]);

        $kategoriList = InventoryItem::KATEGORI_LIST;
        $allItemsForSelect = InventoryItem::orderBy('item_name')->get();
        $inventories = $allItemsForSelect;

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'items' => $items,
                'transactions' => $transactions,
                'borrowings' => $borrowings,
                'totalMasuk' => $totalMasuk,
                'totalKeluar' => $totalKeluar,
                'totalStokTersedia' => $totalStokTersedia,
                'totalStokBersihTersedia' => $totalStokBersihTersedia,
                'totalUnitTerpinjam' => $totalUnitTerpinjam,
                'totalPeminjamanAktif' => $totalPeminjamanAktif,
                'totalJenisBarangTerpinjam' => $totalJenisBarangTerpinjam,
                'totalPeminjamanOverdue' => $totalPeminjamanOverdue,
                'totalItemJenis' => $totalItemJenis,
                'stokAmanCount' => $stokAmanCount,
                'perluPengadaanCount' => $perluPengadaanCount,
            ]);
        }

        return view('inventory.index', compact(
            'activeTab',
            'items',
            'transactions',
            'borrowings',
            'totalMasuk',
            'totalKeluar',
            'totalStokTersedia',
            'totalStokBersihTersedia',
            'totalUnitTerpinjam',
            'totalPeminjamanAktif',
            'totalJenisBarangTerpinjam',
            'totalPeminjamanOverdue',
            'totalItemJenis',
            'stokAmanCount',
            'perluPengadaanCount',
            'modalMasukList',
            'modalKeluarList',
            'modalStokAmanList',
            'modalPerluPengadaanList',
            'kategoriList',
            'allItemsForSelect',
            'inventories'
        ));
    }

    /**
     * Pastikan pengguna memiliki hak akses kelola Inventory (Admin Management atau Admin Inventory).
     */
    protected function authorizeManagement(Request $request): void
    {
        $user = Auth::user();
        if (!$user || !$user->isActive() || !$user->canManageInventory()) {
            if ($request->wantsJson() || $request->ajax()) {
                abort(response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Tindakan ini hanya dapat dilakukan oleh Admin Management atau Admin Inventory.',
                ], 403));
            }
            abort(403, 'Akses ditolak. Tindakan ini hanya dapat dilakukan oleh Admin Management atau Admin Inventory.');
        }
    }

    /**
     * Simpan Master Barang Baru
     */
    public function storeItem(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorizeManagement($request);

        $validated = $request->validate([
            'kode_barang' => 'nullable|string|max:50',
            'nama_barang' => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
            'stok_sekarang' => 'nullable|integer|min:0',
            'stok_minimum' => 'nullable|integer|min:0',
            'satuan' => 'nullable|string|max:50',
            'lokasi_default' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $code = $request->input('barang_id', $request->input('kode_barang'));
        if (empty($code)) {
            $code = 'INV-' . strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $validated['kategori']), 0, 3)) . '-' . rand(100, 999);
        }

        $initialStock = (int) ($validated['stok_sekarang'] ?? $request->input('stok_awal', 0));

        $item = InventoryItem::create([
            'item_code' => $code,
            'kode_barang' => $code,
            'item_name' => $validated['nama_barang'],
            'nama_barang' => $validated['nama_barang'],
            'category' => $validated['kategori'],
            'kategori' => $validated['kategori'],
            'unit_measure' => $validated['satuan'] ?? 'pcs',
            'satuan' => $validated['satuan'] ?? 'pcs',
            'stock' => $initialStock,
            'stok_sekarang' => $initialStock,
            'minimum_stock' => (int) ($validated['stok_minimum'] ?? 0),
            'stok_minimum' => (int) ($validated['stok_minimum'] ?? 0),
            'location' => $validated['lokasi_default'] ?? 'Gudang Utama',
            'lokasi_default' => $validated['lokasi_default'] ?? 'Gudang Utama',
            'description' => $validated['keterangan'] ?? null,
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'user_name' => Auth::user()?->name ?? 'Admin HSSE',
            'module' => 'Inventory',
            'action' => 'TAMBAH',
            'record_type' => InventoryItem::class,
            'record_id' => $item->id,
            'description' => "Menambahkan master barang: {$item->item_name} [{$item->item_code}]",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Master barang berhasil disimpan!', 'item' => $item]);
        }

        return redirect()->route('inventory.index', ['tab' => 'master'])->with('success', 'Master barang baru berhasil disimpan!');
    }

    /**
     * Perbarui Master Barang
     */
    public function updateItem(Request $request, $id): RedirectResponse|JsonResponse
    {
        $this->authorizeManagement($request);

        $item = InventoryItem::findOrFail($id);

        $validated = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
            'stok_sekarang' => 'nullable|integer|min:0',
            'stok_minimum' => 'nullable|integer|min:0',
            'satuan' => 'nullable|string|max:50',
            'lokasi_default' => 'nullable|string|max:100',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $item->update([
            'item_name' => $validated['nama_barang'],
            'nama_barang' => $validated['nama_barang'],
            'category' => $validated['kategori'],
            'kategori' => $validated['kategori'],
            'unit_measure' => $validated['satuan'] ?? $item->unit_measure,
            'satuan' => $validated['satuan'] ?? $item->satuan,
            'stock' => isset($validated['stok_sekarang']) ? (int) $validated['stok_sekarang'] : $item->stock,
            'stok_sekarang' => isset($validated['stok_sekarang']) ? (int) $validated['stok_sekarang'] : $item->stok_sekarang,
            'minimum_stock' => isset($validated['stok_minimum']) ? (int) $validated['stok_minimum'] : $item->minimum_stock,
            'stok_minimum' => isset($validated['stok_minimum']) ? (int) $validated['stok_minimum'] : $item->stok_minimum,
            'location' => $validated['lokasi_default'] ?? $item->location,
            'lokasi_default' => $validated['lokasi_default'] ?? $item->lokasi_default,
            'description' => $validated['keterangan'] ?? $item->description,
            'keterangan' => $validated['keterangan'] ?? $item->keterangan,
        ]);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'user_name' => Auth::user()?->name ?? 'Admin HSSE',
            'module' => 'Inventory',
            'action' => 'UBAH',
            'record_type' => InventoryItem::class,
            'record_id' => $item->id,
            'description' => "Memperbarui master barang: {$item->item_name} [{$item->item_code}]",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Master barang berhasil diperbarui!', 'item' => $item]);
        }

        return redirect()->route('inventory.index', ['tab' => 'master'])->with('success', 'Master barang berhasil diperbarui!');
    }

    /**
     * Hapus Master Barang
     */
    public function destroyItem(Request $request, $id): RedirectResponse|JsonResponse
    {
        $this->authorizeManagement($request);

        $item = InventoryItem::findOrFail($id);
        $name = $item->item_name;
        $code = $item->item_code;

        $item->transactions()->delete();
        $item->delete();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'user_name' => Auth::user()?->name ?? 'Admin HSSE',
            'module' => 'Inventory',
            'action' => 'HAPUS',
            'record_type' => InventoryItem::class,
            'record_id' => (int) $id,
            'description' => "Menghapus master barang: {$name} [{$code}]",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Master barang berhasil dihapus!']);
        }

        return redirect()->route('inventory.index', ['tab' => 'master'])->with('success', 'Master barang berhasil dihapus!');
    }

    /**
     * Catat Transaksi Terpadu (Masuk / Keluar)
     */
    public function storeTransaction(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorizeManagement($request);

        $itemId = $request->input('inventory_id', $request->input('inventory_item_id'));
        $rawType = strtolower(trim((string) $request->input('jenis_transaksi', 'masuk')));
        $jenisTransaksi = ($rawType === 'keluar') ? 'Keluar' : 'Masuk';
        $tanggal = $request->input('tanggal_transaksi', $request->input('tanggal', date('Y-m-d')));
        $petugas = $request->input('petugas', Auth::user()?->name ?? 'Administrator HSSE');

        $request->merge([
            'inventory_item_id' => $itemId,
            'jenis_transaksi' => $jenisTransaksi,
            'tanggal' => $tanggal,
            'petugas' => $petugas,
        ]);

        $validated = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'jenis_transaksi' => 'required|in:Masuk,Keluar',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date',
            'petugas' => 'required|string|max:100',
            'keterangan' => 'nullable|string|max:500',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $item = InventoryItem::lockForUpdate()->findOrFail($validated['inventory_item_id']);

            if ($validated['jenis_transaksi'] === 'Keluar' && ($item->stock ?? $item->stok_sekarang ?? 0) < $validated['jumlah']) {
                $msg = "Stok barang '{$item->item_name}' tidak mencukupi (Tersedia: {$item->stock}, diminta: {$validated['jumlah']}).";
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return back()->withInput()->withErrors(['jumlah' => $msg])->with('error', $msg);
            }

            // Update Stock
            if ($validated['jenis_transaksi'] === 'Masuk') {
                $newStock = ($item->stock ?? 0) + $validated['jumlah'];
            } else {
                $newStock = max(0, ($item->stock ?? 0) - $validated['jumlah']);
            }

            $item->update([
                'stock' => $newStock,
                'stok_sekarang' => $newStock,
            ]);

            $tx = InventoryTransaction::create([
                'inventory_item_id' => $item->id,
                'transaction_type' => $validated['jenis_transaksi'],
                'jenis_transaksi' => $validated['jenis_transaksi'],
                'quantity' => $validated['jumlah'],
                'jumlah' => $validated['jumlah'],
                'transaction_date' => $validated['tanggal'],
                'tanggal' => $validated['tanggal'],
                'person_in_charge' => $validated['petugas'],
                'petugas' => $validated['petugas'],
                'notes' => $validated['keterangan'] ?? null,
                'keterangan' => $validated['keterangan'] ?? null,
            ]);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => Auth::user()?->name ?? 'Admin HSSE',
                'module' => 'Inventory',
                'action' => 'TRANSAKSI_' . strtoupper($validated['jenis_transaksi']),
                'record_type' => InventoryTransaction::class,
                'record_id' => $tx->id,
                'description' => "Mencatat transaksi {$validated['jenis_transaksi']} {$validated['jumlah']} {$item->satuan} untuk {$item->item_name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Transaksi {$validated['jenis_transaksi']} berhasil dicatat!",
                    'transaction' => $tx,
                ]);
            }

            return redirect()->route('inventory.index', ['tab' => 'history'])
                ->with('success', "Transaksi {$validated['jenis_transaksi']} barang berhasil dicatat!");
        });
    }

    /**
     * Tampilkan halaman mutasi transaksi terpisah sesuai Apps/inventory/transactions.blade.php
     */
    public function transactions(Request $request): View
    {
        return $this->index($request);
    }

    /**
     * Catat Peminjaman Barang Baru
     */
    public function storeBorrowing(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorizeManagement($request);

        if ($request->filled('item_id') && !$request->filled('inventory_item_id')) {
            $request->merge(['inventory_item_id' => $request->input('item_id')]);
        }
        if ($request->filled('borrower_division') && !$request->filled('department')) {
            $request->merge(['department' => $request->input('borrower_division')]);
        }

        $validated = $request->validate([
            'inventory_item_id' => 'required|exists:inventory_items,id',
            'quantity_borrowed' => 'required|integer|min:1',
            'borrower_name' => 'required|string|max:255',
            'department' => 'nullable|string|max:100',
            'borrow_date' => 'required|date',
            'expected_return_date' => 'nullable|date|after_or_equal:borrow_date',
            'purpose' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:500',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $item = InventoryItem::lockForUpdate()->findOrFail($validated['inventory_item_id']);

            // Validasi: Barang habis pakai tidak untuk dipinjam
            $katLower = strtolower($item->kategori ?? $item->category ?? '');
            if (str_contains($katLower, 'habis pakai') || str_contains($katLower, 'consumable') || str_contains($katLower, 'p3k')) {
                $msg = "Barang kategori '{$item->kategori}' merupakan perlengkapan habis pakai/medis dan tidak dapat dipinjam. Gunakan menu 'Catat Barang Keluar' untuk mutasi pemakaian.";
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return back()->withInput()->withErrors(['inventory_item_id' => $msg])->with('error', $msg);
            }

            // Hitung stok yang sedang dipinjam
            $currentBorrowed = (int) $item->borrowings()
                ->where('status', '!=', InventoryBorrowing::STATUS_RETURNED)
                ->sum(DB::raw('quantity_borrowed - quantity_returned'));

            $physicalStock = (int) ($item->stock ?? $item->stok_sekarang ?? 0);
            $availableStock = max(0, $physicalStock - $currentBorrowed);

            if ($validated['quantity_borrowed'] > $availableStock) {
                $msg = "Stok barang '{$item->item_name}' tidak mencukupi untuk dipinjam. Stok fisik: {$physicalStock}, sedang dipinjam: {$currentBorrowed}, tersedia: {$availableStock}, diminta: {$validated['quantity_borrowed']}.";
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return back()->withInput()->withErrors(['quantity_borrowed' => $msg])->with('error', $msg);
            }

            $borrowing = InventoryBorrowing::create([
                'inventory_item_id' => $item->id,
                'quantity_borrowed' => $validated['quantity_borrowed'],
                'quantity_returned' => 0,
                'borrower_name' => $validated['borrower_name'],
                'department' => $validated['department'] ?? 'Operasional Dieng',
                'borrow_date' => $validated['borrow_date'],
                'expected_return_date' => $validated['expected_return_date'] ?? null,
                'status' => InventoryBorrowing::STATUS_BORROWED,
                'purpose' => $validated['purpose'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'recorded_by' => Auth::user()?->name ?? 'Petugas HSSE',
                'user_id' => Auth::id(),
            ]);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => Auth::user()?->name ?? 'Petugas HSSE',
                'module' => 'Inventory',
                'action' => 'PINJAM_BARANG',
                'record_type' => InventoryBorrowing::class,
                'record_id' => $borrowing->id,
                'description' => "Mencatat peminjaman {$validated['quantity_borrowed']} {$item->satuan} {$item->item_name} kepada {$validated['borrower_name']} ({$validated['department']})",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Peminjaman barang berhasil dicatat!',
                    'borrowing' => $borrowing,
                ]);
            }

            return redirect()->route('inventory.index', ['tab' => 'borrowed'])
                ->with('success', "Peminjaman {$validated['quantity_borrowed']} {$item->item_name} kepada {$validated['borrower_name']} berhasil dicatat!");
        });
    }

    /**
     * Catat Pengembalian Barang (Penuh atau Sebagian)
     */
    public function returnBorrowing(Request $request, $id): RedirectResponse|JsonResponse
    {
        $this->authorizeManagement($request);

        $borrowing = InventoryBorrowing::with('item')->findOrFail($id);

        $remaining = $borrowing->remaining_quantity;
        if ($remaining <= 0 || $borrowing->status === InventoryBorrowing::STATUS_RETURNED) {
            $msg = "Transaksi peminjaman ini sudah lunas/selesai dikembalikan.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $validated = $request->validate([
            'quantity_returned' => "required|integer|min:1|max:{$remaining}",
            'actual_return_date' => 'required|date',
            'return_condition' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        return DB::transaction(function () use ($borrowing, $validated, $request, $remaining) {
            $item = InventoryItem::lockForUpdate()->findOrFail($borrowing->inventory_item_id);

            $qtyReturn = (int) $validated['quantity_returned'];
            $newReturned = (int) $borrowing->quantity_returned + $qtyReturn;
            $newRemaining = max(0, (int) $borrowing->quantity_borrowed - $newReturned);

            $newStatus = ($newRemaining === 0)
                ? InventoryBorrowing::STATUS_RETURNED
                : InventoryBorrowing::STATUS_PARTIAL;

            // Catatan log audit pengembalian
            $petugas = Auth::user()?->name ?? 'Petugas HSSE';
            $tgl = Carbon::parse($validated['actual_return_date'])->format('d/m/Y H:i');
            $condition = $validated['return_condition'] ?? 'Baik';
            $logNote = "[{$tgl}] Dikembalikan {$qtyReturn} unit (Kondisi: {$condition}) oleh {$borrowing->borrower_name}, diterima oleh {$petugas}. Catatan: " . ($validated['notes'] ?? '-');

            $existingNotes = $borrowing->notes ? $borrowing->notes . "\n" . $logNote : $logNote;

            $borrowing->update([
                'quantity_returned' => $newReturned,
                'actual_return_date' => $validated['actual_return_date'],
                'status' => $newStatus,
                'notes' => $existingNotes,
            ]);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'user_name' => $petugas,
                'module' => 'Inventory',
                'action' => 'PENGEMBALIAN_BARANG',
                'record_type' => InventoryBorrowing::class,
                'record_id' => $borrowing->id,
                'description' => "Mencatat pengembalian {$qtyReturn} {$item->satuan} {$item->item_name} dari {$borrowing->borrower_name} (Status: {$newStatus}, Sisa: {$newRemaining})",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $message = ($newStatus === InventoryBorrowing::STATUS_RETURNED)
                ? "Seluruh barang ({$borrowing->quantity_borrowed} unit) berhasil dikembalikan penuh! Transaksi telah dipindahkan ke riwayat pengembalian."
                : "Pengembalian sebagian ({$qtyReturn} unit) berhasil dicatat. Sisa yang masih dipinjam: {$newRemaining} unit.";

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'borrowing' => $borrowing->fresh(),
                ]);
            }

            return redirect()->route('inventory.index', ['tab' => 'borrowed'])->with('success', $message);
        });
    }

    /**
     * Export Riwayat Transaksi (History) Inventory to PDF format.
     * Sesuai ketentuan: Hanya mengambil data dari Riwayat Transaksi (History), bukan Master Barang & Stok Real-Time.
     */
    public function export(Request $request): Response
    {
        $txQuery = InventoryTransaction::with('item');

        $searchH = trim($request->input('trx_search', $request->input('search_history', $request->input('search', ''))));
        if ($searchH !== '') {
            $txQuery->where(function ($q) use ($searchH) {
                $q->where('person_in_charge', 'like', "%{$searchH}%")
                  ->orWhere('petugas', 'like', "%{$searchH}%")
                  ->orWhere('notes', 'like', "%{$searchH}%")
                  ->orWhere('keterangan', 'like', "%{$searchH}%")
                  ->orWhereHas('item', function ($sub) use ($searchH) {
                      $sub->where('item_name', 'like', "%{$searchH}%")
                          ->orWhere('nama_barang', 'like', "%{$searchH}%")
                          ->orWhere('item_code', 'like', "%{$searchH}%")
                          ->orWhere('kode_barang', 'like', "%{$searchH}%")
                          ->orWhere('location', 'like', "%{$searchH}%")
                          ->orWhere('lokasi_default', 'like', "%{$searchH}%");
                  });
            });
        }

        $jenisVal = $request->input('trx_jenis', $request->input('jenis'));
        if (!empty($jenisVal) && $jenisVal !== 'Semua') {
            $txQuery->where(function ($q) use ($jenisVal) {
                $q->where('transaction_type', $jenisVal)
                  ->orWhere('jenis_transaksi', $jenisVal)
                  ->orWhere('transaction_type', ucfirst($jenisVal))
                  ->orWhere('jenis_transaksi', ucfirst($jenisVal));
            });
        }

        if ($request->filled('trx_kategori') && $request->input('trx_kategori') !== 'Semua') {
            $katVal = $request->input('trx_kategori');
            $txQuery->whereHas('item', function ($q) use ($katVal) {
                $q->where('category', $katVal)->orWhere('kategori', $katVal);
            });
        }

        if ($request->filled('trx_start') && $request->filled('trx_end')) {
            $txQuery->whereBetween('transaction_date', [
                \Carbon\Carbon::parse($request->input('trx_start'))->startOfDay(),
                \Carbon\Carbon::parse($request->input('trx_end'))->endOfDay(),
            ]);
        } elseif ($request->filled('trx_start')) {
            $txQuery->where('transaction_date', '>=', \Carbon\Carbon::parse($request->input('trx_start'))->startOfDay());
        } elseif ($request->filled('trx_end')) {
            $txQuery->where('transaction_date', '<=', \Carbon\Carbon::parse($request->input('trx_end'))->endOfDay());
        }

        $records = $txQuery->orderBy('transaction_date', 'desc')->get();

        $pdf = Pdf::loadView('exports.inventory-history-pdf', [
            'reportTitle' => 'LAPORAN RIWAYAT TRANSAKSI BARANG (HISTORY INVENTORY)',
            'moduleName' => 'Inventory - Riwayat Transaksi',
            'filterInfo' => [
                'Jenis Transaksi' => $jenisVal ?: 'Semua',
                'Kategori' => $request->input('trx_kategori', 'Semua'),
                'Periode' => ($request->filled('trx_start') ? $request->input('trx_start') : 'Awal') . ' s/d ' . ($request->filled('trx_end') ? $request->input('trx_end') : 'Sekarang'),
                'Pencarian' => $searchH ?: '-',
            ],
            'records' => $records,
            'userName' => Auth::user()->name ?? 'Administrator',
            'generatedAt' => now()->translatedFormat('d F Y, H:i:s'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Laporan-Riwayat-Inventory-' . now()->format('Ymd-His') . '.pdf');
    }
}
