<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Admin Model
 * Handles transaction management and journal operations
 */
class AdminModel extends Model
{
	protected $table = 'transaksi';
	protected $primaryKey = 'id';
	protected $allowedFields = [
		'kode_akun',
		'keterangan',
		'tanggal_transaksi',
		'pos_saldo',
		'pos_laporan',
		'bukti_transaksi',
		'akun',
		'debit',
		'kredit',
		'pos_akun',
		'ref',
		'id_sewa'
	];
	protected $useTimestamps = false;

	/**
	 * Display journal entries for current month/year
	 *
	 * @return array
	 */
	public function tampilJurnalUmum(): array
	{
		return $this->where('YEAR(tanggal_transaksi)', date('Y'))
			->where('MONTH(tanggal_transaksi)', date('m'))
			->orderBy('tanggal_transaksi', 'ASC')
			->findAll();
	}

	/**
	 * Get transactions by transaction proof/evidence number
	 *
	 * @param string $buktiTransaksi
	 * @return array
	 */
	public function getTransaksiById(string $buktiTransaksi): array
	{
		return $this->where('bukti_transaksi', $buktiTransaksi)->findAll();
	}

	/**
	 * Add transaction (not used - marked in original)
	 *
	 * @param array $data
	 * @return bool|int
	 */
	public function tambahTransaksi(array $data)
	{
		return $this->insert($data);
	}

	/**
	 * Delete journal entry by transaction proof number
	 *
	 * @param string $buktiTransaksi
	 * @return bool
	 */
	public function hapusJurnalUmum(string $buktiTransaksi): bool
	{
		return $this->where('bukti_transaksi', $buktiTransaksi)->delete();
	}

	/**
	 * Update transaction (not used - marked in original)
	 *
	 * @param int $id
	 * @param array $data
	 * @return bool
	 */
	public function ubahTransaksi(int $id, array $data): bool
	{
		return $this->update($id, $data);
	}

	/**
	 * Get dropdown options from chart of accounts
	 *
	 * @return array
	 */
	public function ambilDropdown(): array
	{
		return $this->db->table('daftar_akun')->get()->getResult();
	}

	/**
	 * Get account information by account code
	 *
	 * @param string $kodeAkun
	 * @return array
	 */
	public function isiFieldByKode(string $kodeAkun): array
	{
		$result = $this->db->table('daftar_akun')
			->where('kode_akun', $kodeAkun)
			->get()
			->getRowArray();

		if ($result) {
			return [
				'kode_akun' => $result['kode_akun'],
				'akun' => $result['akun'],
				'pos_laporan' => $result['pos_laporan'],
				'pos_akun' => $result['pos_akun']
			];
		}

		return [];
	}

	/**
	 * Search journal entries by month and year
	 *
	 * @param string $tahun
	 * @param string $bulan
	 * @return array
	 */
	public function cariBulanTahunJurnalUmum(string $tahun, string $bulan): array
	{
		return $this->where('YEAR(tanggal_transaksi)', $tahun)
			->where('MONTH(tanggal_transaksi)', $bulan)
			->orderBy('tanggal_transaksi', 'ASC')
			->findAll();
	}

	/**
	 * Search journal entries by year
	 *
	 * @param string $tahun
	 * @return array
	 */
	public function cariTahunJurnalUmum(string $tahun): array
	{
		return $this->where('YEAR(tanggal_transaksi)', $tahun)
			->orderBy('tanggal_transaksi', 'ASC')
			->findAll();
	}

	/**
	 * Search journal entries by keyword
	 *
	 * @param string $katakunci
	 * @return array
	 */
	public function cariJurnalUmum(string $katakunci): array
	{
		return $this->like('kode_akun', $katakunci)
			->orLike('bukti_transaksi', $katakunci)
			->orLike('pos_laporan', $katakunci)
			->orLike('debit', $katakunci)
			->orLike('kredit', $katakunci)
			->orLike('akun', $katakunci)
			->orLike('keterangan', $katakunci)
			->orderBy('tanggal_transaksi', 'ASC')
			->findAll();
	}

	/**
	 * Search journal entries by date range
	 *
	 * @param string $tanggalAwal
	 * @param string $tanggalAkhir
	 * @return array
	 */
	public function cariTanggalJurnalUmum(string $tanggalAwal, string $tanggalAkhir): array
	{
		return $this->where('tanggal_transaksi >=', $tanggalAwal)
			->where('tanggal_transaksi <=', $tanggalAkhir)
			->orderBy('tanggal_transaksi', 'ASC')
			->findAll();
	}

	/**
	 * Get total debit based on various filters
	 *
	 * @param array $filters
	 * @return float
	 */
	public function totalDebit(array $filters = []): float
	{
		$builder = $this->builder();
		$builder->selectSum('debit', 'total');

		if (isset($filters['katakunci'])) {
			$katakunci = $filters['katakunci'];
			$builder->like('kode_akun', $katakunci)
				->orLike('bukti_transaksi', $katakunci)
				->orLike('pos_laporan', $katakunci)
				->orLike('debit', $katakunci)
				->orLike('kredit', $katakunci)
				->orLike('akun', $katakunci)
				->orLike('keterangan', $katakunci);
		} elseif (isset($filters['tanggal_awal']) && isset($filters['tanggal_akhir'])) {
			$builder->where('tanggal_transaksi >=', $filters['tanggal_awal'])
				->where('tanggal_transaksi <=', $filters['tanggal_akhir']);
		} elseif (isset($filters['bulan_post']) && isset($filters['tahun_post'])) {
			$builder->where('MONTH(tanggal_transaksi)', $filters['bulan_post'])
				->where('YEAR(tanggal_transaksi)', $filters['tahun_post']);
		} elseif (isset($filters['tahun_post'])) {
			$builder->where('YEAR(tanggal_transaksi)', $filters['tahun_post']);
		} else {
			$builder->where('YEAR(tanggal_transaksi)', date('Y'))
				->where('MONTH(tanggal_transaksi)', date('m'));
		}

		$result = $builder->get()->getRow();
		return $result ? (float)$result->total : 0.0;
	}

	/**
	 * Get total credit based on various filters
	 *
	 * @param array $filters
	 * @return float
	 */
	public function totalKredit(array $filters = []): float
	{
		$builder = $this->builder();
		$builder->selectSum('kredit', 'total');

		if (isset($filters['katakunci'])) {
			$katakunci = $filters['katakunci'];
			$builder->like('kode_akun', $katakunci)
				->orLike('bukti_transaksi', $katakunci)
				->orLike('pos_laporan', $katakunci)
				->orLike('debit', $katakunci)
				->orLike('kredit', $katakunci)
				->orLike('akun', $katakunci)
				->orLike('keterangan', $katakunci);
		} elseif (isset($filters['tanggal_awal']) && isset($filters['tanggal_akhir'])) {
			$builder->where('tanggal_transaksi >=', $filters['tanggal_awal'])
				->where('tanggal_transaksi <=', $filters['tanggal_akhir']);
		} elseif (isset($filters['bulan_post']) && isset($filters['tahun_post'])) {
			$builder->where('MONTH(tanggal_transaksi)', $filters['bulan_post'])
				->where('YEAR(tanggal_transaksi)', $filters['tahun_post']);
		} elseif (isset($filters['tahun_post'])) {
			$builder->where('YEAR(tanggal_transaksi)', $filters['tahun_post']);
		} else {
			$builder->where('YEAR(tanggal_transaksi)', date('Y'))
				->where('MONTH(tanggal_transaksi)', date('m'));
		}

		$result = $builder->get()->getRow();
		return $result ? (float)$result->total : 0.0;
	}

	/**
	 * Get month dropdown array
	 *
	 * @return array
	 */
	public function ddBulan(): array
	{
		return [
			'1' => ['angka' => '01', 'bulan' => 'Januari'],
			'2' => ['angka' => '02', 'bulan' => 'Februari'],
			'3' => ['angka' => '03', 'bulan' => 'Maret'],
			'4' => ['angka' => '04', 'bulan' => 'April'],
			'5' => ['angka' => '05', 'bulan' => 'Mei'],
			'6' => ['angka' => '06', 'bulan' => 'Juni'],
			'7' => ['angka' => '07', 'bulan' => 'Juli'],
			'8' => ['angka' => '08', 'bulan' => 'Agustus'],
			'9' => ['angka' => '09', 'bulan' => 'September'],
			'10' => ['angka' => '10', 'bulan' => 'Oktober'],
			'11' => ['angka' => '11', 'bulan' => 'November'],
			'12' => ['angka' => '12', 'bulan' => 'Desember']
		];
	}

	/**
	 * Generate next transaction proof number
	 *
	 * @return string
	 */
	public function buktiTransaksi(): string
	{
		$query = $this->orderBy('id', 'DESC')->limit(1)->first();

		if ($query && isset($query['bukti_transaksi'])) {
			$kode = intval($query['bukti_transaksi']) + 1;
		} else {
			$kode = 1;
		}

		return str_pad($kode, 6, '0', STR_PAD_LEFT);
	}
}
