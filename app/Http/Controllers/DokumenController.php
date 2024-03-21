<?php

namespace App\Http\Controllers;

require_once 'D:\MBKM BATCH 6 DISPENDUKCAPIL MALANG\testjwt\vendor\imagekit\imagekit\src\ImageKit\ImageKit.php';

use Imagekit\Imagekit;
use App\Models\Dokumen;
use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;



class DokumenController extends Controller
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

    public function uploadDokumen(Request $request)
    {
        $this->validate($request,[
            'jenis_dokumen' => 'required|max:255',
            'no_dokumen' => 'required|max:255',
            'nama' => 'required|max:255',
            'file_dokumen' => 'required|max:5000|mimes:jpg,png,jpeg'
        ]);

        $dokumen = new Dokumen();

        // image upload
        if ($request->hasFile('file_dokumen')) {
            $file = $request->file('file_dokumen');

            $publicKey = "public_snWP+TjEVEFfh9Xah5yyd0CUbmw=";
            $privateKey = "private_Yvrn1tktDyOieGLVW0WlWMNdpZk=";
            $urlEndpoint = "https://ik.imagekit.io/binarthufail";

            // Inisialisasi koneksi dengan ImageKit.io
            $imageKit = new ImageKit(
                $publicKey,
                $privateKey,
                $urlEndpoint
            );

            // Konfigurasi untuk upload file
            $uploadResponse = $imageKit->upload(
                array(
                    'file' => $file->getPathname(),
                    'fileName' => time() . $file->getClientOriginalName(),
                    //'tags' => ['dokumen'] // Tag opsional untuk mengelompokkan file
                )
            );

            $success = false;
            $success = !isset($uploadResponse->error);

            if ($success) {
                $dokumen->file_dokumen = $uploadResponse->result->url;
            } else {
                $error = $uploadResponse->error;
            }
        }

        $dokumen->jenis_dokumen = $request->input('jenis_dokumen');
        $dokumen->no_dokumen = $request->input('no_dokumen');
        $dokumen->nama = $request->input('nama');
        // $dokumen->user_id = Auth::user()->id;
        $dokumen->save();
        if ($dokumen) {
            return response()->json([
                'success' => true,
                'message' => 'Upload Succes',
                'data' => $dokumen
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'upload Failed',
                'data' => ''
            ], 400);
        }
    }

    public function showAlldokumen()
    {
        $dokumen = Dokumen::all();

        if ($dokumen->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No dokumen found.',
                'data' => []
            ], 404);
        } else {
            return response()->json([
                'success' => true,
                'message' => 'dokumen found.',
                'data' => $dokumen
            ], 200);
        }
    }
}
