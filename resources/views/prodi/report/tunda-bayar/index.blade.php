@extends('layouts.prodi')
@section('title')
Daftar Tunda Bayar
@endsection
@section('content')
<section class="content">
    <div class="row align-items-end">
        <div class="col-xl-12 col-12">
            <div class="box bg-primary-light pull-up">
                <div class="box-body p-xl-0">
                    <div class="row align-items-center">
                        <div class="col-12 col-lg-3"><img
                                src="{{asset('images/images/svg-icon/color-svg/custom-14.svg')}}" alt="">
                        </div>
                        <div class="col-12 col-lg-9">
                            <h2>Daftar Tunda Bayar Mahasiswa</h2>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="box">
                <div class="box-header with-border d-flex justify-content-between">
                    <div class="d-flex justify-content-start">
                        <!-- Modal trigger button -->
                        <form action="{{ route('prodi.report.tunda-bayar') }}" method="get" id="semesterForm">

                            <div class="row">
                                <div class="col-md-3 pt-2">
                                    <label for="semester_view" class="form-label">Semester</label>
                                </div>
                                <div class="col-md-8 ms-4">
                                    <select class="form-select" name="id_semester" id="id_semester"
                                        onchange="document.getElementById('semesterForm').submit();">
                                        <option value="" selected disabled>-- Pilih Semester --</option>
                                        @foreach ($semester as $p)
                                        <option value="{{$p->id_semester}}" @if (request()->get('id_semester') &&
                                            request()->get('id_semester') ==
                                            $p->id_semester) selected @elseif ($p->id_semester == $semester_aktif)
                                            selected @endif
                                            >{{$p->nama_semester}}</option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="box">
                                <div class="row g-0 py-2">
                                    @foreach ($count as $c)
                                    <div class="col-12 col-lg-3">
                                        <div class="box-body be-1 border-light">
                                            <div class="flexbox mb-1">
                                                <span>
                                                    <i class="fa {{$c['class']}} fs-30"></i><br>
                                                    {{$c['status']}}
                                                </span>
                                                <span class="text-primary fs-40">{{$c['jumlah']}}</span>
                                            </div>
                                            <div class="progress progress-xxs mt-10 mb-0">
                                                <div class="progress-bar" role="progressbar"
                                                    style="width: {{$c['persen']}}%; height: 5px;" aria-valuenow="{{$c['jumlah']}}"
                                                    aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive mt-5">
                        <table id="data" class="table table-hover table-bordered margin-top-10 w-p100">
                            <thead>
                               <tr>
                                  <th class="text-center align-middle">No</th>
                                  <th class="text-center align-middle">Semester</th>
                                  <th class="text-center align-middle">NIM</th>
                                  <th class="text-center align-middle">Nama Mahasiswa</th>
                                  <th class="text-center align-middle">Keterangan</th>
                                  <th class="text-center align-middle">Status</th>
                                  <th class="text-center align-middle">Terakhir Update</th>

                               </tr>
                            </thead>
                            <tbody>
                              @foreach ($data as $d)
                              <tr>
                                  <td class="text-center align-middle">{{$loop->iteration}}</td>
                                  <td class="text-center align-middle">{{$d->semester->nama_semester}}</td>
                                  <td class="text-center align-middle">{{$d->nim}}</td>
                                  <td class="text-start align-middle">{{$d->riwayat->nama_mahasiswa}}</td>
                                  <td class="text-start align-middle">{{$d->keterangan}}</td>
                                  <td class="text-center align-middle">
                                      @php
                                      switch ($d->status) {
                                          case 0:
                                              $text = 'warning';
                                              break;
                                          case 2:
                                          case 3:
                                              $text = 'primary';
                                              break;
                                          case 4:
                                              $text = 'success';
                                              break;
                                          default:
                                              $text = 'danger';
                                              break;
                                      }
                                  @endphp
                                  <span class="badge bg-{{$text}}">
                                      {{$d->status_text}}
                                  </span>

                                  </td>
                                  <td class="text-center align-middle">{{$d->terakhir_update}}</td>

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
<script src="{{asset('assets/vendor_components/select2/dist/js/select2.min.js')}}"></script>
<script>
    $(document).ready(function() {


    $('#data').DataTable({
        "paging": true,      // Menampilkan pagination
        "ordering": true,    // Mengizinkan pengurutan kolom
        "searching": true    // Menambahkan kotak pencarian
    });

    $('#id_semester').select2({
        width: '100%'
    });

});

</script>
@endpush
