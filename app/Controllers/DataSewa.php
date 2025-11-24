<?php

namespace App\Controllers;

use App\Models\DsModel;
use App\Models\LabarugiModel;
use CodeIgniter\Controller;

class DataSewa extends BaseController
{
    protected $dsModel;
    protected $labarugiModel;
    protected $validation;
    protected $session;
    protected $db;

    public function __construct()
    {
        $this->dsModel = new DsModel();
        $this->labarugiModel = new LabarugiModel();
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

        $data['judul'] = 'Data Sewa';
        $data['active'] = 'active';
        $data['status'] = ['L', 'BL'];
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();
        $data['dd_kendaraan'] = $this->db->table('data_kendaraan')->get()->getResult();
        $data['dd_bulan'] = $this->labarugiModel->ddBulan();

        return view('templates/dash_header', $data)
            . view('templates/adm_sidebar', $data)
            . view('templates/adm_header', $data)
            . view('admin/master-data/data_sewa/data_sewa.php', $data)
            . view('templates/adm_footer')
            . view('templates/dash_footer');
    }

    public function data_kembali()
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        }

        $data['judul'] = 'Data Sewa';
        $data['active'] = 'active';
        $data['status'] = ['L', 'BL'];
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();
        $data['dd_bulan'] = $this->labarugiModel->ddBulan();

        return view('templates/dash_header', $data)
            . view('templates/adm_sidebar', $data)
            . view('templates/adm_header', $data)
            . view('admin/master-data/data_sewa/data_kembali.php', $data)
            . view('templates/adm_footer')
            . view('templates/dash_footer');
    }

    public function tambah_ds()
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        } else {
            if ($this->session->get('role_id') == '1') {
                return redirect()->to(base_url('admin'));
            }
        }

        $data['judul'] = 'Tambah Data Sewa';
        $data['active'] = 'active';
        $data['dd_kendaraan'] = $this->db->table('data_kendaraan')->get()->getResult();
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();
        $data['bukti_transaksi'] = $this->dsModel->buktiTransaksi();
        $data['id_sewa'] = $this->dsModel->idSewa();

        $rules = [
            'nama_penyewa' => 'required',
            'tgl_sewa' => 'required',
            'tgl_kembali' => 'required'
        ];

        if (!$this->validate($rules)) {
            $data['validation'] = $this->validation;
            return view('templates/dash_header', $data)
                . view('templates/adm_sidebar', $data)
                . view('templates/adm_header', $data)
                . view('admin/master-data/data_sewa/tambah_ds', $data)
                . view('templates/adm_footer')
                . view('templates/dash_footer');
        } else {
            $this->dsModel->tambahDatasewa();
            $this->dsModel->tambahTransaksiDs();
            return redirect()->to(base_url('data_sewa'));
        }
    }

    public function get_kodeakun()
    {
        $akun = $this->request->getPost('akun');
        $data = $this->dsModel->isiFieldByKode($akun);
        return $this->response->setJSON($data);
    }

    public function updatelunas()
    {
        $this->dsModel->updatelunas($this->request->getPost('id_update'));
        return redirect()->to(base_url('data_sewa'));
    }

    public function hapusdata($id_sewa)
    {
        $this->dsModel->hapusdata($id_sewa);
        return redirect()->to(base_url('data_sewa'));
    }

    public function hapusdata_ju($id_sewa)
    {
        $this->dsModel->hapusdata($id_sewa);
        return redirect()->to(base_url('admin/jurnal_umum'));
    }

    public function update_ds($id_sewa)
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        } else {
            if ($this->session->get('role_id') == '1') {
                return redirect()->to(base_url('admin'));
            }
        }

        $data['judul'] = 'Ubah Data Sewa';
        $data['active'] = 'active';
        $data['dd_kendaraan'] = $this->db->table('data_kendaraan')->get()->getResult();
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();
        $data['bukti_transaksi'] = $this->dsModel->buktiTransaksi();
        $data['d_sewa'] = $this->dsModel->getDatasewa($id_sewa);
        $data['d_trans'] = $this->dsModel->getDatatrans($id_sewa);

        $rules = [
            'nama_penyewa' => 'required',
            'tgl_sewa' => 'required',
            'tgl_kembali' => 'required'
        ];

        if (!$this->validate($rules)) {
            $data['validation'] = $this->validation;
            return view('templates/dash_header', $data)
                . view('templates/adm_sidebar', $data)
                . view('templates/adm_header', $data)
                . view('admin/master-data/data_sewa/update_ds', $data)
                . view('templates/adm_footer')
                . view('templates/dash_footer');
        } else {
            if (count($this->request->getPost('id')) == 5) {
                $this->dsModel->update5data($id_sewa);
            } elseif ($this->request->getPost('data2')) {
                $this->dsModel->update2data($id_sewa);
            } else {
                $this->dsModel->updateDs($id_sewa);
                $this->dsModel->updateTrans($id_sewa);
            }
            return redirect()->to(base_url('data_sewa'));
        }
    }

    public function update_ju($id_sewa)
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        } else {
            if ($this->session->get('role_id') == '1') {
                return redirect()->to(base_url('admin'));
            }
        }

        $data['judul'] = 'Ubah Data Sewa';
        $data['active'] = 'active';
        $data['dd_kendaraan'] = $this->db->table('data_kendaraan')->get()->getResult();
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();
        $data['bukti_transaksi'] = $this->dsModel->buktiTransaksi();
        $data['d_sewa'] = $this->dsModel->getDatasewa($id_sewa);
        $data['d_trans'] = $this->dsModel->getDatatrans($id_sewa);

        $rules = [
            'nama_penyewa' => 'required',
            'tgl_sewa' => 'required',
            'tgl_kembali' => 'required'
        ];

        if (!$this->validate($rules)) {
            $data['validation'] = $this->validation;
            return view('templates/dash_header', $data)
                . view('templates/adm_sidebar', $data)
                . view('templates/adm_header', $data)
                . view('admin/master-data/data_sewa/update_ds', $data)
                . view('templates/adm_footer')
                . view('templates/dash_footer');
        } else {
            if (count($this->request->getPost('id')) == 5) {
                $this->dsModel->update5data($id_sewa);
            } elseif ($this->request->getPost('data2')) {
                $this->dsModel->update2data($id_sewa);
            } else {
                $this->dsModel->updateDs($id_sewa);
                $this->dsModel->updateTrans($id_sewa);
            }
            return redirect()->to(base_url('admin/jurnal_umum'));
        }
    }

    public function cetak_ds()
    {
        $data['status'] = ['L', 'BL'];

        $dompdf = new \Dompdf\Dompdf();
        $html = view('laporan/laporan_datasewa', $data);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream("laporan_datasewa.pdf", array('Attachment' => 0));
    }

    public function cetak_dk()
    {
        $data['status'] = ['L', 'BL'];

        $dompdf = new \Dompdf\Dompdf();
        $html = view('laporan/laporan_datakembali', $data);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream("laporan_datakembali.pdf", array('Attachment' => 0));
    }

    // TAMBAH KENDARAAN

    public function tambah_mobil()
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        } else {
            if ($this->session->get('role_id') == '1') {
                return redirect()->to(base_url('admin'));
            }
        }

        $data['judul'] = 'Tambah Mobil';
        $data['active'] = 'active';
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();
        $data['data_kendaraan'] = $this->dsModel->tampilMobil();

        $rules = [
            'nama' => 'required',
            'plat' => 'required'
        ];

        if (!$this->validate($rules)) {
            $data['validation'] = $this->validation;
            return view('templates/dash_header', $data)
                . view('templates/adm_sidebar', $data)
                . view('templates/adm_header', $data)
                . view('admin/master-data/data_sewa/tambah_mobil', $data)
                . view('templates/adm_footer')
                . view('templates/dash_footer');
        } else {
            $this->dsModel->tambahMobil();
            return redirect()->to(base_url('data_sewa/tambah_mobil'));
        }
    }

    public function update_mobil($id)
    {
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        } else {
            if ($this->session->get('role_id') == '1') {
                return redirect()->to(base_url('admin'));
            }
        }

        $data['judul'] = 'Tambah Mobil';
        $data['active'] = 'active';
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();
        $data['data_kendaraan'] = $this->dsModel->tampilMobil();

        $rules = [
            'nama' => 'required',
            'plat' => 'required'
        ];

        if (!$this->validate($rules)) {
            $data['d_mobil'] = $this->db->table('data_kendaraan')
                ->where('id', $id)
                ->get()
                ->getRowArray();
            $data['validation'] = $this->validation;
            return view('templates/dash_header', $data)
                . view('templates/adm_sidebar', $data)
                . view('templates/adm_header', $data)
                . view('admin/master-data/data_sewa/update_mobil', $data)
                . view('templates/adm_footer')
                . view('templates/dash_footer');
        } else {
            $this->dsModel->updateMobil();
            return redirect()->to(base_url('data_sewa/tambah_mobil'));
        }
    }

    public function hapus_mobil($id)
    {
        $this->dsModel->hapusMobil($id);
        return redirect()->to(base_url('Data_sewa/tambah_mobil'));
    }
}
