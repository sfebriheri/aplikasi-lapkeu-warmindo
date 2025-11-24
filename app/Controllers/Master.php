<?php

namespace App\Controllers;

use App\Models\MasterModel;
use App\Models\AdminModel;
use App\Models\LabarugiModel;
use CodeIgniter\Controller;

class Master extends BaseController
{
    protected $masterModel;
    protected $adminModel;
    protected $labarugiModel;
    protected $validation;
    protected $session;
    protected $db;

    public function __construct()
    {
        $this->masterModel = new MasterModel();
        $this->adminModel = new AdminModel();
        $this->labarugiModel = new LabarugiModel();
        $this->validation = \Config\Services::validation();
        $this->session = \Config\Services::session();
        $this->db = \Config\Database::connect();
        helper(['form', 'url']);
    }

    // DAFTAR AKUN

    public function index()
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        }

        $data['judul'] = 'Daftar Akun';
        $data['active'] = 'active';
        $data['daftar_akun'] = $this->masterModel->tampilDaftarakun();
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();

        if ($this->request->getPost('katakunci')) {
            $data['daftar_akun'] = $this->masterModel->cariDaftarakun();
        }

        return view('templates/dash_header', $data)
            . view('templates/adm_sidebar', $data)
            . view('templates/adm_header', $data)
            . view('admin/master-data/daftar_akun', $data)
            . view('templates/adm_footer')
            . view('templates/dash_footer');
    }

    public function tambah_daftarakun()
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        } else {
            if ($this->session->get('role_id') == '1') {
                return redirect()->to(base_url('admin'));
            }
        }

        $data['judul'] = 'Tambah Akun';
        $data['active'] = 'active';
        $data['kode_al'] = $this->masterModel->kodeAl();
        $data['kode_at'] = $this->masterModel->kodeAt();
        $data['kode_k'] = $this->masterModel->kodeK();
        $data['kode_p'] = $this->masterModel->kodeP();
        $data['kode_ek'] = $this->masterModel->kodeEk();
        $data['kode_b'] = $this->masterModel->kodeB();
        $data['kode_pjk'] = $this->masterModel->kodePjk();
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();

        $rules = [
            'kode_akun' => 'required',
            'akun' => 'required'
        ];

        if (!$this->validate($rules)) {
            $data['validation'] = $this->validation;
            return view('templates/dash_header', $data)
                . view('templates/adm_sidebar', $data)
                . view('templates/adm_header', $data)
                . view('admin/master-data/daftar_akun/tambah_daftarakun', $data)
                . view('templates/adm_footer')
                . view('templates/dash_footer');
        } else {
            $this->session->setFlashdata('pesan_sukses', 'Ditambahkan');
            $this->masterModel->tambahDaftarAkun();
            return redirect()->to(base_url('master'));
        }
    }

    public function hapusDaftarAkun($kode_akun)
    {
        $this->masterModel->hapusDaftarAkun($kode_akun);
        $this->session->setFlashdata('pesan_sukses', 'Dihapus');
        return redirect()->to(base_url('master'));
    }

    public function ubahDaftarAkun($kode_akun)
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        } else {
            if ($this->session->get('role_id') == '1') {
                return redirect()->to(base_url('admin'));
            }
        }

        $data['judul'] = 'Tambah Akun';
        $data['active'] = 'active';
        $data['daftar_akun'] = $this->masterModel->getDaftarAkunById($kode_akun);
        $data['pos_nr'] = ['Aset Lancar', 'Aset Tetap', 'Kewajiban', 'Ekuitas'];
        $data['pos_lr'] = ['Pendapatan', 'Beban', 'Pajak'];
        $data['kode_al'] = $this->masterModel->kodeAl();
        $data['kode_at'] = $this->masterModel->kodeAt();
        $data['kode_k'] = $this->masterModel->kodeK();
        $data['kode_p'] = $this->masterModel->kodeP();
        $data['kode_ek'] = $this->masterModel->kodeEk();
        $data['kode_b'] = $this->masterModel->kodeB();
        $data['kode_pjk'] = $this->masterModel->kodePjk();
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();
        $data['pos_laporan'] = ['Laporan Posisi Keuangan', 'Laba Rugi'];

        $rules = [
            'kode_akun' => 'required',
            'akun' => 'required'
        ];

        if (!$this->validate($rules)) {
            $data['validation'] = $this->validation;
            return view('templates/dash_header', $data)
                . view('templates/adm_sidebar', $data)
                . view('templates/adm_header', $data)
                . view('admin/master-data/daftar_akun/ubah_daftarakun', $data)
                . view('templates/adm_footer')
                . view('templates/dash_footer');
        } else {
            $this->session->setFlashdata('pesan_sukses', 'Diubah');
            $this->masterModel->ubahDaftarAkun();
            return redirect()->to(base_url('master'));
        }
    }

    public function cetak_daftarakun()
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        } else {
            if ($this->session->get('role_id') == '1') {
                return redirect()->to(base_url('admin'));
            }
        }

        $data['daftar_akun'] = $this->masterModel->tampilDaftarakun();

        $dompdf = new \Dompdf\Dompdf();
        $html = view('laporan/laporan_daftarakun', $data);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream("laporan_daftarakun.pdf", array('Attachment' => 0));
    }

    // END DAFTAR AKUN

    // SALDO AWAL

    public function saldo_awal()
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        } else {
            if ($this->session->get('role_id') == '1') {
                return redirect()->to(base_url('admin'));
            }
        }

        $data['judul'] = 'Master Data';
        $data['active'] = 'active';
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();

        if ($this->request->getPost('katakunci')) {
            $katakunci = $this->request->getPost('katakunci', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $builder = $this->db->table('saldo_awal');
            $builder->like('kode_akun', $katakunci);
            $builder->orLike('tanggal_transaksi', $katakunci);
            $builder->orLike('pos_laporan', $katakunci);
            $builder->orLike('debit', $katakunci);
            $builder->orLike('kredit', $katakunci);
            $builder->orLike('akun', $katakunci);
            $builder->orLike('pos_akun', $katakunci);
            $builder->orLike('keterangan', $katakunci);
            $builder->orderBy('tanggal_transaksi', 'ASC');
            $data['bukber'] = $builder->get()->getResultArray();
        } else {
            $data['bukber'] = $this->db->table('daftar_akun')
                ->where('akun IS NOT NULL')
                ->get()
                ->getResultArray();
        }

        return view('templates/dash_header', $data)
            . view('templates/adm_sidebar', $data)
            . view('templates/adm_header', $data)
            . view('admin/master-data/saldo_awal/saldoawal', $data)
            . view('templates/adm_footer')
            . view('templates/dash_footer');
    }

    public function tambah_saldoawal()
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        } else {
            if ($this->session->get('role_id') == '1') {
                return redirect()->to(base_url('admin'));
            }
        }

        $data['judul'] = 'Saldo Awal';
        $data['active'] = 'active';
        $data['dd_kodeakun'] = $this->adminModel->ambilDropdown();
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();
        $data['bukber'] = $this->db->table('daftar_akun')
            ->where('akun IS NOT NULL')
            ->get()
            ->getResultArray();

        $rules = [
            'kode_akun' => 'required',
            'akun' => 'required',
            'keterangan' => 'required',
            'pos_laporan' => 'required'
        ];

        if (!$this->validate($rules)) {
            $data['validation'] = $this->validation;
            return view('templates/dash_header', $data)
                . view('templates/adm_sidebar', $data)
                . view('templates/adm_header', $data)
                . view('admin/master-data/saldo_awal/tambah_saldoawal', $data)
                . view('templates/adm_footer')
                . view('templates/dash_footer');
        } else {
            $this->session->setFlashdata('pesan_sukses', 'Ditambahkan');
            $this->masterModel->tambahSaldoawal();
            return redirect()->to(base_url('master/saldo_awal'));
        }
    }

    public function hapusSaldoAwal($id)
    {
        $this->masterModel->hapusSaldoAwal($id);
        $this->session->setFlashdata('pesan_sukses', 'Dihapus');
        return redirect()->to(base_url('master/saldo_awal'));
    }

    public function ubahSaldoAwal($id)
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        } else {
            if ($this->session->get('role_id') == '1') {
                return redirect()->to(base_url('admin'));
            }
        }

        $data['judul'] = 'Ubah Saldo Awal';
        $data['active'] = 'active';
        $data['saldo_awal'] = $this->masterModel->getSaldoAwalById($id);
        $data['dd_kodeakun'] = $this->adminModel->ambilDropdown();
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();
        $data['pos_laporan'] = ['Laporan Posisi Keuangan', 'Laba Rugi'];

        $rules = [
            'kode_akun' => 'required',
            'akun' => 'required'
        ];

        if (!$this->validate($rules)) {
            $data['validation'] = $this->validation;
            return view('templates/dash_header', $data)
                . view('templates/adm_sidebar', $data)
                . view('templates/adm_header', $data)
                . view('admin/master-data/saldo_awal/ubah_saldoawal', $data)
                . view('templates/adm_footer')
                . view('templates/dash_footer');
        } else {
            $this->session->setFlashdata('pesan_sukses', 'Diubah');
            $this->masterModel->ubahSaldoAwal();
            return redirect()->to(base_url('master/saldo_awal'));
        }
    }

    public function ubah_saldoawal()
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        } else {
            if ($this->session->get('role_id') == '1') {
                return redirect()->to(base_url('admin'));
            }
        }

        $this->session->setFlashdata('pesan_sukses', 'Diubah');
        $this->masterModel->ubahSaldoAwal();
        return redirect()->to(base_url('master/saldo_awal'));
    }

    // END SALDO AWAL

    // START KAS MASUK DAN KELUAR

    public function kas_masuk()
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        }

        $data['judul'] = 'Kas Masuk';
        $data['active'] = 'active';
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();
        $data['nama_bulan'] = date('m');
        $data['tahun'] = date('y');
        $data['dd_bulan'] = $this->labarugiModel->ddBulan();

        if ($this->request->getPost('tanggal_awal')) {
            $data['t_aw'] = $this->request->getPost('tanggal_awal');
            $data['t_ak'] = $this->request->getPost('tanggal_akhir');
        } elseif ($this->request->getPost('tahun_post') && $this->request->getPost('bulan_post')) {
            $data['tahun_post'] = $this->request->getPost('tahun_post');
            $bulan = $this->request->getPost('bulan_post');
            $data['nama_bulan'] = date("F", strtotime(date("Y-" . $bulan . "-d")));
        } elseif ($this->request->getPost('tahun_post')) {
            $data['tahun'] = $this->request->getPost('tahun_post');
        } else {
            $data['tahun'] = date("Y");
            $data['nama_bulan'] = date("F", strtotime(date("Y-m-d")));
        }

        $data['kas_masuk'] = $this->masterModel->kas();

        return view('templates/dash_header', $data)
            . view('templates/adm_sidebar', $data)
            . view('templates/adm_header', $data)
            . view('admin/master-data/laporan/kasmasuk', $data)
            . view('templates/adm_footer')
            . view('templates/dash_footer');
    }

    public function cetakkasmasuk()
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        }

        $data['nama_bulan'] = date('m');
        $data['tahun'] = date('y');

        if ($this->request->getPost('tanggal_awal')) {
            $data['t_aw'] = $this->request->getPost('tanggal_awal');
            $data['t_ak'] = $this->request->getPost('tanggal_akhir');
        } elseif ($this->request->getPost('tahun_post') && $this->request->getPost('bulan_post')) {
            $data['tahun_post'] = $this->request->getPost('tahun_post');
            $bulan = $this->request->getPost('bulan_post');
            $data['nama_bulan'] = date("F", strtotime(date("Y-" . $bulan . "-d")));
        } elseif ($this->request->getPost('tahun_post')) {
            $data['tahun'] = $this->request->getPost('tahun_post');
        } else {
            $data['tahun'] = date("Y");
            $data['nama_bulan'] = date("F", strtotime(date("Y-m-d")));
        }

        $data['kas_masuk'] = $this->masterModel->kas();

        $dompdf = new \Dompdf\Dompdf();
        $html = view('laporan/laporan_kasmasuk', $data);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream("laporan_kasmasuk.pdf", array('Attachment' => 0));
    }

    public function kas_keluar()
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        }

        $data['judul'] = 'Kas Keluar';
        $data['active'] = 'active';
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();
        $data['nama_bulan'] = date('m');
        $data['tahun'] = date('y');
        $data['dd_bulan'] = $this->labarugiModel->ddBulan();

        if ($this->request->getPost('tanggal_awal')) {
            $data['t_aw'] = $this->request->getPost('tanggal_awal');
            $data['t_ak'] = $this->request->getPost('tanggal_akhir');
        } elseif ($this->request->getPost('tahun_post') && $this->request->getPost('bulan_post')) {
            $data['tahun_post'] = $this->request->getPost('tahun_post');
            $bulan = $this->request->getPost('bulan_post');
            $data['nama_bulan'] = date("F", strtotime(date("Y-" . $bulan . "-d")));
        } elseif ($this->request->getPost('tahun_post')) {
            $data['tahun'] = $this->request->getPost('tahun_post');
        } else {
            $data['tahun'] = date("Y");
            $data['nama_bulan'] = date("F", strtotime(date("Y-m-d")));
        }

        $data['kas_keluar'] = $this->masterModel->kas();

        return view('templates/dash_header', $data)
            . view('templates/adm_sidebar', $data)
            . view('templates/adm_header', $data)
            . view('admin/master-data/laporan/kaskeluar', $data)
            . view('templates/adm_footer')
            . view('templates/dash_footer');
    }

    public function cetakkaskeluar()
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        }

        $data['nama_bulan'] = date('m');
        $data['tahun'] = date('y');

        if ($this->request->getPost('tanggal_awal')) {
            $data['t_aw'] = $this->request->getPost('tanggal_awal');
            $data['t_ak'] = $this->request->getPost('tanggal_akhir');
        } elseif ($this->request->getPost('tahun_post') && $this->request->getPost('bulan_post')) {
            $data['tahun_post'] = $this->request->getPost('tahun_post');
            $bulan = $this->request->getPost('bulan_post');
            $data['nama_bulan'] = date("F", strtotime(date("Y-" . $bulan . "-d")));
        } elseif ($this->request->getPost('tahun_post')) {
            $data['tahun'] = $this->request->getPost('tahun_post');
        } else {
            $data['tahun'] = date("Y");
            $data['nama_bulan'] = date("F", strtotime(date("Y-m-d")));
        }

        $data['kas_keluar'] = $this->masterModel->kas();

        $dompdf = new \Dompdf\Dompdf();
        $html = view('laporan/laporan_kaskeluar', $data);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream("laporan_kaskeluar.pdf", array('Attachment' => 0));
    }
}
