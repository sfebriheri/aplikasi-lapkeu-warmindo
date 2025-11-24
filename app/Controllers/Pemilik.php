<?php

namespace App\Controllers;

use App\Models\PemilikModel;
use CodeIgniter\Controller;

class Pemilik extends BaseController
{
    protected $pemilikModel;
    protected $validation;
    protected $session;
    protected $db;

    public function __construct()
    {
        $this->pemilikModel = new PemilikModel();
        $this->validation = \Config\Services::validation();
        $this->session = \Config\Services::session();
        $this->db = \Config\Database::connect();
        helper(['form', 'url']);

        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message', '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        } else {
            if ($this->session->get('role_id') == '2') {
                return redirect()->to(base_url('admin'));
            }
        }
    }

    public function index()
    {
        $data['judul'] = 'Aktivasi';
        $data['active'] = 'active';
        $data['user'] = $this->db->table('user')
            ->where('email', $this->session->get('email'))
            ->get()
            ->getRowArray();
        $data['data_user'] = $this->db->table('user')->get()->getResultArray();

        return view('templates/dash_header', $data)
            . view('templates/adm_sidebar', $data)
            . view('templates/adm_header', $data)
            . view('pemilik/data_user', $data)
            . view('templates/adm_footer')
            . view('templates/dash_footer');
    }

    public function update_aktif($id)
    {
        $this->pemilikModel->updateAktif($id);
        return redirect()->to(base_url('pemilik'));
    }

    public function update_nonaktif($id)
    {
        $this->pemilikModel->updateNonaktif($id);
        return redirect()->to(base_url('pemilik'));
    }

    public function hapus($id)
    {
        $this->pemilikModel->hapus($id);
        return redirect()->to(base_url('pemilik'));
    }

    public function update_uplevel($id)
    {
        $this->pemilikModel->upLevel($id);
        return redirect()->to(base_url('pemilik'));
    }

    public function update_downlevel($id)
    {
        $this->pemilikModel->downLevel($id);
        return redirect()->to(base_url('pemilik'));
    }
}
