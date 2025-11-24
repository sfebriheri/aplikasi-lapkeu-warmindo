<?php

namespace App\Controllers;

use App\Models\PoskeuModel;
use App\Models\LabarugiModel;
use App\Models\PmodalModel;
use CodeIgniter\Controller;

class Poskeu extends BaseController
{
    protected $poskeuModel;
    protected $labarugiModel;
    protected $pmodalModel;
    protected $validation;
    protected $session;
    protected $db;

    public function __construct()
    {
        $this->poskeuModel = new PoskeuModel();
        $this->labarugiModel = new LabarugiModel();
        $this->pmodalModel = new PmodalModel();
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
        $data['judul'] = 'posisi_keuangan';
        $data['active'] = 'active';
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();
        $data['pos_nr1'] = ['Aset Lancar', 'Aset Tetap'];
        $data['pos_nr2'] = ['Kewajiban', 'Ekuitas'];
        $data['pos_lr'] = ['Pendapatan', 'Beban'];
        $data['dd_bulan'] = $this->labarugiModel->ddBulan();

        if ($this->request->getPost('tahun_post') && $this->request->getPost('bulan_post')) {
            $data['tahun'] = $this->request->getPost('tahun_post');
            $data['bulan'] = $this->request->getPost('bulan_post');
            $tahun_post = $this->request->getPost('tahun_post');
            $bulan_post = $this->request->getPost('bulan_post');
            $bulan2 = $bulan_post - 1;
            $tanggal_inti = date($tahun_post . "-" . $bulan_post . "-d");
            $data['nama_bulan'] = date("F", strtotime(date("Y") . "-" . $bulan_post . "-01"));
            $data['dk_awal_k'] = date("Y-m-d", strtotime("$tanggal_inti first day of -$bulan2 month"));
            $data['dk_akhir_k'] = date("Y-m-d", strtotime("$tanggal_inti last day of -1 month"));
            $data['posakun'] = $this->poskeuModel->tampilPosakun();
            $data['lr'] = $this->pmodalModel->totalLabarugi($data);
        } elseif ($this->request->getPost('tanggal_awal')) {
            $tahun_jika = date('Y', strtotime($this->request->getPost('tanggal_awal')));
            $data['tahun_jika'] = $tahun_jika;
            $data['tahun'] = $tahun_jika;
            $tgl_awal = $this->request->getPost('tanggal_awal');

            if ($this->request->getPost('tanggal_awal') == date($tahun_jika . '-01-01')) {
                $data['bulan'] = date('m', strtotime($tgl_awal));
                $data['tahun'] = date('Y', strtotime($tgl_awal));
                $data['dk_awal_k'] = $this->request->getPost('tanggal_awal');
                $data['dk_akhir_k'] = $this->request->getPost('tanggal_akhir');
                $data['lr'] = $this->pmodalModel->totalLabarugi($data);
            } elseif (date("m", strtotime($this->request->getPost('tanggal_awal'))) == '1') {
                $data['bulan'] = date('m', strtotime($tgl_awal));
                $data_bulan = $tgl_awal;
                $data['tahun'] = date('Y', strtotime($tgl_awal));
                $data['dk_awal_k'] = date("Y-m-d", strtotime("first day of $data_bulan"));
                $data['dk_akhir_k'] = date("Y-m-d", strtotime("$tgl_awal -1 day"));
                $data['dk_awal_k1'] = $this->request->getPost('tanggal_awal');
                $data['dk_akhir_k1'] = $this->request->getPost('tanggal_akhir');
                $data['lr'] = $this->pmodalModel->totalLabarugi($data);
            } else {
                $data['bulan'] = date('m', strtotime($tgl_awal));
                $data_bulan = $data['bulan'];
                $data_kurang = $data['bulan'] - 1;
                $data['tahun'] = date('Y', strtotime($tgl_awal));
                $data['dk_awal_k'] = date("Y-m-d", strtotime("first day of $data_bulan-$data_kurang"));
                $data['dk_akhir_k'] = date("Y-m-d", strtotime("$tgl_awal -1 day"));
                $data['dk_awal_k1'] = $this->request->getPost('tanggal_awal');
                $data['dk_akhir_k1'] = $this->request->getPost('tanggal_akhir');
                $data['lr'] = $this->pmodalModel->totalLabarugi($data);
            }
        } elseif ($this->request->getPost('tahun_post')) {
            $data['bulan'] = 1;
            $data['tahun'] = $this->request->getPost('tahun_post');
            $data['posakun'] = $this->poskeuModel->tampilPosakun();
            $data['lr'] = $this->pmodalModel->totalLabarugi($data);
        } else {
            $data['bulan'] = date('m');
            $data['tahun'] = date('Y');
            $bulan = $data['bulan'];
            $tahun = date('Y');
            $data['nama_bulan'] = date("F", strtotime(date("Y") . "-" . $bulan . "-01"));

            if ($data['bulan'] != 1) {
                $bulan2 = $bulan - 1;
                $tanggal_inti = date($tahun . "-" . $bulan . "-d");
                $data['dk_awal_k'] = date("Y-m-d", strtotime("$tanggal_inti first day of -$bulan2 month"));
                $data['dk_akhir_k'] = date("Y-m-d", strtotime("$tanggal_inti last day of -1 month"));
                $data['posakun'] = $this->poskeuModel->tampilPosakun();
                $data['lr'] = $this->pmodalModel->totalLabarugi($data);
            } else {
                $data['posakun'] = $this->poskeuModel->tampilPosakun();
                $data['lr'] = $this->pmodalModel->totalLabarugi($data);
            }
        }

        return view('templates/dash_header', $data)
            . view('templates/adm_sidebar', $data)
            . view('templates/adm_header', $data)
            . view('admin/master-data/laporan/posisi_keuangan', $data)
            . view('templates/adm_footer')
            . view('templates/dash_footer');
    }

    public function cetak_poskeu()
    {
        $data['pos_ekuitas'] = $this->pmodalModel->posEkuitas();
        $data['dd_bulan'] = $this->labarugiModel->ddBulan();
        $data['pos_nr1'] = ['Aset Lancar', 'Aset Tetap'];
        $data['pos_nr2'] = ['Kewajiban', 'Ekuitas'];
        $data['pos_lr'] = ['Pendapatan', 'Beban'];

        // Same date processing logic as index
        if ($this->request->getPost('tahun_post') && $this->request->getPost('bulan_post')) {
            $data['tahun'] = $this->request->getPost('tahun_post');
            $data['bulan'] = $this->request->getPost('bulan_post');
            $tahun_post = $this->request->getPost('tahun_post');
            $bulan_post = $this->request->getPost('bulan_post');
            $bulan2 = $bulan_post - 1;
            $tanggal_inti = date($tahun_post . "-" . $bulan_post . "-d");
            $data['nama_bulan'] = date("F", strtotime(date("Y") . "-" . $bulan_post . "-01"));
            $data['dk_awal_k'] = date("Y-m-d", strtotime("$tanggal_inti first day of -$bulan2 month"));
            $data['dk_akhir_k'] = date("Y-m-d", strtotime("$tanggal_inti last day of -1 month"));
            $data['posakun'] = $this->poskeuModel->tampilPosakun();
            $data['lr'] = $this->pmodalModel->totalLabarugi($data);
        } elseif ($this->request->getPost('tanggal_awal')) {
            $tahun_jika = date('Y', strtotime($this->request->getPost('tanggal_awal')));
            $data['tahun_jika'] = $tahun_jika;
            $data['tahun'] = $tahun_jika;
            $tgl_awal = $this->request->getPost('tanggal_awal');

            if ($this->request->getPost('tanggal_awal') == date($tahun_jika . '-01-01')) {
                $data['bulan'] = date('m', strtotime($tgl_awal));
                $data['tahun'] = date('Y', strtotime($tgl_awal));
                $data['dk_awal_k'] = $this->request->getPost('tanggal_awal');
                $data['dk_akhir_k'] = $this->request->getPost('tanggal_akhir');
                $data['lr'] = $this->pmodalModel->totalLabarugi($data);
            } elseif (date("m", strtotime($this->request->getPost('tanggal_awal'))) == '1') {
                $data['bulan'] = date('m', strtotime($tgl_awal));
                $data_bulan = $tgl_awal;
                $data['tahun'] = date('Y', strtotime($tgl_awal));
                $data['dk_awal_k'] = date("Y-m-d", strtotime("first day of $data_bulan"));
                $data['dk_akhir_k'] = date("Y-m-d", strtotime("$tgl_awal -1 day"));
                $data['dk_awal_k1'] = $this->request->getPost('tanggal_awal');
                $data['dk_akhir_k1'] = $this->request->getPost('tanggal_akhir');
                $data['lr'] = $this->pmodalModel->totalLabarugi($data);
            } else {
                $data['bulan'] = date('m', strtotime($tgl_awal));
                $data_bulan = $data['bulan'];
                $data_kurang = $data['bulan'] - 1;
                $data['tahun'] = date('Y', strtotime($tgl_awal));
                $data['dk_awal_k'] = date("Y-m-d", strtotime("first day of $data_bulan-$data_kurang"));
                $data['dk_akhir_k'] = date("Y-m-d", strtotime("$tgl_awal -1 day"));
                $data['dk_awal_k1'] = $this->request->getPost('tanggal_awal');
                $data['dk_akhir_k1'] = $this->request->getPost('tanggal_akhir');
                $data['lr'] = $this->pmodalModel->totalLabarugi($data);
            }
        } elseif ($this->request->getPost('tahun_post')) {
            $data['bulan'] = 1;
            $data['tahun'] = $this->request->getPost('tahun_post');
            $data['posakun'] = $this->poskeuModel->tampilPosakun();
            $data['lr'] = $this->pmodalModel->totalLabarugi($data);
        } else {
            $data['bulan'] = date('m');
            $data['tahun'] = date('Y');
            $bulan = $data['bulan'];
            $tahun = date('Y');
            $data['nama_bulan'] = date("F", strtotime(date("Y") . "-" . $bulan . "-01"));

            if ($data['bulan'] != 1) {
                $bulan2 = $bulan - 1;
                $tanggal_inti = date($tahun . "-" . $bulan . "-d");
                $data['dk_awal_k'] = date("Y-m-d", strtotime("$tanggal_inti first day of -$bulan2 month"));
                $data['dk_akhir_k'] = date("Y-m-d", strtotime("$tanggal_inti last day of -1 month"));
                $data['posakun'] = $this->poskeuModel->tampilPosakun();
                $data['lr'] = $this->pmodalModel->totalLabarugi($data);
            } else {
                $data['posakun'] = $this->poskeuModel->tampilPosakun();
                $data['lr'] = $this->pmodalModel->totalLabarugi($data);
            }
        }

        $dompdf = new \Dompdf\Dompdf();
        $html = view('laporan/laporan_posisikeuangan', $data);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream("laporan_posisikeuangan.pdf", array('Attachment' => 0));
    }
}
