<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Poskeu Model
 * Handles balance sheet (Posisi Keuangan) data operations
 */
class PoskeuModel extends Model
{
	protected $table = 'daftar_akun';
	protected $primaryKey = 'id';
	protected $allowedFields = [];

	/**
	 * Get all accounts for balance sheet report
	 *
	 * @return array
	 */
	public function tampilPosAkun()
	{
		return $this->where('pos_laporan', 'Laporan Posisi Keuangan')->findAll();
	}
}
