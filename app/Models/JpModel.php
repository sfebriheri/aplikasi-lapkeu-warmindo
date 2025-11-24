<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Journal Pencatatan Model
 * Handles recording journal (JP) operations
 */
class JpModel extends Model
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
	 * Display journal entries for current month/year with JP reference
	 *
	 * @return array
	 */
	public function tampilJp(): array
	{
		$month = date('m');
		$tahun = date('Y');
		return $this->where('MONTH(tanggal_transaksi)', $month)
			->where('YEAR(tanggal_transaksi)', $tahun)
			->where('ref', 'JP')
			->findAll();
	}

	/**
	 * Search journal entries by date range
	 *
	 * @param string $tanggalAwal
	 * @param string $tanggalAkhir
	 * @return array
	 */
	public function cariTanggalJp(string $tanggalAwal, string $tanggalAkhir): array
	{
		return $this->where('tanggal_transaksi >=', $tanggalAwal)
			->where('tanggal_transaksi <=', $tanggalAkhir)
			->where('ref', 'JP')
			->orderBy('tanggal_transaksi', 'ASC')
			->findAll();
	}

	/**
	 * Search journal entries by month and year
	 *
	 * @param string $tahun
	 * @param string $bulan
	 * @return array
	 */
	public function cariBulanTahunJp(string $tahun, string $bulan): array
	{
		return $this->where('YEAR(tanggal_transaksi)', $tahun)
			->where('MONTH(tanggal_transaksi)', $bulan)
			->where('ref', 'JP')
			->orderBy('tanggal_transaksi', 'ASC')
			->findAll();
	}

	/**
	 * Search journal entries by year
	 *
	 * @param string $tahun
	 * @return array
	 */
	public function cariTahunJp(string $tahun): array
	{
		return $this->where('YEAR(tanggal_transaksi)', $tahun)
			->where('ref', 'JP')
			->orderBy('tanggal_transaksi', 'ASC')
			->findAll();
	}

	/**
	 * Search journal entries by keyword
	 *
	 * @param string $katakunci
	 * @return array
	 */
	public function cariJp(string $katakunci): array
	{
		return $this->like('kode_akun', $katakunci)
			->orLike('bukti_transaksi', $katakunci)
			->orLike('pos_laporan', $katakunci)
			->orLike('debit', $katakunci)
			->orLike('kredit', $katakunci)
			->orLike('akun', $katakunci)
			->orLike('keterangan', $katakunci)
			->where('ref', 'JP')
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
		$builder->where('ref', 'JP');

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
			$builder->where('MONTH(tanggal_transaksi)', date('m'))
				->where('YEAR(tanggal_transaksi)', date('Y'));
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
		$builder->where('ref', 'JP');

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
			$builder->where('MONTH(tanggal_transaksi)', date('m'))
				->where('YEAR(tanggal_transaksi)', date('Y'));
		}

		$result = $builder->get()->getRow();
		return $result ? (float)$result->total : 0.0;
	}

	/**
	 * Add journal entries (batch insert)
	 *
	 * @param array $postData
	 * @return bool|int
	 */
	public function tambahJp(array $postData)
	{
		$dateNow = date($postData['tahun'] . '-' . $postData['bulan'] . '-d');
		$tglPost = date("Y-m-d", strtotime("last day of $dateNow"));

		$kodeAkun = $postData['kode_akun'];
		$keterangan = $postData['keterangan_copy'];
		$posSaldo = $postData['pos_saldo'];
		$posLaporan = $postData['pos_laporan'];
		$buktiTransaksi = $postData['bukti_copy'];
		$akun = $postData['akun'];
		$debit = $postData['debit'];
		$kredit = $postData['kredit'];
		$posAkun = $postData['pos_akun'];

		$data = [];
		$index = 0;

		foreach ($kodeAkun as $datakd) {
			$data[] = [
				'kode_akun' => $datakd,
				'keterangan' => $keterangan,
				'tanggal_transaksi' => $tglPost,
				'pos_saldo' => $posSaldo[$index],
				'pos_laporan' => $posLaporan[$index],
				'bukti_transaksi' => $buktiTransaksi,
				'akun' => $akun[$index],
				'debit' => $debit[$index],
				'kredit' => $kredit[$index],
				'pos_akun' => $posAkun[$index],
				'ref' => 'JP'
			];
			$index++;
		}

		return $this->insertBatch($data);
	}

	/**
	 * Update journal entries (batch update)
	 *
	 * @param array $postData
	 * @return int|bool
	 */
	public function updateJp(array $postData)
	{
		$dateNow = date($postData['tahun'] . '-' . $postData['bulan'] . '-d');
		$tglPost = date("Y-m-d", strtotime("last day of $dateNow"));

		$id = $postData['id'];
		$kodeAkun = $postData['kode_akun'];
		$keterangan = $postData['keterangan_copy'];
		$posSaldo = $postData['pos_saldo'];
		$posLaporan = $postData['pos_laporan'];
		$buktiTransaksi = $postData['bukti_copy'];
		$akun = $postData['akun'];
		$debit = $postData['debit'];
		$kredit = $postData['kredit'];
		$posAkun = $postData['pos_akun'];

		$data1 = [];
		$index = 0;

		foreach ($kodeAkun as $datakd) {
			$data1[] = [
				'id' => $id[$index],
				'kode_akun' => $datakd,
				'keterangan' => $keterangan,
				'tanggal_transaksi' => $tglPost,
				'pos_saldo' => $posSaldo[$index],
				'pos_laporan' => $posLaporan[$index],
				'bukti_transaksi' => $buktiTransaksi,
				'akun' => $akun[$index],
				'debit' => $debit[$index],
				'kredit' => $kredit[$index],
				'pos_akun' => $posAkun[$index]
			];
			$index++;
		}

		return $this->updateBatch($data1, 'id');
	}

	/**
	 * Get journal data by transaction proof number
	 *
	 * @param string $buktiTransaksi
	 * @return array
	 */
	public function getDataJp(string $buktiTransaksi): array
	{
		$data12 = $this->where('bukti_transaksi', $buktiTransaksi)->findAll();

		$dataForm = [];

		foreach ($data12 as $key) {
			$dataForm[] = [
				'id' => $key['id'],
				'kode_akun' => $key['kode_akun'],
				'akun' => $key['akun'],
				'tanggal_transaksi' => $key['tanggal_transaksi'],
				'bukti_transaksi' => $key['bukti_transaksi'],
				'pos_saldo' => $key['pos_saldo'],
				'pos_laporan' => $key['pos_laporan'],
				'debit' => $key['debit'],
				'kredit' => $key['kredit'],
				'keterangan' => $key['keterangan'],
				'pos_akun' => $key['pos_akun']
			];
		}

		$new = count($dataForm);
		$result = ['data_count' => $new];

		// Format data based on count
		if ($new == 2) {
			$result['dataform'] = [
				'id' => [$dataForm[0]['id'], $dataForm[1]['id'], null, null],
				'kode_akun' => [$dataForm[0]['kode_akun'], $dataForm[1]['kode_akun'], null, null],
				'akun' => [$dataForm[0]['akun'], $dataForm[1]['akun'], null, null],
				'tanggal_transaksi' => [$dataForm[0]['tanggal_transaksi'], $dataForm[1]['tanggal_transaksi'], null, null],
				'bukti_transaksi' => [$dataForm[0]['bukti_transaksi'], $dataForm[1]['bukti_transaksi'], null, null],
				'pos_saldo' => [$dataForm[0]['pos_saldo'], $dataForm[1]['pos_saldo'], null, null],
				'pos_laporan' => [$dataForm[0]['pos_laporan'], $dataForm[1]['pos_laporan'], null, null],
				'debit' => [$dataForm[0]['debit'], $dataForm[1]['debit'], null, null],
				'keterangan' => [$dataForm[0]['keterangan'], $dataForm[1]['keterangan'], null, null],
				'kredit' => [$dataForm[0]['kredit'], $dataForm[1]['kredit'], null, null],
				'pos_akun' => [$dataForm[0]['pos_akun'], $dataForm[1]['pos_akun'], null, null]
			];
		} elseif ($new == 3) {
			$result['dataform'] = [
				'id' => [$dataForm[0]['id'], $dataForm[1]['id'], $dataForm[2]['id'], null],
				'kode_akun' => [$dataForm[0]['kode_akun'], $dataForm[1]['kode_akun'], $dataForm[2]['kode_akun'], null],
				'akun' => [$dataForm[0]['akun'], $dataForm[1]['akun'], $dataForm[2]['akun'], null],
				'tanggal_transaksi' => [$dataForm[0]['tanggal_transaksi'], $dataForm[1]['tanggal_transaksi'], $dataForm[2]['tanggal_transaksi'], null],
				'bukti_transaksi' => [$dataForm[0]['bukti_transaksi'], $dataForm[1]['bukti_transaksi'], $dataForm[2]['bukti_transaksi'], null],
				'pos_saldo' => [$dataForm[0]['pos_saldo'], $dataForm[1]['pos_saldo'], $dataForm[2]['pos_saldo'], null],
				'pos_laporan' => [$dataForm[0]['pos_laporan'], $dataForm[1]['pos_laporan'], $dataForm[2]['pos_laporan'], null],
				'debit' => [$dataForm[0]['debit'], $dataForm[1]['debit'], $dataForm[2]['debit'], null],
				'keterangan' => [$dataForm[0]['keterangan'], $dataForm[1]['keterangan'], $dataForm[2]['keterangan'], null],
				'kredit' => [$dataForm[0]['kredit'], $dataForm[1]['kredit'], $dataForm[2]['kredit'], null],
				'pos_akun' => [$dataForm[0]['pos_akun'], $dataForm[1]['pos_akun'], $dataForm[2]['pos_akun'], null]
			];
		} elseif ($new == 4) {
			$result['dataform'] = [
				'id' => [$dataForm[0]['id'], $dataForm[1]['id'], $dataForm[2]['id'], $dataForm[3]['id']],
				'kode_akun' => [$dataForm[0]['kode_akun'], $dataForm[1]['kode_akun'], $dataForm[2]['kode_akun'], $dataForm[3]['kode_akun']],
				'akun' => [$dataForm[0]['akun'], $dataForm[1]['akun'], $dataForm[2]['akun'], $dataForm[3]['akun']],
				'tanggal_transaksi' => [$dataForm[0]['tanggal_transaksi'], $dataForm[1]['tanggal_transaksi'], $dataForm[2]['tanggal_transaksi'], $dataForm[3]['tanggal_transaksi']],
				'bukti_transaksi' => [$dataForm[0]['bukti_transaksi'], $dataForm[1]['bukti_transaksi'], $dataForm[2]['bukti_transaksi'], $dataForm[3]['bukti_transaksi']],
				'pos_saldo' => [$dataForm[0]['pos_saldo'], $dataForm[1]['pos_saldo'], $dataForm[2]['pos_saldo'], $dataForm[3]['pos_saldo']],
				'pos_laporan' => [$dataForm[0]['pos_laporan'], $dataForm[1]['pos_laporan'], $dataForm[2]['pos_laporan'], $dataForm[3]['pos_laporan']],
				'debit' => [$dataForm[0]['debit'], $dataForm[1]['debit'], $dataForm[2]['debit'], $dataForm[3]['debit']],
				'keterangan' => [$dataForm[0]['keterangan'], $dataForm[1]['keterangan'], $dataForm[2]['keterangan'], $dataForm[3]['keterangan']],
				'kredit' => [$dataForm[0]['kredit'], $dataForm[1]['kredit'], $dataForm[2]['kredit'], $dataForm[3]['kredit']],
				'pos_akun' => [$dataForm[0]['pos_akun'], $dataForm[1]['pos_akun'], $dataForm[2]['pos_akun'], $dataForm[3]['pos_akun']]
			];
		}

		return $result;
	}

	/**
	 * Delete journal entries by transaction proof number
	 *
	 * @param string $buktiTransaksi
	 * @return bool
	 */
	public function hapusJp(string $buktiTransaksi): bool
	{
		return $this->where('bukti_transaksi', $buktiTransaksi)->delete();
	}
}
