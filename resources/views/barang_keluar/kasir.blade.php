@extends('layouts.app')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @include('barang_keluar.kasir_styles')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Kolom Kiri - Area Pencarian dan Detail Barang -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-cash-register"></i> Kasir - Transaksi Barang Keluar</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="barang_search">Cari Barang:</label>
                                    <select id="barang_search" class="form-control barang-select">
                                        <option value="">-- Pilih Barang --</option>
                                        @foreach ($data as $item )
                                            <option value="{{ $item->id }}">{{ $item->nama_barang }} - {{ $item->merek }} - {{ $item->harga }}</option>

                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mt-4">
                                    <button id="btn-add-to-cart" class="btn btn-success btn-block mt-2">
                                        <i class="fas fa-plus"></i> Tambah ke Keranjang
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Barang Terpilih -->
                        <div id="selected-product-info" class="mb-4" style="display: none;">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">Informasi Barang Terpilih</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Kode:</strong> <span id="selected-kode"></span></p>
                                            <p><strong>Nama:</strong> <span id="selected-nama"></span></p>
                                            <p><strong>Harga:</strong> Rp. <span id="selected-harga"></span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Stok Tersedia:</strong> <span id="selected-stok"></span></p>
                                            <div class="form-group">
                                                <label for="jumlah">Jumlah:</label>
                                                <input type="number" id="jumlah" class="form-control" min="1"
                                                    value="1">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Daftar Barang dalam Keranjang -->
                        <div class="card">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0">Daftar Barang dalam Keranjang</h6>
                            </div>
                            <div class="card-body product-list">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover table-fixed" id="cart-table">
                                        <thead>
                                            <tr>
                                                <th width="5%">#</th>
                                                <th width="15%">Kode</th>
                                                <th width="25%">Nama Barang</th>
                                                <th width="15%">Harga</th>
                                                <th width="10%">Jumlah</th>
                                                <th width="15%">Subtotal</th>
                                                <th width="15%">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="cart-items">
                                            <!-- Data keranjang akan ditambahkan secara dinamis -->
                                        </tbody>
                                    </table>
                                </div>
                                <div id="empty-cart-message" class="text-center py-3">
                                    <i class="fas fa-shopping-cart fa-3x text-muted"></i>
                                    <p class="mt-2 text-muted">Keranjang belanja masih kosong</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan - Ringkasan Belanja -->
            <div class="col-md-4">
                <div class="card cart-summary mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-shopping-cart"></i> Ringkasan Belanja</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="tanggal_transaksi">Tanggal Transaksi:</label>
                            <input type="date" id="tanggal_transaksi" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label for="customer_name">Nama Pelanggan:</label>
                            <input type="text" id="customer_name" class="form-control"
                                placeholder="Masukkan nama pelanggan">
                        </div>

                        <hr>

                        <div class="row mb-2">
                            <div class="col-7"><strong>Total Item:</strong></div>
                            <div class="col-5 text-right"><span id="total-items">0</span> item</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-7"><strong>Total Belanja:</strong></div>
                            <div class="col-5 text-right">Rp. <span id="total-belanja">0</span></div>
                        </div>
                        <div class="form-group">
                            <label for="total_bayar">Total Bayar:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp.</span>
                                </div>
                                <input type="number" id="total_bayar" class="form-control" placeholder="0">
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-7"><strong>Kembalian:</strong></div>
                            <div class="col-5 text-right">Rp. <span id="kembalian">0</span></div>
                        </div>

                        <hr>

                        <div class="d-grid gap-2">
                            <button id="btn-process-payment" class="btn btn-lg btn-primary btn-block mb-2" disabled>
                                <i class="fas fa-money-bill-wave"></i> Proses Pembayaran
                            </button>
                            <button id="btn-reset-transaction" class="btn btn-warning btn-block">
                                <i class="fas fa-redo"></i> Reset Transaksi
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Struk -->
    <div class="modal fade" id="receiptModal" tabindex="-1" role="dialog" aria-labelledby="receiptModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="receiptModalLabel">Struk Pembayaran</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="printArea" class="receipt">
                        <div class="receipt-header">
                            <h4>TOKO BAROKAH</h4>
                            <p>Jl. Contoh No. 123, Kota</p>
                            <p>Telp: 0812-3456-7890</p>
                            <hr>
                        </div>
                        <div class="receipt-content">
                            <p>No. Struk: <span id="receipt-invoice"></span></p>
                            <p>Tanggal: <span id="receipt-date"></span></p>
                            <p>Kasir: <span id="receipt-cashier">{{ Auth::user()->name }}</span></p>
                            <p>Pelanggan: <span id="receipt-customer">-</span></p>
                            <hr>
                            <div id="receipt-items">
                                <!-- Item akan ditambahkan secara dinamis -->
                            </div>
                            <div class="receipt-totals">
                                <p>Total Item: <span id="receipt-total-items"></span></p>
                                <p>Total Belanja: Rp. <span id="receipt-total-amount"></span></p>
                                <p>Tunai: Rp. <span id="receipt-cash"></span></p>
                                <p>Kembalian: Rp. <span id="receipt-change"></span></p>
                            </div>
                        </div>
                        <div class="receipt-footer">
                            <p>Terima kasih atas kunjungan Anda!</p>
                            <p>Barang yang sudah dibeli tidak dapat ditukar kembali</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer no-print">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="button" id="btn-print-receipt" class="btn btn-primary">
                        <i class="fas fa-print"></i> Cetak
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Reset -->
    <div class="modal fade" id="resetModal" tabindex="-1" role="dialog" aria-labelledby="resetModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="resetModalLabel">Konfirmasi Reset Transaksi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin mereset transaksi ini? Semua data yang sudah diinput akan dihapus.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" id="btn-confirm-reset" class="btn btn-warning">Ya, Reset</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @include('barang_keluar.kasir_scripts')
@endsection
