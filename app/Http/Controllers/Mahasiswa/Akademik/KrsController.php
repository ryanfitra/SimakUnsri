<?php

namespace App\Http\Controllers\Mahasiswa\Akademik;

use Carbon\Carbon;
use App\Models\Fakultas;
use App\Models\Semester;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use App\Models\SemesterAktif;
use App\Models\PenundaanBayar;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\BatasIsiKRSManual;
use App\Models\BeasiswaMahasiswa;
use App\Models\Connection\Tagihan;
use App\Models\Dosen\BiodataDosen;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Dosen\PenugasanDosen;
use App\Models\Connection\Registrasi;
use App\Models\Perkuliahan\MataKuliah;
use App\Models\Mahasiswa\PengajuanCuti;
use App\Models\Perkuliahan\KelasKuliah;
use function PHPUnit\Framework\isEmpty;
use Illuminate\Cache\RateLimiting\Limit;
use App\Models\PembayaranManualMahasiswa;
use App\Models\Perkuliahan\MatkulMerdeka;
use App\Models\Mahasiswa\RiwayatPendidikan;
use App\Models\Perkuliahan\PrasyaratMatkul;
use App\Models\Perkuliahan\BimbingMahasiswa;
use App\Models\Perkuliahan\AktivitasMahasiswa;
use App\Models\Perkuliahan\PesertaKelasKuliah;
use App\Models\Perkuliahan\TranskripMahasiswa;
use App\Models\Perkuliahan\RencanaPembelajaran;
use App\Models\Perkuliahan\NilaiTransferPendidikan;
use App\Models\Perkuliahan\AktivitasKuliahMahasiswa;
use App\Models\Perkuliahan\KonversiAktivitas;
use App\Models\Perkuliahan\NilaiPerkuliahan;

class KrsController extends Controller
{
    public function index(Request $request)
    {
        // Ambil id_registrasi_mahasiswa dari request
        $id_reg = auth()->user()->fk_id;

        // Ambil data riwayat pendidikan mahasiswa
        $riwayat_pendidikan = RiwayatPendidikan::with('prodi')->where('id_registrasi_mahasiswa', $id_reg)->first();
        // dd($riwayat_pendidikan);

        $semester_aktif = SemesterAktif::first()->id_semester;

        return view('mahasiswa.perkuliahan.krs.index', ['riwayat_pendidikan' => $riwayat_pendidikan, 'semester_aktif' => $semester_aktif]);
    }

    public function show($id)
    {
        try {
            // Cari data riwayat pendidikan berdasarkan ID
            $riwayat_pendidikan = RiwayatPendidikan::findOrFail($id);

            // Periksa apakah sks_maks_pmm kosong
            if (!$riwayat_pendidikan->sks_maks_pmm) {
                // Tampilkan halaman untuk update sks_maks_pmm
                return view('mahasiswa.perkuliahan.krs.krs-regular.sks_maks_pmm', compact('riwayat_pendidikan'));
            } else {
                // Tampilkan halaman lain
                return view('mahasiswa.perkuliahan.krs.krs-regular.index', compact('riwayat_pendidikan'));
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }
    }

    public function updateSksMaksPmm(Request $request, $id)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'sks_maks_pmm' => 'required|numeric|min:1',
            ]);

            // Cari data berdasarkan ID
            $riwayat = RiwayatPendidikan::findOrFail($id);

            // Update nilai sks_maks_pmm
            $riwayat->sks_maks_pmm = $validated['sks_maks_pmm'];
            $riwayat->save();

            // Redirect dengan pesan sukses
            return redirect()->route('riwayat.edit', $id)->with('success', 'SKS Maksimal PMM berhasil diperbarui.');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Jika data tidak ditemukan
            return redirect()->back()->with('error', 'Data tidak ditemukan.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Jika validasi gagal
            return redirect()->back()->withErrors($e->validator)->withInput();

        } catch (\Exception $e) {
            // Error umum lainnya
            return redirect()->back()->with('error', 'Terjadi kesalahan. Silakan coba lagi.');
        }
    }

    public function submit(Request $request)
    {
        try {
            // Ambil id_registrasi_mahasiswa dari request
            $id_reg = $request->input('id_reg');
            $semester_aktif = SemesterAktif::first()->id_semester;

            // Ambil 3 karakter paling kanan dari $semester_aktif
            $kode_kelas_kuliah = substr($semester_aktif, -3);

            // Ambil data riwayat pendidikan mahasiswa
            $riwayat_pendidikan = RiwayatPendidikan::where('id_registrasi_mahasiswa', $id_reg)->first();

            // Ambil data peserta
            $peserta = PesertaKelasKuliah::where('id_registrasi_mahasiswa', $id_reg)
                ->where('nama_kelas_kuliah', 'like', '%' . $kode_kelas_kuliah . '%') // menggunakan like dengan 3 karakter paling kanan
                ->get();

            // Ambil aktivitas mahasiswa
            $aktivitas = AktivitasMahasiswa::with(['anggota_aktivitas', 'bimbing_mahasiswa', 'konversi'])
                // ->whereHas('bimbing_mahasiswa', function ($query) {
                //     $query->whereNotNull('id_bimbing_mahasiswa');
                // })
                ->whereHas('anggota_aktivitas', function ($query) use ($riwayat_pendidikan) {
                    $query->where('id_registrasi_mahasiswa', $riwayat_pendidikan->id_registrasi_mahasiswa)
                        ->where('nim', $riwayat_pendidikan->nim);
                })
                ->where('id_semester', $semester_aktif)
                ->where('id_prodi', $riwayat_pendidikan->id_prodi)
                ->whereIn('id_jenis_aktivitas', ['1', '2', '3', '4', '5', '6', '22'])
                ->get();

            $krs_aktivitas_mbkm = AktivitasMahasiswa::with(['anggota_aktivitas'])
                ->whereHas('anggota_aktivitas' , function($query) use ($id_reg) {
                        $query->where('id_registrasi_mahasiswa', $id_reg);
                })
                // ->where('approve_krs', 1)
                ->where('id_semester', $semester_aktif)
                ->where('id_prodi', $riwayat_pendidikan->id_prodi)
                ->whereIn('id_jenis_aktivitas',['13','14','15','16','17','18','19','20', '21'])
                ->get();

            // Validasi jika data peserta atau aktivitas tidak ditemukan
            if ($peserta->isEmpty() && $aktivitas->isEmpty() && $krs_aktivitas_mbkm->isEmpty()) {
                return redirect()->back()->withErrors('Data peserta atau aktivitas tidak ditemukan.');
            }

            // Update 'submitted' untuk setiap peserta
            foreach ($peserta as $item) {
                $item->update(['submitted' => 1]);
            }

            // Update 'submitted' untuk setiap aktivitas
            foreach ($aktivitas as $item) {
                $item->update(['submitted' => 1]);
            }

            // Update 'submitted' untuk setiap aktivitas mbkm
            foreach ($krs_aktivitas_mbkm as $item) {
                $item->update(['submitted' => 1]);
            }

            // Redirect dengan pesan sukses
            return redirect()->back()->with('success', 'Data berhasil disubmit.');
        } catch (\Exception $e) {
            // Redirect dengan pesan error jika terjadi kesalahan
            return redirect()->back()->withErrors('Gagal submit data. Silakan coba lagi.');
        }

    }

    public function view(Request $request)
    {
        // DATA BAHAN
        $semester_aktif = SemesterAktif::first();

        if ($request->has('semester') && $request->semester != '') {
            $semester_select = $request->semester;
        } else {
            $semester_select = $semester_aktif->id_semester;
        }
        // dd($semester_select);

        $id_reg = auth()->user()->fk_id;

        $riwayat_pendidikan = RiwayatPendidikan::with('pembimbing_akademik')
        ->select('riwayat_pendidikans.*')
        ->where('id_registrasi_mahasiswa', $id_reg)
            ->first();

        // dd($riwayat_pendidikan);
        // if ( !$riwayat_pendidikan -> sks_maks_pmm) {
        //     // return response()->json(['message' => 'Anda tidak bisa mengambil Mata Kuliah / Aktivitas, KRS anda telah disetujui Pembimbing Akademik.'], 400);
        //     return redirect()->back()->with('error', 'Anda tidak bisa mengambil Mata Kuliah / Aktivitas, KRS anda telah disetujui Pembimbing Akademik.');
        // }

        $total_sks_akt = 0;
        $total_sks_regular = 0;
        $total_sks_merdeka = 0;

        //DATA AKTIVITAS
        $db = new MataKuliah();

        $db_akt = new AktivitasMahasiswa();

        // $data_akt = $db->getMKAktivitas($riwayat_pendidikan->id_prodi, $riwayat_pendidikan->id_kurikulum);

        list($krs_akt, $data_akt_ids, $mk_akt) = $db_akt->getKrsAkt($id_reg, $semester_select);

        $semester = Semester::orderBy('id_semester', 'DESC')
        ->whereBetween('id_semester', [$riwayat_pendidikan->id_periode_masuk, $semester_aktif->id_semester])
            // ->whereRaw('RIGHT(id_semester, 1) != ?', [3])
            ->get();

        // Mengambil status mahasiswa untuk semester aktif
        $status_mahasiswa = $semester->where('id_semester', $semester_select)
            ->pluck('id_status_mahasiswa')
            ->first();

        // Menentukan status mahasiswa berdasarkan hasil query
        $data_status_mahasiswa = $status_mahasiswa !== null ? $status_mahasiswa : 'X';

        // $semester_urut = Semester::orderBy('id_semester', 'ASC')
        //             // ->limit(10)
        //             ->get();

        $semester_ke = Semester::orderBy('id_semester', 'ASC')
            ->whereBetween('id_semester', [$riwayat_pendidikan->id_periode_masuk, $semester_aktif->id_semester])
            ->whereRaw('RIGHT(id_semester, 1) != ?', [3])
            ->count();

        $sks_max = $db->getSksMax($id_reg, $semester_aktif->id_semester, $riwayat_pendidikan->id_periode_masuk);

        $krs_regular = $db->getKrsRegular($id_reg, $riwayat_pendidikan, $semester_select, $data_akt_ids);

        $krs_merdeka = $db->getKrsMerdeka($id_reg, $semester_select, $riwayat_pendidikan->id_prodi);

        // DATA MK_MERDEKA
        $fakultas = Fakultas::all();

        // MATAKULIAH TANPA GANJIL GENAP
        $mk_regular = $db->getMKRegular();
        // dd($mk_regular);

        // TAGIHAN PEMBAYARAN
        $beasiswa = BeasiswaMahasiswa::where('id_registrasi_mahasiswa', $id_reg)->first();
        // dd($beasiswa);

        $id_test = Registrasi::where('rm_nim', $riwayat_pendidikan->nim)->pluck('rm_no_test')->first();

        // dd($semester_select);
        // Jika 1 angka terakhir dari semester_select tidak sama dengan 3
        if (substr($semester_select, -1) == 3 ) {
            $akm_genap = AktivitasKuliahMahasiswa::where('id_registrasi_mahasiswa', $id_reg)
                ->where('id_semester', $semester_select - 1)
                ->orderBy('id_semester', 'DESC')
                ->first();

                // dd($akm_genap);
            if(!$akm_genap){
                return redirect()->back()->with('error', "Aktivitas Kuliah anda pada semester genap tidak ditemukan!! Silahkan Hubungi Koor. Program Studi!");
            }
        }

        $total_sks_genap = (substr($semester_select, -1) == 3)
            ? $akm_genap->sks_semester
            : 0;

            // dd($semester_aktif->id_semester);
        try {
            $tagihan = Tagihan::with('pembayaran')
                ->whereIn('nomor_pembayaran', [$id_test, $riwayat_pendidikan->nim])
                ->where('kode_periode', (substr($semester_select, -1) == 3)
                    ? $semester_select-1
                    : $semester_select
                )
                ->first();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengambil data tagihan');
        }

        $cuti = PengajuanCuti::where('id_registrasi_mahasiswa', $id_reg)->where('id_semester', $semester_aktif->id_semester)->first();
        // dd($cuti);

        $transkrip = TranskripMahasiswa::select(
                DB::raw('SUM(CAST(sks_mata_kuliah AS UNSIGNED)) as total_sks'), // Mengambil total SKS tanpa nilai desimal
                DB::raw('ROUND(SUM(nilai_indeks * sks_mata_kuliah) / SUM(sks_mata_kuliah), 2) as ipk') // Mengambil IPK dengan 2 angka di belakang koma
            )
            ->where('id_registrasi_mahasiswa', $id_reg)
            ->whereNotIn('nilai_huruf', ['F', ''])
            ->first();

        $krs_aktivitas_mbkm = AktivitasMahasiswa::with(['anggota_aktivitas'])
            ->whereHas('anggota_aktivitas', function ($query) use ($id_reg) {
                $query->where('id_registrasi_mahasiswa', $id_reg);
            })
            // ->where('approve_krs', 1)
            ->where('id_semester', $semester_aktif->id_semester)
            ->whereIn('id_jenis_aktivitas', ['13', '14', '15', '16', '17', '18', '19', '20', '21'])
            ->get();

        // TOTAL SELURUH SKS
        $total_sks_akt = $krs_akt->sum('konversi.sks_mata_kuliah');
        $total_sks_merdeka = $krs_merdeka->sum('sks_mata_kuliah');
        $total_sks_regular = $krs_regular->sum('sks_mata_kuliah');
        $total_sks_mbkm = $krs_aktivitas_mbkm->sum('sks_aktivitas');

        $total_sks = $total_sks_regular + $total_sks_merdeka + $total_sks_akt + $total_sks_mbkm + $total_sks_genap;
        // dd($total_sks_genap);
        // dd($total_sks, $total_sks_regular , $total_sks_merdeka , $total_sks_akt , $total_sks_mbkm , $total_sks_genap);
        if (substr($semester_select, -1) == 3) {
            if ($total_sks_genap == 24) {
                $sks_max = 0;
            } elseif($total_sks_genap > 15 && $total_sks_genap < 24) {
                $sks_max = $sks_max - $total_sks_genap;
            }else{
                $sks_max = 9;
            }
        } else {
            $sks_max = $sks_max;
        }

        // dd($sks_max);

        // Fungsi cek batas isi KRS mulai
        $today = Carbon::now()->toDateString();
        $batas_isi_krs = Carbon::parse($semester_aktif->krs_selesai)->toDateString();
        $mulai_isi_krs = Carbon::parse($semester_aktif->krs_mulai)->toDateString();

        if ($today >= $semester_aktif->tanggal_mulai_kprs && $today <= $semester_aktif->tanggal_akhir_kprs) {
            $batas_isi_krs =  Carbon::parse($semester_aktif->tanggal_akhir_kprs)->toDateString();
            $mulai_isi_krs = Carbon::parse($semester_aktif->krs_mulai)->toDateString();
        }

        // dd($batas_isi_krs, $mulai_isi_krs, $today, $semester_aktif->tanggal_mulai_kprs, $semester_aktif->tanggal_akhir_kprs);

        $batas_isi_krs_manual = BatasIsiKRSManual::where('id_registrasi_mahasiswa', $id_reg)->where('id_semester', $semester_aktif->id_semester)->first();

        if($batas_isi_krs_manual && $today <= $batas_isi_krs_manual->batas_isi_krs){
            $batas_isi_krs =  Carbon::parse($batas_isi_krs_manual->batas_isi_krs)->toDateString();
        }
        // Fungsi cek batas isi KRS Selesai

        $batas_pembayaran = Carbon::parse($semester_aktif->batas_bayar_ukt)->toDateString();

        $masa_tenggang = Carbon::parse($semester_aktif->batas_bayar_ukt)->addDays(30)->toDateString();

        $penundaan_pembayaran = PenundaanBayar::where('id_registrasi_mahasiswa', $id_reg)
            ->where('id_semester', $semester_aktif->id_semester)
            ->count();

        $pembayaran_manual = PembayaranManualMahasiswa::with(['semester', 'riwayat'])
        ->where('id_registrasi_mahasiswa', $id_reg)
            ->where('id_semester', $semester_aktif->id_semester)
            ->count();

        $non_gelar = $riwayat_pendidikan->id_jenis_daftar == '14' ? 1 : 0;

        $regular_submitted = $krs_regular->where('submitted', 0)->count();
        $merdeka_submitted = $krs_merdeka->where('submitted', 0)->count();
        $aktivitas_submitted = $krs_akt->where('submitted', 0)->count();
        $mbkm_submitted = $krs_aktivitas_mbkm->where('submitted', 0)->count();
        $total_krs_submitted = $regular_submitted + $merdeka_submitted + $aktivitas_submitted + $mbkm_submitted;
        // dd($total_krs_submitted, $mbkm_submitted);

        $sks_aktivitas_mbkm = [
            "10",
            "20"
        ];

        // Periksa apakah sks_maks_pmm kosong
        if (!$riwayat_pendidikan->sks_maks_pmm && $riwayat_pendidikan->id_jenis_daftar === '14') {
            // Tampilkan halaman untuk update sks_maks_pmm
            return view('mahasiswa.perkuliahan.krs.krs-regular.sks_maks_pmm', compact(
                'sks_aktivitas_mbkm',
                'mk_regular',
                'semester_select',
                'riwayat_pendidikan',
                'semester_aktif',
                'krs_regular',
                'krs_merdeka',
                'total_sks_merdeka',
                'total_sks_regular',
                // 'akm',
                'sks_max',
                'semester',
                'total_sks',
                'status_mahasiswa',
                'data_status_mahasiswa',
                'semester_ke',
                'fakultas',
                'krs_akt',
                'mk_akt',
                'total_sks_akt',
                'beasiswa',
                'tagihan',
                'cuti',
                'transkrip',
                'batas_pembayaran',
                'batas_isi_krs',
                'mulai_isi_krs',
                'today',
                'masa_tenggang',
                'penundaan_pembayaran',
                'non_gelar',
                'pembayaran_manual',
                'total_krs_submitted'
            ));
        } else {
            // Tampilkan halaman lain
            // return view('mahasiswa.perkuliahan.krs.krs-regular.index', compact('riwayat_pendidikan'));

            return view('mahasiswa.perkuliahan.krs.krs-regular.index', [
                'formatDosenPengajar' => function ($dosenPengajar) {
                    return $this->formatDosenPengajar($dosenPengajar);
                }
            ], compact(
                'mk_regular',
                'semester_select',
                'riwayat_pendidikan',
                'semester_aktif',
                'krs_regular',
                'krs_merdeka',
                'total_sks_merdeka',
                'total_sks_regular',
                // 'akm',
                'sks_max',
                'semester',
                'total_sks',
                'status_mahasiswa',
                'data_status_mahasiswa',
                'semester_ke',
                'fakultas',
                'krs_akt',
                'mk_akt',
                'total_sks_akt',
                'beasiswa',
                'tagihan',
                'cuti',
                'transkrip',
                'batas_pembayaran',
                'batas_isi_krs',
                'mulai_isi_krs',
                'today',
                'masa_tenggang',
                'penundaan_pembayaran',
                'non_gelar',
                'pembayaran_manual',
                'total_krs_submitted'
            ));
        }
    }

    public function pilih_prodi(Request $request)
    {
        $fakultasId = $request->input('id');
        $id_semester = $request->input('semester');
        $id_prodi = $request->input('id_prodi');

        $prodi = ProgramStudi::where('fakultas_id', $fakultasId)->get();

        return response()->json(['prodi' => $prodi]);
    }


    public function pilihMataKuliahMerdeka(Request $request)
    {
        $id_reg = auth()->user()->fk_id;

        $semester_aktif = SemesterAktif::first()->id_semester;
        // Ambil id_prodi dari request

        $id_prodi = $request->input('id_prodi');

        $prodi_mhs =RiwayatPendidikan::where('id_registrasi_mahasiswa', $id_reg)
                    ->pluck('id_prodi')->first();


        // $selectedFakultasId = $request->input('fakultas_id');

        // $prodi = ProgramStudi::where('fakultas_id', $selectedFakultasId)->get();


        $db = new MataKuliah();

        // Query untuk mengambil data mata kuliah merdeka berdasarkan id_prodi yang dipilih
        $krs_merdeka = $db->getKrsMerdeka($id_reg, $semester_aktif, $prodi_mhs);

        $mkMerdeka = $db->getMKMerdeka($semester_aktif, $id_prodi);
        // dd($mkMerdeka);

        return response()->json(['mk_merdeka' => $mkMerdeka, 'krs_merdeka'=>$krs_merdeka, 'prodi_mhs'=>$prodi_mhs, 'prodi_mk'=>$id_prodi ]);
    }


    public function get_kelas_kuliah(Request $request)
    {
        $idMatkul = $request->get('id_matkul');

        $id_reg = auth()->user()->fk_id;
        $prodi_id = RiwayatPendidikan::where('id_registrasi_mahasiswa', $id_reg)
                    ->pluck('id_prodi');

        $semester_aktif = SemesterAktif::pluck('id_semester');

        $kelasKuliah = KelasKuliah::with(['dosen_pengajar','dosen_pengajar.dosen', 'ruang_perkuliahan'])
                    ->whereHas('dosen_pengajar' , function($query) {
                            $query->whereNotNull('id_dosen');
                        })
                    ->withCount('peserta_kelas')
                    ->where('id_matkul', $idMatkul)
                    ->where('id_semester',  $semester_aktif)
                    ->where('id_prodi', $prodi_id)
                    ->orderBy('nama_kelas_kuliah')
                    ->get();

                    // dd($kelasKuliah);

        foreach ($kelasKuliah as $kelas) {
            $kelas->is_kelas_ambil = $this->cekApakahKelasSudahDiambil($request->user()->fk_id, $kelas->id_matkul);
        }

        //  dd($kelas);

        return response()->json($kelasKuliah);
    }


    public function get_kelas_kuliah_merdeka(Request $request)
    {
        $idMatkul = $request->get('id_matkul');

        $id_reg = auth()->user()->fk_id;

        $idProdi = $request->get('id_prodi');
        // $prodi_id = RiwayatPendidikan::where('id_registrasi_mahasiswa', $id_reg)
        //             ->pluck('id_prodi');

        $semester_aktif = SemesterAktif::pluck('id_semester');

        $kelasKuliah = KelasKuliah::with(['dosen_pengajar','dosen_pengajar.dosen', 'ruang_perkuliahan'])
                    ->whereHas('dosen_pengajar' , function($query) {
                            $query->whereNotNull('id_dosen');
                        })
                    ->withCount('peserta_kelas')
                    ->where('id_matkul', $idMatkul)
                    ->where('id_semester',  $semester_aktif)
                    ->where('id_prodi', $idProdi)
                    ->orderBy('nama_kelas_kuliah')
                    ->get();

        // dd($kelasKuliah);

        foreach ($kelasKuliah as $kelas) {
            $kelas->is_kelas_ambil = $this->cekApakahKelasSudahDiambil($request->user()->fk_id, $kelas->id_matkul);
        }

        // dd($kelasKuliah);
        return response()->json($kelasKuliah);
    }


    private function cekApakahKelasSudahDiambil($id_registrasi_mahasiswa, $id_matkul)
    {
        $kelasDiambil = PesertaKelasKuliah::where('id_registrasi_mahasiswa', $id_registrasi_mahasiswa)
            ->where('id_matkul', $id_matkul)
            ->exists();

        return $kelasDiambil;
    }

    public function ambilKelasKuliah(Request $request)
    {
        try {
            $idKelasKuliah = $request->input('id_kelas_kuliah');
            $id_reg = auth()->user()->fk_id;

            $riwayat_pendidikan = RiwayatPendidikan::select('riwayat_pendidikans.*', 'biodata_dosens.id_dosen', 'biodata_dosens.nama_dosen')
                    ->where('id_registrasi_mahasiswa', $id_reg)
                    ->leftJoin('biodata_dosens', 'biodata_dosens.id_dosen', '=', 'riwayat_pendidikans.dosen_pa')
                    ->first();
            // $prodi = $riwayat_pendidikan->id_prodi;
            // $kurikulum = $riwayat_pendidikan->id_kurikulum;

            $semester_aktif = SemesterAktif::first();

            // Pengecekan apakah KRS sudah diApprove
            $approved_krs = PesertaKelasKuliah::with(['kelas_kuliah'])
                        ->whereHas('kelas_kuliah', function($query) use ($semester_aktif) {
                            $query ->where('id_semester', $semester_aktif->id_semester);
                        })
                        ->where('id_registrasi_mahasiswa', $id_reg)
                        ->where('nama_kelas_kuliah', 'LIKE', '241%' )
                        ->where('approved', 1)
                        ->count();
                        // dd($approved_krs);

            $approved_akt = AktivitasMahasiswa::with(['anggota_aktivitas'])
                        ->whereHas('anggota_aktivitas', function($query) use ($id_reg) {
                            $query ->where('id_registrasi_mahasiswa', $id_reg);
                        })
                        ->where('id_semester', $semester_aktif->id_semester )
                        ->where('approve_krs', 1)
                        ->whereNotIn('id_jenis_aktivitas', ['7'])
                        ->count();
                        // dd($approved);

            if ( $approved_krs > 0 || $approved_akt > 0) {
                return response()->json(['message' => 'Anda tidak bisa mengambil Mata Kuliah / Aktivitas, KRS anda telah disetujui Pembimbing Akademik.'], 400);
                // return redirect()->back()->with('error', 'Anda tidak bisa mengambil Mata Kuliah / Aktivitas, KRS anda telah disetujui Pembimbing Akademik.');
            }

            $krs_aktivitas_mbkm = AktivitasMahasiswa::with(['anggota_aktivitas'])
                    ->whereHas('anggota_aktivitas' , function($query) use ($id_reg) {
                            $query->where('id_registrasi_mahasiswa', $id_reg);
                    })
                    // ->where('approve_krs', 1)
                    ->where('id_semester', $semester_aktif->id_semester)
                    ->whereIn('id_jenis_aktivitas',['13','14','15','16','17','18','19','20', '21'])
                    ->get();

            $db = new MataKuliah();
            $db_akt = new AktivitasMahasiswa();

            list($krs_akt, $data_akt_ids) = $db_akt->getKrsAkt($id_reg, $semester_aktif->id_semester);

            $sks_max = $db->getSksMax($id_reg, $semester_aktif->id_semester, $riwayat_pendidikan->id_periode_masuk);
            $krs_regular = $db->getKrsRegular($id_reg, $riwayat_pendidikan, $semester_aktif->id_semester, $data_akt_ids);
            $krs_merdeka = $db->getKrsMerdeka($id_reg, $semester_aktif->id_semester, $riwayat_pendidikan->id_prodi);

            if (substr($semester_aktif->id_semester, -1) == 3) {
                $total_sks_genap = AktivitasKuliahMahasiswa::where('id_registrasi_mahasiswa', $id_reg)
                    ->where('id_semester', $semester_aktif->id_semester-1)
                    ->orderBy('id_semester', 'DESC')
                    ->pluck('sks_semester')
                    ->first();

                if ($total_sks_genap == 24) {
                    $sks_max = 0;
                } elseif($total_sks_genap > 15 && $total_sks_genap < 24) {
                    $sks_max = $sks_max - $total_sks_genap;
                }elseif($total_sks_genap >= 0 && $total_sks_genap < 15) {
                    $sks_max = 9;
                }
            }else{
                $total_sks_genap = 0;
                $sks_max = $sks_max;
            }

            $total_sks_akt = $krs_akt->sum('konversi.sks_mata_kuliah');
            $total_sks_merdeka = $krs_merdeka->sum('sks_mata_kuliah');
            $total_sks_regular = $krs_regular->sum('sks_mata_kuliah');
            $total_sks_mbkm = $krs_aktivitas_mbkm->sum('sks_aktivitas');

            $total_sks = $total_sks_regular + $total_sks_merdeka + $total_sks_akt + $total_sks_mbkm;
            // $total_sks = $total_sks_regular;
            // dd($sks_max, $total_sks);

            $sks_mk = KelasKuliah::select('sks_mata_kuliah')
                    ->leftJoin('mata_kuliahs', 'mata_kuliahs.id_matkul', '=', 'kelas_kuliahs.id_matkul')
                    ->where('id_kelas_kuliah', $idKelasKuliah)
                    ->pluck('sks_mata_kuliah')
                    ->first();

            $non_gelar = RiwayatPendidikan::where('id_registrasi_mahasiswa', $id_reg)
                    ->where('id_jenis_daftar', '14')
                    ->count();

            // dd($krs_aktivitas_mbkm);
            // Pengecekan apakah SKS maksimum telah tercapai
            if ($sks_max == 0  && $non_gelar == 0) {
                return response()->json(['message' => 'Data AKM Anda Tidak Ditemukan, Silahkan Hubungi Admin Program Studi.', 'sks_max' => $sks_max], 400);
            }

            $sisa_sks = $sks_max-$total_sks;

            if(substr($semester_aktif->id_semester, -1) == 3){
                if (($total_sks + $sks_mk) > $sks_max) {
                    return response()->json([
                        'message' => "Total SKS Semester Genap dan Semester Antara tidak boleh melebihi 24 SKS!!\nAnda hanya bisa ambil Mata Kuliah dengan bobot $sisa_sks sks",
                        'sks_max' => $sks_max
                        // Anda telah mengambil $total_sks_genap sks pada Semester Genap dan $total_sks sks pada Semester Antara!!\n
                    ], 400);
                }
            }else{
                if (($total_sks + $sks_mk) > $sks_max) {
                    return response()->json([
                        'message' => "Total SKS Semester tidak boleh melebihi sks maksimum!!\nAnda telah Mengambil $total_sks SKS",
                        'sks_max' => $sks_max
                    ], 400);
                }
            }




            $kelas_mk = KelasKuliah::leftJoin('mata_kuliahs', 'mata_kuliahs.id_matkul','=','kelas_kuliahs.id_matkul')
                    ->where('id_kelas_kuliah', $idKelasKuliah)->first();

            $jumlah_peserta = PesertaKelasKuliah::where('id_kelas_kuliah', $idKelasKuliah)->count();

            // Pengecekan kapasitas kelas
            if ($jumlah_peserta >= $kelas_mk->ruang_perkuliahan->kapasitas_ruang) {
                return response()->json(['message' => 'Kapasitas kelas sudah penuh.'], 400);
            }

            //QUERY RPS
            $rps=RencanaPembelajaran::where('id_matkul', $kelas_mk->id_matkul)->get();

            if ($rps->count() == 0) {
                return response()->json(['message' => 'Rencana Pembelajaran Semester tidak ditemukan untuk mata kuliah ini.'], 400);
            }

            $today = Carbon::now()->toDateString();

            // if($today >= $semester_aktif->krs_mulai && $today <= $semester_aktif->krs_selesai ){
            //     return response()->json(['message' => 'Periode pengisian KRS pada Semester yang Anda pilih telah berakhir.'], 400);
            // }
            // elseif(($today >= $semester_aktif->tanggal_mulai_kprs && $today <= $semester_aktif->tanggal_akhir_kprs )){
            //     return response()->json(['message' => 'Periode pengisian KRS pada Semester yang Anda pilih telah berakhir.'], 400);
            // }else
            // {
            //     $batas_isi_krs =  NULL;
            // }

            DB::beginTransaction();

            $peserta = PesertaKelasKuliah::create([
                'feeder' => 0,
                'submitted' => 0,
                'approved' => 0,
                'id_kelas_kuliah' => $idKelasKuliah,
                'id_registrasi_mahasiswa' => $id_reg,
                'nim' => $riwayat_pendidikan->nim,
                'id_mahasiswa' => $riwayat_pendidikan->id_mahasiswa,
                'nama_mahasiswa' => $riwayat_pendidikan->nama_mahasiswa,
                'nama_program_studi' => $riwayat_pendidikan->nama_program_studi,
                'id_prodi' => $riwayat_pendidikan->id_prodi,
                'nama_kelas_kuliah' => $kelas_mk->nama_kelas_kuliah,
                'id_matkul' => $kelas_mk->id_matkul,
                'kode_mata_kuliah' => $kelas_mk->kode_mata_kuliah,
                'nama_mata_kuliah' => $kelas_mk->nama_mata_kuliah,
                'angkatan' => $riwayat_pendidikan->periode_masuk->id_tahun_ajaran,
            ]);

            DB::commit();

            // Jika ingin mengembalikan tampilan (view), bisa seperti ini:
            // return view('mahasiswa.perkuliahan.krs.index', [
            //     'peserta' => $peserta,
            //     'sks_max' => $sks_max,
            //     'sks_mk' => $sks_mk,
            //     'jumlah_peserta' => $jumlah_peserta,
            //     'riwayat_pendidikan' =>  $riwayat_pendidikan,
            //     'message' => 'Data berhasil disimpan'
            // ]);

            // Namun, pada konteks ini (AJAX/JSON), biasanya tetap menggunakan response()->json.
            // Jika tetap ingin pakai view, pastikan permintaan dari frontend memang mengharapkan HTML, bukan JSON.

            return response()->json(['message' => 'Data berhasil disimpan', 'sks_max' => $sks_max, 'sks_mk' => $sks_mk, 'peserta' => $peserta, 'jumlah_peserta' => $jumlah_peserta], 200);
        } catch (\Exception $e) {
            DB::rollback();

            // Jika ingin mengembalikan tampilan error:
            // return view('mahasiswa.perkuliahan.krs.index', ['error' => 'Terjadi kesalahan saat menyimpan data.']);

            return response()->json(['message' => 'Terjadi kesalahan saat menyimpan data. '], 500);
        }
    }


    public function hapus_kelas_kuliah(PesertaKelasKuliah $pesertaKelas)
    {
        // $peserta= PesertaKelasKuliah::where('id', $pesertaKelas)->first();
        // dd($pesertaKelas);

        if ($pesertaKelas->approved ==1) {
            // return redirect()->back()->with('error', 'Rencana Pembelajaran Semester tidak ditemukan untuk mata kuliah ini.');
            // return response()->json(['message' => 'Anda tidak dapat menghapus Mata Kuliah ini.'], 400);
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus Mata Kuliah ini, Mata Kuliah telah disetujui Dosen Pembimbing Akademik');
        }

        $pesertaKelas->delete();

        return redirect()->back()->with('success', 'Mata Kuliah Berhasil di Hapus');
    }

    public function cekPrasyarat(Request $request)
    {
        $idMatkul = $request->get('id_matkul');
        $id_reg = $request->get('id_reg');

        $id_prodi = RiwayatPendidikan::where('id_registrasi_mahasiswa', $id_reg)
                    ->pluck('id_prodi')->first();

        $prodi_non_homebase = KelasKuliah::whereIn('id_prodi', [$id_prodi])
                        // ->whereHas('kelas_kuliah', function($query) use ($idMatkul) {
                        //     $query ->where('id_matkul', $idMatkul);
                        // })
                        ->where('id_matkul', $idMatkul)
                        ->first();

        // Dapatkan mata kuliah prasyarat
        $prasyarat = PrasyaratMatkul::where('id_matkul', $idMatkul)->pluck('id_matkul_prasyarat');

        // Jika tidak ada prasyarat, langsung return true
        if ($prasyarat->isEmpty() || empty($prodi_non_homebase)) {
            return response()->json(['prasyarat_dipenuhi' => true]);
        }

        // Cek apakah mahasiswa sudah mengambil mata kuliah prasyarat
        $mataKuliahDipenuhi = NilaiPerkuliahan::where('id_registrasi_mahasiswa', $id_reg)
                ->whereIn('id_matkul', $prasyarat)
                ->exists();

        $mataKuliahDipenuhi_2 = NilaiTransferPendidikan::where('id_registrasi_mahasiswa', $id_reg)
                ->whereIn('id_matkul', $prasyarat)
                // ->where('approved', '1')
                ->exists();

        $mataKuliahDipenuhi_3 = KonversiAktivitas::with(['matkul'])->join('anggota_aktivitas_mahasiswas as ang', 'konversi_aktivitas.id_anggota', 'ang.id_anggota')
                        ->whereIn('id_matkul', $prasyarat)
                        ->where('ang.id_registrasi_mahasiswa', $id_reg)
                        ->exists();

        if ($mataKuliahDipenuhi || $mataKuliahDipenuhi_2 || $mataKuliahDipenuhi_3) {
            return response()->json(['prasyarat_dipenuhi' => true]);
        } else {
            // Dapatkan nama mata kuliah prasyarat yang belum diambil
            $mataKuliahSyarat = MataKuliah::whereIn('id_matkul', $prasyarat)
                ->pluck('nama_mata_kuliah')
                ->toArray();

            // Gabungkan nama mata kuliah prasyarat dengan koma
            $mataKuliahSyaratString = implode(', ', $mataKuliahSyarat);
            // dd($mataKuliahSyaratString);

            return response()->json([
                'prasyarat_dipenuhi' => false,
                'mata_kuliah_syarat' => $mataKuliahSyaratString,
                'prodi_non_homebase' => $prodi_non_homebase,
                'id_prodi'=>$id_prodi
            ]);
        }
    }

    public function krs_print(Request $request, $id_semester)
    {
        $id_reg = auth()->user()->fk_id;

        $riwayat_pendidikan = RiwayatPendidikan::with('pembimbing_akademik')
                ->where('id_registrasi_mahasiswa', $id_reg)
                ->first();

        $prodi = ProgramStudi::with(['fakultas', 'jurusan'])
                ->where('id_prodi', $riwayat_pendidikan->id_prodi)->first();

        $fakultas_pdf = (str_replace("Fakultas ","",$prodi->fakultas->nama_fakultas));
        // dd($fakultas_pdf);

        $semester_aktif = SemesterAktif::first();

        $today = Carbon::now();
        $deadline = Carbon::parse($semester_aktif->krs_selesai);

        $db = new MataKuliah();

        $data_akt = $db->getMKAktivitas($riwayat_pendidikan->id_prodi, $riwayat_pendidikan->id_kurikulum);

        if(isEmpty($data_akt))
        {
            $mk_akt=NULL;
            $data_akt_ids = NULL;

        }
        else
        {
            $mk_akt = $data_akt;
            $data_akt_ids = array_column($mk_akt, 'id_matkul');
        }

        if ($request->has('semester') && $request->semester != '') {
            $semester_select = $request->semester;
        } else {
            $semester_select = SemesterAktif::first()->id_semester;
        }

        $data = PesertaKelasKuliah::with('kelas_kuliah')
                    ->whereHas('kelas_kuliah', function($query) use ($id_semester) {
                        $query ->where('id_semester', $id_semester);
                    })
                    ->where('id_registrasi_mahasiswa', $id_reg)
                    ->get();
        // dd($prodi);

        $krs_regular = $db->getKrsRegular($id_reg, $riwayat_pendidikan, $id_semester, $data_akt_ids)->where('approved', 1);

        $total_sks_regular = $krs_regular->sum('sks_mata_kuliah');

        $nama_mhs = $riwayat_pendidikan->nama_mahasiswa;
        $nim = $riwayat_pendidikan->nim;
        $nama_smt = Semester::where('id_semester', $id_semester)->first()->nama_semester;
        $dosen_pa = BiodataDosen::where('id_dosen', $riwayat_pendidikan->dosen_pa)->first();
        // dd($request->semester);
        if (empty($dosen_pa)) {
            return response()->json(['error' => 'Dosen PA tidak ditemukan.']);
        }

        //DATA AKTIVITAS
        $db = new MataKuliah();

        $db_akt = new AktivitasMahasiswa();

        $data_akt = $db->getMKAktivitas($riwayat_pendidikan->id_prodi, $riwayat_pendidikan->id_kurikulum);

        list($krs_akt, $data_akt_ids) = $db_akt->getKrsAkt($id_reg, $id_semester);

        $krs_akt = $krs_akt->where('approve_krs', 1);

        $semester = AktivitasKuliahMahasiswa::where('id_registrasi_mahasiswa', $id_reg)
                    ->orderBy('id_semester', 'DESC')
                    ->get();

        // Mengambil status mahasiswa untuk semester aktif
        $status_mahasiswa = $semester->where('id_semester', $id_semester)
                    ->pluck('id_status_mahasiswa')
                    ->first();

        // Menentukan status mahasiswa berdasarkan hasil query
        $data_status_mahasiswa = $status_mahasiswa !== null ? $status_mahasiswa : 'X';

        $krs_merdeka = $db->getKrsMerdeka($id_reg, $id_semester, $riwayat_pendidikan->id_prodi)->where('approved', 1);

        $krs_aktivitas_mbkm = AktivitasMahasiswa::with(['anggota_aktivitas'])
                    ->whereHas('anggota_aktivitas' , function($query) use ($id_reg) {
                            $query->where('id_registrasi_mahasiswa', $id_reg);
                    })
                    ->where('approve_krs', 1)
                    ->where('id_semester', $semester_aktif->id_semester)
                    ->whereIn('id_jenis_aktivitas',['13','14','15','16','17','18','19','20', '21'])
                    ->get();

        // dd($krs_akt);
        // if(!empty($krs_aktivitas_mbkm->get())){
        //     $sks_akt_mbkm = $krs_aktivitas_mbkm->get()->sks_aktivitas;
        // }else{
        //     $sks_akt_mbkm =0;
        // }

    // TOTAL SELURUH SKS
        $total_sks_akt = $krs_akt->sum('konversi.sks_mata_kuliah');
        $total_sks_merdeka = $krs_merdeka->sum('sks_mata_kuliah');
        $total_sks_regular = $krs_regular->sum('sks_mata_kuliah');
        $total_sks_mbkm = $krs_aktivitas_mbkm->sum('sks_aktivitas');

        $total_sks = $total_sks_regular + $total_sks_merdeka + $total_sks_akt + $total_sks_mbkm ;
        // $total_sks = $total_sks_regular + $total_sks_merdeka + $total_sks_akt;

        // dd($total_sks_mbkm);

        $tgl_krs_regular = $krs_regular->first();
        $tgl_krs_merdeka = $krs_merdeka->first();
        $tgl_krs_akt = $krs_akt->first();
        $tgl_krs_mbkm = $krs_aktivitas_mbkm->first();

        // if (empty($tgl_krs_regular) && empty($tgl_krs_merdeka) && empty($tgl_krs_akt) ) {
        //     return response()->json(['error' => 'KRS anda belum disetujui Dosen PA.']);
        // }

        if (empty($krs_regular->first()) && empty($krs_merdeka->first()) && empty($krs_akt->first())&& empty($krs_aktivitas_mbkm->first())) {
            return redirect()->back()->with('error' , 'KRS tidak dapat dicetak, KRS belum disetujui Dosen PA');
        }

        if (!empty($tgl_krs_regular)) {
            $tanggal_approve = Carbon::parse($tgl_krs_regular->tanggal_approve);
        }
        elseif (!empty($tgl_krs_merdeka))
        {
            $tanggal_approve = Carbon::parse($tgl_krs_merdeka->tanggal_approve);
        }
        elseif (!empty($tgl_krs_akt))
        {
            $tanggal_approve = Carbon::parse($tgl_krs_akt->tanggal_approve);
        }
        elseif (!empty($tgl_krs_mbkm))
        {
            $tanggal_approve = Carbon::parse($tgl_krs_mbkm->tanggal_approve);
        }
        else
        {
            $tanggal_approve = '-';
        }

        // dd($tgl_krs_mbkm);

        $pdf = PDF::loadview('mahasiswa.perkuliahan.krs.krs-regular.pdf', [
            'today'=> $today,
            'deadline'=> $deadline,
            'data' => $data,
            'nim' => $nim,
            'nama_mhs' => $nama_mhs,
            'dosen_pa' => $dosen_pa,
            'prodi' => $prodi,
            'fakultas_pdf' => $fakultas_pdf,
            'nama_smt' => $nama_smt,
            'semester_aktif' => $semester_aktif,
            'id_semester' => $id_semester,
            'total_sks_regular' => $total_sks_regular,
            'krs_regular'=> $krs_regular,
            'data_status_mahasiswa' => $data_status_mahasiswa,
            'krs_regular' => $krs_regular,
            'krs_merdeka' => $krs_merdeka,
            'krs_akt' => $krs_akt,
            'total_sks_akt' => $total_sks_akt,
            'total_sks_merdeka' => $total_sks_merdeka,
            'total_sks_regular' => $total_sks_regular,
            'total_sks_mbkm' => $total_sks_mbkm,
            'total_sks' => $total_sks,
            'tanggal_approve' => $tanggal_approve,
            'krs_aktivitas_mbkm' => $krs_aktivitas_mbkm
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('KRS_' . $nim . '_' . $nama_smt . '.pdf');
    }

    public function checkDosenPA($id_semester)
    {
        $id_reg = auth()->user()->fk_id;

        $riwayat_pendidikan = RiwayatPendidikan::with('pembimbing_akademik')
            ->where('id_registrasi_mahasiswa', $id_reg)
            ->first();

        $dosen_pa = BiodataDosen::where('id_dosen', $riwayat_pendidikan->dosen_pa)->first();

        if (empty($dosen_pa)) {
            return response()->json(['error' => 'Dosen PA belum ditentukan, Silahkan Hubungi Koor. Program Studi.']);
        }

        return response()->json(['success' => true]);
    }

}
