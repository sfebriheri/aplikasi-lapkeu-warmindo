# CI3 to CI4 - Real Migration Examples

## Example 1: Simple Model Migration

### CI3 Model (Model_poskeu.php)
```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_poskeu extends CI_Model
{
    public function tampil_posakun()
    {
        return $this->db->get('daftar_akun')->result_array();
    }
}
```

### CI4 Model (PoskeuModel.php)
```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class PoskeuModel extends Model
{
    protected $table = 'daftar_akun';
    protected $primaryKey = 'kode_akun';
    protected $returnType = 'array';
    protected $allowedFields = ['kode_akun', 'akun', 'pos_laporan', 'pos_akun', 'saldo_normal'];

    public function tampilPosakun()
    {
        return $this->findAll();
    }
}
```

---

## Example 2: Complex Model with Custom Queries (Model_admin.php)

### CI3 Version (Excerpt)
```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_admin extends CI_Model
{
    public function tampil_jurnalumum()
    {
        $this->db->where('year(tanggal_transaksi)', date('Y'));
        $this->db->where('month(tanggal_transaksi)', date('m'));
        $this->db->order_by('tanggal_transaksi', 'ASC');
        return $this->db->get('transaksi')->result_array();
    }

    public function total_debit()
    {
        $month = date('m');
        $tahun = date('Y');
        $this->db->where('year(tanggal_transaksi)', $tahun);
        $this->db->where('month(tanggal_transaksi)', $month);
        $this->db->select('SUM(debit) as total');
        return $this->db->get('transaksi')->row()->total;
    }

    public function bukti_transaksi()
    {
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get('transaksi');
        $kode = $query->row_array();

        if ($query->num_rows() <> 0) {
            $data = $kode['bukti_transaksi'];
            $kode = intval($data) + 1;
        } else {
            $kode = 1;
        }

        $kodemax = str_pad($kode, 6, "0", STR_PAD_LEFT);
        return $kodemax;
    }
}
```

### CI4 Version
```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminModel extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'kode_akun', 'akun', 'keterangan', 'tanggal_transaksi',
        'pos_saldo', 'pos_laporan', 'bukti_transaksi',
        'debit', 'kredit', 'pos_akun', 'ref'
    ];

    public function tampilJurnalumum()
    {
        return $this->where('YEAR(tanggal_transaksi)', date('Y'))
                    ->where('MONTH(tanggal_transaksi)', date('m'))
                    ->orderBy('tanggal_transaksi', 'ASC')
                    ->findAll();
    }

    public function totalDebit($tahun = null, $bulan = null)
    {
        $tahun = $tahun ?? date('Y');
        $bulan = $bulan ?? date('m');

        $result = $this->selectSum('debit', 'total')
                       ->where('YEAR(tanggal_transaksi)', $tahun)
                       ->where('MONTH(tanggal_transaksi)', $bulan)
                       ->first();

        return $result['total'] ?? 0;
    }

    public function buktiTransaksi()
    {
        $lastTransaction = $this->orderBy('id', 'DESC')
                                ->first();

        if ($lastTransaction) {
            $kode = intval($lastTransaction['bukti_transaksi']) + 1;
        } else {
            $kode = 1;
        }

        return str_pad($kode, 6, "0", STR_PAD_LEFT);
    }
}
```

---

## Example 3: Controller with Authentication (Admin.php)

### CI3 Version (Excerpt)
```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Model_admin');
        $this->load->library('form_validation');
    }

    public function index()
    {
        if (!$this->session->userdata('email')) {
            $this->session->set_flashdata('message',
                '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            redirect('auth');
        }

        $data['judul'] = 'Menu Utama';
        $data['active'] = 'active';
        $data['user'] = $this->db->get_where('user',
            ['email' => $this->session->userdata('email')])->row_array();

        $this->load->view('templates/dash_header', $data);
        $this->load->view('templates/adm_sidebar', $data);
        $this->load->view('templates/adm_header', $data);
        $this->load->view('admin/index', $data);
        $this->load->view('templates/adm_footer');
    }

    public function transaksi_m()
    {
        if (!$this->session->userdata('email')) {
            $this->session->set_flashdata('message',
                '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            redirect('auth');
        } else {
            if ($this->session->userdata('role_id') == '1') {
                redirect('admin');
            }
        }

        $data['judul'] = 'Transaksi';
        $data['active'] = 'active';
        $data['dd_kodeakun'] = $this->Model_admin->ambil_dropdown();
        $data['user'] = $this->db->get_where('user',
            ['email' => $this->session->userdata('email')])->row_array();
        $data['bukti_transaksi'] = $this->Model_admin->bukti_transaksi();

        $this->load->view('templates/dash_header', $data);
        $this->load->view('templates/adm_sidebar', $data);
        $this->load->view('templates/adm_header', $data);
        $this->load->view('admin/master-data/transaksi_multi', $data);
        $this->load->view('templates/adm_footer');
        $this->load->view('templates/dash_footer');
    }
}
```

### CI4 Version (Using AuthFilter)
```php
<?php

namespace App\Controllers;

use App\Models\AdminModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class Admin extends Controller
{
    protected $adminModel;
    protected $userModel;
    protected $validation;
    protected $session;
    protected $db;

    public function __construct()
    {
        $this->adminModel = new AdminModel();
        $this->userModel = new UserModel();
        $this->validation = \Config\Services::validation();
        $this->session = \Config\Services::session();
        $this->db = \Config\Database::connect();
        helper(['form', 'url']);
    }

    public function index()
    {
        // Auth check handled by AuthFilter
        $data = [
            'judul' => 'Menu Utama',
            'active' => 'active',
            'user' => $this->userModel->getUserByEmail($this->session->get('email'))
        ];

        return view('templates/dash_header', $data)
             . view('templates/adm_sidebar', $data)
             . view('templates/adm_header', $data)
             . view('admin/index', $data)
             . view('templates/adm_footer');
    }

    public function transaksi_m()
    {
        // Check role (only for specific role checks)
        if ($this->session->get('role_id') == '1') {
            return redirect()->to(base_url('admin'));
        }

        $data = [
            'judul' => 'Transaksi',
            'active' => 'active',
            'dd_kodeakun' => $this->adminModel->ambilDropdown(),
            'user' => $this->userModel->getUserByEmail($this->session->get('email')),
            'bukti_transaksi' => $this->adminModel->buktiTransaksi()
        ];

        return view('templates/dash_header', $data)
             . view('templates/adm_sidebar', $data)
             . view('templates/adm_header', $data)
             . view('admin/master-data/transaksi_multi', $data)
             . view('templates/adm_footer')
             . view('templates/dash_footer');
    }
}
```

**AuthFilter.php** (New in CI4):
```php
<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->get('email')) {
            $session->setFlashdata('message',
                '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
            return redirect()->to(base_url('auth'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
```

---

## Example 4: Form Processing with Batch Insert

### CI3 Version
```php
public function insert_transaksi_m()
{
    $this->form_validation->set_rules('kode_akun[]', 'Kode Akun', 'required');
    $this->form_validation->set_rules('debit[]', 'Debit', 'required');
    $this->form_validation->set_rules('kredit[]', 'Kredit', 'required');
    $this->form_validation->set_rules('keterangan[]', 'Keterangan', 'required');

    if ($this->form_validation->run() == FALSE) {
        $this->session->set_flashdata('pesan_error', validation_errors());
        redirect('admin/transaksi_m');
    } else {
        $kode_akun = $_POST['kode_akun'];
        $keterangan = $_POST['keterangan'];
        $tanggal_transaksi = $_POST['tanggal_transaksi'];
        $pos_saldo = $_POST['pos_saldo'];
        $pos_laporan = $_POST['pos_laporan'];
        $bukti_transaksi = $_POST['bukti_transaksi'];
        $akun = $_POST['akun'];
        $debit = $_POST['debit'];
        $kredit = $_POST['kredit'];
        $pos_akun = $_POST['pos_akun'];

        $data = array();
        $index = 0;

        foreach ($kode_akun as $datakd) {
            array_push($data, array(
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
            ));
            $index++;
        }

        // Balance validation
        foreach ($data as $d) {
            $jumlah[] = $d['debit'];
            $jumlahk[] = $d['kredit'];
        }

        $jumlahnya = array_sum($jumlah);
        $jumlahknya = array_sum($jumlahk);

        if ($jumlahnya == $jumlahknya) {
            $this->db->insert_batch('transaksi', $data);
            $this->session->set_flashdata('pesan_sukses', 'Ditambahkan');
            $this->session->set_flashdata('pesan_balance', 'Sudah Balance');
            redirect('admin/transaksi_m');
        } else {
            $this->session->set_flashdata('pesan_error', 'Ditambahkan');
            $this->session->set_flashdata('pesan_tidakbalance', 'Tidak Balance');
            redirect('admin/transaksi_m');
        }
    }
}
```

### CI4 Version
```php
public function insertTransaksiM()
{
    $rules = [
        'kode_akun.*' => 'required',
        'debit.*' => 'required|numeric',
        'kredit.*' => 'required|numeric',
        'keterangan.*' => 'required'
    ];

    if (!$this->validate($rules)) {
        $this->session->setFlashdata('pesan_error',
            $this->validator->getErrors());
        return redirect()->to(base_url('admin/transaksi_m'));
    }

    $kodeAkun = $this->request->getPost('kode_akun');
    $keterangan = $this->request->getPost('keterangan');
    $tanggalTransaksi = $this->request->getPost('tanggal_transaksi');
    $posSaldo = $this->request->getPost('pos_saldo');
    $posLaporan = $this->request->getPost('pos_laporan');
    $buktiTransaksi = $this->request->getPost('bukti_transaksi');
    $akun = $this->request->getPost('akun');
    $debit = $this->request->getPost('debit');
    $kredit = $this->request->getPost('kredit');
    $posAkun = $this->request->getPost('pos_akun');

    $data = [];
    $index = 0;

    foreach ($kodeAkun as $datakd) {
        $data[] = [
            'kode_akun' => $datakd,
            'keterangan' => $keterangan[$index],
            'tanggal_transaksi' => $tanggalTransaksi[$index],
            'pos_saldo' => $posSaldo[$index],
            'pos_laporan' => $posLaporan[$index],
            'bukti_transaksi' => $buktiTransaksi[$index],
            'akun' => $akun[$index],
            'debit' => $debit[$index],
            'kredit' => $kredit[$index],
            'pos_akun' => $posAkun[$index],
            'ref' => 'JU'
        ];
        $index++;
    }

    // Balance validation
    $totalDebit = array_sum(array_column($data, 'debit'));
    $totalKredit = array_sum(array_column($data, 'kredit'));

    if ($totalDebit == $totalKredit) {
        $this->adminModel->insertBatch($data);
        $this->session->setFlashdata('pesan_sukses', 'Ditambahkan');
        $this->session->setFlashdata('pesan_balance', 'Sudah Balance');
        return redirect()->to(base_url('admin/transaksi_m'));
    } else {
        $this->session->setFlashdata('pesan_error', 'Ditambahkan');
        $this->session->setFlashdata('pesan_tidakbalance', 'Tidak Balance');
        return redirect()->to(base_url('admin/transaksi_m'));
    }
}
```

---

## Example 5: File Upload

### CI3 Version
```php
public function edit_profil()
{
    if (!$this->session->userdata('email')) {
        $this->session->set_flashdata('message',
            '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
        redirect('auth');
    }

    $data['judul'] = 'Edit Profil';
    $data['active'] = 'active';
    $data['user'] = $this->db->get_where('user',
        ['email' => $this->session->userdata('email')])->row_array();

    $this->form_validation->set_rules('nama', 'Nama', 'required|trim');

    if ($this->form_validation->run() == false) {
        $this->load->view('templates/dash_header', $data);
        $this->load->view('templates/adm_sidebar', $data);
        $this->load->view('templates/adm_header', $data);
        $this->load->view('admin/edit_profil', $data);
        $this->load->view('templates/adm_footer');
        $this->load->view('templates/dash_footer');
    } else {
        $nama = $this->input->post('nama');
        $email = $this->input->post('email');

        $upload_image = $_FILES['gambar']['name'];

        if ($upload_image) {
            $config['allowed_types'] = 'gif|jpg|png';
            $config['max_size'] = '2048';
            $config['upload_path'] = './assets/img/profile/';

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('gambar')) {
                $old_image = $data['user']['gambar'];

                if ($old_image != 'default.jpg') {
                    unlink(FCPATH . 'assets/img/profile/' . $old_image);
                }

                $new_image = $this->upload->data('file_name');
                $this->db->set('gambar', $new_image);
            } else {
                echo $this->upload->display_errors();
            }
        }

        $this->db->set('nama', $nama);
        $this->db->where('email', $email);
        $this->db->update('user');

        $this->session->set_flashdata('message',
            '<div class="alert alert-success" role="alert">Profil berhasil diubah!</div>');
        redirect('admin/profil');
    }
}
```

### CI4 Version
```php
public function editProfil()
{
    $data = [
        'judul' => 'Edit Profil',
        'active' => 'active',
        'user' => $this->userModel->getUserByEmail($this->session->get('email'))
    ];

    $rules = ['nama' => 'required|min_length[3]'];

    if ($this->request->getMethod() === 'post' && $this->validate($rules)) {
        $nama = $this->request->getPost('nama', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $email = $this->session->get('email');

        $updateData = ['nama' => $nama];

        // Handle file upload
        $imageFile = $this->request->getFile('gambar');

        if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
            $validationRule = [
                'gambar' => [
                    'rules' => 'uploaded[gambar]|max_size[gambar,2048]|is_image[gambar]|mime_in[gambar,image/jpg,image/jpeg,image/png,image/gif]',
                ],
            ];

            if ($this->validate($validationRule)) {
                // Delete old image
                $oldImage = $data['user']['gambar'];
                if ($oldImage != 'default.jpg') {
                    $oldPath = FCPATH . 'assets/img/profile/' . $oldImage;
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }

                // Move new image
                $newName = $imageFile->getRandomName();
                $imageFile->move(FCPATH . 'assets/img/profile', $newName);
                $updateData['gambar'] = $newName;
            }
        }

        // Update user
        $user = $this->userModel->getUserByEmail($email);
        $this->userModel->update($user['id'], $updateData);

        $this->session->setFlashdata('message',
            '<div class="alert alert-success" role="alert">Profil berhasil diubah!</div>');
        return redirect()->to(base_url('admin/profil'));
    }

    $data['validation'] = $this->validator;

    return view('templates/dash_header', $data)
         . view('templates/adm_sidebar', $data)
         . view('templates/adm_header', $data)
         . view('admin/edit_profil', $data)
         . view('templates/adm_footer')
         . view('templates/dash_footer');
}
```

---

## Example 6: PDF Generation

### CI3 Version
```php
public function pdf()
{
    if (!$this->session->userdata('email')) {
        $this->session->set_flashdata('message',
            '<div class="alert alert-danger" role="alert">Anda harus login terlebih dahulu</div>');
        redirect('auth');
    }

    $this->load->library('dompdf_gen');

    $data['jurnal_umum'] = $this->Model_admin->tampil_jurnalumum();
    $data['total_debit'] = $this->Model_admin->total_debit();
    $data['total_kredit'] = $this->Model_admin->total_kredit();

    $this->load->view('laporan/laporan_ju', $data);

    $paper_size = 'A4';
    $orientation = 'landscape';
    $html = $this->output->get_output();
    $this->dompdf->set_paper($paper_size, $orientation);

    $this->dompdf->load_html($html);
    $this->dompdf->render();
    $this->dompdf->stream("laporan_jurnalumum.pdf", array('Attachment' => 0));
}
```

### CI4 Version
```php
use Dompdf\Dompdf;
use Dompdf\Options;

public function pdf()
{
    $data = [
        'jurnal_umum' => $this->adminModel->tampilJurnalumum(),
        'total_debit' => $this->adminModel->totalDebit(),
        'total_kredit' => $this->adminModel->totalKredit()
    ];

    $html = view('laporan/laporan_ju', $data);

    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    $dompdf->stream("laporan_jurnalumum.pdf", ["Attachment" => 0]);
}
```

---

## Example 7: Complex Date Filtering (Buku Besar)

### CI3 Version (Simplified)
```php
public function index()
{
    if ($this->input->post('tanggal_awal')) {
        $t_aw = $this->input->post('tanggal_awal');
        $t_ak = $this->input->post('tanggal_akhir');
        $data['t_aw'] = $t_aw;
        $data['t_ak'] = $t_ak;

        $month_awal = date("n", strtotime($t_aw));
        $tahun = date("Y", strtotime($t_aw));
        $bulan = $month_awal;

        $data['tahun'] = $tahun;
        $data['bulan'] = $bulan;

        if ($t_aw != date($tahun."-01-01")) {
            $data['date_awal'] = date($tahun."-01-01");
            $data['date_akhir'] = date("Y-m-d", strtotime("$t_aw -1 day"));
            $data['tgl_awal_data'] = $t_aw;
            $data['tgl_akhir_data'] = $t_ak;
        } else {
            // Handle if start date is Jan 1
        }
    }
}
```

### CI4 Version
```php
use CodeIgniter\I18n\Time;

public function index()
{
    if ($this->request->getPost('tanggal_awal')) {
        $tAw = $this->request->getPost('tanggal_awal');
        $tAk = $this->request->getPost('tanggal_akhir');

        $dateAwal = Time::parse($tAw);
        $tahun = $dateAwal->getYear();
        $bulan = $dateAwal->getMonth();

        $data = [
            't_aw' => $tAw,
            't_ak' => $tAk,
            'tahun' => $tahun,
            'bulan' => $bulan
        ];

        if ($tAw != date($tahun."-01-01")) {
            $data['date_awal'] = date($tahun."-01-01");
            $data['date_akhir'] = $dateAwal->subDays(1)->toDateString();
            $data['tgl_awal_data'] = $tAw;
            $data['tgl_akhir_data'] = $tAk;
        } else {
            // Handle if start date is Jan 1
        }
    }
}
```

---

## Example 8: AJAX Response (Get Account by Code)

### CI3 Version
```php
public function get_kodeakun()
{
    $kode_akun = $this->input->post('kode_akun');
    $data = $this->Model_admin->isi_field_byKode($kode_akun);
    echo json_encode($data);
}
```

### CI4 Version
```php
public function getKodeakun()
{
    $kodeAkun = $this->request->getPost('kode_akun');
    $data = $this->adminModel->isiFieldByKode($kodeAkun);

    return $this->response->setJSON($data);
}
```

---

## Quick Conversion Checklist

When converting a controller/model:

1. [ ] Add namespace declaration
2. [ ] Add use statements for models/services
3. [ ] Change class extension (CI_Controller → Controller, CI_Model → Model)
4. [ ] Update constructor
5. [ ] Replace $this->load->model() with new ModelName()
6. [ ] Replace $this->load->view() with view() or return view()
7. [ ] Replace $this->input->post() with $this->request->getPost()
8. [ ] Replace $this->session->userdata() with $this->session->get()
9. [ ] Replace redirect() with return redirect()->to()
10. [ ] Replace $this->form_validation with $this->validate()
11. [ ] Update database query syntax
12. [ ] Replace ->row_array() with ->getRowArray()
13. [ ] Replace ->result_array() with ->getResultArray()
14. [ ] Update file upload logic
15. [ ] Test thoroughly!

---

These examples should give you a clear understanding of the transformation patterns needed for your migration.
