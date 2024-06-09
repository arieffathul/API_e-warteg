<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Makanan;
use Exception;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $response = $this->default_response;

        // data cart dengan transaction id null
        $carts = Cart::where('pembeli_id', $request->user()->id)
            ->whereNull('transaction_id')
            ->with('makanan.category')
            ->get();

        $response['success'] = true;
        $response['data'] = $carts;

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
        // validasi makanan tersedia
        $request->validate([
            'makanan_id' => 'required|exists:makanans,id',
            'qty' => 'nullable|integer|min:1',
        ]);

        // validasi qty tak lebih dari stock (bila ada)
        $makanan = Makanan::find($request->makanan_id);

        if (isset($makanan->stock) && $makanan->stock < $request->qty) {
            $response['success'] = false;
            $response['message'] = 'Stock tidak mencukupi';
            return response()->json($response);
        }

        // simpan cart
        $cart = Cart::where('makanan_id', $request->makanan_id)
            ->where('pembeli_id', $request->user()->id)
            ->whereNull('transaction_id')
            ->first();

        if ($cart) {
            $cart->qty += $request->qty;
            $cart->harga = $makanan->price;
            $cart->total = $cart->harga * $cart->qty;
            $cart->save();
        } else {
            $cart = new Cart();
            $cart->pembeli_id = $request->user()->id;
            $cart->makanan_id = $request->makanan_id;
            $cart->qty = $request->qty;
            $cart->harga = $makanan->price;
            $cart->total = $cart->harga * $cart->qty;
            $cart->save();
        }

        $cart->load('makanan');

        $response['success'] = true;
        $response['message'] = 'Makanan berhasil dimasukkan ke keranjang';
        $response['data'] = $cart;
        return response()->json($response);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $response = $this->default_response;

        try{
            $cart = Cart::with(['makanan', 'user'])->find($id);

            $response['success'] = true;
            $response['data'] = $cart;
        } catch(Exception $e){
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
        $response = $this->default_response;

        // validasi makanan id ada di table makanan
        $request->validate([
            'qty' => 'required|numeric|min:1',
        ]);

        // validasi qty tak lebih dari stok
        $cart = Cart::where('pembeli_id', $request->user()->id)
            ->whereNull('transaction_id')
            ->with('makanan')
            ->find($id);

        if (empty($cart)) {
            $response['success'] = false;
            $response['message'] = 'Keranjang tidak ditemukan';
            return response()->json($response);
        }

        if (isset($cart->makanan->stock) && $request->qty > $cart->makanan->stock) {
            $response['success'] = false;
            $response['message'] = 'Stok tidak cukup';
            return response()->json($response);
        }

        // simpan ke table cart
        $cart = Cart::where('pembeli_id', $request->user()->id)
            ->whereNull('transaction_id')
            ->find($id);

        $cart->harga = $cart->makanan->price;
        $cart->qty = $request->qty;
        $cart->total = $cart->harga * $cart->qty;
        $cart->save();


        $response['success'] = true;
        $response['message'] = 'Cart berhasil diubah';
        $response['data'] = $cart;
        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $response = $this->default_response;

        $cart = Cart::where('pembeli_id', $request->user()->id)
            ->whereNull('transaction_id')
            ->find($id);

        if (empty($cart)) {
            $response['success'] = false;
            $response['message'] = 'Keranjang tidak ditemukan';
            return response()->json($response);
        }

        $cart->delete();
        $response['success'] = true;
        $response['message'] = 'Keranjang berhasil dihapus';
        return response()->json($response);
    }
}
