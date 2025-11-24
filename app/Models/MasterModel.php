<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Master Model
 * Handles master data operations including chart of accounts and initial balances
 */
class MasterModel extends Model
{
	protected $table = 'daftar_akun';
	protected $primaryKey = 'kode_akun';
	protected $allowedFields = [
		'kode_akun',
		'akun',
		'pos_laporan',
		'pos_akun',
		'saldo_normal'
	];
	protected $useTimestamps = false;

	/**
	 * Display all chart of accounts
	 *
	 * @return array
	 */
	public function tampilDaftarAkun(): array
	{
		return $this->findAll();
	}

	/**
	 * Get chart of accounts by account code
	 *
	 * @param string $kodeAkun
	 * @return array|null
	 */
	public function getDaftarAkunById(string $kodeAkun): ?array
	{
		return $this->where('kode_akun', $kodeAkun)->first();
	}

	/**
	 * Add chart of account
	 *
	 * @param array $data
	 * @return bool|string
	 */
	public function tambahDaftarAkun(array $data)
	{
		return $this->insert($data);
	}

	/**
	 * Delete chart of account
	 *
	 * @param string $kodeAkun
	 * @return bool
	 */
	public function hapusDaftarAkun(string $kodeAkun): bool
	{
		return $this->delete($kodeAkun);
	}

	/**
	 * Update chart of account
	 *
	 * @param string $kodeAkun
	 * @param array $data
	 * @return bool
	 */
	public function ubahDaftarAkun(string $kodeAkun, array $data): bool
	{
		return $this->update($kodeAkun, $data);
	}

	/**
	 * Search chart of accounts
	 *
	 * @param string $katakunci
	 * @return array
	 */
	public function cariDaftarAkun(string $katakunci): array
	{
		return $this->like('kode_akun', $katakunci)
			->orLike('akun', $katakunci)
			->orLike('pos_laporan', $katakunci)
			->orLike('pos_akun', $katakunci)
			->findAll();
	}

	/**
	 * Generate next account code for Aktiva Lancar (1-1xx)
	 *
	 * @return string
	 */
	public function kodeAl(): string
	{
		$kunci = '1-1';
		$builder = $this->builder();
		$builder->like('kode_akun', $kunci);
		$builder->select('RIGHT(daftar_akun.kode_akun, 2) as kode', false);
		$builder->orderBy('kode_akun', 'DESC');
		$builder->limit(1);
		$query = $builder->get();
		$ambilData = $query->getRowArray();

		if ($ambilData && isset($ambilData['kode'])) {
			$angka = $ambilData['kode'];
			preg_match_all('!\d+!', $angka, $matches);
			$kode = implode('', $matches[0]);

			if ($query->getNumRows() != 0) {
				$data = $kode;
				$kode = intval($data) + 1;
			} else {
				$kode = 1;
			}
		} else {
			$kode = 1;
		}

		$kodemax = str_pad($kode, 2, '0', STR_PAD_LEFT);
		return "1-1" . $kodemax;
	}

	/**
	 * Generate next account code for Aktiva Tetap (1-2xx)
	 *
	 * @return string
	 */
	public function kodeAt(): string
	{
		$kunci = '1-2';
		$builder = $this->builder();
		$builder->like('kode_akun', $kunci);
		$builder->select('RIGHT(daftar_akun.kode_akun, 2) as kode', false);
		$builder->orderBy('kode_akun', 'DESC');
		$builder->limit(1);
		$query = $builder->get();
		$ambilData = $query->getRowArray();

		if ($ambilData && isset($ambilData['kode'])) {
			$angka = $ambilData['kode'];
			preg_match_all('!\d+!', $angka, $matches);
			$kode = implode('', $matches[0]);

			if ($query->getNumRows() != 0) {
				$data = $kode;
				$kode = intval($data) + 1;
			} else {
				$kode = 1;
			}
		} else {
			$kode = 1;
		}

		$kodemax = str_pad($kode, 2, '0', STR_PAD_LEFT);
		return "1-2" . $kodemax;
	}

	/**
	 * Generate next account code for Kewajiban (2-xxx)
	 *
	 * @return string
	 */
	public function kodeK(): string
	{
		$kunci = '2-';
		$builder = $this->builder();
		$builder->like('kode_akun', $kunci);
		$builder->select('RIGHT(daftar_akun.kode_akun, 2) as kode', false);
		$builder->orderBy('kode_akun', 'DESC');
		$builder->limit(1);
		$query = $builder->get();
		$ambilData = $query->getRowArray();

		if ($ambilData && isset($ambilData['kode'])) {
			$angka = $ambilData['kode'];
			preg_match_all('!\d+!', $angka, $matches);
			$kode = implode('', $matches[0]);

			if ($query->getNumRows() != 0) {
				$data = $kode;
				$kode = intval($data) + 1;
			} else {
				$kode = 1;
			}
		} else {
			$kode = 1;
		}

		$kodemax = str_pad($kode, 3, '0', STR_PAD_LEFT);
		return "2-" . $kodemax;
	}

	/**
	 * Generate next account code for Ekuitas (3-xxx)
	 *
	 * @return string
	 */
	public function kodeEk(): string
	{
		$kunci = '3-';
		$builder = $this->builder();
		$builder->like('kode_akun', $kunci);
		$builder->select('RIGHT(daftar_akun.kode_akun, 2) as kode', false);
		$builder->orderBy('kode_akun', 'DESC');
		$builder->limit(1);
		$query = $builder->get();
		$ambilData = $query->getRowArray();

		if ($ambilData && isset($ambilData['kode'])) {
			$angka = $ambilData['kode'];
			preg_match_all('!\d+!', $angka, $matches);
			$kode = implode('', $matches[0]);

			if ($query->getNumRows() != 0) {
				$data = $kode;
				$kode = intval($data) + 1;
			} else {
				$kode = 1;
			}
		} else {
			$kode = 1;
		}

		$kodemax = str_pad($kode, 3, '0', STR_PAD_LEFT);
		return "3-" . $kodemax;
	}

	/**
	 * Generate next account code for Pendapatan (4-xxx)
	 *
	 * @return string
	 */
	public function kodeP(): string
	{
		$kunci = '4-';
		$builder = $this->builder();
		$builder->like('kode_akun', $kunci);
		$builder->select('RIGHT(daftar_akun.kode_akun, 2) as kode', false);
		$builder->orderBy('kode_akun', 'DESC');
		$builder->limit(1);
		$query = $builder->get();
		$ambilData = $query->getRowArray();

		if ($ambilData && isset($ambilData['kode'])) {
			$angka = $ambilData['kode'];
			preg_match_all('!\d+!', $angka, $matches);
			$kode = implode('', $matches[0]);

			if ($query->getNumRows() != 0) {
				$data = $kode;
				$kode = intval($data) + 1;
			} else {
				$kode = 1;
			}
		} else {
			$kode = 1;
		}

		$kodemax = str_pad($kode, 3, '0', STR_PAD_LEFT);
		return "4-" . $kodemax;
	}

	/**
	 * Generate next account code for Beban (5-xxx)
	 *
	 * @return string
	 */
	public function kodeB(): string
	{
		$kunci = '5-';
		$builder = $this->builder();
		$builder->like('kode_akun', $kunci);
		$builder->select('RIGHT(daftar_akun.kode_akun, 2) as kode', false);
		$builder->orderBy('kode_akun', 'DESC');
		$builder->limit(1);
		$query = $builder->get();
		$ambilData = $query->getRowArray();

		if ($ambilData && isset($ambilData['kode'])) {
			$angka = $ambilData['kode'];
			preg_match_all('!\d+!', $angka, $matches);
			$kode = implode('', $matches[0]);

			if ($query->getNumRows() != 0) {
				$data = $kode;
				$kode = intval($data) + 1;
			} else {
				$kode = 1;
			}
		} else {
			$kode = 1;
		}

		$kodemax = str_pad($kode, 3, '0', STR_PAD_LEFT);
		return "5-" . $kodemax;
	}

	/**
	 * Generate next account code for Pajak (6-xxx)
	 *
	 * @return string
	 */
	public function kodePjk(): string
	{
		$kunci = '6-';
		$builder = $this->builder();
		$builder->like('kode_akun', $kunci);
		$builder->select('RIGHT(daftar_akun.kode_akun, 2) as kode', false);
		$builder->orderBy('kode_akun', 'DESC');
		$builder->limit(1);
		$query = $builder->get();
		$ambilData = $query->getRowArray();

		if ($ambilData && isset($ambilData['kode'])) {
			$angka = $ambilData['kode'];
			preg_match_all('!\d+!', $angka, $matches);
			$kode = implode('', $matches[0]);

			if ($query->getNumRows() != 0) {
				$data = $kode;
				$kode = intval($data) + 1;
			} else {
				$kode = 1;
			}
		} else {
			$kode = 1;
		}

		$kodemax = str_pad($kode, 3, '0', STR_PAD_LEFT);
		return "6-" . $kodemax;
	}

	/**
	 * Display initial balances (not used in original)
	 *
	 * @return array
	 */
	public function tampilSaldoBb(): array
	{
		$dataTanggal = $this->db->table('transaksi')->get()->getResultArray();
		$year = date('Y');

		if ($year == date('Y')) {
			$tahun2 = $year - 1;
		}

		return $this->db->table('transaksi')
			->where('YEAR(tanggal_transaksi)', $tahun2)
			->get()
			->getResultArray();
	}

	/**
	 * Add initial balance
	 *
	 * @param array $data
	 * @return bool|int
	 */
	public function tambahSaldoAwal(array $data)
	{
		$dateNow = date($data['tahun'] . '-01-d');
		$tglPost = date("Y-m-d", strtotime("first day of $dateNow"));

		$data['tanggal_transaksi'] = $tglPost;

		return $this->db->table('saldo_awal')->insert($data);
	}

	/**
	 * Delete initial balance
	 *
	 * @param int $id
	 * @return bool
	 */
	public function hapusSaldoAwal(int $id): bool
	{
		return $this->db->table('saldo_awal')->where('id', $id)->delete();
	}

	/**
	 * Update initial balance
	 *
	 * @param int $id
	 * @param array $data
	 * @return bool
	 */
	public function ubahSaldoAwal(int $id, array $data): bool
	{
		$dateNow = date($data['tahun'] . '-01-d');
		$tglPost = date("Y-m-d", strtotime("first day of $dateNow"));

		$data['tanggal_transaksi'] = $tglPost;

		return $this->db->table('saldo_awal')->where('id', $id)->update($data);
	}

	/**
	 * Get initial balance by ID
	 *
	 * @param int $id
	 * @return array|null
	 */
	public function getSaldoAwalById(int $id): ?array
	{
		return $this->db->table('saldo_awal')->where('id', $id)->get()->getRowArray();
	}

	/**
	 * Get cash transactions based on filters
	 *
	 * @param array $filters
	 * @return array
	 */
	public function kas(array $filters = []): array
	{
		$builder = $this->db->table('transaksi');
		$builder->where('akun', 'Kas');

		if (isset($filters['tanggal_awal']) && isset($filters['tanggal_akhir'])) {
			$builder->where('tanggal_transaksi >=', $filters['tanggal_awal'])
				->where('tanggal_transaksi <=', $filters['tanggal_akhir']);
		} elseif (isset($filters['tahun_post']) && isset($filters['bulan_post'])) {
			$builder->where('YEAR(tanggal_transaksi)', $filters['tahun_post'])
				->where('MONTH(tanggal_transaksi)', $filters['bulan_post']);
		} elseif (isset($filters['tahun_post'])) {
			$builder->where('YEAR(tanggal_transaksi)', $filters['tahun_post']);
		} else {
			$builder->where('YEAR(tanggal_transaksi)', date("Y"))
				->where('MONTH(tanggal_transaksi)', date("m"));
		}

		return $builder->orderBy('tanggal_transaksi', 'ASC')->get()->getResultArray();
	}
}
