<aside class="main-sidebar">
    <!-- sidebar-->
    <section class="sidebar position-relative">
        <div class="multinav">
            <div class="multinav-scroll" style="height: 100%;">
                <!-- sidebar menu-->
                <ul class="sidebar-menu" data-widget="tree">
                    <li class="header">Menu Utama</li>
                    <li class="{{request()->routeIs('prodi') ? 'active' : ''}}">
                        <a href="{{route('prodi')}}">
                            <i class="fa fa-th-large"><span class="path1"></span><span
                                    class="path2"></span></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="treeview {{request()->routeIs('prodi.data-master.*') ? 'active menu-open' : ''}}">
                        <a href="#">
                            <i span class="fa fa-database"><span class="path1"></span><span
                                    class="path2"></span></i>
                            <span>Data Master</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-right pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li class="{{request()->routeIs('prodi.data-master.detail-prodi') || request()->routeIs('prodi.data-master.detail-prodi.*') ? 'active' : ''}}">
                                <a href="{{route('prodi.data-master.detail-prodi')}}"><i class="icon-Commit"><span
                                            class="path1"></span><span class="path2"></span></i>Detail Prodi</a>
                            </li>
                            <li class="{{request()->routeIs('prodi.data-master.dosen') ? 'active' : ''}}">
                                <a href="{{route('prodi.data-master.dosen')}}"><i class="icon-Commit"><span
                                            class="path1"></span><span class="path2"></span></i>Dosen</a>
                            </li>
                            <li class="{{request()->routeIs('prodi.data-master.mahasiswa') ? 'active' : ''}}">
                                <a href="{{route('prodi.data-master.mahasiswa')}}"><i class="icon-Commit"><span
                                class="path1"></span><span class="path2"></span></i>Mahasiswa</a>
                            </li>
                            <li class="{{request()->routeIs('prodi.data-master.kurikulum') || request()->routeIs('prodi.data-master.kurikulum.*') ?  'active' : ''}}">
                                <a href="{{route('prodi.data-master.kurikulum')}}"><i class="icon-Commit"><span
                                class="path1"></span><span class="path2"></span></i>Kurikulum</a>
                            </li>
                            <li class="{{request()->routeIs('prodi.data-master.mata-kuliah') || request()->routeIs('prodi.data-master.mata-kuliah.*') ? 'active' : ''}}">
                                <a href="{{route('prodi.data-master.mata-kuliah')}}"><i class="icon-Commit"><span
                                class="path1"></span><span class="path2"></span></i>Mata Kuliah</a>
                            </li>
                            <li class="{{request()->routeIs('prodi.data-master.matkul-merdeka') || request()->routeIs('prodi.data-master.matkul-merdeka.*') ? 'active' : ''}}">
                                <a href="{{route('prodi.data-master.matkul-merdeka')}}"><i class="icon-Commit"><span
                                class="path1"></span><span class="path2"></span></i>MK MBKM</a>
                            </li>
                            <li class="{{request()->routeIs('prodi.data-master.ruang-perkuliahan') || request()->routeIs('prodi.data-master.ruang-perkuliahan.*') ? 'active' : ''}}">
                                <a href="{{route('prodi.data-master.ruang-perkuliahan')}}"><i class="icon-Commit"><span
                                class="path1"></span><span class="path2"></span></i>Ruang Perkuliahan</a>
                            </li>
                            <!-- <li class="{{request()->routeIs('prodi.data-master.ruang-perkuliahan') ? 'active' : ''}}">
                                <a href="{{route('prodi.data-master.ruang-perkuliahan')}}"><i class="icon-Commit"><span
                                class="path1"></span><span class="path2"></span></i>Pengumuman</a>
                            </li> -->
                        </ul>
                    </li>
                    <li class="treeview {{request()->routeIs('prodi.data-akademik.*') ? 'active menu-open' : ''}}">
                        <a href="#">
                            <i span class="fa fa-graduation-cap"><span class="path1"></span><span
                                    class="path2"></span></i>
                            <span>Data Akademik</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-right pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li class="{{request()->routeIs('prodi.data-akademik.kelas-penjadwalan') | request()->routeIs('prodi.data-akademik.kelas-penjadwalan.*') ? 'active' : ''}}">
                                <a href="{{route('prodi.data-akademik.kelas-penjadwalan')}}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Kelas dan Penjadwalan</a>
                            </li>
                            <li class="{{request()->routeIs('prodi.data-akademik.krs') || request()->routeIs('prodi.data-akademik.krs.*') ? 'active' : ''}}">
                                <a href="{{route('prodi.data-akademik.krs')}}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Kartu Rencana Studi</a>
                            </li>
                            <li class="{{request()->routeIs('prodi.data-akademik.khs') || request()->routeIs('prodi.data-akademik.khs.*') ? 'active' : ''}}">
                                <a href="{{route('prodi.data-akademik.khs')}}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Kartu Hasil Studi</a>
                            </li>
                            <li class="{{request()->routeIs('prodi.data-akademik.tugas-akhir') || request()->routeIs('prodi.data-akademik.tugas-akhir.*') ? 'active' : ''}}">
                                <a href="{{route('prodi.data-akademik.tugas-akhir')}}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Aktivitas @if(Auth::user()->fk->nama_jenjang_pendidikan == 'S1')Skripsi
                                    @elseif (Auth::user()->fk->nama_jenjang_pendidikan == 'S2')Tesis
                                    @elseif (Auth::user()->fk->nama_jenjang_pendidikan == 'S3')Disertasi
                                    @else Tugas Akhir
                                    @endif
                                     Mhs</a>
                            </li>
                            <li class="{{request()->routeIs('prodi.data-akademik.non-tugas-akhir') || request()->routeIs('prodi.data-akademik.non-tugas-akhir.*') ? 'active' : ''}}">
                                <a href="{{route('prodi.data-akademik.non-tugas-akhir')}}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Aktivitas Non @if(Auth::user()->fk->nama_jenjang_pendidikan == 'S1')Skripsi
                                    @elseif (Auth::user()->fk->nama_jenjang_pendidikan == 'S2')Tesis
                                    @elseif (Auth::user()->fk->nama_jenjang_pendidikan == 'S3')Disertasi
                                    @else Tugas Akhir
                                    @endif</a>
                            </li>
                            <li class="{{request()->routeIs('prodi.data-akademik.sidang-mahasiswa') || request()->routeIs('prodi.data-akademik.sidang-mahasiswa.*') ? 'active' : ''}}">
                                <a href="{{route('prodi.data-akademik.sidang-mahasiswa')}}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Sidang Mahasiswa</a>
                            </li>
                            <li class="{{request()->routeIs('prodi.data-akademik.nilai-transfer-rpl') || request()->routeIs('prodi.data-akademik.nilai-transfer-rpl.*') ? 'active' : ''}}">
                                <a href="{{route('prodi.data-akademik.nilai-transfer-rpl')}}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Nilai Transfer Pendidikan</a>
                            </li>
                        </ul>
                    </li>
                    <li class="treeview {{request()->routeIs('prodi.data-aktivitas.*') ? 'active menu-open' : ''}}">
                        <a href="#">
                            <i span class="fa fa-trophy"><span class="path1"></span><span
                                    class="path2"></span></i>
                            <span>Data Aktivitas</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-right pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li class="{{request()->routeIs('prodi.data-aktivitas.aktivitas-mahasiswa.index') || request()->routeIs('prodi.data-aktivitas.aktivitas-mahasiswa.*')
                                ? 'active' : ''}}">
                                <a href="{{route('prodi.data-aktivitas.aktivitas-mahasiswa.index')}}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Konversi Aktivitas</a>
                            </li>
                            <li class="{{request()->routeIs('prodi.data-aktivitas.aktivitas-pa') || request()->routeIs('prodi.data-aktivitas.aktivitas-pa.*') ? 'active' : ''}}">
                                <a href="{{route('prodi.data-aktivitas.aktivitas-pa')}}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Aktivitas PA</a>
                            </li>
                        </ul>
                    </li>
                    <li class="{{request()->routeIs('prodi.data-lulusan.index') || request()->routeIs('prodi.data-lulusan.*')
                                ? 'active' : ''}}">
                        <a href="{{route('prodi.data-lulusan.index')}}"><i class="fa fa-university"><span class="path1"></span><span class="path2"></span></i> Ajuan Wisuda</a>
                    </li>
                    <li class="header">Report & Monitoring</li>
                    <li class="treeview {{request()->routeIs('prodi.report.*') ? 'active menu-open' : ''}}">
                        <a href="#">
                            <i span class="fa fa-file-text-o"><span class="path1"></span><span
                                    class="path2"></span></i>
                            <span>Report</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-right pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li class="{{request()->routeIs('prodi.report.cuti-mahasiswa') ? 'active' : ''}}"><a href="{{route('prodi.report.cuti-mahasiswa')}}"><i class="icon-Commit"><span
                                class="path1"></span><span class="path2"></span></i>Cuti Mahasiswa</a>
                            </li>
                            <li class="{{request()->routeIs('prodi.report.tunda-bayar') ? 'active' : ''}}"><a href="{{route('prodi.report.tunda-bayar')}}"><i class="icon-Commit"><span
                                class="path1"></span><span class="path2"></span></i>Tunda bayar</a>
                            </li>
                            <li class="{{request()->routeIs('prodi.report.aktivitas-penelitian') ? 'active' : ''}}">
                                <a href="{{route('prodi.report.aktivitas-penelitian')}}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Penelitian Mahasiswa</a>
                            </li>
                            <li class="{{request()->routeIs('prodi.report.aktivitas-lomba') ? 'active' : ''}}">
                                <a href="{{route('prodi.report.aktivitas-lomba')}}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Prestasi Mahasiswa</a>
                            </li>
                        </ul>
                    </li>
                    <li class="treeview {{request()->routeIs('prodi.monitoring.*') ? 'active menu-open' : ''}}">
                        <a href="#">
                            <i span class="fa fa-television"><span class="path1"></span><span
                                    class="path2"></span></i>
                            <span>Monitoring</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-right pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">
                            <li class="{{request()->routeIs('prodi.monitoring.status-mahasiswa') || request()->routeIs('prodi.monitoring.status-mahasiswa.*') ? 'active' : ''}}">
                                <a href="{{route('prodi.monitoring.status-mahasiswa')}}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Status Mahasiswa</a>
                            </li>
                            <li class="{{request()->routeIs('prodi.monitoring.entry-nilai') || request()->routeIs('prodi.monitoring.entry-nilai.*') ? 'active' : ''}}">
                                <a href="{{route('prodi.monitoring.entry-nilai')}}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Entry Nilai Dosen</a>
                            </li>
                            <li class="{{request()->routeIs('prodi.monitoring.pengajaran-dosen') ? 'active' : ''}}">
                                <a href="{{route('prodi.monitoring.pengajaran-dosen')}}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Pengajaran Dosen</a>
                            </li>
                            <li class="{{request()->routeIs('prodi.monitoring.pengisian-krs') || request()->routeIs('prodi.monitoring.pengisian-krs.*') ? 'active' : ''}}">
                                <a href="{{route('prodi.monitoring.pengisian-krs')}}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Pengisian KRS</a>
                            </li>
                            <li class="{{request()->routeIs('prodi.monitoring.lulus-do') || request()->routeIs('prodi.monitoring.lulus-do.*') ? 'active' : ''}}">
                                <a href="{{route('prodi.monitoring.lulus-do')}}"><i class="icon-Commit"><span class="path1"></span><span class="path2"></span></i>Lulus DO</a>
                            </li>
                        </ul>
                    </li>

                    <li class="header">Bantuan</li>
                    <li class="{{request()->routeIs('prodi.bantuan.ganti-password') ? 'active' : ''}}">
                        <a href="{{route('prodi.bantuan.ganti-password')}}">
                            <i class="fa fa-key"><span class="path1"></span><span class="path2"></span></i>
                            <span>Ganti Password</span>
                        </a>
                    </li>
                    <li>
                        <a href="http://repository.unsri.ac.id/id/eprint/155513" target="_blank">
                            <i class="fa fa-question"><span class="path1"></span><span class="path2"></span></i>
                            <span>Panduan Aplikasi</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </section>
    <div class="sidebar-footer text-end">
        <a href="javascript:void(0)" class="link" data-bs-toggle="tooltip" title="Settings"><span
                class="icon-Settings-2"></span></a>
        <a href="{{ route('logout') }}" class="link" data-bs-toggle="tooltip" title="Logout" onclick="event.preventDefault();
        document.getElementById('logout-form').submit();"><span
                class="icon-Lock-overturning"><span class="path1"></span><span class="path2"></span></span></a>
    </div>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>
</aside>
