@extends('layouts.app')

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            height: 38px;
            line-height: 38px;
            padding: 0.375rem 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
    </style>
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Tambah Barang Keluar</h5>
                    </div>

                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('barang-keluar.store') }}" method="POST">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="barang_id">Pilih Barang:</label>
                                <select name="barang_id" id="barang_id" class="form-control barang-select" required>
                                    <option value="">-- Pilih Barang --</option>
                                </select>
                            </div>

                            <div class="card mb-3">
                                <div class="card-body p-3 bg-light">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Nama:</strong> <span id="nama_barang">-</span></p>
                                            <p class="mb-1"><strong>Merek:</strong> <span id="merek">-</span></p>
                                            <p class="mb-1"><strong>Harga:</strong> <span id="harga">-</span></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Satuan:</strong> <span id="satuan">-</span></p>
                                            <p class="mb-1"><strong>Stok Tersedia:</strong> <span id="stok">-</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="jumlah">Jumlah:</label>
                                <input type="number" name="jumlah" id="jumlah" class="form-control" min="1"
                                    required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="tanggal">Tanggal:</label>
                                <input type="date" name="tanggal" id="tanggal" class="form-control"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="keterangan">Keterangan (Opsional):</label>
                                <textarea name="keterangan" id="keterangan" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="form-group mb-4">
                                <label for="total_harga">Total Harga:</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="text" id="total_harga" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('barang-keluar.index') }}" class="btn btn-secondary">Kembali</a>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.barang-select').select2({
                placeholder: 'Cari barang...',
                minimumInputLength: 1,
                ajax: {
                    url: '{{ route('get-barang') }}',
                    dataType: 'json',
                    delay: 250,
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                }
            });

            $('.barang-select').on('change', function() {
                var data = $(this).select2('data')[0];
                if (data) {
                    $('#nama_barang').text(data.nama_barang);
                    $('#merek').text(data.merek);
                    $('#harga').text('Rp ' + formatNumber(data.harga));
                    $('#satuan').text(data.satuan);
                    $('#stok').text(data.stok);

                    // Atur max jumlah sesuai stok
                    $('#jumlah').attr('max', data.stok);

                    // Hitung total
                    calculateTotal();
                }
            });

            $('#jumlah').on('input', function() {
                calculateTotal();
            });

            function calculateTotal() {
                var data = $('.barang-select').select2('data')[0];
                var jumlah = $('#jumlah').val();

                if (data && jumlah > 0) {
                    var total = data.harga * jumlah;
                    $('#total_harga').val(formatNumber(total));
                } else {
                    $('#total_harga').val('0');
                }
            }

            function formatNumber(num) {
                return new Intl.NumberFormat('id-ID').format(num);
            }
        });
    </script>
@endsection
