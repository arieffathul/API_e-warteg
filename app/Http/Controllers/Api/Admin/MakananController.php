<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMakananRequest;
use App\Http\Requests\UpdateMakananRequest;
use App\Models\Makanan;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MakananController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $response = $this->default_response;

        try {
            $makanans = Makanan::with('category')->get();

            $response['success'] = true;
            $response['data'] = [
                'makanan' => $makanans
            ];
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

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
    public function store(StoreMakananRequest $request)
    {
        $response = $this->default_response;

        try {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $path = $file->storeAs('gambar', $file->hashName(), 'public');
            }

            $makanan = new Makanan();
            $makanan->name = $data['name'];
            $makanan->image = $path ?? null;
            $makanan->description = $data['description'];
            $makanan->price = $data['price'];
            $makanan->stock = $data['stock'];
            $makanan->category_id = $data['category_id'];
            $makanan->save();

            $response['success'] = true;
            $response['message'] = 'Makanan Berhasil Ditambahkan';
            $response['data'] = [
                'makanan' => $makanan->with('category')->find($makanan->id)
            ];
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return response()->json($response);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $response = $this->default_response;

        try {
            $makanan = Makanan::with('category')->find($id);

            $response['success'] = true;
            $response['data'] = [
                'makanan' => $makanan
            ];
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
    public function update(UpdateMakananRequest $request, string $id)
    {
        $response = $this->default_response;

        try {
            $data = $request->validated();

            // Cek apakah ada file gambar yang diunggah
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $path = $file->storeAs('gambar', $file->hashName(), 'public');
            }

            // Temukan data makanan berdasarkan ID
            $makanan = Makanan::find($id);
            if (!$makanan) {
                throw new Exception("Makanan not found");
            }

            // Perbarui data makanan
            $makanan->name = $data['name'];
            if($request->hasFile('image')) {
                if($makanan->image) Storage::disk('public')->delete($makanan->image);
                $makanan->image = $path ?? $makanan->image; // Gunakan gambar lama jika tidak ada gambar baru yang diunggah
            }
            $makanan->description = $data['description'];
            $makanan->price = $data['price'];
            $makanan->stock = $data['stock'];
            $makanan->category_id = $data['category_id'];
            $makanan->save();

            // Siapkan respons
            $response['success'] = true;
            $response['data'] = [
                'makanan' => $makanan->with('category')->find($makanan->id)
            ];
        } catch (Exception $e) {
            // Tangani kesalahan dengan lebih baik
            $response['message'] = $e->getMessage();
        }

        return response()->json($response);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $response = $this->default_response;

        try {
            $makanan = Makanan::find($id);

            if ($makanan->image) {
                Storage::disk('public')->delete($makanan->image);
            }
            $makanan->delete();

            $response['success'] = true;
            $response['message'] = 'Makanan Berhasil Dihapus';
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        return response()->json($response);
    }
}
