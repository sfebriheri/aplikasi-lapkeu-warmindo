<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

/**
 * Buku Besar Model
 * Handles general ledger (Buku Besar) data operations
 */
class BukuBesarModel extends Model
{
	protected $table = 'transaksi';
	protected $primaryKey = 'id';
	protected $allowedFields = [];

	/**
	 * Display all transactions for the current year
	 *
	 * @return array
	 */
	public function tampilBukuBesar()
	{
		$date = date('Y');
		return $this->where("YEAR(tanggal_transaksi) = {$date}", null, false)
			->orderBy('tanggal_transaksi', 'ASC')
			->findAll();
	}

	/**
	 * Get general ledger data for current month/year
	 *
	 * @return array|null
	 */
	public function bukuBesar()
	{
		$bulan = date('m');
		$tahun = date('Y');

		// Get all accounts
		$data1 = $this->db->table('daftar_akun')
			->select('akun')
			->get()
			->getResultArray();

		if ($bulan == 1) {
			return null;
		}

		$bulan = $bulan - 1;
		$data12 = [];

		foreach ($data1 as $key) {
			$data12[] = ['akun' => $key['akun']];
		}

		foreach ($data12 as $d12) {
			$sql = $this->db->table('transaksi')
				->where("MONTH(tanggal_transaksi) = {$bulan}", null, false)
				->where("YEAR(tanggal_transaksi) = {$tahun}", null, false)
				->where('akun', $d12['akun'])
				->get()
				->getResultArray();

			$sql_sa = $this->db->table('saldo_awal')
				->where('akun', $d12['akun'])
				->get()
				->getResultArray();

			$total = [];
			if (count($sql) > 0) {
				foreach ($sql as $key) {
					$total[] = $key['debit'] - $key['kredit'];
				}
			}

			return $total;
		}
	}

	/**
	 * Get month dropdown data
	 *
	 * @return array
	 */
	public function ddBulan()
	{
		return [
			'1' => ['angka' => '1', 'bulan' => 'Januari'],
			'2' => ['angka' => '2', 'bulan' => 'Februari'],
			'3' => ['angka' => '3', 'bulan' => 'Maret'],
			'4' => ['angka' => '4', 'bulan' => 'April'],
			'5' => ['angka' => '5', 'bulan' => 'Mei'],
			'6' => ['angka' => '6', 'bulan' => 'Juni'],
			'7' => ['angka' => '7', 'bulan' => 'Juli'],
			'8' => ['angka' => '8', 'bulan' => 'Agustus'],
			'9' => ['angka' => '9', 'bulan' => 'September'],
			'10' => ['angka' => '10', 'bulan' => 'Oktober'],
			'11' => ['angka' => '11', 'bulan' => 'November'],
			'12' => ['angka' => '12', 'bulan' => 'Desember'],
		];
	}
}
