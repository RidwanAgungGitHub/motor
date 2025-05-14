<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangKeluar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BarangKeluarController extends Controller
{
    public function index()
    {
        $barangKeluar = BarangKeluar::with('barang')->latest()->get();
        return view('barang_keluar.index', compact('barangKeluar'));
    }

    public function create()
    {
        $barang = Barang::where('stok', '>', 0)->get();
        return view('barang_keluar.create', compact('barang'));
    }

    public function getBarang(Request $request)
    {
        $search = $request->search;

        if ($search == '') {
            $barang = Barang::where('stok', '>', 0)->limit(5)->get();
        } else {
            $barang = Barang::where('stok', '>', 0)
                ->where(function ($query) use ($search) {
                    $query->where('nama_barang', 'like', '%' . $search . '%')
                        ->orWhere('merek', 'like', '%' . $search . '%');
                })
                ->limit(5)
                ->get();
        }

        $response = array();
        foreach ($barang as $item) {
            $response[] = array(
                'id' => $item->id,
                'text' => $item->nama_barang . ' - ' . $item->merek,
                'nama_barang' => $item->nama_barang,
                'merek' => $item->merek,
                'harga' => $item->harga,
                'stok' => $item->stok,
                'satuan' => $item->satuan
            );
        }

        return response()->json($response);
    }

    /**
     * Search for products in the database (for Select2 in the cashier interface)
     */
    public function searchProduct(Request $request)
    {
        $search = $request->get('q');

        $barang = Barang::where('stok', '>', 0)
            ->where(function ($query) use ($search) {
                $query->where('nama_barang', 'like', '%' . $search . '%')
                    ->orWhere('merek', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            })
            ->limit(10)
            ->get();

        $results = [];
        foreach ($barang as $item) {
            $results[] = [
                'id' => $item->id,
                'text' => $item->nama_barang . ' - ' . $item->merek . ' (Stok: ' . $item->stok . ')',
                'product' => [
                    'id' => $item->id,
                    'kode' => $item->id, // Using ID as code if you don't have a specific code field
                    'nama_barang' => $item->nama_barang,
                    'merek' => $item->merek,
                    'harga_jual' => $item->harga,
                    'stok' => $item->stok,
                    'satuan' => $item->satuan
                ]
            ];
        }

        return response()->json($results);
    }

    public function store(Request $request)
    {
        $request->validate([
            'barang_id' => 'required|exists:barang,id',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date',
        ]);

        // Mulai transaksi DB untuk memastikan konsistensi data
        DB::beginTransaction();

        try {
            $barang = Barang::findOrFail($request->barang_id);

            // Validasi stok cukup
            if ($barang->stok < $request->jumlah) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['jumlah' => 'Stok tidak mencukupi. Stok tersedia: ' . $barang->stok]);
            }

            // Hitung total harga
            $totalHarga = $barang->harga * $request->jumlah;

            // Simpan barang keluar
            BarangKeluar::create([
                'barang_id' => $request->barang_id,
                'jumlah' => $request->jumlah,
                'tanggal' => $request->tanggal,
                'total_harga' => $totalHarga,
                'keterangan' => $request->keterangan ?? null,
            ]);

            // Update stok barang
            $barang->stok -= $request->jumlah;
            $barang->save();

            DB::commit();

            return redirect()->route('barang-keluar.index')
                ->with('success', 'Barang keluar berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function show(BarangKeluar $barangKeluar)
    {
        $barangKeluar->load('barang');
        return view('barang_keluar.show', compact('barangKeluar'));
    }

    public function kasir()
    {
        return view('barang_keluar.kasir');
    }

    public function prosesTransaksi(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barang,id',
            'items.*.jumlah' => 'required|integer|min:1',
        ]);

        $tanggal = now();
        $items = $request->items;
        $totalTransaksi = 0;

        DB::beginTransaction();

        try {
            foreach ($items as $item) {
                $barang = Barang::findOrFail($item['barang_id']);

                // Validasi stok cukup
                if ($barang->stok < $item['jumlah']) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Stok ' . $barang->nama_barang . ' tidak mencukupi. Stok tersedia: ' . $barang->stok
                    ], 422);
                }

                // Hitung total harga
                $totalHarga = $barang->harga * $item['jumlah'];
                $totalTransaksi += $totalHarga;

                // Simpan barang keluar
                BarangKeluar::create([
                    'barang_id' => $item['barang_id'],
                    'jumlah' => $item['jumlah'],
                    'tanggal' => $tanggal,
                    'total_harga' => $totalHarga,
                    'keterangan' => $item['keterangan'] ?? null,
                ]);

                // Update stok barang
                $barang->stok -= $item['jumlah'];
                $barang->save();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil',
                'total' => $totalTransaksi,
                'tanggal' => $tanggal->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save transaction from cashier interface
     */
    public function saveTransaction(Request $request)
    {
        $request->validate([
            'transaction' => 'required|array',
            'transaction.customer_name' => 'nullable|string',
            'transaction.total_amount' => 'required|numeric',
            'transaction.payment_amount' => 'required|numeric',
            'transaction.items' => 'required|array',
        ]);

        $transaction = $request->transaction;

        // Generate invoice number
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . Str::random(5);

        // Begin transaction to ensure all operations succeed or fail together
        DB::beginTransaction();

        try {
            foreach ($transaction['items'] as $item) {
                $barang = Barang::findOrFail($item['id']);

                // Ensure stock is sufficient
                if ($barang->stok < $item['quantity']) {
                    throw new \Exception("Stok {$barang->nama_barang} tidak mencukupi");
                }

                // Create barang keluar record
                BarangKeluar::create([
                    'barang_id' => $item['id'],
                    'jumlah' => $item['quantity'],
                    'tanggal' => $transaction['tanggal'] ?? now(),
                    'total_harga' => $item['subtotal'],
                    'keterangan' => 'Penjualan kasir - ' . $invoiceNumber .
                        ($transaction['customer_name'] ? ' - Pelanggan: ' . $transaction['customer_name'] : ''),
                ]);

                // Update stock
                $barang->stok -= $item['quantity'];
                $barang->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan',
                'invoice_number' => $invoiceNumber
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 422);
        }
    }
}
