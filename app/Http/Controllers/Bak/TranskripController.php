<?php

namespace App\Http\Controllers\Bak;

use App\Http\Controllers\Controller;
use App\Models\Connection\CourseUsept;
use App\Models\Connection\Usept;
use App\Models\Mahasiswa\RiwayatPendidikan;
use App\Models\Perkuliahan\AktivitasKuliahMahasiswa;
use App\Models\Perkuliahan\ListKurikulum;
use App\Models\Perkuliahan\TranskripMahasiswa;
use App\Models\Perpus\BebasPustaka;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class TranskripController extends Controller
{
    public function index()
    {
        // check job batch queue with name transkrip-mahasiswa
        // if not exist, create new job batch queue
        $jobData =  DB::table('job_batches')->where('name', 'transkrip-mahasiswa')->where('pending_jobs', '>', 0)->first();

        $statusSync = $jobData ? 1 : 0;

        $id_batch = $jobData ? $jobData->id : null;

        return view('bak.transkrip.index', [
            'statusSync' => $statusSync,
            'id_batch' => $id_batch,
        ]);
    }

    public function data(Request $request)
    {
        $request->validate([
            'nim' => 'required',
        ]);

        $riwayat = RiwayatPendidikan::with(['prodi.fakultas', 'prodi.jurusan', 'pembimbing_akademik'])->where('nim', $request->nim)->orderBy('id_periode_masuk', 'desc')->first();

        if(!$riwayat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data Mahasiswa tidak ditemukan!!',
            ]);
        }

        $nilai_usept_prodi = ListKurikulum::where('id_kurikulum', $riwayat->id_kurikulum)->first();

        try {
            //code...
            $nilai_usept_mhs = Usept::whereIn('nim', [$riwayat->nim, $riwayat->biodata->nik])->pluck('score');
            $nilai_course = CourseUsept::whereIn('nim', [$riwayat->nim, $riwayat->biodata->nik])->get()->pluck('konversi');

            $all_scores = $nilai_usept_mhs->merge($nilai_course);
            $usept = $all_scores->max();

            $useptData = [
                'score' => $usept,
                'class' => $usept < $nilai_usept_prodi->nilai_usept ? 'danger' : 'success',
                'status' => $usept < $nilai_usept_prodi->nilai_usept ? 'Tidak memenuhi Syarat' : 'Memenuhi Syarat',
            ];

        } catch (\Throwable $th) {
            //throw $th;

            $useptData = [
                'score' => 0,
                'class' => 'danger',
                'status' => 'Database USEPT tidak bisa diakses, silahkan hubungi pengelola USEPT.',
            ];
        }

        $transkrip = TranskripMahasiswa::where('id_registrasi_mahasiswa', $riwayat->id_registrasi_mahasiswa)->get();

        $total_sks = $transkrip->sum('sks_mata_kuliah');
        $total_indeks = $transkrip->sum('nilai_indeks');

        $ipk = ($total_sks * $total_indeks) / $total_sks;

        $akm = AktivitasKuliahMahasiswa::where('id_registrasi_mahasiswa', $riwayat->id_registrasi_mahasiswa)
                ->orderBy('id_semester', 'desc')
                ->get();

        $bebas_pustaka = BebasPustaka::where('id_registrasi_mahasiswa', $riwayat->id_registrasi_mahasiswa)->first();

        $data = [
            'status' => 'success',
            'data' => $transkrip,
            'akm' => $akm,
            'riwayat' => $riwayat,
            'total_sks' => $total_sks,
            'ipk' => $ipk,
            'bebas_pustaka' => $bebas_pustaka,
            'usept' => $useptData,
        ];

        return response()->json($data);

    }

    public function download(Request $request)
    {
        $request->validate([
            'nim' => 'required',
        ]);

        $riwayat = RiwayatPendidikan::with(['prodi.fakultas', 'prodi.jurusan', 'pembimbing_akademik'])->where('nim', $request->nim)->orderBy('id_periode_masuk', 'desc')->first();

        if(!$riwayat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data Mahasiswa tidak ditemukan!!',
            ]);
        }

        $transkrip = TranskripMahasiswa::where('id_registrasi_mahasiswa', $riwayat->id_registrasi_mahasiswa)->get();

        $total_sks = $transkrip->sum('sks_mata_kuliah');
        $bobot = 0;

        foreach ($transkrip as $t) {
            $bobot += $t->nilai_indeks * $t->sks_mata_kuliah;
        }

        $ipk = number_format($bobot / $total_sks, 2);

        $akm = AktivitasKuliahMahasiswa::where('id_registrasi_mahasiswa', $riwayat->id_registrasi_mahasiswa)
                ->orderBy('id_semester', 'desc')
                ->get();


        $pdf = PDF::loadview('bak.transkrip.pdf', [
            'transkrip' => $transkrip,
            'riwayat' => $riwayat,
            'akm' => $akm,
            'total_sks' => $total_sks,
            'ipk' => $ipk,
            'bebas_pustaka' => BebasPustaka::where('id_registrasi_mahasiswa', $riwayat->id_registrasi_mahasiswa)->first(),
         ])
         ->setPaper('a4', 'portrait');

         return $pdf->stream('transkrip-'.$riwayat->nim.'.pdf');
    }
}
