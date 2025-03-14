@extends('layouts.prodi')
@section('title')
Sidang
@if (Auth::user()->fk->nama_jenjang_pendidikan == 'S1')
Skripsi
@elseif (Auth::user()->fk->nama_jenjang_pendidikan == 'S2')
Tesis
@elseif (Auth::user()->fk->nama_jenjang_pendidikan == 'S3')
Disertasi
@else
Tugas Akhir
@endif
@endsection
@section('content')
@include('swal')
<div class="content-header">
    <div class="d-flex align-items-center">
        <div class="me-auto">
            <h3 class="page-title">
                Sidang
                @if (Auth::user()->fk->nama_jenjang_pendidikan == 'S1')
                Skripsi
                @elseif (Auth::user()->fk->nama_jenjang_pendidikan == 'S2')
                Tesis
                @elseif (Auth::user()->fk->nama_jenjang_pendidikan == 'S3')
                Disertasi
                @else
                Tugas Akhir
                @endif Mahasiswa</h3>
            <div class="d-inline-block align-items-center">
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('prodi')}}"><i
                                    class="mdi mdi-home-outline"></i></a></li>
                        <li class="breadcrumb-item" aria-current="page">Data Akademik</li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Sidang
                            @if (Auth::user()->fk->nama_jenjang_pendidikan == 'S1')
                            Skripsi
                            @elseif (Auth::user()->fk->nama_jenjang_pendidikan == 'S2')
                            Tesis
                            @elseif (Auth::user()->fk->nama_jenjang_pendidikan == 'S3')
                            Disertasi
                            @else
                            Tugas Akhir
                            @endif Mahasiswa</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
<section class="content">
    <div class="row">
        <div class="col-12">
            <div class="box box-outline-success bs-3 border-success">

                <div class="box-body">
                    <form action="{{ route('prodi.data-akademik.sidang-mahasiswa') }}" method="get" id="semesterForm">

                        {{-- <p class="mb-0 text-fade fs-18">Semester - </p> --}}
                        <div class="mb-3">
                            <label for="semester_view" class="form-label">Pilih Semester</label>
                            <select class="form-select" name="semester_view" id="semester_view"
                                onchange="document.getElementById('semesterForm').submit();">
                                <option value="" selected disabled>-- Pilih Semester --</option>
                                @foreach ($pilihan_semester as $p)
                                <option value="{{$p->id_semester}}" @if ($semester_view !=null) {{$semester_view==$p->id_semester ? 'selected' : ''}}
                                    @else
                                    {{$semester_aktif->id_semester == $p->id_semester ? 'selected' : ''}}
                                    @endif
                                    >{{$p->nama_semester}}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table id="data" class="table table-bordered table-hover margin-top-10 w-p100"
                            style="font-size: 11px">
                            <thead>
                                <tr>
                                    <th class="text-center align-middle">NO</th>
                                    <th class="text-center align-middle">NIM</th>
                                    <th class="text-center align-middle">NAMA</th>
                                    <th class="text-center align-middle">NAMA AKTIVITAS<br>(MK Konversi)</th>
                                    <th class="text-center align-middle">Pembimbing</th>
                                    <th class="text-center align-middle">Penguji</th>
                                    <th class="text-center align-middle">Status Penguji</th>
                                    <th class="text-center align-middle">Act</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $d)
                                @include('prodi.data-akademik.sidang-mahasiswa.pembatalan-sidang')
                                <tr>
                                    <td class="text-center align-middle"></td>
                                    <td class="text-center align-middle">
                                        {{$d->anggota_aktivitas_personal ? $d->anggota_aktivitas_personal->nim : "-"}}
                                    </td>
                                    <td class="text-start align-middle" style="width: 15%">
                                        {{$d->anggota_aktivitas_personal ? $d->anggota_aktivitas_personal->nama_mahasiswa : "-"}}
                                    </td>
                                    <td class="text-center align-middle">
                                        {{ strtoupper($d->nama_jenis_aktivitas)}}<br>
                                        @if ($d->konversi)
                                            ({{$d->konversi->kode_mata_kuliah}} - {{$d->konversi->nama_mata_kuliah}})
                                        @endif<br>
                                        @if (!$d->nilai_konversi->isEmpty())
                                            <span class="badge badge-lg badge-success">Sudah di Nilai</span>
                                        @endif
                                    </td>
                                    <td class="text-start align-middle">
                                        <ul>
                                            @foreach ($d->bimbing_mahasiswa as $p)
                                            <li>Pembimbing {{$p->pembimbing_ke}} :<br>{{$p->nama_dosen}}</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td class="text-start align-middle">
                                        <ul>
                                            @foreach ($d->uji_mahasiswa as $u)
                                            <li>{{$u->nama_kategori_kegiatan}} :<br>{{$u->nama_dosen}}</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td class="text-center align-middle">
                                        @if ($d->status_uji > 0)
                                            <span class="badge badge-lg badge-danger">Belum Disetujui</span>
                                        @elseif ($d->approved_prodi > 0)
                                            <span class="badge badge-lg badge-warning">Menunggu konfirmasi dosen</span>
                                        @elseif ($d->decline_dosen > 0)
                                            <span class="badge badge-lg badge-danger">Dibatalkan dosen</span>
                                        @else
                                            <span class="badge badge-lg badge-success">Approved</span>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="row d-flex justify-content-center">
                                            @if ($d->status_uji > 0 || $d->decline_dosen > 0)
                                            <form
                                                action="{{route('prodi.data-akademik.sidang-mahasiswa.approve-penguji', $d)}}"
                                                method="post" id="approveForm{{$d->id}}" data-id="{{$d->id}}"
                                                class="approve-class">
                                                @csrf
                                                <div class="row">
                                                    <button type="submit" class="btn btn-sm my-2 btn-success ">Approve
                                                        Penguji</button>
                                                </div>
                                            </form>
                                            @endif
                                            <a href="{{route('prodi.data-akademik.sidang-mahasiswa.edit-detail', $d->id_aktivitas)}}" class="btn btn-warning btn-sm my-2" title="Edit"><i class="fa fa-edit"></i> Edit</a>
                                            <a href="{{route('prodi.data-akademik.sidang-mahasiswa.detail', $d->id)}}" class="btn btn-secondary btn-sm my-2">
                                                <i class="fa fa-eye"></i> Detail
                                            </a>
                                            @if($d->nilai_konversi->isEmpty())
                                                <a href="#" class="btn btn-danger btn-sm my-2" title="Tolak Sidang Mahasiswa" 
                                                data-bs-toggle="modal" data-bs-target="#PembatalanSidangModal{{$d->id}}" 
                                                style="white-space: nowrap;">
                                                <i class="fa fa-ban"></i> Decline
                                                </a>
                                            @else
                                                <button type="button" class="btn btn-danger btn-sm my-2" style="white-space: nowrap;" disabled>
                                                    <i class="fa fa-ban"></i> Decline
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
@endsection
@push('js')
<script src="{{asset('assets/vendor_components/datatable/datatables.min.js')}}"></script>
<script src="{{asset('assets/vendor_components/sweetalert/sweetalert.min.js')}}"></script>
<script>

    $(function() {
        "use strict";

        $('#data').DataTable({
            // default sort by column 6 desc
            "stateSave": true,
            "order": [[ 5, "desc" ]],
            "dom": '<"top"lf<"dt-center"B>>rt<"bottom"ip><"clear">', // Place buttons (B) at the top center
            "buttons": [
                {
                    "extend": 'excelHtml5',
                    "text": 'Download Excel',
                    "className": 'btn btn-primary mt-10'
                }
            ],
            "lengthMenu": [10, 25, 50, 75, 100], // Include the length changing control
            "pageLength": 10, // Set the default number of rows to display
            "columnDefs": [{
                "targets": 0,
                "searchable": false,
                "orderable": false,
                "render": function (data, type, full, meta) {
                    return meta.settings._iDisplayStart + meta.row + 1;
                }
            }],
            "drawCallback": function (settings) {
                var api = this.api();
                var startIndex = api.context[0]._iDisplayStart;
                api.column(0, {page: 'current'}).nodes().each(function (cell, i) {
                    cell.innerHTML = startIndex + i + 1;
                });
            }
        });

        $('.approve-class').on('submit', function(e) {
            e.preventDefault();
            var formId = $(this).data('id');
            swal({
                title: 'Apakah Anda Yakin??',
                text: "Setelah disetujui, penguji tidak bisa diubah lagi!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Lanjutkan',
                cancelButtonText: 'Batal'
            }, function(isConfirm){
                if (isConfirm) {
                    $(`#approveForm${formId}`).unbind('submit').submit();
                    $('#spinner').show();
                }
            });
        });
    });
</script>
@endpush
