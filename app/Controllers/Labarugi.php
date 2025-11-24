<?php

namespace App\Controllers;

use App\Models\LabarugiModel;
use App\Models\AdminModel;
use CodeIgniter\Controller;

class Labarugi extends BaseController
{
    protected $labarugiModel;
    protected $adminModel;
    protected $validation;
    protected $session;
    protected $db;

    public function __construct()
    {
        $this->labarugiModel = new LabarugiModel();
        $this->adminModel = new AdminModel();
        $this->validation = \Config\Services::validation();
        $this->session = \Config\Services::session();
        $this->db = \Config\Database::connect();
        helper(['form', 'url']);

        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        }
    }

    public function index()
    {
        $data['judul'] = 'Laba Rugi';
        $data['active'] = 'active';
        $data['p_akun'] = $this->labarugiModel->tampilPosakun();
        $data['pos_akun'] = ['Pendapatan', 'Beban'];
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();
        $data['dd_bulan'] = $this->labarugiModel->ddBulan();

        if ($this->request->getPost('tanggal_awal')) {
            $tgl_awal = $this->request->getPost('tanggal_awal');
            $tgl_akhir = $this->request->getPost('tanggal_akhir');
            $tahun_jika = date("Y", strtotime($tgl_awal));
            $bulan = date("m", strtotime($tgl_awal));
            $data['tahun_jika'] = $tahun_jika;

            if ($this->request->getPost('tanggal_awal') == date($tahun_jika . '-01-01')) {
                $data['bulan'] = date('m', strtotime($tgl_awal));
                $data['tahun'] = date('Y', strtotime($tgl_awal));
                $data['dk_awal_k'] = $this->request->getPost('tanggal_awal');
                $data['dk_akhir_k'] = $this->request->getPost('tanggal_akhir');
            } elseif (date("m", strtotime($this->request->getPost('tanggal_awal'))) == '1') {
                $data['bulan'] = date('m', strtotime($tgl_awal));
                $data_bulan = $tgl_awal;
                $data['tahun'] = date('Y', strtotime($tgl_awal));
                $data['dk_awal_k'] = date("Y-m-d", strtotime("first day of $data_bulan"));
                $data['dk_akhir_k'] = date("Y-m-d", strtotime("$tgl_awal -1 day"));
                $data['dk_awal_k1'] = $this->request->getPost('tanggal_awal');
                $data['dk_akhir_k1'] = $this->request->getPost('tanggal_akhir');
            } else {
                $data['bulan'] = date('m', strtotime($tgl_awal));
                $data_bulan = $data['bulan'];
                $data_kurang = $data['bulan'] - 1;
                $data['tahun'] = date('Y', strtotime($tgl_awal));
                $data['dk_awal_k'] = date("Y-m-d", strtotime("first day of $data_bulan-$data_kurang"));
                $data['dk_akhir_k'] = date("Y-m-d", strtotime("$tgl_awal -1 day"));
                $data['dk_awal_k1'] = $this->request->getPost('tanggal_awal');
                $data['dk_akhir_k1'] = $this->request->getPost('tanggal_akhir');
            }
        } elseif ($this->request->getPost('tahun_post') && $this->request->getPost('bulan_post')) {
            $data['tahun'] = $this->request->getPost('tahun_post');
            $data['bulan'] = $this->request->getPost('bulan_post');
            $tahun_post = $this->request->getPost('tahun_post');
            $bulan_post = $this->request->getPost('bulan_post');
            $bulan2 = $bulan_post - 1;
            $tanggal_inti = date($tahun_post . "-" . $bulan_post . "-d");
            $data['nama_bulan'] = date("F", strtotime(date("Y") . "-" . $bulan_post . "-01"));
            $data['dk_awal_k'] = date("Y-m-d", strtotime("$tanggal_inti first day of -$bulan2 month"));
            $data['dk_akhir_k'] = date("Y-m-d", strtotime("$tanggal_inti last day of -1 month"));
        } elseif ($this->request->getPost('tahun_post')) {
            $data['bulan'] = 1;
            $data['tahun'] = $this->request->getPost('tahun_post');
        } else {
            $data['bulan'] = date('m');
            $data['tahun'] = date('Y');
            $bulan = date('m');
            $tahun = date('Y');
            $data['nama_bulan'] = date("F", strtotime(date("Y") . "-" . $bulan . "-01"));

            if ($data['bulan'] != 1) {
                $bulan2 = $bulan - 1;
                $tanggal_inti = date($tahun . "-" . $bulan . "-d");
                $data['dk_awal_k'] = date("Y-m-d", strtotime("$tanggal_inti first day of -$bulan2 month"));
                $data['dk_akhir_k'] = date("Y-m-d", strtotime("$tanggal_inti last day of -1 month"));
            }
        }

        return view('templates/dash_header', $data)
            . view('templates/adm_sidebar', $data)
            . view('templates/adm_header', $data)
            . view('admin/master-data/laporan/laba_rugi', $data)
            . view('templates/adm_footer')
            . view('templates/dash_footer');
    }

    public function cetak_lr()
    {
        $data['p_akun'] = $this->labarugiModel->tampilPosakun();
        $data['pos_akun'] = ['Pendapatan', 'Beban', 'Pajak'];

        // Same logic as index
        if ($this->request->getPost('tanggal_awal')) {
            $tgl_awal = $this->request->getPost('tanggal_awal');
            $tgl_akhir = $this->request->getPost('tanggal_akhir');
            $tahun_jika = date("Y", strtotime($tgl_awal));
            $bulan = date("m", strtotime($tgl_awal));
            $data['tahun_jika'] = $tahun_jika;

            if ($this->request->getPost('tanggal_awal') == date($tahun_jika . '-01-01')) {
                $data['bulan'] = date('m', strtotime($tgl_awal));
                $data['tahun'] = date('Y', strtotime($tgl_awal));
                $data['dk_awal_k'] = $this->request->getPost('tanggal_awal');
                $data['dk_akhir_k'] = $this->request->getPost('tanggal_akhir');
            } elseif (date("m", strtotime($this->request->getPost('tanggal_awal'))) == '1') {
                $data['bulan'] = date('m', strtotime($tgl_awal));
                $data_bulan = $tgl_awal;
                $data['tahun'] = date('Y', strtotime($tgl_awal));
                $data['dk_awal_k'] = date("Y-m-d", strtotime("first day of $data_bulan"));
                $data['dk_akhir_k'] = date("Y-m-d", strtotime("$tgl_awal -1 day"));
                $data['dk_awal_k1'] = $this->request->getPost('tanggal_awal');
                $data['dk_akhir_k1'] = $this->request->getPost('tanggal_akhir');
            } else {
                $data['bulan'] = date('m', strtotime($tgl_awal));
                $data_bulan = $data['bulan'];
                $data_kurang = $data['bulan'] - 1;
                $data['tahun'] = date('Y', strtotime($tgl_awal));
                $data['dk_awal_k'] = date("Y-m-d", strtotime("first day of $data_bulan-$data_kurang"));
                $data['dk_akhir_k'] = date("Y-m-d", strtotime("$tgl_awal -1 day"));
                $data['dk_awal_k1'] = $this->request->getPost('tanggal_awal');
                $data['dk_akhir_k1'] = $this->request->getPost('tanggal_akhir');
            }
        } elseif ($this->request->getPost('tahun_post') && $this->request->getPost('bulan_post')) {
            $data['tahun'] = $this->request->getPost('tahun_post');
            $data['bulan'] = $this->request->getPost('bulan_post');
            $tahun_post = $this->request->getPost('tahun_post');
            $bulan_post = $this->request->getPost('bulan_post');
            $bulan2 = $bulan_post - 1;
            $tanggal_inti = date($tahun_post . "-" . $bulan_post . "-d");
            $data['nama_bulan'] = date("F", strtotime(date("Y") . "-" . $bulan_post . "-01"));
            $data['dk_awal_k'] = date("Y-m-d", strtotime("$tanggal_inti first day of -$bulan2 month"));
            $data['dk_akhir_k'] = date("Y-m-d", strtotime("$tanggal_inti last day of -1 month"));
        } elseif ($this->request->getPost('tahun_post')) {
            $data['bulan'] = 1;
            $data['tahun'] = $this->request->getPost('tahun_post');
        } else {
            $data['bulan'] = date('m');
            $data['tahun'] = date('Y');
            $bulan = date('m');
            $tahun = date('Y');
            $data['nama_bulan'] = date("F", strtotime(date("Y") . "-" . $bulan . "-01"));

            if ($data['bulan'] != 1) {
                $bulan2 = $bulan - 1;
                $tanggal_inti = date($tahun . "-" . $bulan . "-d");
                $data['dk_awal_k'] = date("Y-m-d", strtotime("$tanggal_inti first day of -$bulan2 month"));
                $data['dk_akhir_k'] = date("Y-m-d", strtotime("$tanggal_inti last day of -1 month"));
            }
        }

        $dompdf = new \Dompdf\Dompdf();
        $html = view('laporan/laporan_labarugi', $data);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream("laporan_labarugi.pdf", array('Attachment' => 0));
    }

    public function jurnal_penutup()
    {
        $data['judul'] = 'Laba Rugi';
        $data['active'] = 'active';
        $data['p_akun'] = $this->labarugiModel->tampilPosakun();
        $data['pos_akun'] = ['Pendapatan', 'Beban', 'Saldo Laba'];
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();
        $data['dd_bulan'] = $this->labarugiModel->ddBulan();

        // Similar logic to index but defaulting to full year
        if ($this->request->getPost('tanggal_awal')) {
            $tgl_awal = $this->request->getPost('tanggal_awal');
            $tgl_akhir = $this->request->getPost('tanggal_akhir');
            $tahun_jika = date("Y", strtotime($tgl_awal));
            $bulan = date("m", strtotime($tgl_awal));
            $data['tahun_jika'] = $tahun_jika;

            if ($this->request->getPost('tanggal_awal') == date($tahun_jika . '-01-01')) {
                $data['bulan'] = date('m', strtotime($tgl_awal));
                $data['tahun'] = date('Y', strtotime($tgl_awal));
                $data['dk_awal_k'] = $this->request->getPost('tanggal_awal');
                $data['dk_akhir_k'] = $this->request->getPost('tanggal_akhir');
            } elseif (date("m", strtotime($this->request->getPost('tanggal_awal'))) == '1') {
                $data['bulan'] = date('m', strtotime($tgl_awal));
                $data_bulan = $tgl_awal;
                $data['tahun'] = date('Y', strtotime($tgl_awal));
                $data['dk_awal_k'] = date("Y-m-d", strtotime("first day of $data_bulan"));
                $data['dk_akhir_k'] = date("Y-m-d", strtotime("$tgl_awal -1 day"));
                $data['dk_awal_k1'] = $this->request->getPost('tanggal_awal');
                $data['dk_akhir_k1'] = $this->request->getPost('tanggal_akhir');
            } else {
                $data['bulan'] = date('m', strtotime($tgl_awal));
                $data_bulan = $data['bulan'];
                $data_kurang = $data['bulan'] - 1;
                $data['tahun'] = date('Y', strtotime($tgl_awal));
                $data['dk_awal_k'] = date("Y-m-d", strtotime("first day of $data_bulan-$data_kurang"));
                $data['dk_akhir_k'] = date("Y-m-d", strtotime("$tgl_awal -1 day"));
                $data['dk_awal_k1'] = $this->request->getPost('tanggal_awal');
                $data['dk_akhir_k1'] = $this->request->getPost('tanggal_akhir');
            }
        } elseif ($this->request->getPost('tahun_post') && $this->request->getPost('bulan_post')) {
            $data['tahun'] = $this->request->getPost('tahun_post');
            $data['bulan'] = $this->request->getPost('bulan_post');
            $tahun_post = $this->request->getPost('tahun_post');
            $bulan_post = $this->request->getPost('bulan_post');
            $bulan2 = $bulan_post - 1;
            $tanggal_inti = date($tahun_post . "-" . $bulan_post . "-d");
            $data['nama_bulan'] = date("F", strtotime(date("Y") . "-" . $bulan_post . "-01"));
            $data['dk_awal_k'] = date("Y-m-d", strtotime("$tanggal_inti first day of -$bulan2 month"));
            $data['dk_akhir_k'] = date("Y-m-d", strtotime("$tanggal_inti last day of -1 month"));
        } elseif ($this->request->getPost('tahun_post')) {
            $data['bulan'] = 1;
            $data['tahun'] = $this->request->getPost('tahun_post');
        } else {
            $data['tahun'] = date('Y');
            $data['bulan'] = 1;
            $bulan = 1;
            $data['nama_bulan'] = date("F", strtotime(date("Y") . "-" . $bulan . "-01"));
            $data['dk_awal_k'] = date("Y-m-d", strtotime("first day of $bulan"));
            $data['dk_akhir_k'] = date("Y-m-d", strtotime("last day of $bulan"));
        }

        return view('templates/dash_header', $data)
            . view('templates/adm_sidebar', $data)
            . view('templates/adm_header', $data)
            . view('admin/master-data/laporan/jurnal_penutup', $data)
            . view('templates/adm_footer')
            . view('templates/dash_footer');
    }

    public function cetak_penutup()
    {
        $data['p_akun'] = $this->labarugiModel->tampilPosakun();
        $data['pos_akun'] = ['Pendapatan', 'Beban', 'Saldo Laba'];

        // Same logic as jurnal_penutup
        if ($this->request->getPost('tanggal_awal')) {
            $tgl_awal = $this->request->getPost('tanggal_awal');
            $tgl_akhir = $this->request->getPost('tanggal_akhir');
            $tahun_jika = date("Y", strtotime($tgl_awal));
            $bulan = date("m", strtotime($tgl_awal));
            $data['tahun_jika'] = $tahun_jika;

            if ($this->request->getPost('tanggal_awal') == date($tahun_jika . '-01-01')) {
                $data['bulan'] = date('m', strtotime($tgl_awal));
                $data['tahun'] = date('Y', strtotime($tgl_awal));
                $data['dk_awal_k'] = $this->request->getPost('tanggal_awal');
                $data['dk_akhir_k'] = $this->request->getPost('tanggal_akhir');
            } elseif (date("m", strtotime($this->request->getPost('tanggal_awal'))) == '1') {
                $data['bulan'] = date('m', strtotime($tgl_awal));
                $data_bulan = $tgl_awal;
                $data['tahun'] = date('Y', strtotime($tgl_awal));
                $data['dk_awal_k'] = date("Y-m-d", strtotime("first day of $data_bulan"));
                $data['dk_akhir_k'] = date("Y-m-d", strtotime("$tgl_awal -1 day"));
                $data['dk_awal_k1'] = $this->request->getPost('tanggal_awal');
                $data['dk_akhir_k1'] = $this->request->getPost('tanggal_akhir');
            } else {
                $data['bulan'] = date('m', strtotime($tgl_awal));
                $data_bulan = $data['bulan'];
                $data_kurang = $data['bulan'] - 1;
                $data['tahun'] = date('Y', strtotime($tgl_awal));
                $data['dk_awal_k'] = date("Y-m-d", strtotime("first day of $data_bulan-$data_kurang"));
                $data['dk_akhir_k'] = date("Y-m-d", strtotime("$tgl_awal -1 day"));
                $data['dk_awal_k1'] = $this->request->getPost('tanggal_awal');
                $data['dk_akhir_k1'] = $this->request->getPost('tanggal_akhir');
            }
        } elseif ($this->request->getPost('tahun_post') && $this->request->getPost('bulan_post')) {
            $data['tahun'] = $this->request->getPost('tahun_post');
            $data['bulan'] = $this->request->getPost('bulan_post');
            $tahun_post = $this->request->getPost('tahun_post');
            $bulan_post = $this->request->getPost('bulan_post');
            $bulan2 = $bulan_post - 1;
            $tanggal_inti = date($tahun_post . "-" . $bulan_post . "-d");
            $data['nama_bulan'] = date("F", strtotime(date("Y") . "-" . $bulan_post . "-01"));
            $data['dk_awal_k'] = date("Y-m-d", strtotime("$tanggal_inti first day of -$bulan2 month"));
            $data['dk_akhir_k'] = date("Y-m-d", strtotime("$tanggal_inti last day of -1 month"));
        } elseif ($this->request->getPost('tahun_post')) {
            $data['bulan'] = 1;
            $data['tahun'] = $this->request->getPost('tahun_post');
        } else {
            $data['tahun'] = date('Y');
            $data['bulan'] = 1;
            $bulan = 1;
            $data['nama_bulan'] = date("F", strtotime(date("Y") . "-" . $bulan . "-01"));
            $data['dk_awal_k'] = date("Y-m-d", strtotime("first day of $bulan"));
            $data['dk_akhir_k'] = date("Y-m-d", strtotime("last day of $bulan"));
        }

        $dompdf = new \Dompdf\Dompdf();
        $html = view('laporan/laporan_jpenutup', $data);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream("jurnal_penutup.pdf", array('Attachment' => 0));
    }
}
