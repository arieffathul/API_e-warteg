<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $response = $this->default_response;

        $transactions = Transaction::where('pembeli_id', auth()->user()->id)
            ->with('carts') // menggunakan eager loading untuk memuat cart
            ->get();

        $response['success'] = true;
        $response['data'] = $transactions;

        return response()->json($response);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $response = $this->default_response;

        $request->validate([
            'cart_id' => 'required|exists:carts,id',
            'metode_bayar' => 'required|in:Tunai,Kasbon,DANA',
            'tujuan' => 'required',
        ]);

        DB::beginTransaction();
        $cartsQuery = Cart::where('pembeli_id', auth()->user()->id)
            ->whereNull('transaction_id')
            ->whereIn('id', $request->cart_id)
            ->with('makanan');

        $carts = $cartsQuery->get();

        // customer tidak memiliki cart
        if ($carts->count() == 0) {
            $response['message'] = 'Keranjang tidak ditemukan';
            $response['success'] = false;
            return response()->json($response, 404);
        }

        // Validasi Stock

        // total harga produk

        $total_bayar = 0;

        foreach ($carts as $cart) {
            if (isset($cart->makanan->stock) && $cart->makanan->stock < $cart->qty) {
                $response['message'] = 'Stock tidak cukup yang dipesan:' . ($cart->qty) . ', stok tersedia' . ($cart->makanan->stock) . '.(' . $cart->makanan->name . ')';
                $response['success'] = false;
                return response()->json($response, 404);
            }

            $total_bayar += $cart->total;
        }

        // kurangi stock makanan

        foreach ($carts as $cart) {
            if (isset($cart->makanan->stock)){
                $cart->makanan->stock -= $cart->qty;
                // $cart->makanan->decrement('stock', $cart->qty); //Tanpa save
    
                $cart->makanan->save();
            }
        }


        // simpan data transaction
        $transaction = new Transaction();
        $transaction->pembeli_id = auth()->user()->id;
        $transaction->total_bayar = $total_bayar;
        $transaction->metode_bayar = $request->metode_bayar;
        $transaction->tujuan = $request->tujuan;
        $transaction->save();

        // update data cart
        $cartsQuery->update([
            'transaction_id' => $transaction->id
        ]);

        DB::commit();

        $response['data'] = $transaction;
        $response['success'] = true;
        $response['message'] = 'transaction success';
        return response()->json($response, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $response = $this->default_response;

        try {
            $transaction = Transaction::with('carts')->find($id); // menggunakan eager loading untuk memuat cart

            $response['success'] = true;
            $response['data'] = $transaction;
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $response = $this->default_response;

        $transaction = Transaction::where('pembeli_id', $request->user()->id)
            ->find($id);

        if (empty($transaction)) {
            $response['success'] = false;
            $response['message'] = 'Transaksi tidak ditemukan';
            return response()->json($response);
        }

        $transaction->delete();
        $response['success'] = true;
        $response['message'] = 'Transaksi berhasil dihapus';
        return response()->json($response);
    }
}
