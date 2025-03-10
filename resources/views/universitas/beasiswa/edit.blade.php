<div class="modal fade" id="editModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">
                    Edit Data
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="editForm">
                @csrf
                @method('patch')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="id_registrasi_mahasiswa" class="form-label">Mahasiswa</label>
                            <input type="text" class="form-control" disabled id="edit_mahasiswa">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="id_jenis_beasiswa" class="form-label">Jenis Beasiswa</label>
                            <select name="id_jenis_beasiswa" id="edit_id_jenis_beasiswa" required class="form-select">
                                <option value="" disabled selected>-- Pilih Salah Satu --</option>
                                @foreach ($jenis as $j)
                                <option value="{{$j->id}}">{{$j->nama_jenis_beasiswa}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="id_pembiayaan" class="form-label">Pembiayaan</label>
                            <select name="id_pembiayaan" id="edit_id_pembiayaan" required class="form-select">
                                <option value="" disabled selected>-- Pilih Salah Satu --</option>
                                @foreach ($pembiayaan as $p)
                                <option value="{{$p->id_pembiayaan}}">{{$p->nama_pembiayaan}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="tanggal_mulai_beasiswa" class="form-label">Tanggal Mulai Beasiswa</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-calendar"></i></span>
                                <input type="text" class="form-control" name="tanggal_mulai_beasiswa" id="edit_tanggal_mulai_beasiswa" aria-describedby="helpId" placeholder="" required />
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="tanggal_akhir_beasiswa" class="form-label">Tanggal Akhir Beasiswa</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-calendar"></i></span>
                                <input type="text" class="form-control" name="tanggal_akhir_beasiswa" id="edit_tanggal_akhir_beasiswa" aria-describedby="helpId" placeholder="" required />
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="link_sk" class="form-label">Link SK</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon1"><i class="fa fa-link"></i></span>
                                <input type="text" class="form-control" name="link_sk" id="edit_link_sk" aria-describedby="helpId" placeholder="" required />
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Tutup
                    </button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

