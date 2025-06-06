<div class="modal fade" id="filter-button" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="filterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterTitle">
                    {{-- filter icon fa --}}
                    <i class="fa fa-filter"></i> Filter
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('prodi.data-master.mahasiswa')}}" method="get">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="angkatan" class="form-label">Angkatan</label>
                            <select multiple class="form-select" name="angkatan[]" id="angkatanFilter">
                                <option value="">
                                    -- Pilih Angkatan --
                                </option>
                                @foreach ($angkatan as $p)
                                <option value="{{$p->angkatan_raw}}" {{ in_array($p->angkatan_raw, old('angkatan',
                                    request()->get('angkatan', []))) ? 'selected' : '' }}>
                                    {{$p->angkatan_raw}}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        {{-- <div class="col-md-12 mb-3">
                            <label for="status" class="form-label">Status Mahasiswa</label>
                            <select multiple class="form-select" name="status[]" id="statusFilter">
                                <option value="">
                                    -- Pilih Status Mahasiswa --
                                </option>
                                @foreach ($status as $s)
                                <option value="{{$s['id']}}" {{ in_array($s['id'], old('status',
                                    request()->get('status', []))) ? 'selected' : '' }}>
                                    {{$s['nama']}}
                                </option>
                                @endforeach
                            </select>
                        </div> --}}
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Tutup
                    </button>
                    <button type="submit" class="btn btn-primary" id="apply-filter">Apply</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('js')

<script>
    $(document).ready(function () {
        $('#angkatanFilter').select2({
            placeholder: '-- Pilih Angkatan -- ',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#filter-button')
        });
        $('#statusFilter').select2({
            placeholder: '-- Pilih Status Mahasiswa -- ',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#filter-button')
        });
        $('#tahun_angkatan').select2({
            placeholder: '-- Pilih Angkatan -- ',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#setAngkatanModal')
        });
    });

</script>
@endpush
