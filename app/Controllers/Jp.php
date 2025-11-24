<?php

namespace App\Controllers;

use App\Models\JpModel;
use App\Models\AdminModel;
use CodeIgniter\Controller;

class Jp extends BaseController
{
    protected $jpModel;
    protected $adminModel;
    protected $validation;
    protected $session;
    protected $db;

    public function __construct()
    {
        $this->jpModel = new JpModel();
        $this->adminModel = new AdminModel();
        $this->validation = \Config\Services::validation();
        $this->session = \Config\Services::session();
        $this->db = \Config\Database::connect();
        helper(['form', 'url']);
    }

    public function index()
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        }

        $data['judul'] = 'Jurnal Penyesuaian';
        $data['active'] = 'active';
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();
        $data['dd_bulan'] = $this->adminModel->ddBulan();

        if ($this->request->getPost('katakunci')) {
            $data['tampil_jp'] = $this->jpModel->cariJp();
            $data['katakunci'] = $this->request->getPost('katakunci');
            $data['total_debit'] = $this->jpModel->totalDebit();
            $data['total_kredit'] = $this->jpModel->totalKredit();
        } elseif ($this->request->getPost('tanggal_awal')) {
            $data['tampil_jp'] = $this->jpModel->cariTanggalJp();
            $data['tanggal_awal'] = $this->request->getPost('tanggal_awal');
            $data['tanggal_akhir'] = $this->request->getPost('tanggal_akhir');
            $data['total_debit'] = $this->jpModel->totalDebit();
            $data['total_kredit'] = $this->jpModel->totalKredit();
        } elseif ($this->request->getPost('bulan_post') && $this->request->getPost('tahun_post')) {
            $data['tampil_jp'] = $this->jpModel->cariBulantahunjp();
            $bulan = $this->request->getPost('bulan_post');
            $data['bulan_nama'] = date("F", strtotime(date("Y") . "-" . $bulan . "-01"));
            $data['total_debit'] = $this->jpModel->totalDebit();
            $data['total_kredit'] = $this->jpModel->totalKredit();
        } elseif ($this->request->getPost('tahun_post')) {
            $data['tampil_jp'] = $this->jpModel->cariTahunjp();
            $data['total_debit'] = $this->jpModel->totalDebit();
            $data['total_kredit'] = $this->jpModel->totalKredit();
        } else {
            $bulan = date('m');
            $data['bulan_nama'] = date("F", strtotime(date("Y") . "-" . $bulan . "-01"));
            $data['tampil_jp'] = $this->jpModel->tampilJp();
            $data['total_debit'] = $this->jpModel->totalDebit();
            $data['total_kredit'] = $this->jpModel->totalKredit();
        }

        return view('templates/dash_header', $data)
            . view('templates/adm_sidebar', $data)
            . view('templates/adm_header', $data)
            . view('admin/master-data/laporan/jurnal_penyesuaian', $data)
            . view('templates/adm_footer')
            . view('templates/dash_footer');
    }

    public function tambah_jp()
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        } else {
            if ($this->session->get('role_id') == '1') {
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda tidak bisa mengakses halaman ini</div>');
                return redirect()->to(base_url('admin'));
            }
        }

        $data['judul'] = 'posisi_keuangan';
        $data['active'] = 'active';
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();
        $data['bukti_transaksi'] = $this->adminModel->buktiTransaksi();
        $data['dd_kodeakun'] = $this->adminModel->ambilDropdown();
        $data['dd_bulan'] = $this->adminModel->ddBulan();

        $rules = [
            'kode_akun.*' => 'required',
            'akun.*' => 'required',
            'pos_saldo.*' => 'required',
            'pos_laporan.*' => 'required'
        ];

        if (!$this->validate($rules)) {
            $data['validation'] = $this->validation;
            return view('templates/dash_header', $data)
                . view('templates/adm_sidebar', $data)
                . view('templates/adm_header', $data)
                . view('admin/master-data/jp/tambah_jp', $data)
                . view('templates/adm_footer')
                . view('templates/dash_footer');
        } else {
            $this->jpModel->tambahJp();
            return redirect()->to(base_url('Jp'));
        }
    }

    public function update_jp($bukti_transaksi)
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        } else {
            if ($this->session->get('role_id') == '1') {
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda tidak bisa mengakses halaman ini</div>');
                return redirect()->to(base_url('admin'));
            }
        }

        $data['judul'] = 'posisi_keuangan';
        $data['active'] = 'active';
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();
        $data['bukti_transaksi'] = $this->adminModel->buktiTransaksi();
        $data['dd_kodeakun'] = $this->adminModel->ambilDropdown();
        $data['dd_bulan'] = $this->adminModel->ddBulan();
        $data['data_jp'] = $this->jpModel->getdatajp($bukti_transaksi);
        $data['pos_saldo'] = ['Debit', 'Kredit'];
        $data['pos_laporan'] = ['Laporan Posisi Keuangan', 'Laba Rugi'];

        $rules = [
            'kode_akun.*' => 'required',
            'akun.*' => 'required',
            'pos_saldo.*' => 'required',
            'pos_laporan.*' => 'required'
        ];

        $data['data_count'] = count($this->db->table('transaksi')
            ->where('bukti_transaksi', $bukti_transaksi)
            ->get()
            ->getResultArray());

        if (!$this->validate($rules)) {
            $data['validation'] = $this->validation;
            return view('templates/dash_header', $data)
                . view('templates/adm_sidebar', $data)
                . view('templates/adm_header', $data)
                . view('admin/master-data/jp/update_jp', $data)
                . view('templates/adm_footer')
                . view('templates/dash_footer');
        } else {
            $this->jpModel->updateJp();
            return redirect()->to(base_url('Jp'));
        }
    }

    public function hapus_jp($bukti_transaksi)
    {
        $this->jpModel->hapusJp($bukti_transaksi);
        return redirect()->to(base_url('Jp'));
    }

    public function cetak_jp()
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        }

        if ($this->request->getPost('katakunci')) {
            $data['tampil_jp'] = $this->jpModel->cariJp();
            $data['katakunci'] = $this->request->getPost('katakunci');
            $data['total_debit'] = $this->jpModel->totalDebit();
            $data['total_kredit'] = $this->jpModel->totalKredit();
        } elseif ($this->request->getPost('tanggal_awal')) {
            $data['tampil_jp'] = $this->jpModel->cariTanggalJp();
            $data['tanggal_awal'] = $this->request->getPost('tanggal_awal');
            $data['tanggal_akhir'] = $this->request->getPost('tanggal_akhir');
            $data['total_debit'] = $this->jpModel->totalDebit();
            $data['total_kredit'] = $this->jpModel->totalKredit();
        } elseif ($this->request->getPost('bulan_post') && $this->request->getPost('tahun_post')) {
            $data['tampil_jp'] = $this->jpModel->cariBulantahunjp();
            $bulan = $this->request->getPost('bulan_post');
            $data['bulan_nama'] = date("F", strtotime(date("Y") . "-" . $bulan . "-01"));
            $data['total_debit'] = $this->jpModel->totalDebit();
            $data['total_kredit'] = $this->jpModel->totalKredit();
        } elseif ($this->request->getPost('tahun_post')) {
            $data['tampil_jp'] = $this->jpModel->cariTahunjp();
            $data['total_debit'] = $this->jpModel->totalDebit();
            $data['total_kredit'] = $this->jpModel->totalKredit();
        } else {
            $bulan = date('m');
            $data['bulan_nama'] = date("F", strtotime(date("Y") . "-" . $bulan . "-01"));
            $data['tampil_jp'] = $this->jpModel->tampilJp();
            $data['total_debit'] = $this->jpModel->totalDebit();
            $data['total_kredit'] = $this->jpModel->totalKredit();
        }

        $dompdf = new \Dompdf\Dompdf();
        $html = view('laporan/laporan_jp', $data);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream("laporan_jurnalpenyesuaian.pdf", array('Attachment' => 0));
    }
}
