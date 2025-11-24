# CodeIgniter 3 to CodeIgniter 4 Migration Plan
## LAPKEU Warmindo Application

**Generated:** 2025-11-24
**Status:** Planning Phase
**Total Files to Migrate:** 22 files (11 controllers + 11 models)

---

## Table of Contents
1. [Current State Analysis](#current-state-analysis)
2. [Controllers Migration Map](#controllers-migration-map)
3. [Models Migration Map](#models-migration-map)
4. [Key Transformation Rules](#key-transformation-rules)
5. [Special Considerations](#special-considerations)
6. [Step-by-Step Migration Checklist](#step-by-step-migration-checklist)
7. [Testing Strategy](#testing-strategy)

---

## 1. Current State Analysis

### CI3 Structure (Legacy)
- **Location:** `/application/`
- **Controllers:** 11 files (3,772 total lines)
- **Models:** 11 files (2,574 total lines)
- **Entry Point:** `/index.php` (root)
- **Framework Version:** CodeIgniter 3.1.9

### CI4 Structure (Target)
- **Location:** `/app/`
- **Controllers:** 3 files (Auth.php, BaseController.php, Home.php) - ALREADY MIGRATED
- **Models:** 2 files (AccountingModel.php, UserModel.php) - ALREADY MIGRATED
- **Entry Point:** `/public/index.php`
- **Framework Version:** CodeIgniter 4.5

### Already Migrated
- ✅ Auth Controller (358 lines → CI4 implemented)
- ✅ User Model (215 lines → UserModel.php)
- ✅ Accounting Model (261 lines → AccountingModel.php)

---

## 2. Controllers Migration Map

| # | CI3 Controller | CI4 Controller | Lines | Priority | Complexity | Status |
|---|----------------|----------------|-------|----------|------------|--------|
| 1 | Admin.php | Admin.php | 725 | HIGH | HIGH | Pending |
| 2 | ~~Auth.php~~ | ~~Auth.php~~ | 358 | - | - | ✅ DONE |
| 3 | Buku_besar.php | BukuBesar.php | 398 | HIGH | MEDIUM | Pending |
| 4 | Data_sewa.php | DataSewa.php | 382 | MEDIUM | MEDIUM | Pending |
| 5 | Jp.php | Jp.php | 231 | MEDIUM | LOW | Pending |
| 6 | Labarugi.php | Labarugi.php | 482 | HIGH | HIGH | Pending |
| 7 | Master.php | Master.php | 497 | HIGH | MEDIUM | Pending |
| 8 | Pemilik.php | Pemilik.php | 103 | LOW | LOW | Pending |
| 9 | Per_modal.php | PerModal.php | 276 | MEDIUM | MEDIUM | Pending |
| 10 | Poskeu.php | Poskeu.php | 295 | HIGH | MEDIUM | Pending |
| 11 | Welcome.php | Home.php | 25 | LOW | LOW | Pending |

### Controller Migration Details

#### 1. Admin.php → Admin.php
**Source:** `/application/controllers/Admin.php`
**Target:** `/app/Controllers/Admin.php`
**Lines:** 725
**Complexity:** HIGH (multi-transaction handling, file uploads, complex validation)

**Key Methods to Migrate:**
- `__construct()` - Load models and libraries
- `index()` - Dashboard
- `transaksi_m()` - Multi-row transaction entry
- `insert_transaksi_m()` - Batch insert with balance validation
- `ubahTransaksi()` - Update transactions
- `hapusTransaksi()` - Delete transactions
- `jurnal_umum()` - General journal report with filtering
- `pdf()` - PDF generation for reports
- `profil()` - User profile management
- `edit_profil()` - Profile editing with image upload
- `ganti_password()` - Password change with validation

**Special Handling:**
- Batch inserts: `$this->db->insert_batch()` → `$this->model->insertBatch()`
- Batch updates: `$this->db->update_batch()` → `$this->model->updateBatch()`
- File uploads: CI3 Upload library → CI4 File handling
- Session flash data: `set_flashdata()` → `setFlashdata()`
- Form validation: CI3 form_validation library → CI4 Validation service
- Direct DB queries: `$this->db->get_where()` → Model methods or Query Builder

---

#### 3. Buku_besar.php → BukuBesar.php
**Source:** `/application/controllers/Buku_besar.php`
**Target:** `/app/Controllers/BukuBesar.php`
**Lines:** 398
**Complexity:** MEDIUM (date calculations, ledger logic)

**Key Methods:**
- `index()` - Ledger display with date filtering
- `pdf()` - PDF generation for ledger

**Special Handling:**
- Complex date calculations for period ranges
- Month/year filtering logic
- Opening balance calculations

---

#### 4. Data_sewa.php → DataSewa.php
**Source:** `/application/controllers/Data_sewa.php`
**Target:** `/app/Controllers/DataSewa.php`
**Lines:** 382
**Complexity:** MEDIUM

**Key Methods:**
- Rental data management
- CRUD operations for rental records

---

#### 5. Jp.php → Jp.php
**Source:** `/application/controllers/Jp.php`
**Target:** `/app/Controllers/Jp.php`
**Lines:** 231
**Complexity:** LOW

---

#### 6. Labarugi.php → Labarugi.php
**Source:** `/application/controllers/Labarugi.php`
**Target:** `/app/Controllers/Labarugi.php`
**Lines:** 482
**Complexity:** HIGH (financial calculations, report generation)

**Key Methods:**
- Income statement generation
- Period-based calculations
- PDF export

---

#### 7. Master.php → Master.php
**Source:** `/application/controllers/Master.php`
**Target:** `/app/Controllers/Master.php`
**Lines:** 497
**Complexity:** MEDIUM (master data management)

**Key Methods:**
- `index()` - Chart of accounts listing
- `tambah_daftarakun()` - Add account
- `ubahDaftarAkun()` - Edit account
- `hapusDaftarAkun()` - Delete account
- `saldo_awal()` - Opening balance management
- `kas_masuk()` - Cash in report
- `kas_keluar()` - Cash out report

**Special Handling:**
- Code generation for accounts (kode_al, kode_at, etc.)
- Dynamic form validation

---

#### 8. Pemilik.php → Pemilik.php
**Source:** `/application/controllers/Pemilik.php`
**Target:** `/app/Controllers/Pemilik.php`
**Lines:** 103
**Complexity:** LOW

---

#### 9. Per_modal.php → PerModal.php
**Source:** `/application/controllers/Per_modal.php`
**Target:** `/app/Controllers/PerModal.php`
**Lines:** 276
**Complexity:** MEDIUM

**Key Methods:**
- Capital changes report
- Equity calculations

---

#### 10. Poskeu.php → Poskeu.php
**Source:** `/application/controllers/Poskeu.php`
**Target:** `/app/Controllers/Poskeu.php`
**Lines:** 295
**Complexity:** MEDIUM

**Key Methods:**
- `index()` - Financial position report
- `cetak_poskeu()` - PDF generation

**Special Handling:**
- Complex period calculations
- Integration with profit/loss calculations

---

#### 11. Welcome.php → Home.php
**Source:** `/application/controllers/Welcome.php`
**Target:** `/app/Controllers/Home.php`
**Lines:** 25
**Complexity:** LOW (simple landing page)

---

## 3. Models Migration Map

| # | CI3 Model | CI4 Model | Lines | Priority | Complexity | Status |
|---|-----------|-----------|-------|----------|------------|--------|
| 1 | ~~Accounting_model.php~~ | ~~AccountingModel.php~~ | 261 | - | - | ✅ DONE |
| 2 | Model_admin.php | AdminModel.php | 337 | HIGH | MEDIUM | Pending |
| 3 | Model_bb.php | BukuBesarModel.php | 95 | HIGH | LOW | Pending |
| 4 | Model_ds.php | DataSewaModel.php | 538 | MEDIUM | HIGH | Pending |
| 5 | Model_jp.php | JpModel.php | 342 | MEDIUM | MEDIUM | Pending |
| 6 | Model_labarugi.php | LabaRugiModel.php | 47 | HIGH | LOW | Pending |
| 7 | Model_master.php | MasterModel.php | 374 | HIGH | MEDIUM | Pending |
| 8 | Model_pemilik.php | PemilikModel.php | 40 | LOW | LOW | Pending |
| 9 | Model_pmodal.php | PerModalModel.php | 312 | MEDIUM | MEDIUM | Pending |
| 10 | Model_poskeu.php | PoskeuModel.php | 13 | HIGH | LOW | Pending |
| 11 | ~~User_model.php~~ | ~~UserModel.php~~ | 215 | - | - | ✅ DONE |

### Model Migration Details

#### 2. Model_admin.php → AdminModel.php
**Source:** `/application/models/Model_admin.php`
**Target:** `/app/Models/AdminModel.php`
**Lines:** 337

**Key Methods:**
- `tampil_jurnalumum()` - Display general journal
- `getTransaksiById()` - Get transaction by ID
- `tambahTransaksi()` - Add transaction
- `hapusJurnalUmum()` - Delete journal entry
- `ambil_dropdown()` - Get dropdown data
- `isi_field_byKode()` - Fill fields by code (AJAX)
- `cari_jurnalumum()` - Search journal
- `total_debit()` - Calculate total debit
- `total_kredit()` - Calculate total credit
- `bukti_transaksi()` - Generate transaction proof number

**CI4 Model Configuration:**
```php
protected $table = 'transaksi';
protected $primaryKey = 'id';
protected $allowedFields = ['kode_akun', 'akun', 'keterangan', 'tanggal_transaksi',
                             'pos_saldo', 'pos_laporan', 'bukti_transaksi',
                             'debit', 'kredit', 'pos_akun', 'ref'];
protected $useTimestamps = false;
```

---

#### 3. Model_bb.php → BukuBesarModel.php
**Source:** `/application/models/Model_bb.php`
**Target:** `/app/Models/BukuBesarModel.php`
**Lines:** 95

**Key Methods:**
- `tampil_bukubesar()` - Display ledger
- `dd_bulan()` - Month dropdown data

---

#### 4. Model_ds.php → DataSewaModel.php
**Source:** `/application/models/Model_ds.php`
**Target:** `/app/Models/DataSewaModel.php`
**Lines:** 538 (LARGEST MODEL)

**Complexity:** HIGH - This is the most complex model
**Special Handling:**
- Rental transactions
- Complex business logic
- Multiple joins and calculations

---

#### 5. Model_jp.php → JpModel.php
**Source:** `/application/models/Model_jp.php`
**Target:** `/app/Models/JpModel.php`
**Lines:** 342

---

#### 6. Model_labarugi.php → LabaRugiModel.php
**Source:** `/application/models/Model_labarugi.php`
**Target:** `/app/Models/LabaRugiModel.php`
**Lines:** 47 (SMALLEST MODEL)

**Key Methods:**
- `dd_bulan()` - Month dropdown
- Simple profit/loss calculations

---

#### 7. Model_master.php → MasterModel.php
**Source:** `/application/models/Model_master.php`
**Target:** `/app/Models/MasterModel.php`
**Lines:** 374

**Key Methods:**
- `tampil_daftarakun()` - Display chart of accounts
- `getDaftarAkunById()` - Get account by ID
- `tambahDaftarAkun()` - Add account
- `ubahDaftarAkun()` - Update account
- `hapusDaftarAkun()` - Delete account
- `kode_al()`, `kode_at()`, `kode_k()`, etc. - Code generators
- `tambah_saldoawal()` - Add opening balance
- `kas()` - Cash transactions

**Special Handling:**
- Auto-increment code generation logic
- Multiple code patterns (1-1xx, 1-2xx, 2-xxx, etc.)

---

#### 8. Model_pemilik.php → PemilikModel.php
**Source:** `/application/models/Model_pemilik.php`
**Target:** `/app/Models/PemilikModel.php`
**Lines:** 40

---

#### 9. Model_pmodal.php → PerModalModel.php
**Source:** `/application/models/Model_pmodal.php`
**Target:** `/app/Models/PerModalModel.php`
**Lines:** 312

**Key Methods:**
- Capital/equity calculations
- Owner's equity tracking

---

#### 10. Model_poskeu.php → PoskeuModel.php
**Source:** `/application/models/Model_poskeu.php`
**Target:** `/app/Models/PoskeuModel.php`
**Lines:** 13 (SECOND SMALLEST)

**Key Methods:**
- Financial position calculations
- Simple queries

---

## 4. Key Transformation Rules

### A. File Structure Changes

#### CI3 → CI4
```
CI3: defined('BASEPATH') OR exit('No direct script access allowed');
     class ClassName extends CI_Controller

CI4: namespace App\Controllers;
     use CodeIgniter\Controller;
     class ClassName extends Controller
```

### B. Controllers - Method Transformations

#### 1. Constructor
**CI3:**
```php
public function __construct() {
    parent::__construct();
    $this->load->model('Model_admin');
    $this->load->library('form_validation');
}
```

**CI4:**
```php
protected $adminModel;
protected $validation;

public function __construct() {
    $this->adminModel = new \App\Models\AdminModel();
    $this->validation = \Config\Services::validation();
    helper(['form', 'url']);
}
```

---

#### 2. Loading Views
**CI3:**
```php
$this->load->view('templates/header', $data);
$this->load->view('admin/index', $data);
$this->load->view('templates/footer');
```

**CI4:**
```php
return view('templates/header', $data)
     . view('admin/index', $data)
     . view('templates/footer');
```

---

#### 3. Session Handling
**CI3:**
```php
$this->session->userdata('email')
$this->session->set_flashdata('message', 'Success');
$this->session->set_userdata('key', 'value');
$this->session->unset_userdata('key');
```

**CI4:**
```php
$this->session->get('email')
$this->session->setFlashdata('message', 'Success');
$this->session->set('key', 'value');
$this->session->remove('key');
```

---

#### 4. Redirects
**CI3:**
```php
redirect('controller/method');
```

**CI4:**
```php
return redirect()->to('controller/method');
// OR
return redirect()->to(base_url('controller/method'));
```

---

#### 5. Input Handling
**CI3:**
```php
$this->input->post('field', true);
$this->input->get('param');
```

**CI4:**
```php
$this->request->getPost('field', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$this->request->getGet('param');
```

---

#### 6. Form Validation
**CI3:**
```php
$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
if ($this->form_validation->run() == FALSE) {
    // Validation failed
}
```

**CI4:**
```php
$rules = [
    'email' => 'required|valid_email'
];

if (!$this->validate($rules)) {
    // Validation failed
    $data['validation'] = $this->validator;
}
```

---

#### 7. Database Queries - Direct Access
**CI3:**
```php
$this->db->get_where('user', ['email' => $email])->row_array();
$this->db->where('status', 'active')->get('table')->result_array();
$this->db->insert('table', $data);
$this->db->update('table', $data, ['id' => $id]);
$this->db->delete('table', ['id' => $id]);
```

**CI4:**
```php
$db = \Config\Database::connect();
$db->table('user')->where('email', $email)->get()->getRowArray();
$db->table('table')->where('status', 'active')->get()->getResultArray();
$db->table('table')->insert($data);
$db->table('table')->update($data, ['id' => $id]);
$db->table('table')->delete(['id' => $id]);
```

**BETTER APPROACH - Use Models:**
```php
$this->userModel->where('email', $email)->first();
$this->model->where('status', 'active')->findAll();
$this->model->insert($data);
$this->model->update($id, $data);
$this->model->delete($id);
```

---

#### 8. Batch Operations
**CI3:**
```php
$this->db->insert_batch('table', $data_array);
$this->db->update_batch('table', $data_array, 'id');
```

**CI4:**
```php
$db = \Config\Database::connect();
$db->table('table')->insertBatch($data_array);
$db->table('table')->updateBatch($data_array, 'id');

// OR in Model:
$this->model->insertBatch($data_array);
$this->model->updateBatch($data_array);
```

---

#### 9. File Uploads
**CI3:**
```php
$config['upload_path'] = './uploads/';
$config['allowed_types'] = 'gif|jpg|png';
$config['max_size'] = 2048;

$this->load->library('upload', $config);

if ($this->upload->do_upload('userfile')) {
    $data = $this->upload->data();
    $file_name = $data['file_name'];
}
```

**CI4:**
```php
$file = $this->request->getFile('userfile');

if ($file->isValid() && !$file->hasMoved()) {
    $validationRule = [
        'userfile' => [
            'rules' => 'uploaded[userfile]|max_size[userfile,2048]|is_image[userfile]',
        ],
    ];

    if ($this->validate($validationRule)) {
        $newName = $file->getRandomName();
        $file->move(WRITEPATH . 'uploads', $newName);
    }
}
```

---

### C. Models - Class Structure

#### CI3 Model
```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_admin extends CI_Model {
    protected $table = 'transaksi';

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    public function get_by_id($id) {
        return $this->db->get_where($this->table, ['id' => $id])->row_array();
    }

    public function insert_data($data) {
        return $this->db->insert($this->table, $data);
    }
}
```

#### CI4 Model
```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminModel extends Model {
    protected $table = 'transaksi';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'kode_akun', 'akun', 'keterangan', 'tanggal_transaksi',
        'pos_saldo', 'pos_laporan', 'bukti_transaksi',
        'debit', 'kredit', 'pos_akun', 'ref'
    ];

    protected $useTimestamps = false;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation rules
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;

    public function getById($id) {
        return $this->find($id);
    }

    public function insertData($data) {
        return $this->insert($data);
    }
}
```

---

### D. Query Builder Changes

| CI3 | CI4 |
|-----|-----|
| `->row()` | `->getRow()` |
| `->row_array()` | `->getRowArray()` |
| `->result()` | `->getResult()` |
| `->result_array()` | `->getResultArray()` |
| `->num_rows()` | `->getNumRows()` |
| `$this->db->insert_id()` | `$db->insertID()` |
| `$this->db->affected_rows()` | `$db->affectedRows()` |

---

### E. Helper Functions

| CI3 | CI4 |
|-----|-----|
| `base_url()` | `base_url()` (same) |
| `site_url()` | `site_url()` (same) |
| `redirect()` | `redirect()->to()` |
| `form_open()` | `form_open()` (same) |
| `anchor()` | `anchor()` (same) |

---

## 5. Special Considerations

### A. Session Authentication Middleware

In CI3, authentication is checked in each controller method:
```php
if (!$this->session->userdata('email')) {
    $this->session->set_flashdata('message', 'Login required');
    redirect('auth');
}
```

In CI4, use **Filters** for authentication:

**Create:** `/app/Filters/AuthFilter.php`
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

**Register in:** `/app/Config/Filters.php`
```php
public $aliases = [
    'auth' => \App\Filters\AuthFilter::class,
];

public $filters = [
    'auth' => ['before' => [
        'admin/*',
        'master/*',
        'poskeu/*',
        // ... other protected routes
    ]],
];
```

---

### B. PDF Generation (Dompdf)

**CI3 Approach:**
```php
$this->load->library('dompdf_gen');
$html = $this->output->get_output();
$this->dompdf->set_paper('A4', 'landscape');
$this->dompdf->load_html($html);
$this->dompdf->render();
$this->dompdf->stream("laporan.pdf");
```

**CI4 Approach:**

1. Install via Composer:
```bash
composer require dompdf/dompdf
```

2. Use in Controller:
```php
use Dompdf\Dompdf;
use Dompdf\Options;

public function pdf() {
    $data = [...];
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

### C. Email Configuration

**CI3:** Uses `config/email.php` or inline configuration

**CI4:** Uses `.env` file

**Add to `.env`:**
```env
# Email Configuration
email.fromEmail = noreply@lapkeu.com
email.fromName = LAPKEU Warmindo
email.protocol = smtp
email.SMTPHost = ssl://smtp.googlemail.com
email.SMTPUser = your-email@gmail.com
email.SMTPPass = your-app-password
email.SMTPPort = 465
email.SMTPCrypto = ssl
email.mailType = html
```

**Usage in Controller:**
```php
$email = \Config\Services::email();
$email->setFrom(env('email.fromEmail'), env('email.fromName'));
$email->setTo($recipient);
$email->setSubject('Subject');
$email->setMessage($message);
$email->send();
```

---

### D. Date/Time Handling

**CI3:**
```php
$date = date('Y-m-d');
$timestamp = strtotime($date);
```

**CI4:** Use `Time` class
```php
use CodeIgniter\I18n\Time;

$time = Time::now('Asia/Jakarta');
$date = $time->toDateString();
$formatted = $time->toDateTimeString();
```

---

### E. Complex Query Handling

For complex queries that mix saldo_awal and transaksi tables, maintain CI3's approach but adapt syntax:

**CI3:**
```php
$this->db->select('...');
$this->db->from('saldo_awal');
$this->db->where('...');
$this->db->union();
$this->db->select('...');
$this->db->from('transaksi');
```

**CI4:**
```php
$db = \Config\Database::connect();

$query1 = $db->table('saldo_awal')
    ->select('...')
    ->where('...')
    ->getCompiledSelect();

$query2 = $db->table('transaksi')
    ->select('...')
    ->where('...');

$finalQuery = "$query1 UNION $query2";
$result = $db->query($finalQuery)->getResultArray();
```

OR use Raw Queries:
```php
$sql = "
    SELECT ... FROM saldo_awal WHERE ...
    UNION
    SELECT ... FROM transaksi WHERE ...
";
$result = $db->query($sql)->getResultArray();
```

---

### F. Balance Validation Logic

The application has complex balance validation in Admin controller:

```php
// Calculate debit and credit sums
foreach ($data as $d) {
    $jumlah[] = $d['debit'];
    $jumlahk[] = $d['kredit'];
}

$jumlahnya = array_sum($jumlah);
$jumlahknya = array_sum($jumlahk);

if ($jumlahnya == $jumlahknya) {
    // Balanced - proceed with insert/update
    $this->db->insert_batch('transaksi', $data);
    $this->session->set_flashdata('pesan_balance', 'Sudah Balance');
} else {
    // Not balanced - show error
    $this->session->set_flashdata('pesan_tidakbalance', 'Tidak Balance');
}
```

**This logic must be preserved in CI4** as it's critical business logic.

---

### G. Account Code Generation

The Master model has auto-increment code generation:

```php
// Generate code for Aset Lancar (1-1xx)
public function kode_al() {
    $kunci = '1-1';
    $this->db->like('kode_akun', $kunci);
    $this->db->select('RIGHT(daftar_akun.kode_akun,2) as kode', FALSE);
    // ... padding logic
    return "1-1" . $kodemax;
}
```

**CI4 Approach:**
```php
public function kodeAL() {
    $kunci = '1-1';
    $result = $this->select('kode_akun')
        ->like('kode_akun', $kunci)
        ->orderBy('kode_akun', 'DESC')
        ->first();

    if ($result) {
        $lastCode = substr($result['kode_akun'], -2);
        $newCode = intval($lastCode) + 1;
    } else {
        $newCode = 1;
    }

    return "1-1" . str_pad($newCode, 2, "0", STR_PAD_LEFT);
}
```

---

## 6. Step-by-Step Migration Checklist

### Phase 1: Preparation (Week 1)
- [ ] **Backup entire application and database**
- [ ] Set up development environment with CI4
- [ ] Review all CI3 custom libraries in `/application/libraries/`
- [ ] Document custom helpers in `/application/helpers/`
- [ ] Map all routes used in the application
- [ ] Identify all database tables and relationships
- [ ] Create migration plan document (THIS DOCUMENT)

### Phase 2: Foundation (Week 1-2)
- [ ] Create authentication filter (`AuthFilter.php`)
- [ ] Create role-based access filter (`RoleFilter.php`)
- [ ] Set up base controller with common methods
- [ ] Configure `.env` file for database, email, etc.
- [ ] Set up routes in `/app/Config/Routes.php`
- [ ] Migrate custom libraries to CI4 format
- [ ] Migrate helpers to `/app/Helpers/`

### Phase 3: Models Migration (Week 2-3)
Priority: Start with dependencies first

#### High Priority Models
- [ ] **Model_admin.php → AdminModel.php** (337 lines)
  - [ ] Define table and fields
  - [ ] Migrate query methods
  - [ ] Test CRUD operations
  - [ ] Test balance validation logic

- [ ] **Model_master.php → MasterModel.php** (374 lines)
  - [ ] Account code generation methods
  - [ ] Opening balance methods
  - [ ] Cash flow methods

- [ ] **Model_bb.php → BukuBesarModel.php** (95 lines)
  - [ ] Ledger display methods

- [ ] **Model_labarugi.php → LabaRugiModel.php** (47 lines)
  - [ ] Simple profit/loss methods

#### Medium Priority Models
- [ ] **Model_ds.php → DataSewaModel.php** (538 lines) - COMPLEX
- [ ] **Model_jp.php → JpModel.php** (342 lines)
- [ ] **Model_pmodal.php → PerModalModel.php** (312 lines)

#### Low Priority Models
- [ ] **Model_poskeu.php → PoskeuModel.php** (13 lines)
- [ ] **Model_pemilik.php → PemilikModel.php** (40 lines)

### Phase 4: Controllers Migration (Week 3-5)
Priority: Core functionality first

#### Week 3: Core Controllers
- [ ] **Welcome.php → Home.php** (25 lines) - SIMPLE START
  - [ ] Basic landing page
  - [ ] Test routing

- [ ] **Admin.php → Admin.php** (725 lines) - CRITICAL
  - [ ] Dashboard (`index()`)
  - [ ] Transaction entry (`transaksi_m()`)
  - [ ] Batch insert with validation (`insert_transaksi_m()`)
  - [ ] Edit transaction (`ubahTransaksi()`)
  - [ ] Delete transaction (`hapusTransaksi()`)
  - [ ] General journal (`jurnal_umum()`)
  - [ ] PDF generation (`pdf()`)
  - [ ] Profile management (`profil()`, `edit_profil()`)
  - [ ] Password change (`ganti_password()`)

#### Week 4: Master Data & Reports
- [ ] **Master.php → Master.php** (497 lines)
  - [ ] Chart of accounts CRUD
  - [ ] Opening balance management
  - [ ] Cash in/out reports

- [ ] **Buku_besar.php → BukuBesar.php** (398 lines)
  - [ ] Ledger display with filtering
  - [ ] PDF generation

- [ ] **Labarugi.php → Labarugi.php** (482 lines)
  - [ ] Income statement generation
  - [ ] Period calculations
  - [ ] PDF export

#### Week 5: Additional Modules
- [ ] **Poskeu.php → Poskeu.php** (295 lines)
  - [ ] Financial position report
  - [ ] PDF generation

- [ ] **Data_sewa.php → DataSewa.php** (382 lines)
- [ ] **Per_modal.php → PerModal.php** (276 lines)
- [ ] **Jp.php → Jp.php** (231 lines)
- [ ] **Pemilik.php → Pemilik.php** (103 lines)

### Phase 5: Views Migration (Week 5-6)
- [ ] Migrate `/application/views/templates/` to `/app/Views/templates/`
- [ ] Update all view files with CI4 syntax
- [ ] Replace `<?= form_error() ?>` with CI4 validation display
- [ ] Update asset paths (CSS, JS, images)
- [ ] Test all forms and their submissions

Views to migrate (65 files):
- [ ] Authentication views (login, register, forgot password)
- [ ] Dashboard views
- [ ] Transaction entry forms
- [ ] Report views (journal, ledger, financial statements)
- [ ] Master data forms
- [ ] Profile management views
- [ ] PDF templates for reports

### Phase 6: Additional Features (Week 6)
- [ ] Migrate Dompdf library integration
- [ ] Migrate email functionality
- [ ] Set up file upload directories with proper permissions
- [ ] Configure session handling
- [ ] Set up CSRF protection
- [ ] Configure database migrations for schema changes

### Phase 7: Testing (Week 7-8)
#### Unit Testing
- [ ] Test all model methods individually
- [ ] Test authentication and authorization
- [ ] Test form validation rules
- [ ] Test database operations (CRUD)
- [ ] Test batch operations
- [ ] Test balance validation logic

#### Integration Testing
- [ ] Test complete user workflows:
  - [ ] User registration and login
  - [ ] Transaction entry (single and batch)
  - [ ] Transaction editing and deletion
  - [ ] Chart of accounts management
  - [ ] Opening balance entry
  - [ ] Report generation (all types)
  - [ ] PDF exports
  - [ ] Profile management
  - [ ] Password reset flow

#### System Testing
- [ ] Test with real data from CI3 database
- [ ] Performance testing (large datasets)
- [ ] Security testing
- [ ] Cross-browser testing
- [ ] Mobile responsiveness testing

### Phase 8: Deployment (Week 8)
- [ ] Set up production environment
- [ ] Migrate database (if schema changes)
- [ ] Configure production `.env` file
- [ ] Set file permissions
- [ ] Configure web server (Apache/Nginx)
- [ ] Set up SSL certificate
- [ ] Performance optimization
- [ ] Security hardening
- [ ] Create deployment documentation

### Phase 9: Handover (Week 9)
- [ ] User training
- [ ] Administrator training
- [ ] Documentation:
  - [ ] User manual
  - [ ] Administrator manual
  - [ ] Developer documentation
  - [ ] API documentation (if any)
- [ ] Monitoring and support plan

---

## 7. Testing Strategy

### A. Unit Tests

Create tests in `/tests/` directory:

```php
<?php

namespace Tests\Models;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\AdminModel;

class AdminModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new AdminModel();
    }

    public function testGetTransaksiById()
    {
        $result = $this->model->getTransaksiById('000001');
        $this->assertIsArray($result);
    }

    public function testTotalDebit()
    {
        $total = $this->model->totalDebit('2024', '11');
        $this->assertIsNumeric($total);
    }
}
```

### B. Feature Tests

Test complete workflows:

```php
public function testTransactionEntry()
{
    // 1. Login
    $session = \Config\Services::session();
    $session->set(['email' => 'test@test.com', 'role_id' => 2]);

    // 2. Go to transaction page
    $result = $this->withSession($session)
        ->get('admin/transaksi_m');
    $result->assertOK();

    // 3. Submit transaction
    $data = [
        'kode_akun' => ['1-101', '2-001'],
        'debit' => [100000, 0],
        'kredit' => [0, 100000],
        // ... other fields
    ];

    $result = $this->withSession($session)
        ->post('admin/insert_transaksi_m', $data);

    // 4. Verify redirect and success message
    $result->assertRedirect();
    $this->assertEquals('Ditambahkan', $session->getFlashdata('pesan_sukses'));
}
```

### C. Database Testing

Test data integrity:

```php
public function testBalanceValidation()
{
    // Insert unbalanced transaction (should fail)
    $data = [
        ['debit' => 100000, 'kredit' => 0, ...],
        ['debit' => 0, 'kredit' => 50000, ...], // Not balanced!
    ];

    // Should not be inserted
    $result = $this->model->insertBatchWithValidation($data);
    $this->assertFalse($result);
}
```

---

## 8. Risk Mitigation

### High-Risk Areas

1. **Balance Validation Logic** (CRITICAL)
   - Risk: Incorrect balance calculation could corrupt financial data
   - Mitigation: Extensive testing with real data, double-check formulas

2. **Batch Transaction Operations** (HIGH)
   - Risk: Data loss or corruption during batch insert/update
   - Mitigation: Use database transactions, test thoroughly

3. **Opening Balance Calculations** (HIGH)
   - Risk: Incorrect opening balances affect all reports
   - Mitigation: Validate against CI3 calculations

4. **Report Generation** (MEDIUM)
   - Risk: Incorrect calculations in financial statements
   - Mitigation: Cross-verify with CI3 reports, test with known data

5. **File Uploads** (MEDIUM)
   - Risk: Security vulnerabilities, file permission issues
   - Mitigation: Validate file types, set proper permissions

---

## 9. Code Migration Templates

### Template 1: Simple Controller

```php
<?php

namespace App\Controllers;

use App\Models\YourModel;
use CodeIgniter\Controller;

class YourController extends Controller
{
    protected $yourModel;
    protected $validation;
    protected $session;

    public function __construct()
    {
        $this->yourModel = new YourModel();
        $this->validation = \Config\Services::validation();
        $this->session = \Config\Services::session();
        helper(['form', 'url']);
    }

    public function index()
    {
        // Check authentication (if not using filter)
        if (!$this->session->get('email')) {
            $this->session->setFlashdata('message',
                '<div class="alert alert-danger">Login required</div>');
            return redirect()->to(base_url('auth'));
        }

        $data = [
            'judul' => 'Page Title',
            'active' => 'active',
            'user' => $this->yourModel->getUserByEmail($this->session->get('email')),
            'records' => $this->yourModel->findAll()
        ];

        return view('templates/dash_header', $data)
             . view('templates/adm_sidebar', $data)
             . view('templates/adm_header', $data)
             . view('your/view', $data)
             . view('templates/adm_footer')
             . view('templates/dash_footer');
    }

    public function create()
    {
        if (!$this->session->get('email')) {
            return redirect()->to(base_url('auth'));
        }

        $rules = [
            'field1' => 'required',
            'field2' => 'required|numeric'
        ];

        if ($this->request->getMethod() === 'post' && $this->validate($rules)) {
            $data = [
                'field1' => $this->request->getPost('field1'),
                'field2' => $this->request->getPost('field2')
            ];

            if ($this->yourModel->insert($data)) {
                $this->session->setFlashdata('pesan_sukses', 'Ditambahkan');
                return redirect()->to(base_url('your/controller'));
            }
        }

        $data = [
            'judul' => 'Create',
            'validation' => $this->validator
        ];

        return view('your/create', $data);
    }
}
```

### Template 2: Simple Model

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class YourModel extends Model
{
    protected $table = 'your_table';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;

    protected $allowedFields = [
        'field1',
        'field2',
        'field3'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'field1' => 'required',
        'field2' => 'required|numeric'
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;

    // Custom methods
    public function getByCustomField($value)
    {
        return $this->where('field1', $value)->findAll();
    }

    public function getUserByEmail($email)
    {
        return $this->db->table('user')
            ->where('email', $email)
            ->get()
            ->getRowArray();
    }
}
```

---

## 10. Migration Timeline

| Week | Phase | Tasks | Deliverables |
|------|-------|-------|--------------|
| 1 | Preparation | Environment setup, documentation, backup | Migration plan, backup |
| 2 | Foundation | Auth filters, base classes, config | Auth system, config files |
| 3 | Models (High Priority) | AdminModel, MasterModel, LabaRugiModel, BukuBesarModel | 4 models migrated |
| 4 | Models (Medium/Low) | Remaining 5 models | All 11 models complete |
| 5 | Controllers (Core) | Home, Admin (partial) | Core functionality |
| 6 | Controllers (Master) | Admin (complete), Master, BukuBesar, Labarugi | Master data & reports |
| 7 | Controllers (Additional) | Poskeu, DataSewa, PerModal, Jp, Pemilik | All controllers complete |
| 8 | Views & Features | All views, PDF, email | Complete UI |
| 9-10 | Testing | Unit, integration, system tests | Test reports |
| 11 | Deployment | Production setup, data migration | Live application |
| 12 | Handover | Training, documentation | User manual, docs |

**Total Estimated Time:** 12 weeks (3 months)

---

## 11. Success Criteria

The migration is considered successful when:

1. ✅ All 11 controllers are migrated and functional
2. ✅ All 11 models are migrated and tested
3. ✅ All 65 views are migrated and responsive
4. ✅ User authentication and authorization work correctly
5. ✅ All CRUD operations function properly
6. ✅ Balance validation logic works correctly
7. ✅ All reports generate correctly
8. ✅ PDF exports work for all reports
9. ✅ File uploads work securely
10. ✅ Email notifications work
11. ✅ No data loss or corruption
12. ✅ Performance is equal to or better than CI3
13. ✅ All tests pass (unit, integration, system)
14. ✅ Security audit completed
15. ✅ Documentation completed
16. ✅ User acceptance testing passed

---

## 12. Rollback Plan

If critical issues occur during deployment:

1. **Stop deployment immediately**
2. **Restore CI3 application** from backup
3. **Restore database** from backup
4. **Verify CI3 application is functional**
5. **Document issues encountered**
6. **Fix issues in development**
7. **Re-test thoroughly**
8. **Schedule new deployment**

---

## 13. Support Contacts

- **Developer:** [Your Name]
- **Database Administrator:** [DBA Name]
- **System Administrator:** [SysAdmin Name]
- **Project Manager:** [PM Name]

---

## 14. Additional Resources

### Official Documentation
- [CodeIgniter 4 User Guide](https://codeigniter.com/user_guide/)
- [CI3 to CI4 Upgrade Guide](https://codeigniter4.github.io/userguide/installation/upgrade_4xx.html)
- [Dompdf Documentation](https://github.com/dompdf/dompdf)

### Useful Tools
- PHPUnit for testing
- Composer for dependency management
- Git for version control
- Database migration tools

---

## Appendix A: File Inventory

### Controllers to Migrate (9 remaining)
1. Admin.php (725 lines) - COMPLEX
2. Buku_besar.php (398 lines)
3. Data_sewa.php (382 lines)
4. Jp.php (231 lines)
5. Labarugi.php (482 lines)
6. Master.php (497 lines)
7. Pemilik.php (103 lines)
8. Per_modal.php (276 lines)
9. Poskeu.php (295 lines)
10. Welcome.php (25 lines)

### Models to Migrate (9 remaining)
1. Model_admin.php (337 lines)
2. Model_bb.php (95 lines)
3. Model_ds.php (538 lines) - LARGEST
4. Model_jp.php (342 lines)
5. Model_labarugi.php (47 lines) - SMALLEST
6. Model_master.php (374 lines)
7. Model_pemilik.php (40 lines)
8. Model_pmodal.php (312 lines)
9. Model_poskeu.php (13 lines)

### Total Lines of Code
- **Controllers:** 3,414 lines (excluding Auth)
- **Models:** 2,098 lines (excluding User_model and Accounting_model)
- **Total:** 5,512 lines to migrate

---

## Document Version History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2025-11-24 | Migration Team | Initial migration plan created |

---

**END OF MIGRATION PLAN**

This document should be updated as the migration progresses. Mark items as complete and document any deviations from the plan.
