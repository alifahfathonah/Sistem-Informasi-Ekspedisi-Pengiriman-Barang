<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Pengiriman;
use App\StatusPengiriman;
use App\DetailStatusPengiriman;
use Auth;
use DB;

class KurirStatusPengirimanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('kurir.status_pengiriman.index');
    }

    
    public function createStatusBarang()
    {
        $id_pengiriman = Input::get('id_pengiriman');
        $status_pengiriman = StatusPengiriman::with('pengiriman.kecamatan_penerima.kota')
            ->where('status_pengiriman.id_pengiriman', $id_pengiriman)
            ->where('status_pengiriman.status', 0)->first();
        if ($status_pengiriman) {
            return response()->json([
            'pesan' =>  'data json berhasil didapatkan',
            'kode' => 1,
            'data' => $status_pengiriman
            ]);
        }
        return response()->json([
            'pesan' => 'data json tidak ada',
            'kode' => 0,
            'data' => $status_pengiriman
            ]);
    }
    
    public function store(Request $request)
    {
        $nama_penerima = $request->input('nama_penerima');
        $id_pengiriman = $request->input('id_pengiriman');

        $status_pengiriman = StatusPengiriman::where('id_pengiriman', $id_pengiriman)->first();
        $status_pengiriman->update([
            'status' => true
        ]);
        
        $id_status_pengiriman = $status_pengiriman->id;

        // menyimpan ke tabel detail_status_pengiriman
        $detail_status = new DetailStatusPengiriman ();
        $detail_status->id_status_pengiriman = $id_status_pengiriman;
        $detail_status->keterangan = "Barang diterima oleh pelanggan" . ", " . $nama_penerima . " [" . Auth::user()->nama . "]";
        $detail_status->id_user = Auth::user()->id;
        $detail_status->save();


        return redirect()->back()->with('alert', 'Data berhasil diterima!');
    }

    public function dataTable()
    {
        $pengiriman = Pengiriman::with('kecamatan_penerima.kota', 'status_pengiriman.detail_status_pengiriman')->select('pengiriman.*');
        return datatables()->eloquent($pengiriman)->make(true);
    }

}