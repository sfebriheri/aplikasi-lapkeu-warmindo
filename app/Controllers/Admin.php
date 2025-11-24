<?php

namespace App\Controllers;

use App\Models\AdminModel;
use CodeIgniter\Controller;

class Admin extends BaseController
{
    protected $adminModel;
    protected $validation;
    protected $session;
    protected $db;

    public function __construct()
    {
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

        $data['judul'] = 'Menu Utama';
        $data['active'] = 'active';
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();

        return view('templates/dash_header', $data)
            . view('templates/adm_sidebar', $data)
            . view('templates/adm_header', $data)
            . view('admin/index', $data)
            . view('templates/adm_footer');
    }

    // TRANSAKSI

    public function transaksi()
    {
        $data['judul'] = 'Transaksi';
        $data['active'] = 'active';
        $data['dd_kodeakun'] = $this->adminModel->ambilDropdown();
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();

        $rules = [
            'kode_akun' => 'required',
            'akun' => 'required',
            'keterangan' => 'required',
            'pos_saldo' => 'required',
            'pos_laporan' => 'required',
            'tanggal_transaksi' => 'required',
            'bukti_transaksi' => 'required'
        ];

        if (!$this->validate($rules)) {
            $data['validation'] = $this->validation;
            return view('templates/dash_header', $data)
                . view('templates/adm_sidebar', $data)
                . view('templates/adm_header', $data)
                . view('admin/master-data/transaksi', $data)
                . view('templates/adm_footer')
                . view('templates/dash_footer');
        } else {
            $this->adminModel->tambahTransaksi();
            $this->session->setFlashdata('pesan_sukses', 'Ditambahkan');

            return view('templates/dash_header', $data)
                . view('templates/adm_sidebar', $data)
                . view('templates/adm_header', $data)
                . view('admin/master-data/transaksi', $data)
                . view('templates/adm_footer')
                . view('templates/dash_footer');
        }
    }

    // TRANSAKSI MULTI INPUT

    public function transaksi_m()
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        } else {
            if ($this->session->get('role_id') == '1') {
                return redirect()->to(base_url('admin'));
            }
        }

        $data['judul'] = 'Transaksi';
        $data['active'] = 'active';
        $data['dd_kodeakun'] = $this->adminModel->ambilDropdown();
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();
        $data['bukti_transaksi'] = $this->adminModel->buktiTransaksi();

        return view('templates/dash_header', $data)
            . view('templates/adm_sidebar', $data)
            . view('templates/adm_header', $data)
            . view('admin/master-data/transaksi_multi', $data)
            . view('templates/adm_footer')
            . view('templates/dash_footer');
    }

    public function insert_transaksi_m()
    {
        $rules = [
            'kode_akun.*' => 'required',
            'debit.*' => 'required',
            'kredit.*' => 'required',
            'keterangan.*' => 'required',
            'tanggal_transaksi.*' => 'required',
            'bukti_transaksi.*' => 'required'
        ];

        if (!$this->validate($rules)) {
            $this->session->setFlashdata('pesan_error', $this->validator->getErrors());
            return redirect()->to(base_url('admin/transaksi_m'));
        }

        // Get data from POST
        $kode_akun = $this->request->getPost('kode_akun');
        $keterangan = $this->request->getPost('keterangan');
        $tanggal_transaksi = $this->request->getPost('tanggal_transaksi');
        $pos_saldo = $this->request->getPost('pos_saldo');
        $pos_laporan = $this->request->getPost('pos_laporan');
        $bukti_transaksi = $this->request->getPost('bukti_transaksi');
        $akun = $this->request->getPost('akun');
        $debit = $this->request->getPost('debit');
        $kredit = $this->request->getPost('kredit');
        $pos_akun = $this->request->getPost('pos_akun');

        $data = [];
        $index = 0;

        foreach ($kode_akun as $datakd) {
            $data[] = [
                'kode_akun' => $datakd,
                'keterangan' => $keterangan[$index],
                'tanggal_transaksi' => $tanggal_transaksi[$index],
                'pos_saldo' => $pos_saldo[$index],
                'pos_laporan' => $pos_laporan[$index],
                'bukti_transaksi' => $bukti_transaksi[$index],
                'akun' => $akun[$index],
                'debit' => $debit[$index],
                'kredit' => $kredit[$index],
                'pos_akun' => $pos_akun[$index],
                'ref' => 'JU'
            ];
            $index++;
        }

        $jumlah = [];
        $jumlahk = [];
        foreach ($data as $d) {
            $jumlah[] = $d['debit'];
            $jumlahk[] = $d['kredit'];
        }

        $jumlahnya = array_sum($jumlah);
        $jumlahknya = array_sum($jumlahk);

        if ($jumlahnya == $jumlahknya) {
            $this->db->table('transaksi')->insertBatch($data);
            $this->session->setFlashdata('pesan_sukses', 'Ditambahkan');
            $this->session->setFlashdata('pesan_balance', 'Sudah Balance');
            return redirect()->to(base_url('admin/transaksi_m'));
        } else {
            $this->session->setFlashdata('pesan_error', 'Ditambahkan');
            $this->session->setFlashdata('pesan_tidakbalance', 'Tidak Balance');
            return redirect()->to(base_url('admin/transaksi_m'));
        }
    }

    public function get_kodeakun()
    {
        $kode_akun = $this->request->getPost('kode_akun');
        $data = $this->adminModel->isiFieldByKode($kode_akun);
        return $this->response->setJSON($data);
    }

    // UBAH TRANSAKSI

    public function ubahTransaksi($bukti_transaksi)
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        } else {
            if ($this->session->get('role_id') == '1') {
                return redirect()->to(base_url('admin'));
            }
        }

        $data['dd_kodeakun'] = $this->adminModel->ambilDropdown();
        $data['judul'] = 'Ubah Transaksi';
        $data['active'] = 'active';

        $data12 = $this->adminModel->getTransaksiById($bukti_transaksi);

        $data['pos_saldo'] = ['Debit', 'Kredit'];
        $data['pos_laporan'] = ['Laporan Posisi Keuangan', 'Laba Rugi'];
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();

        $rules = [
            'kode_akun.*' => 'required',
            'akun.*' => 'required',
            'keterangan.*' => 'required',
            'pos_saldo.*' => 'required',
            'pos_laporan.*' => 'required',
            'tanggal_transaksi.*' => 'required',
            'bukti_transaksi.*' => 'required'
        ];

        if (!$this->validate($rules)) {
            $data_form = [];

            foreach ($data12 as $key) {
                $data_form[] = [
                    'id' => $key['id'],
                    'kode_akun' => $key['kode_akun'],
                    'akun' => $key['akun'],
                    'tanggal_transaksi' => $key['tanggal_transaksi'],
                    'bukti_transaksi' => $key['bukti_transaksi'],
                    'pos_saldo' => $key['pos_saldo'],
                    'pos_laporan' => $key['pos_laporan'],
                    'debit' => $key['debit'],
                    'kredit' => $key['kredit'],
                    'keterangan' => $key['keterangan'],
                    'pos_akun' => $key['pos_akun']
                ];
            }

            $new = count($data_form);
            $data['data_count'] = $new;

            if ($new == 2) {
                $dataform = [
                    'id' => [$data_form[0]['id'], $data_form[1]['id'], null, null],
                    'kode_akun' => [$data_form[0]['kode_akun'], $data_form[1]['kode_akun'], null, null],
                    'akun' => [$data_form[0]['akun'], $data_form[1]['akun'], null, null],
                    'tanggal_transaksi' => [$data_form[0]['tanggal_transaksi'], $data_form[1]['tanggal_transaksi'], null, null],
                    'bukti_transaksi' => [$data_form[0]['bukti_transaksi'], $data_form[1]['bukti_transaksi'], null, null],
                    'pos_saldo' => [$data_form[0]['pos_saldo'], $data_form[1]['pos_saldo'], null, null],
                    'pos_laporan' => [$data_form[0]['pos_laporan'], $data_form[1]['pos_laporan'], null, null],
                    'debit' => [$data_form[0]['debit'], $data_form[1]['debit'], null, null],
                    'keterangan' => [$data_form[0]['keterangan'], $data_form[1]['keterangan'], null, null],
                    'kredit' => [$data_form[0]['kredit'], $data_form[1]['kredit'], null, null],
                    'pos_akun' => [$data_form[0]['pos_akun'], $data_form[1]['pos_akun'], null, null]
                ];
            } else if ($new == 3) {
                $dataform = [
                    'id' => [$data_form[0]['id'], $data_form[1]['id'], $data_form[2]['id'], null],
                    'kode_akun' => [$data_form[0]['kode_akun'], $data_form[1]['kode_akun'], $data_form[2]['kode_akun'], null],
                    'akun' => [$data_form[0]['akun'], $data_form[1]['akun'], $data_form[2]['akun'], null],
                    'tanggal_transaksi' => [$data_form[0]['tanggal_transaksi'], $data_form[1]['tanggal_transaksi'], $data_form[2]['tanggal_transaksi'], null],
                    'bukti_transaksi' => [$data_form[0]['bukti_transaksi'], $data_form[1]['bukti_transaksi'], $data_form[2]['bukti_transaksi'], null],
                    'pos_saldo' => [$data_form[0]['pos_saldo'], $data_form[1]['pos_saldo'], $data_form[2]['pos_saldo'], null],
                    'pos_laporan' => [$data_form[0]['pos_laporan'], $data_form[1]['pos_laporan'], $data_form[2]['pos_laporan'], null],
                    'debit' => [$data_form[0]['debit'], $data_form[1]['debit'], $data_form[2]['debit'], null],
                    'keterangan' => [$data_form[0]['keterangan'], $data_form[1]['keterangan'], $data_form[2]['keterangan'], null],
                    'kredit' => [$data_form[0]['kredit'], $data_form[1]['kredit'], $data_form[2]['kredit'], null],
                    'pos_akun' => [$data_form[0]['pos_akun'], $data_form[1]['pos_akun'], $data_form[2]['pos_akun'], null]
                ];
            } else if ($new == 4) {
                $dataform = [
                    'id' => [$data_form[0]['id'], $data_form[1]['id'], $data_form[2]['id'], $data_form[3]['id']],
                    'kode_akun' => [$data_form[0]['kode_akun'], $data_form[1]['kode_akun'], $data_form[2]['kode_akun'], $data_form[3]['kode_akun']],
                    'akun' => [$data_form[0]['akun'], $data_form[1]['akun'], $data_form[2]['akun'], $data_form[3]['akun']],
                    'tanggal_transaksi' => [$data_form[0]['tanggal_transaksi'], $data_form[1]['tanggal_transaksi'], $data_form[2]['tanggal_transaksi'], $data_form[3]['tanggal_transaksi']],
                    'bukti_transaksi' => [$data_form[0]['bukti_transaksi'], $data_form[1]['bukti_transaksi'], $data_form[2]['bukti_transaksi'], $data_form[3]['bukti_transaksi']],
                    'pos_saldo' => [$data_form[0]['pos_saldo'], $data_form[1]['pos_saldo'], $data_form[2]['pos_saldo'], $data_form[3]['pos_saldo']],
                    'pos_laporan' => [$data_form[0]['pos_laporan'], $data_form[1]['pos_laporan'], $data_form[2]['pos_laporan'], $data_form[3]['pos_laporan']],
                    'debit' => [$data_form[0]['debit'], $data_form[1]['debit'], $data_form[2]['debit'], $data_form[3]['debit']],
                    'keterangan' => [$data_form[0]['keterangan'], $data_form[1]['keterangan'], $data_form[2]['keterangan'], $data_form[3]['keterangan']],
                    'kredit' => [$data_form[0]['kredit'], $data_form[1]['kredit'], $data_form[2]['kredit'], $data_form[3]['kredit']],
                    'pos_akun' => [$data_form[0]['pos_akun'], $data_form[1]['pos_akun'], $data_form[2]['pos_akun'], $data_form[3]['pos_akun']]
                ];
            }

            $data['transaksi'] = $dataform;
            $data['validation'] = $this->validation;

            return view('templates/dash_header', $data)
                . view('templates/adm_sidebar', $data)
                . view('templates/adm_header', $data)
                . view('admin/master-data/ubahtransaksi', $data)
                . view('templates/adm_footer')
                . view('templates/dash_footer');
        } else {
            // Get data from POST
            $id = $this->request->getPost('id');
            $kode_akun = $this->request->getPost('kode_akun');
            $keterangan = $this->request->getPost('keterangan');
            $tanggal_transaksi = $this->request->getPost('tanggal_transaksi');
            $pos_saldo = $this->request->getPost('pos_saldo');
            $pos_laporan = $this->request->getPost('pos_laporan');
            $bukti_transaksi_post = $this->request->getPost('bukti_transaksi');
            $akun = $this->request->getPost('akun');
            $debit = $this->request->getPost('debit');
            $kredit = $this->request->getPost('kredit');
            $pos_akun = $this->request->getPost('pos_akun');

            $data1 = [];
            $index = 0;

            foreach ($kode_akun as $datakd) {
                $data1[] = [
                    'id' => $id[$index],
                    'kode_akun' => $datakd,
                    'keterangan' => $keterangan[$index],
                    'tanggal_transaksi' => $tanggal_transaksi[$index],
                    'pos_saldo' => $pos_saldo[$index],
                    'pos_laporan' => $pos_laporan[$index],
                    'bukti_transaksi' => $bukti_transaksi_post[$index],
                    'akun' => $akun[$index],
                    'debit' => $debit[$index],
                    'kredit' => $kredit[$index],
                    'pos_akun' => $pos_akun[$index]
                ];
                $index++;
            }

            $jumlah = [];
            $jumlahk = [];
            foreach ($data1 as $d) {
                $jumlah[] = $d['debit'];
                $jumlahk[] = $d['kredit'];
            }

            $jumlahnya = array_sum($jumlah);
            $jumlahknya = array_sum($jumlahk);

            if ($jumlahnya == $jumlahknya) {
                $this->db->table('transaksi')->updateBatch($data1, 'id');
                $this->session->setFlashdata('pesan_sukses', 'Diperbaharui');
                $this->session->setFlashdata('pesan_balance', 'Sudah Balance');
                return redirect()->to(base_url('admin/jurnal_umum'));
            } else {
                $this->session->setFlashdata('pesan_error', 'Ditambahkan');
                $this->session->setFlashdata('pesan_tidakbalance', 'Tidak Balance');
                return redirect()->to(base_url('admin/jurnal_umum'));
            }
        }
    }

    public function hapusTransaksi($bukti_transaksi)
    {
        $this->adminModel->hapusJurnalUmum($bukti_transaksi);
        $this->session->setFlashdata('flash', 'Dihapus');
        return redirect()->to(base_url('admin/jurnal_umum'));
    }

    // LAPORAN

    public function jurnal_umum()
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        }

        $data['judul'] = 'Jurnal Umum';
        $data['active'] = 'active';
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();
        $data['dd_bulan'] = $this->adminModel->ddBulan();

        // Search jurnal umum by keyword and date
        if ($this->request->getPost('katakunci')) {
            $data['jurnal_umum'] = $this->adminModel->cariJurnalumum();
            $data['katakunci'] = $this->request->getPost('katakunci');
            $data['total_debit'] = $this->adminModel->totalDebit();
            $data['total_kredit'] = $this->adminModel->totalKredit();
        } elseif ($this->request->getPost('tanggal_awal')) {
            $data['jurnal_umum'] = $this->adminModel->cariTanggalJurnalumum();
            $data['tanggal_awal'] = $this->request->getPost('tanggal_awal');
            $data['tanggal_akhir'] = $this->request->getPost('tanggal_akhir');
            $data['total_debit'] = $this->adminModel->totalDebit();
            $data['total_kredit'] = $this->adminModel->totalKredit();
        } elseif ($this->request->getPost('bulan_post') && $this->request->getPost('tahun_post')) {
            $data['jurnal_umum'] = $this->adminModel->cariBulantahunjurnalumum();
            $bulan = $this->request->getPost('bulan_post');
            $data['bulan_nama'] = date("F", strtotime(date("Y") . "-" . $bulan . "-01"));
            $data['total_debit'] = $this->adminModel->totalDebit();
            $data['total_kredit'] = $this->adminModel->totalKredit();
        } elseif ($this->request->getPost('tahun_post')) {
            $data['jurnal_umum'] = $this->adminModel->cariTahunjurnalumum();
            $data['total_debit'] = $this->adminModel->totalDebit();
            $data['total_kredit'] = $this->adminModel->totalKredit();
        } else {
            $bulan = date('m');
            $data['bulan_nama'] = date("F", strtotime(date("Y") . "-" . $bulan . "-01"));
            $data['jurnal_umum'] = $this->adminModel->tampilJurnalumum();
            $data['total_debit'] = $this->adminModel->totalDebit();
            $data['total_kredit'] = $this->adminModel->totalKredit();
        }

        return view('templates/dash_header', $data)
            . view('templates/adm_sidebar', $data)
            . view('templates/adm_header', $data)
            . view('admin/master-data/laporan/jurnal_umum', $data)
            . view('templates/adm_footer');
    }

    public function pdf()
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        }

        $dompdf = new \Dompdf\Dompdf();

        if ($this->request->getPost('katakunci')) {
            $data['jurnal_umum'] = $this->adminModel->cariJurnalumum();
            $data['katakunci'] = $this->request->getPost('katakunci');
            $data['total_debit'] = $this->adminModel->totalDebit();
            $data['total_kredit'] = $this->adminModel->totalKredit();
        } elseif ($this->request->getPost('tanggal_awal')) {
            $data['jurnal_umum'] = $this->adminModel->cariTanggalJurnalumum();
            $data['tanggal_awal'] = $this->request->getPost('tanggal_awal');
            $data['tanggal_akhir'] = $this->request->getPost('tanggal_akhir');
            $data['total_debit'] = $this->adminModel->totalDebit();
            $data['total_kredit'] = $this->adminModel->totalKredit();
        } elseif ($this->request->getPost('bulan_post') && $this->request->getPost('tahun_post')) {
            $data['jurnal_umum'] = $this->adminModel->cariBulantahunjurnalumum();
            $bulan = $this->request->getPost('bulan_post');
            $data['bulan_nama'] = date("F", strtotime(date("Y") . "-" . $bulan . "-01"));
            $data['total_debit'] = $this->adminModel->totalDebit();
            $data['total_kredit'] = $this->adminModel->totalKredit();
        } elseif ($this->request->getPost('tahun_post')) {
            $data['jurnal_umum'] = $this->adminModel->cariTahunjurnalumum();
            $data['total_debit'] = $this->adminModel->totalDebit();
            $data['total_kredit'] = $this->adminModel->totalKredit();
        } else {
            $bulan = date('m');
            $data['bulan_nama'] = date("F", strtotime(date("Y") . "-" . $bulan . "-01"));
            $data['jurnal_umum'] = $this->adminModel->tampilJurnalumum();
            $data['total_debit'] = $this->adminModel->totalDebit();
            $data['total_kredit'] = $this->adminModel->totalKredit();
        }

        $html = view('laporan/laporan_ju', $data);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream("laporan_jurnalumum.pdf", array('Attachment' => 0));
    }

    public function laba_rugi()
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        }

        $data['judul'] = 'Laba Rugi';
        $data['active'] = 'active';
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();

        return view('templates/dash_header', $data)
            . view('templates/adm_sidebar', $data)
            . view('templates/adm_header', $data)
            . view('admin/master-data/laporan/laba_rugi', $data)
            . view('templates/adm_footer')
            . view('templates/dash_footer');
    }

    // DATA PROFIL

    public function profil()
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        }

        $data['judul'] = 'Profil';
        $data['active'] = 'active';
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();

        return view('templates/dash_header', $data)
            . view('templates/adm_sidebar', $data)
            . view('templates/adm_header', $data)
            . view('admin/profil', $data)
            . view('templates/adm_footer')
            . view('templates/dash_footer');
    }

    public function edit_profil()
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        }

        $data['judul'] = 'Edit Profil';
        $data['active'] = 'active';
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();

        $rules = ['nama' => 'required|min_length[3]'];

        if (!$this->validate($rules)) {
            $data['validation'] = $this->validation;
            return view('templates/dash_header', $data)
                . view('templates/adm_sidebar', $data)
                . view('templates/adm_header', $data)
                . view('admin/edit_profil', $data)
                . view('templates/adm_footer')
                . view('templates/dash_footer');
        } else {
            $nama = $this->request->getPost('nama');
            $email = $this->request->getPost('email');

            // Handle image upload
            $upload_image = $this->request->getFile('gambar');

            if ($upload_image && $upload_image->isValid() && !$upload_image->hasMoved()) {
                $validationRule = [
                    'gambar' => [
                        'rules' => 'uploaded[gambar]|max_size[gambar,2048]|is_image[gambar]|mime_in[gambar,image/jpg,image/jpeg,image/png,image/gif]',
                    ],
                ];

                if ($this->validate($validationRule)) {
                    $old_image = $data['user']['gambar'];

                    if ($old_image != 'default.jpg') {
                        $oldImagePath = FCPATH . 'assets/img/profile/' . $old_image;
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }

                    $new_image = $upload_image->getRandomName();
                    $upload_image->move(FCPATH . 'assets/img/profile/', $new_image);

                    $this->db->table('user')
                        ->where('email', $email)
                        ->update(['gambar' => $new_image]);
                }
            }

            $this->db->table('user')
                ->where('email', $email)
                ->update(['nama' => $nama]);

            $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Profil berhasil diubah!</div>');
            return redirect()->to(base_url('admin/profil'));
        }
    }

    public function ganti_password()
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        }

        $data['judul'] = 'Ganti Password';
        $data['active'] = 'active';
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();

        $rules = [
            'passwordsekarang' => 'required|min_length[3]',
            'passwordbaru' => 'required|min_length[6]|matches[ulangipassword]',
            'ulangipassword' => 'required|min_length[6]|matches[passwordbaru]'
        ];

        if (!$this->validate($rules)) {
            $data['validation'] = $this->validation;
            return view('templates/dash_header', $data)
                . view('templates/adm_sidebar', $data)
                . view('templates/adm_header', $data)
                . view('admin/ganti_password', $data)
                . view('templates/adm_footer')
                . view('templates/dash_footer');
        } else {
            $passwordsekarang = $this->request->getPost('passwordsekarang');
            $passwordbaru = $this->request->getPost('passwordbaru');

            if (!password_verify($passwordsekarang, $data['user']['password'])) {
                $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Password Lama Salah</div>');
                return redirect()->to(base_url('admin/ganti_password'));
            } else {
                if ($passwordsekarang == $passwordbaru) {
                    $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Password baru tidak boleh sama dengan yang lama</div>');
                    return redirect()->to(base_url('admin/ganti_password'));
                } else {
                    $password_hash = password_hash($passwordbaru, PASSWORD_DEFAULT);

                    $this->db->table('user')
                        ->where('email', $this->session->get('email'))
                        ->update(['password' => $password_hash]);

                    $this->session->setFlashdata('message', '<div class="alert alert-success" role="alert">Password berhasil diganti</div>');
                    return redirect()->to(base_url('admin/ganti_password'));
                }
            }
        }
    }
}
