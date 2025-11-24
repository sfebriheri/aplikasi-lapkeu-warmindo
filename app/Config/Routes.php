<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default route - redirect to auth
$routes->get('/', 'Auth::index');

// Authentication routes
$routes->group('auth', function ($routes) {
    $routes->get('/', 'Auth::index');
    $routes->post('/', 'Auth::index');
    $routes->get('register', 'Auth::register');
    $routes->post('register', 'Auth::register');
    $routes->get('logout', 'Auth::logout');
    $routes->get('lupas', 'Auth::lupas');
    $routes->post('lupas', 'Auth::lupas');
    $routes->get('resetpassword', 'Auth::resetpassword');
    $routes->get('gantipassword', 'Auth::gantipassword');
    $routes->post('gantipassword', 'Auth::gantipassword');
});

// Admin routes (protected by AuthFilter)
$routes->group('admin', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Admin::index');
    $routes->get('dashboard', 'Admin::index');
    $routes->get('transaksi', 'Admin::transaksi');
    $routes->post('transaksi', 'Admin::transaksi');
    $routes->get('transaksi_m', 'Admin::transaksi_m');
    $routes->post('insert_transaksi_m', 'Admin::insert_transaksi_m');
    $routes->get('ubahTransaksi/(:any)', 'Admin::ubahTransaksi/$1');
    $routes->post('ubahTransaksi/(:any)', 'Admin::ubahTransaksi/$1');
    $routes->get('hapusTransaksi/(:any)', 'Admin::hapusTransaksi/$1');
    $routes->get('jurnal_umum', 'Admin::jurnal_umum');
    $routes->post('jurnal_umum', 'Admin::jurnal_umum');
    $routes->get('pdf', 'Admin::pdf');
    $routes->post('pdf', 'Admin::pdf');
    $routes->get('laba_rugi', 'Admin::laba_rugi');
    $routes->get('profil', 'Admin::profil');
    $routes->post('edit_profil', 'Admin::edit_profil');
    $routes->get('edit_profil', 'Admin::edit_profil');
    $routes->get('ganti_password', 'Admin::ganti_password');
    $routes->post('ganti_password', 'Admin::ganti_password');
    $routes->post('get_kodeakun', 'Admin::get_kodeakun');
});

// Accounting routes (protected by AuthFilter)
$routes->group('', ['filter' => 'auth'], function ($routes) {
    // Chart of Accounts
    $routes->get('master', 'Master::index');
    $routes->get('master/add', 'Master::add');
    $routes->post('master/save', 'Master::save');
    $routes->get('master/edit/(:num)', 'Master::edit/$1');
    $routes->post('master/update/(:num)', 'Master::update/$1');
    $routes->get('master/delete/(:num)', 'Master::delete/$1');
    $routes->post('master/ambil_dropdown', 'Master::ambil_dropdown');
    $routes->post('master/isi_saldoawal', 'Master::isi_saldoawal');
    $routes->get('master/saldoawal', 'Master::saldoawal');

    // Journal entries
    $routes->get('jp', 'Jp::index');
    $routes->get('jp/add', 'Jp::add');
    $routes->post('jp/save', 'Jp::save');
    $routes->get('jp/detail/(:num)', 'Jp::detail/$1');
    $routes->get('jp/approve/(:num)', 'Jp::approve/$1');
    $routes->get('jp/edit/(:num)', 'Jp::edit/$1');
    $routes->post('jp/update/(:num)', 'Jp::update/$1');
    $routes->get('jp/delete/(:num)', 'Jp::delete/$1');

    // General ledger
    $routes->get('buku_besar', 'BukuBesar::index');
    $routes->get('buku_besar/account/(:num)', 'BukuBesar::account/$1');
    $routes->post('buku_besar', 'BukuBesar::index');
    $routes->get('buku_besar/pdf', 'BukuBesar::pdf');
    $routes->post('buku_besar/pdf', 'BukuBesar::pdf');

    // Financial reports
    $routes->get('labarugi', 'Labarugi::index');
    $routes->post('labarugi', 'Labarugi::index');
    $routes->get('labarugi/pdf', 'Labarugi::pdf');
    $routes->post('labarugi/pdf', 'Labarugi::pdf');

    $routes->get('poskeu', 'Poskeu::index');
    $routes->post('poskeu', 'Poskeu::index');
    $routes->get('poskeu/pdf', 'Poskeu::pdf');
    $routes->post('poskeu/pdf', 'Poskeu::pdf');

    $routes->get('per_modal', 'PerModal::index');
    $routes->post('per_modal', 'PerModal::index');
    $routes->get('per_modal/pdf', 'PerModal::pdf');
    $routes->post('per_modal/pdf', 'PerModal::pdf');

    // Data rental
    $routes->get('data_sewa', 'DataSewa::index');
    $routes->get('data_sewa/add', 'DataSewa::add');
    $routes->post('data_sewa/save', 'DataSewa::save');
    $routes->get('data_sewa/edit/(:num)', 'DataSewa::edit/$1');
    $routes->post('data_sewa/update/(:num)', 'DataSewa::update/$1');
    $routes->get('data_sewa/delete/(:num)', 'DataSewa::delete/$1');

    // Owner/User management
    $routes->get('pemilik', 'Pemilik::index');
    $routes->post('pemilik', 'Pemilik::index');
    $routes->get('pemilik/add', 'Pemilik::add');
    $routes->post('pemilik/save', 'Pemilik::save');
    $routes->get('pemilik/edit/(:num)', 'Pemilik::edit/$1');
    $routes->post('pemilik/update/(:num)', 'Pemilik::update/$1');
    $routes->get('pemilik/aktif/(:num)', 'Pemilik::updateAktif/$1');
    $routes->get('pemilik/nonaktif/(:num)', 'Pemilik::updateNonaktif/$1');
    $routes->get('pemilik/upgrade/(:num)', 'Pemilik::upLevel/$1');
    $routes->get('pemilik/downgrade/(:num)', 'Pemilik::downLevel/$1');
    $routes->get('pemilik/delete/(:num)', 'Pemilik::hapus/$1');
});
