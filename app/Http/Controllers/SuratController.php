<?php

namespace App\Http\Controllers;

use App\Models\Surat;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class SuratController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth:api',['except'=>['login', 'register']]);
    }

    public function uploadSurat(Request $request)
    {
        $this->validate($request,[
            'name' => 'required|max:255',
            'path' => 'required|max:5000|mimes:pdf,jpg,png,jpeg'
        ]);

        $surat = new Surat();

        // image upload
        if($request->hasFile('path')) {

        $allowedfileExtension=['pdf','jpg','png','jpeg'];
        $file = $request->file('path');
        $extenstion = $file->getClientOriginalExtension();
        $check = in_array($extenstion, $allowedfileExtension);

        if($check){
            $name = time() . $file->getClientOriginalName();
            $file->move('images', $name);
            $surat->path = $name;
        }
        }

        $surat->name = $request->input('name');
        $surat->user_id = Auth::user()->id;
        $surat->save();
        if ($surat) {
            return response()->json([
                'success' => true,
                'message' => 'Upload Succes',
                'data' => $surat
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'upload Failed',
                'data' => ''
            ], 400);
        }
    }

    public function showAllSurat()
    {
        $surats = Surat::all();

        if ($surats->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No surat found.',
                'data' => []
            ], 404);
        } else {
            return response()->json([
                'success' => true,
                'message' => 'Surat found.',
                'data' => $surats
            ], 200);
        }
    }

    public function updateSurat(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'path' => 'max:5000|mimes:pdf,jpg,png'
        ]);

        $surat = Surat::findOrFail($id);

        // Jika ada file yang diunggah, proses pembaruan path
        if ($request->hasFile('path')) {
            $allowedfileExtension = ['pdf', 'jpg', 'png', 'jpeg'];
            $file = $request->file('path');
            $extension = $file->getClientOriginalExtension();
            $check = in_array($extension, $allowedfileExtension);

            if ($check) {
                $name = time() . $file->getClientOriginalName();
                $file->move('images', $name);
                $surat->path = $name;
            }
        }

        // Perbarui nama surat
        $surat->name = $request->input('name');
        $surat->save();
        if ($surat) {
            return response()->json([
                'success' => true,
                'message' => 'Surat updated successfully.',
                'data' => $surat
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update surat.',
                'data' => ''
            ], 400);
        }
    }

    public function searchSurat(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
        ]);

        $name = $request->input('name');

        $surat = Surat::where('name', 'like', "%$name%")->get();

        if ($surat->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No surat found with the given name.',
                'data' => []
            ], 404);
        } else {
            return response()->json([
                'success' => true,
                'message' => 'Surat found.',
                'data' => $surat
            ], 200);
        }
    }

    public function deleteSurat(Request $request, $id)
{
    $surat = Surat::findOrFail($id);
    // Hapus file terkait jika ada
    if (!empty($surat->path)) {
        Storage::delete('images/' . $surat->path);
    }

    $surat->delete();
    return response()->json([
        'success' => true,
        'message' => 'Surat deleted successfully.',
        'data' => $surat
    ], 200);
}
}
