<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mutasi Transaksi Inventaris | HSSE Project</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --navy: #102b3f;
            --navy-dark: #071e2e;
            --green: #07845c;
            --page-bg: #f3f7fb;
            --border: #dce7f2;
            --muted: #70849b;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--page-bg);
            color: #243b53;
            font-family: Arial, sans-serif;
            font-size: 13px;
        }

        .sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: 244px;
            padding: 17px 8px;
            overflow-y: auto;
            color: white;
            background: linear-gradient(180deg, var(--navy), var(--navy-dark));
            z-index: 1000;
        }

        .brand {
            display: flex;
            gap: 10px;
            align-items: center;
            padding: 0 12px 23px;
            border-bottom: 1px solid #ffffff20;
        }

        .brand-mark {
            display: grid;
            place-items: center;
            width: 35px;
            height: 35px;
            border-radius: 9px;
            background: #087f5b;
            font-size: 19px;
        }

        .brand strong { font-size: 14px; }
        .brand small {
            display: block;
            margin-top: 4px;
            color: #c3d2df;
            font-size: 9px;
        }

        .nav-heading {
            margin: 23px 13px 9px;
            color: #91a8ba;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .nav-item-link {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 11px 13px;
            margin-bottom: 4px;
            color: #e0e9f0;
            text-decoration: none;
            border-radius: 6px;
            font-size: 12px;
        }

        .nav-item-link:hover {
            color: white;
            background: #ffffff12;
        }

        .nav-item-link.active {
            background: var(--green);
            color: white;
            font-weight: bold;
        }

        .nav-icon {
            width: 19px;
            text-align: center;
            font-size: 15px;
        }

        .nav-sub {
            padding-left: 28px;
            border-left: 1px solid #ffffff25;
            margin-left: 22px;
        }

        .main {
            margin-left: 244px;
            min-height: 100vh;
        }

        .topbar {
            height: 54px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 22px;
            background: white;
            border-bottom: 1px solid var(--border);
        }

        .content {
            padding: 23px 20px;
        }

        .page-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .page-heading h1 {
            margin: 0 0 6px;
            font-size: 22px;
            font-weight: bold;
        }

        .breadcrumb-text {
            color: var(--muted);
            font-size: 11px;
        }

        .panel {
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 18px;
        }

        @media (max-width: 768px) {
            .sidebar { position: static; width: 100%; }
            .main { margin-left: 0; }
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="brand">
            <div class="brand-mark">⬟</div>
            <div>
                <strong>HSSE PROJECT</strong>
                <small>PT Geo Dipa Energi (Persero) – Unit Dieng</small>
            </div>
        </div>

        <nav>
            <a href="{{ route('dashboard') }}" class="nav-item-link">
                <span class="nav-icon">▦</span> Dashboard
            </a>
            <a href="{{ route('daily-work.index') }}" class="nav-item-link">
                <span class="nav-icon">▤</span> Daily Work
            </a>
            <a href="{{ route('hsse-plan.index') }}" class="nav-item-link">
                <span class="nav-icon">▣</span> HSSE Plan
            </a>
            <a href="#" class="nav-item-link"><span class="nav-icon">▧</span> Events</a>

            <a href="{{ route('inventory.index') }}" class="nav-item-link">
                <span class="nav-icon">▣</span> 7. Inventory
            </a>
            <a href="{{ route('inventory.transactions') }}" class="nav-item-link active ps-4">
                <i class="bi bi-clock-history"></i> Riwayat Mutasi
            </a>

            <div class="nav-heading">Data Monitoring</div>
            <div class="nav-sub">
                <a href="#" class="nav-item-link">◷ &nbsp; Man Hours</a>
                <a href="#" class="nav-item-link">♙ &nbsp; Workforce</a>
                <a href="#" class="nav-item-link">⚠ &nbsp; Incident</a>
                <a href="#" class="nav-item-link">☑ &nbsp; Issue & Follow Up</a>
            </div>

            <a href="#" class="nav-item-link mt-3">
                <span class="nav-icon">⚙</span> Manajemen Pengguna
            </a>
        </nav>
    </aside>

    <div class="main">
        <header class="topbar">
            <div class="fw-bold text-secondary">
                <i class="bi bi-arrow-left-right me-1"></i> Pencatatan & Riwayat Mutasi Barang
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end" style="font-size: 11px;">
                    <strong>Administrator</strong>
                    <div class="text-muted">Divisi HSSE</div>
                </div>
            </div>
        </header>

        <main class="content">
            <div class="page-heading">
                <div>
                    <h1><i class="bi bi-clock-history me-2"></i>Riwayat & Input Transaksi Barang</h1>
                    <div class="breadcrumb-text">Dashboard › Inventory › Riwayat Mutasi</div>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Master Barang
                    </a>
                    @canManageInventory
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTransaksi">
                            <i class="bi bi-plus-circle me-1"></i> Catat Transaksi Baru
                        </button>
                    @endcanManageInventory
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- FILTER TRANSAKSI -->
            <div class="panel mb-4">
                <form method="GET" action="{{ route('inventory.transactions') }}" class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold mb-1">Barang</label>
                        <select name="inventory_id" class="form-select form-select-sm">
                            <option value="">-- Semua Barang --</option>
                            @foreach($inventories as $inv)
                                <option value="{{ $inv->id }}" {{ request('inventory_id') == $inv->id ? 'selected' : '' }}>
                                    [{{ $inv->barang_id }}] {{ $inv->nama_barang }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold mb-1">Jenis Transaksi</label>
                        <select name="jenis_transaksi" class="form-select form-select-sm">
                            <option value="">-- Semua Jenis --</option>
                            <option value="masuk" {{ request('jenis_transaksi') == 'masuk' ? 'selected' : '' }}>Barang Masuk</option>
                            <option value="keluar" {{ request('jenis_transaksi') == 'keluar' ? 'selected' : '' }}>Barang Keluar</option>
                            <option value="penyesuaian" {{ request('jenis_transaksi') == 'penyesuaian' ? 'selected' : '' }}>Penyesuaian (Audit)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold mb-1">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 d-flex gap-2 align-self-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-filter"></i> Terapkan Filter
                        </button>
                        <a href="{{ route('inventory.transactions') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                            <i class="bi bi-arrow-clockwise"></i>
                        </a>
                    </div>
                </form>
            </div>

            <!-- TABEL RIWAYAT TRANSAKSI -->
            <div class="panel p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Tanggal</th>
                                <th>Barang Terkait</th>
                                <th class="text-center">Jenis Mutasi</th>
                                <th class="text-center">Jumlah</th>
                                <th>Petugas / PIC</th>
                                <th>No. Dokumen / Referensi</th>
                                <th>Lokasi Penempatan</th>
                                <th class="pe-3">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $trx)
                                @php
                                    $invItem = $trx->inventory ?? $trx->item;
                                    $jenisLower = strtolower(trim((string)($trx->jenis_transaksi ?? $trx->transaction_type ?? '')));
                                    $isMasuk = $jenisLower === 'masuk';
                                    $isKeluar = $jenisLower === 'keluar';
                                    $tglTrx = $trx->transaction_date ?? $trx->tanggal ?? $trx->created_at;
                                    $picName = $trx->petugas ?? $trx->person_in_charge ?? $trx->nama_pengisi ?? 'Petugas HSSE';
                                    $namaBarang = $invItem?->nama_barang ?? $invItem?->item_name;
                                    $kodeBarang = $invItem?->kode_barang ?? $invItem?->item_code ?? '-';
                                    $satuanBarang = $invItem?->satuan ?? $invItem?->unit_measure ?? 'Unit';
                                    $lokasiBarang = $invItem?->lokasi_default ?? $invItem?->location ?? 'Gudang HSSE';
                                    $qtyVal = (int) ($trx->jumlah ?? $trx->quantity ?? 0);
                                @endphp
                                <tr>
                                    <td class="ps-3 text-nowrap">
                                        <i class="bi bi-calendar3 text-muted me-1"></i>
                                        {{ $tglTrx ? \Carbon\Carbon::parse($tglTrx)->format('d/m/Y') : '-' }}
                                        <div class="text-muted" style="font-size: 10px;">
                                            <i class="bi bi-clock me-1"></i>{{ $trx->created_at ? $trx->created_at->format('H:i') : ($tglTrx ? \Carbon\Carbon::parse($tglTrx)->format('H:i') : '-') }} WIB
                                        </div>
                                    </td>
                                    <td>
                                        @if($namaBarang)
                                            <div class="fw-bold">{{ $namaBarang }}</div>
                                            <small class="badge bg-light text-secondary font-monospace">{{ $kodeBarang }}</small>
                                        @else
                                            <div class="fw-bold text-secondary fst-italic">Master Barang Dihapus</div>
                                            <small class="badge bg-light text-muted font-monospace">ID: #{{ $trx->inventory_item_id }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($isMasuk)
                                            <span class="badge bg-success"><i class="bi bi-box-arrow-in-down me-1"></i> Masuk</span>
                                        @elseif($isKeluar)
                                            <span class="badge bg-danger"><i class="bi bi-box-arrow-up me-1"></i> Keluar</span>
                                        @else
                                            <span class="badge bg-warning text-dark"><i class="bi bi-sliders me-1"></i> Penyesuaian</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold">
                                        @if($isMasuk)
                                            <span class="text-success">+{{ number_format($qtyVal) }}</span>
                                        @elseif($isKeluar)
                                            <span class="text-danger">-{{ number_format($qtyVal) }}</span>
                                        @else
                                            <span class="text-warning text-dark">{{ $qtyVal >= 0 ? '+'.$qtyVal : $qtyVal }}</span>
                                        @endif
                                        <small class="text-muted fw-normal">{{ $satuanBarang }}</small>
                                    </td>
                                    <td>
                                        <i class="bi bi-person text-muted me-1"></i>{{ $picName }}
                                    </td>
                                    <td>
                                        <span class="font-monospace text-muted">{{ $trx->reference_number ?? $trx->nomor_dokumen ?? '-' }}</span>
                                    </td>
                                    <td>{{ $lokasiBarang }}</td>
                                    <td class="pe-3"><small class="text-muted">{{ $trx->keterangan ?? $trx->notes ?? '-' }}</small></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-clock-history fs-2 d-block mb-2"></i>
                                        Belum ada riwayat mutasi transaksi barang.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-top">
                    {{ $transactions->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </main>
    </div>

    @canManageInventory
    <!-- MODAL CATAT TRANSAKSI -->
    <div class="modal fade" id="modalTransaksi" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('inventory.transactions.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">Catat Transaksi Logistik</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Barang</label>
                        <select name="inventory_id" class="form-select form-select-sm" required>
                            <option value="">-- Pilih Barang dari Master --</option>
                            @foreach($inventories as $inv)
                                <option value="{{ $inv->id }}">
                                    [{{ $inv->barang_id }}] {{ $inv->nama_barang }} (Sisa Stok: {{ $inv->current_stock }} {{ $inv->satuan }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col">
                            <label class="form-label small fw-bold">Jenis Mutasi</label>
                            <select name="jenis_transaksi" class="form-select form-select-sm" required>
                                <option value="masuk">Barang Masuk (Pengadaan / Retur)</option>
                                <option value="keluar">Barang Keluar (Distribusi / Pakai)</option>
                                <option value="penyesuaian">Penyesuaian (Hasil Stock Opname)</option>
                            </select>
                        </div>
                        <div class="col">
                            <label class="form-label small fw-bold">Jumlah</label>
                            <input type="number" name="jumlah" class="form-control form-control-sm" min="1" placeholder="1" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Nama Petugas / PIC</label>
                        <input type="text" name="nama_pengisi" class="form-control form-control-sm" placeholder="Nama yang mencatat atau penerima" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col">
                            <label class="form-label small fw-bold">Nomor Dokumen (PO / BAST / SPB)</label>
                            <input type="text" name="nomor_dokumen" class="form-control form-control-sm" placeholder="Contoh: BAST-2026/X/01">
                        </div>
                        <div class="col">
                            <label class="form-label small fw-bold">Tanggal Transaksi</label>
                            <input type="date" name="tanggal_transaksi" value="{{ date('Y-m-d') }}" class="form-control form-control-sm" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Lokasi Barang / Penempatan</label>
                        <input type="text" name="lokasi_barang" class="form-control form-control-sm" placeholder="Contoh: Dieng Power Plant Unit 1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Keterangan / Keperluan</label>
                        <textarea name="keterangan" class="form-control form-control-sm" rows="2" placeholder="Keperluan pemakaian, vendor pengadaan, dll..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>
    @endcanManageInventory

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

