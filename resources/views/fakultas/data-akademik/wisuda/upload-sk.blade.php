<div class="modal fade" id="uploadModal{{$d->id}}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">
                    Upload SK Yudisium
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('fakultas.wisuda.upload-sk-yudisium', $d->id)}}" method="post" id="upload-class-{{$d->id}}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="no_sk_yudisium" class="form-label">No SK Yudisium</label>
                            <input type="text" class="form-control" name="no_sk_yudisium" placeholder="Masukkan No SK Yudisium" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tgl_sk_yudisium" class="form-label">Tanggal SK Yudisium</label>
                            <input type="date" class="form-control" name="tgl_sk_yudisium" required>
                            <span class="badge badge-danger-light mt-2">* Gunakan tanggal tanda tangan SK Yudisium</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tgl_yudisium" class="form-label">Tanggal Yudisium</label>
                            <input type="date" class="form-control" name="tgl_yudisium" required>
                            <span class="badge badge-danger-light mt-2">* Gunakan tanggal kegiatan Yudisium</span>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="col-md-12">Cari atau Upload File SK Yudisium</label>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <select name="id" id="nama_file_{{$d->id}}" class="form-select">
                                        <option value="">-- Cari File SK Yudisium --</option>
                                    </select>
                                    <small class="text-muted mt-2">Cari file SK Yudisium yang sudah ada</small>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" value="1" id="uploadBaru{{$d->id}}" name="upload_baru">
                                        <label class="form-check-label" for="uploadBaru{{$d->id}}">
                                            Upload file SK Yudisium baru
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3" id="uploadFileSection{{$d->id}}" style="display:none;">
                            <label for="sk_yudisium_file_{{$d->id}}" class="form-label">File SK Yudisium (.pdf)</label>
                            <input type="file" class="form-control" name="sk_yudisium_file" id="sk_yudisium_file_{{$d->id}}"
                                aria-describedby="fileHelpId" accept=".pdf" />
                            <small class="text-muted">Upload file baru jika belum ada di daftar</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Tutup
                    </button>
                    <button type="submit" class="btn btn-danger">
                        Setuju
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('js')
<script src="{{asset('assets/vendor_components/datatable/datatables.min.js')}}"></script>
<script src="{{asset('assets/vendor_components/select2/dist/js/select2.min.js')}}"></script>
<script>
    $(function(){
        $('#uploadBaru{{$d->id}}').on('change', function(){
            if($(this).is(':checked')){
                $('#uploadFileSection{{$d->id}}').show();
                $('#sk_yudisium_file_{{$d->id}}').attr('required', true);
                $('#nama_file_{{$d->id}}').attr('disabled', true);
            } else {
                $('#uploadFileSection{{$d->id}}').hide();
                $('#sk_yudisium_file_{{$d->id}}').attr('required', false);
                $('#nama_file_{{$d->id}}').attr('disabled', false);
            }
        });
    });

    $('#uploadModal{{$d->id}}').on('shown.bs.modal', function () {
        $("#nama_file_{{$d->id}}").select2({
            dropdownParent: $('#uploadModal{{$d->id}}'),
            placeholder : '-- Masukan No SK Yudisium / Nama File --',
            width: '100%',
            minimumInputLength: 3,
            ajax: {
                url: "{{route('fakultas.wisuda.search-sk-yudisium')}}",
                type: "GET",
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term // search term
                    };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.nama_file + " ("+item.fakultas.nama_fakultas+")",
                                id: item.id
                            }
                        })
                    };
                },
            }
        });
    });
    
    $('#upload-class-{{$d->id}}').submit(function(e){
        e.preventDefault();
        swal({
            title: 'Upload SK Yudisium',
            text: "Apakah anda yakin upload file?",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Lanjutkan',
            cancelButtonText: 'Batal'
        }, function(isConfirm){
            if (isConfirm) {
                $('#upload-class-{{$d->id}}').unbind('submit').submit();
                $('#spinner').show();
            }
        });
    });
    
    
</script>
@endpush
