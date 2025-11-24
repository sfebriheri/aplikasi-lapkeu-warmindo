# CI3 to CI4 Migration - Quick Reference Guide

## Controllers Naming Convention

| CI3 Filename | CI4 Filename | CI4 Class Name |
|--------------|--------------|----------------|
| Admin.php | Admin.php | Admin |
| Auth.php | Auth.php | Auth |
| Buku_besar.php | BukuBesar.php | BukuBesar |
| Data_sewa.php | DataSewa.php | DataSewa |
| Jp.php | Jp.php | Jp |
| Labarugi.php | Labarugi.php | Labarugi |
| Master.php | Master.php | Master |
| Pemilik.php | Pemilik.php | Pemilik |
| Per_modal.php | PerModal.php | PerModal |
| Poskeu.php | Poskeu.php | Poskeu |
| Welcome.php | Home.php | Home |

## Models Naming Convention

| CI3 Filename | CI4 Filename | CI4 Class Name |
|--------------|--------------|----------------|
| Accounting_model.php | AccountingModel.php | AccountingModel |
| Model_admin.php | AdminModel.php | AdminModel |
| Model_bb.php | BukuBesarModel.php | BukuBesarModel |
| Model_ds.php | DataSewaModel.php | DataSewaModel |
| Model_jp.php | JpModel.php | JpModel |
| Model_labarugi.php | LabaRugiModel.php | LabaRugiModel |
| Model_master.php | MasterModel.php | MasterModel |
| Model_pemilik.php | PemilikModel.php | PemilikModel |
| Model_pmodal.php | PerModalModel.php | PerModalModel |
| Model_poskeu.php | PoskeuModel.php | PoskeuModel |
| User_model.php | UserModel.php | UserModel |

## Quick Syntax Changes

### File Headers
```php
// CI3
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class MyController extends CI_Controller {

// CI4
<?php
namespace App\Controllers;
use CodeIgniter\Controller;
class MyController extends Controller {
```

### Loading Models
```php
// CI3
$this->load->model('Model_admin');
$this->Model_admin->method();

// CI4
$this->adminModel = new \App\Models\AdminModel();
$this->adminModel->method();
```

### Session
```php
// CI3
$this->session->userdata('key')
$this->session->set_flashdata('key', 'value')

// CI4
$this->session->get('key')
$this->session->setFlashdata('key', 'value')
```

### Input
```php
// CI3
$this->input->post('field', true)

// CI4
$this->request->getPost('field', FILTER_SANITIZE_FULL_SPECIAL_CHARS)
```

### Database
```php
// CI3
$this->db->get_where('table', ['id' => $id])->row_array()

// CI4 (Query Builder)
$db = \Config\Database::connect();
$db->table('table')->where('id', $id)->get()->getRowArray()

// CI4 (Model - PREFERRED)
$this->model->find($id)
```

### Query Results
```php
// CI3 → CI4
->row() → ->getRow()
->row_array() → ->getRowArray()
->result() → ->getResult()
->result_array() → ->getResultArray()
```

### Redirect
```php
// CI3
redirect('controller/method');

// CI4
return redirect()->to('controller/method');
return redirect()->to(base_url('controller/method'));
```

### Views
```php
// CI3
$this->load->view('view_name', $data);

// CI4
return view('view_name', $data);
```

### Form Validation
```php
// CI3
$this->form_validation->set_rules('field', 'Label', 'required');
if ($this->form_validation->run() == FALSE) {

// CI4
$rules = ['field' => 'required'];
if (!$this->validate($rules)) {
```

### File Upload
```php
// CI3
$config['upload_path'] = './uploads/';
$this->load->library('upload', $config);
if ($this->upload->do_upload('userfile')) {

// CI4
$file = $this->request->getFile('userfile');
if ($file->isValid() && !$file->hasMoved()) {
    $file->move(WRITEPATH . 'uploads');
```

## Migration Priority Order

### Phase 1: Models (High Priority)
1. AdminModel (337 lines)
2. MasterModel (374 lines)
3. LabaRugiModel (47 lines)
4. BukuBesarModel (95 lines)

### Phase 2: Models (Medium Priority)
5. DataSewaModel (538 lines) - COMPLEX
6. JpModel (342 lines)
7. PerModalModel (312 lines)

### Phase 3: Models (Low Priority)
8. PoskeuModel (13 lines)
9. PemilikModel (40 lines)

### Phase 4: Controllers (Core)
1. Home (25 lines) - SIMPLE
2. Admin (725 lines) - CRITICAL

### Phase 5: Controllers (Master Data)
3. Master (497 lines)
4. BukuBesar (398 lines)
5. Labarugi (482 lines)

### Phase 6: Controllers (Additional)
6. Poskeu (295 lines)
7. DataSewa (382 lines)
8. PerModal (276 lines)
9. Jp (231 lines)
10. Pemilik (103 lines)

## Critical Business Logic to Preserve

### 1. Balance Validation
```php
// Calculate total debit and kredit
foreach ($data as $d) {
    $jumlah[] = $d['debit'];
    $jumlahk[] = $d['kredit'];
}

$jumlahnya = array_sum($jumlah);
$jumlahknya = array_sum($jumlahk);

if ($jumlahnya == $jumlahknya) {
    // Balanced - proceed
} else {
    // Not balanced - error
}
```

### 2. Account Code Generation
```php
// Pattern: 1-1xx (Aset Lancar)
// Pattern: 1-2xx (Aset Tetap)
// Pattern: 2-xxx (Kewajiban)
// Pattern: 3-xxx (Ekuitas)
// Pattern: 4-xxx (Pendapatan)
// Pattern: 5-xxx (Beban)
// Pattern: 6-xxx (Pajak)
```

### 3. Batch Operations
```php
// CI3
$this->db->insert_batch('transaksi', $data);
$this->db->update_batch('transaksi', $data, 'id');

// CI4
$db->table('transaksi')->insertBatch($data);
$db->table('transaksi')->updateBatch($data, 'id');

// OR in Model
$this->model->insertBatch($data);
$this->model->updateBatch($data);
```

## Common Pitfalls to Avoid

1. **Don't forget namespaces** in CI4 files
2. **Use return** before redirect() in CI4
3. **Use base_url()** in redirects for proper URL formation
4. **Filter names must match** when using AuthFilter
5. **Model property $allowedFields** must include all insertable fields
6. **Always use getRowArray()** not row_array()
7. **Session methods are camelCase** in CI4 (setFlashdata not set_flashdata)
8. **Request methods use get/post prefix** (getPost not post)

## Testing Checklist

### For Each Controller
- [ ] Authentication works
- [ ] All CRUD operations function
- [ ] Form validation works
- [ ] Success/error messages display
- [ ] Redirects work correctly
- [ ] File uploads work (if applicable)
- [ ] PDF generation works (if applicable)

### For Each Model
- [ ] Insert operations work
- [ ] Update operations work
- [ ] Delete operations work
- [ ] Select queries return correct data
- [ ] Batch operations work
- [ ] Custom methods function correctly

### For Each Report
- [ ] Date filtering works
- [ ] Calculations are accurate
- [ ] Data displays correctly
- [ ] PDF export works
- [ ] Opening balances are correct

## Estimated Timeline

- **Preparation:** 1 week
- **Foundation:** 1 week
- **Models Migration:** 2 weeks
- **Controllers Migration:** 3 weeks
- **Views Migration:** 1 week
- **Testing:** 2 weeks
- **Deployment:** 1 week
- **Handover:** 1 week

**Total: 12 weeks (3 months)**

## Quick Start Steps

1. **Backup everything** (code + database)
2. **Read full migration plan** (CI3_TO_CI4_MIGRATION_PLAN.md)
3. **Set up CI4 environment** (.env configuration)
4. **Create AuthFilter** for authentication
5. **Migrate simplest model first** (PoskeuModel - 13 lines)
6. **Test model thoroughly**
7. **Migrate simplest controller** (Home - 25 lines)
8. **Test controller thoroughly**
9. **Continue with priority order**
10. **Test after each migration**

## Resources

- Full Migration Plan: `/CI3_TO_CI4_MIGRATION_PLAN.md`
- CI4 User Guide: https://codeigniter.com/user_guide/
- CI3 to CI4 Upgrade: https://codeigniter4.github.io/userguide/installation/upgrade_4xx.html

---

**Remember:** Test frequently, migrate incrementally, and maintain CI3 backup throughout the process!
