<?php

namespace App\Controllers;

use App\Models\BukuBesarModel;
use App\Models\AdminModel;
use CodeIgniter\Controller;

class BukuBesar extends BaseController
{
    protected $bukuBesarModel;
    protected $adminModel;
    protected $validation;
    protected $session;
    protected $db;

    public function __construct()
    {
        $this->bukuBesarModel = new BukuBesarModel();
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
        $data['judul'] = 'Buku Besar';
        $data['active'] = 'active';
        $data['dd_kodeakun'] = $this->adminModel->ambilDropdown();
        $data['dd_bulan'] = $this->bukuBesarModel->ddBulan();
        $data['jurnal_umum'] = $this->bukuBesarModel->tampilBukubesar();
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();
        $data['bukber'] = $this->db->table('daftar_akun')
            ->where('akun IS NOT NULL')
            ->get()
            ->getResultArray();

        // Process date and month filters
        if ($this->request->getPost('tanggal_awal')) {
            $t_aw = $this->request->getPost('tanggal_awal');
            $t_ak = $this->request->getPost('tanggal_akhir');

            $data['t_aw'] = $t_aw;
            $data['t_ak'] = $t_ak;

            $month_awal = date("n", strtotime($t_aw));
            $month_akhir = date("n", strtotime($t_ak));
            $tahun = date("Y", strtotime($t_aw));
            $bulan = $month_awal;

            $data['tahun'] = $tahun;
            $data['bulan'] = $bulan;
            $data['nama_bulan'] = date("F", strtotime(date("Y") . "-" . $bulan . "-01"));

            if ($t_aw != date($tahun . "-01-01")) {
                $bulan2 = $bulan - 1;
                $data['tahun_sa1'] = $tahun;
                $data['tahun_sa'] = $tahun;
                $data['tahun_sa2'] = $tahun - 1;
                $data['date_awal'] = date($tahun . "-01-01");
                $data['date_akhir_data12'] = date("Y-m-d", strtotime($t_ak));
                $data['date_akhir'] = date("Y-m-d", strtotime("$t_aw -1 day"));
                $data['tgl_awal_data'] = $t_aw;
                $data['tgl_akhir_data'] = $t_ak;
            } else {
                $date_now = date($tahun . "-" . $bulan . "-d");
                $data['tahun_sa1'] = $tahun;
                $data['tahun_sa'] = $tahun;
                $data['date_awal'] = date($tahun . "-" . $bulan . "-01");
                $data['date_akhir'] = date($tahun . "-01-31");
                $data['tgl_awal_data'] = $t_aw;
                $data['tgl_akhir_data'] = $t_ak;
            }
        } elseif ($this->request->getPost('bulan_post')) {
            $data['tahun'] = $this->request->getPost('tahun_post');
            $data['bulan'] = $this->request->getPost('bulan_post');
            $tahun = $data['tahun'];
            $bulan = $data['bulan'];
            $data['nama_bulan'] = date("F", strtotime(date("Y") . "-" . $bulan . "-01"));

            if ($bulan != 1) {
                $bulan2 = $bulan - 1;
                $data['tahun_sa'] = $tahun;
                $date_now = date($tahun . "-" . $bulan . "-d");
                $data['date_awal'] = date("Y-m-d", strtotime("$date_now first day of -$bulan2 month"));
                $data['date_akhir'] = date("Y-m-d", strtotime("$date_now last day of -1 month"));
                $data['tgl_awal_data'] = date("Y-m-d", strtotime("first day of $date_now"));
                $data['tgl_akhir_data'] = date("Y-m-d", strtotime("last day of $date_now"));
            } else {
                $date_now = date($tahun . "-" . $bulan . "-d");
                $data['tahun_sa'] = $tahun;
                $data['date_awal'] = date($tahun . "-" . $bulan . "-01");
                $data['date_akhir'] = date($tahun . "-01-31");
                $data['tgl_awal_data'] = date("Y-m-d", strtotime("first day of $date_now"));
                $data['tgl_akhir_data'] = date("Y-m-d", strtotime("last day of $date_now"));
            }
        } else {
            if ($this->request->getPost('tahun_post')) {
                $data['tahun'] = $this->request->getPost('tahun_post');
                $data['bulan'] = 1;
                $bulan = 1;
                $tahun = $this->request->getPost('tahun_post');
                $data['tahun_sa'] = $tahun;
                $date_now = date($tahun . "-" . $bulan . "-d");
                $data['nama_bulan'] = 'Tahun';
                $data['date_awal'] = date("Y-m-d", strtotime("first day of $date_now"));
                $data['date_akhir'] = date("Y-m-d", strtotime("$date_now last day of +11 month"));
                $data['tgl_awal_data'] = date("Y-m-d", strtotime("first day of $date_now"));
                $data['tgl_akhir_data'] = date("Y-m-d", strtotime("$date_now last day of +11 month"));
            } else {
                $data['tahun'] = date('Y');
                $data['bulan'] = date('m');
                $bulan = date('m');
                $tahun = date('Y');
                $data['nama_bulan'] = date("F", strtotime(date("Y") . "-" . $bulan . "-01"));

                if ($bulan != 1) {
                    $bulan2 = $bulan - 1;
                    $data['tahun_sa'] = $tahun;
                    $date_now = date("Y-" . $bulan . "-d");
                    $data['date_awal'] = date("Y-m-d", strtotime("$date_now first day of -$bulan2 month"));
                    $data['date_akhir'] = date("Y-m-d", strtotime("$date_now last day of -1 month"));
                    $data['tgl_awal_data'] = date("Y-m-d", strtotime("first day of $date_now"));
                    $data['tgl_akhir_data'] = date("Y-m-d", strtotime("last day of $date_now"));
                } else {
                    $date_now = date("Y-" . $bulan . "-d");
                    $data['tahun_sa'] = $data['tahun'];
                    $data['date_awal'] = date("Y-" . $bulan . "-01");
                    $data['date_akhir'] = date("Y-01-31");
                    $data['tgl_awal_data'] = date("Y-m-d", strtotime("first day of $date_now"));
                    $data['tgl_akhir_data'] = date("Y-m-d", strtotime("last day of $date_now"));
                }
            }
        }

        return view('templates/dash_header', $data)
            . view('templates/adm_sidebar', $data)
            . view('templates/adm_header', $data)
            . view('admin/master-data/laporan/buku_besar', $data)
            . view('templates/adm_footer')
            . view('templates/dash_footer');
    }

    public function pdf()
    {
        $data['bukber'] = $this->db->table('daftar_akun')
            ->where('akun IS NOT NULL')
            ->get()
            ->getResultArray();

        // Same logic as index for date processing
        if ($this->request->getPost('tanggal_awal')) {
            $t_aw = $this->request->getPost('tanggal_awal');
            $t_ak = $this->request->getPost('tanggal_akhir');
            $data['t_aw'] = $t_aw;
            $data['t_ak'] = $t_ak;
            $month_awal = date("n", strtotime($t_aw));
            $tahun = date("Y", strtotime($t_aw));
            $bulan = $month_awal;
            $data['tahun'] = $tahun;
            $data['bulan'] = $bulan;
            $data['nama_bulan'] = date("F", strtotime(date("Y") . "-" . $bulan . "-01"));

            if ($t_aw != date($tahun . "-01-01")) {
                $data['tahun_sa1'] = $tahun;
                $data['tahun_sa'] = $tahun;
                $data['tahun_sa2'] = $tahun - 1;
                $data['date_awal'] = date($tahun . "-01-01");
                $data['date_akhir_data12'] = date("Y-m-d", strtotime($t_ak));
                $data['date_akhir'] = date("Y-m-d", strtotime("$t_aw -1 day"));
                $data['tgl_awal_data'] = $t_aw;
                $data['tgl_akhir_data'] = $t_ak;
            } else {
                $data['tahun_sa1'] = $tahun;
                $data['tahun_sa'] = $tahun;
                $data['date_awal'] = date($tahun . "-" . $bulan . "-01");
                $data['date_akhir'] = date($tahun . "-01-31");
                $data['tgl_awal_data'] = $t_aw;
                $data['tgl_akhir_data'] = $t_ak;
            }
        } elseif ($this->request->getPost('bulan_post')) {
            $data['tahun'] = $this->request->getPost('tahun_post');
            $data['bulan'] = $this->request->getPost('bulan_post');
            $tahun = $data['tahun'];
            $bulan = $data['bulan'];
            $data['nama_bulan'] = date("F", strtotime(date("Y") . "-" . $bulan . "-01"));

            if ($bulan != 1) {
                $bulan2 = $bulan - 1;
                $data['tahun_sa'] = $tahun;
                $date_now = date($tahun . "-" . $bulan . "-d");
                $data['date_awal'] = date("Y-m-d", strtotime("$date_now first day of -$bulan2 month"));
                $data['date_akhir'] = date("Y-m-d", strtotime("$date_now last day of -1 month"));
                $data['tgl_awal_data'] = date("Y-m-d", strtotime("first day of $date_now"));
                $data['tgl_akhir_data'] = date("Y-m-d", strtotime("last day of $date_now"));
            } else {
                $date_now = date($tahun . "-" . $bulan . "-d");
                $data['tahun_sa'] = $tahun;
                $data['date_awal'] = date($tahun . "-" . $bulan . "-01");
                $data['date_akhir'] = date($tahun . "-01-31");
                $data['tgl_awal_data'] = date("Y-m-d", strtotime("first day of $date_now"));
                $data['tgl_akhir_data'] = date("Y-m-d", strtotime("last day of $date_now"));
            }
        } else {
            if ($this->request->getPost('tahun_post')) {
                $data['tahun'] = $this->request->getPost('tahun_post');
                $data['bulan'] = 1;
                $bulan = 1;
                $tahun = $this->request->getPost('tahun_post');
                $data['tahun_sa'] = $tahun;
                $date_now = date($tahun . "-" . $bulan . "-d");
                $data['nama_bulan'] = 'Tahun';
                $data['date_awal'] = date("Y-m-d", strtotime("first day of $date_now"));
                $data['date_akhir'] = date("Y-m-d", strtotime("$date_now last day of +11 month"));
                $data['tgl_awal_data'] = date("Y-m-d", strtotime("first day of $date_now"));
                $data['tgl_akhir_data'] = date("Y-m-d", strtotime("$date_now last day of +11 month"));
            } else {
                $data['tahun'] = date('Y');
                $data['bulan'] = date('m');
                $bulan = date('m');
                $tahun = date('Y');
                $data['nama_bulan'] = date("F", strtotime(date("Y") . "-" . $bulan . "-01"));

                if ($bulan != 1) {
                    $bulan2 = $bulan - 1;
                    $data['tahun_sa'] = $tahun;
                    $date_now = date("Y-" . $bulan . "-d");
                    $data['date_awal'] = date("Y-m-d", strtotime("$date_now first day of -$bulan2 month"));
                    $data['date_akhir'] = date("Y-m-d", strtotime("$date_now last day of -1 month"));
                    $data['tgl_awal_data'] = date("Y-m-d", strtotime("first day of $date_now"));
                    $data['tgl_akhir_data'] = date("Y-m-d", strtotime("last day of $date_now"));
                } else {
                    $date_now = date("Y-" . $bulan . "-d");
                    $data['tahun_sa'] = $data['tahun'];
                    $data['date_awal'] = date("Y-" . $bulan . "-01");
                    $data['date_akhir'] = date("Y-01-31");
                    $data['tgl_awal_data'] = date("Y-m-d", strtotime("first day of $date_now"));
                    $data['tgl_akhir_data'] = date("Y-m-d", strtotime("last day of $date_now"));
                }
            }
        }

        $dompdf = new \Dompdf\Dompdf();
        $html = view('laporan/laporan_bukubesar', $data);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream("laporan_bukubesar.pdf", array('Attachment' => 0));
    }
}
