<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Data Sewa Model
 * Handles rental/lease data operations and related transactions
 */
class DsModel extends Model
{
	protected $table = 'data_sewa';
	protected $primaryKey = 'id_sewa';
	protected $allowedFields = [
		'nama_penyewa',
		'tgl_sewa',
		'tgl_kembali',
		'biaya_sewa',
		'uang_muka',
		'bayar',
		'kendaraan',
		'tgl_lunas',
		'status',
		'id_sewa'
	];
	protected $useTimestamps = false;

	/**
	 * Display all rental data
	 *
	 * @return array
	 */
	public function tampilDataSewa(): array
	{
		return $this->findAll();
	}

	/**
	 * Add rental data
	 *
	 * @param array $data
	 * @return bool|int
	 */
	public function tambahDataSewa(array $data)
	{
		// Process status-dependent fields
		if ($data['status'] == 'L') {
			$data['tgl_lunas'] = $data['tgl_kembali'];
			$data['bayar'] = $data['biaya_sewa'];
			$data['uang_muka'] = '0.00';
		} else {
			$data['tgl_lunas'] = '0000-00-00';
			$data['bayar'] = '0.00';
		}

		return $this->insert($data);
	}

	/**
	 * Add rental transaction to journal
	 *
	 * @param array $postData
	 * @return bool|int
	 */
	public function tambahTransaksiDs(array $postData)
	{
		$kodeAkun = $postData['kode_akun'];
		$keterangan = $postData['keterangan'];
		$tanggalTransaksi = $postData['tanggal_transaksi'];
		$posSaldo = $postData['pos_saldo'];
		$posLaporan = $postData['pos_laporan'];
		$buktiTransaksi = $postData['bukti_transaksi'];
		$akun = $postData['akun'];
		$debit = $postData['debit'];
		$kredit = $postData['kredit'];
		$posAkun = $postData['pos_akun'];
		$idSewa = $postData['id_sewa'];

		$dataT = [];
		$index = 0;

		foreach ($akun as $datakd) {
			if ($datakd == 'Piutang Usaha') {
				$dataT[] = [
					'kode_akun' => $kodeAkun[$index],
					'keterangan' => "Sewa " . $keterangan[$index],
					'tanggal_transaksi' => $tanggalTransaksi[$index],
					'pos_saldo' => $posSaldo[$index],
					'pos_laporan' => $posLaporan[$index],
					'bukti_transaksi' => $buktiTransaksi[$index],
					'akun' => $datakd,
					'debit' => $kredit[2] - $debit[0],
					'kredit' => $kredit[$index],
					'pos_akun' => $posAkun[$index],
					'id_sewa' => $idSewa,
					'ref' => 'JU'
				];
			} else {
				$dataT[] = [
					'kode_akun' => $kodeAkun[$index],
					'keterangan' => "Sewa " . $keterangan[$index],
					'tanggal_transaksi' => $tanggalTransaksi[$index],
					'pos_saldo' => $posSaldo[$index],
					'pos_laporan' => $posLaporan[$index],
					'bukti_transaksi' => $buktiTransaksi[$index],
					'akun' => $datakd,
					'debit' => $debit[$index],
					'kredit' => $kredit[$index],
					'pos_akun' => $posAkun[$index],
					'id_sewa' => $idSewa,
					'ref' => 'JU'
				];
			}
			$index++;
		}

		return $this->db->table('transaksi')->insertBatch($dataT);
	}

	/**
	 * Update rental status to paid (lunas) and create journal entry
	 *
	 * @param string $idSewa
	 * @param string $tanggalLunas
	 * @return bool
	 */
	public function updateLunas(string $idSewa, string $tanggalLunas): bool
	{
		// Generate next transaction proof number
		$query = $this->db->table('transaksi')->orderBy('id', 'DESC')->limit(1)->get()->getRowArray();
		if ($query && isset($query['bukti_transaksi'])) {
			$kode = intval($query['bukti_transaksi']) + 1;
		} else {
			$kode = 1;
		}
		$kodemax = str_pad($kode, 6, '0', STR_PAD_LEFT);

		// Get rental data
		$data = $this->where('id_sewa', $idSewa)->first();

		// Update rental data to paid status
		$updateData = [
			'nama_penyewa' => $data['nama_penyewa'],
			'tgl_sewa' => $data['tgl_sewa'],
			'tgl_kembali' => $data['tgl_kembali'],
			'biaya_sewa' => $data['biaya_sewa'],
			'uang_muka' => $data['uang_muka'],
			'bayar' => $data['biaya_sewa'] - $data['uang_muka'],
			'kendaraan' => $data['kendaraan'],
			'tgl_lunas' => $tanggalLunas,
			'status' => 'L'
		];

		$this->update($idSewa, $updateData);

		// Create journal entry for payment
		$dataTrans = $this->db->table('transaksi')->where('id_sewa', $idSewa)->get()->getResultArray();

		$kodeAkun = [$dataTrans[0]['kode_akun'], $dataTrans[1]['kode_akun']];
		$keterangan = [$dataTrans[0]['keterangan'], $dataTrans[1]['keterangan']];
		$posSaldo = [$dataTrans[0]['pos_saldo'], $dataTrans[1]['pos_saldo']];
		$posLaporan = [$dataTrans[0]['pos_laporan'], $dataTrans[1]['pos_laporan']];
		$buktiTransaksi = $kodemax;
		$akun = [$dataTrans[0]['akun'], $dataTrans[1]['akun']];
		$debit = [$dataTrans[1]['debit'], 0];
		$kredit = [0, $dataTrans[1]['debit']];
		$posAkun = [$dataTrans[0]['pos_akun'], $dataTrans[1]['pos_akun']];

		$dataT = [];
		$index = 0;

		foreach ($akun as $datakd) {
			$dataT[] = [
				'kode_akun' => $kodeAkun[$index],
				'keterangan' => $keterangan[$index],
				'tanggal_transaksi' => $tanggalLunas,
				'pos_saldo' => $posSaldo[$index],
				'pos_laporan' => $posLaporan[$index],
				'bukti_transaksi' => $buktiTransaksi,
				'akun' => $datakd,
				'debit' => $debit[$index],
				'kredit' => $kredit[$index],
				'pos_akun' => $posAkun[$index],
				'id_sewa' => $idSewa,
				'ref' => 'JU'
			];
			$index++;
		}

		return $this->db->table('transaksi')->insertBatch($dataT);
	}

	/**
	 * Delete rental data and related transactions
	 *
	 * @param string $idSewa
	 * @return bool
	 */
	public function hapusData(string $idSewa): bool
	{
		$this->db->table('transaksi')->where('id_sewa', $idSewa)->delete();
		return $this->delete($idSewa);
	}

	/**
	 * Get rental data by ID
	 *
	 * @param string $idSewa
	 * @return array|null
	 */
	public function getDataSewa(string $idSewa): ?array
	{
		return $this->where('id_sewa', $idSewa)->first();
	}

	/**
	 * Get transaction data by rental ID
	 *
	 * @param string $idSewa
	 * @return array
	 */
	public function getDataTrans(string $idSewa): array
	{
		return $this->db->table('transaksi')->where('id_sewa', $idSewa)->get()->getResultArray();
	}

	/**
	 * Update rental data
	 *
	 * @param string $idSewa
	 * @param array $data
	 * @return bool
	 */
	public function updateDs(string $idSewa, array $data): bool
	{
		return $this->update($idSewa, $data);
	}

	/**
	 * Update transaction batch
	 *
	 * @param array $postData
	 * @return int|bool
	 */
	public function updateTrans(array $postData)
	{
		$id = $postData['id'];
		$kodeAkun = $postData['kode_akun'];
		$keterangan = $postData['keterangan'];
		$tanggalTransaksi = $postData['tanggal_transaksi'];
		$posSaldo = $postData['pos_saldo'];
		$posLaporan = $postData['pos_laporan'];
		$buktiTransaksi = $postData['bukti_transaksi'];
		$akun = $postData['akun'];
		$debit = $postData['debit'];
		$kredit = $postData['kredit'];
		$posAkun = $postData['pos_akun'];
		$idSewa = $postData['id_sewa'];

		$dataT = [];
		$index = 0;

		foreach ($akun as $datakd) {
			if ($datakd == 'Piutang Usaha') {
				$dataT[] = [
					'id' => $id[$index],
					'kode_akun' => $kodeAkun[$index],
					'keterangan' => "Sewa " . $keterangan[$index],
					'tanggal_transaksi' => $tanggalTransaksi[$index],
					'pos_saldo' => $posSaldo[$index],
					'pos_laporan' => $posLaporan[$index],
					'bukti_transaksi' => $buktiTransaksi[$index],
					'akun' => $datakd,
					'debit' => $kredit[2] - $debit[0],
					'kredit' => $kredit[$index],
					'pos_akun' => $posAkun[$index],
					'id_sewa' => $idSewa,
					'ref' => 'JU'
				];
			} else {
				$dataT[] = [
					'id' => $id[$index],
					'kode_akun' => $kodeAkun[$index],
					'keterangan' => "Sewa " . $keterangan[$index],
					'tanggal_transaksi' => $tanggalTransaksi[$index],
					'pos_saldo' => $posSaldo[$index],
					'pos_laporan' => $posLaporan[$index],
					'bukti_transaksi' => $buktiTransaksi[$index],
					'akun' => $datakd,
					'debit' => $debit[$index],
					'kredit' => $kredit[$index],
					'pos_akun' => $posAkun[$index],
					'id_sewa' => $idSewa,
					'ref' => 'JU'
				];
			}
			$index++;
		}

		return $this->db->table('transaksi')->updateBatch($dataT, 'id');
	}

	/**
	 * Update both rental and transaction data (delete and reinsert)
	 *
	 * @param string $idSewa
	 * @param array $postData
	 * @return bool|int
	 */
	public function update2Data(string $idSewa, array $postData)
	{
		// Delete existing data
		$this->db->table('transaksi')->where('id_sewa', $idSewa)->delete();
		$this->delete($idSewa);

		// Process status-dependent fields
		if ($postData['status'] == 'L') {
			$tglLunasPost = $postData['tgl_kembali'];
			$bayarPost = $postData['biaya_sewa'];
			$uangMukaPost = '-';
		} else {
			$uangMukaPost = $postData['uang_muka'];
			$tglLunasPost = '-';
			$bayarPost = '-';
		}

		// Insert rental data
		$data = [
			'nama_penyewa' => $postData['nama_penyewa'],
			'tgl_sewa' => $postData['tgl_sewa'],
			'tgl_kembali' => $postData['tgl_kembali'],
			'biaya_sewa' => $postData['biaya_sewa'],
			'uang_muka' => $uangMukaPost,
			'bayar' => $bayarPost,
			'kendaraan' => 'Avanza',
			'tgl_lunas' => $tglLunasPost,
			'status' => $postData['status'],
			'id_sewa' => $postData['id_sewa']
		];

		$this->insert($data);

		// Insert transaction data
		$kodeAkun = $postData['kode_akun'];
		$keterangan = $postData['keterangan'];
		$tanggalTransaksi = $postData['tanggal_transaksi'];
		$posSaldo = $postData['pos_saldo'];
		$posLaporan = $postData['pos_laporan'];
		$buktiTransaksi = $postData['bukti_transaksi'];
		$akun = $postData['akun'];
		$debit = $postData['debit'];
		$kredit = $postData['kredit'];
		$posAkun = $postData['pos_akun'];

		$dataT = [];
		$index = 0;

		foreach ($akun as $datakd) {
			if ($datakd == 'Piutang Usaha') {
				$dataT[] = [
					'kode_akun' => $kodeAkun[$index],
					'keterangan' => "Sewa " . $keterangan[$index],
					'tanggal_transaksi' => $tanggalTransaksi[$index],
					'pos_saldo' => $posSaldo[$index],
					'pos_laporan' => $posLaporan[$index],
					'bukti_transaksi' => $buktiTransaksi[$index],
					'akun' => $datakd,
					'debit' => $kredit[2] - $debit[0],
					'kredit' => $kredit[$index],
					'pos_akun' => $posAkun[$index],
					'id_sewa' => $idSewa,
					'ref' => 'JU'
				];
			} else {
				$dataT[] = [
					'kode_akun' => $kodeAkun[$index],
					'keterangan' => "Sewa " . $keterangan[$index],
					'tanggal_transaksi' => $tanggalTransaksi[$index],
					'pos_saldo' => $posSaldo[$index],
					'pos_laporan' => $posLaporan[$index],
					'bukti_transaksi' => $buktiTransaksi[$index],
					'akun' => $datakd,
					'debit' => $debit[$index],
					'kredit' => $kredit[$index],
					'pos_akun' => $posAkun[$index],
					'id_sewa' => $idSewa,
					'ref' => 'JU'
				];
			}
			$index++;
		}

		return $this->db->table('transaksi')->insertBatch($dataT);
	}

	/**
	 * Update 5 data entries (for paid status with multiple transactions)
	 *
	 * @param string $idSewa
	 * @param array $postData
	 * @return int|bool
	 */
	public function update5Data(string $idSewa, array $postData)
	{
		// Update rental data
		$data = [
			'nama_penyewa' => $postData['nama_penyewa'],
			'tgl_sewa' => $postData['tgl_sewa'],
			'tgl_kembali' => $postData['tgl_kembali'],
			'biaya_sewa' => $postData['biaya_sewa'],
			'uang_muka' => $postData['uang_muka'],
			'bayar' => $postData['biaya_sewa'] - $postData['uang_muka'],
			'kendaraan' => $postData['kendaraan'],
			'tgl_lunas' => $postData['tgl_lunas'],
			'status' => 'L'
		];

		$this->update($idSewa, $data);

		// Prepare transaction data
		$tgl012 = $postData['tgl_sewa'];
		$tgl34 = $postData['tgl_lunas'];
		$bukti012 = $postData['bukti_ds'];
		$bukti34 = $postData['bukti_lunas'];
		$uangMuka = $postData['uang_muka'];
		$pj = $postData['biaya_sewa'];
		$piutang = $pj - $uangMuka;

		$kodeAkun = $postData['kode_akun'];
		$keterangan = $postData['keterangan'][0];
		$tanggalTransaksi = [$tgl012, $tgl012, $tgl012, $tgl34, $tgl34];
		$posSaldo = $postData['pos_saldo'];
		$posAkun = $postData['pos_akun'];
		$posLaporan = $postData['pos_laporan'];
		$akun = $postData['akun'];
		$buktiTransaksi = [$bukti012, $bukti012, $bukti012, $bukti34, $bukti34];
		$debit = [$uangMuka, $piutang, 0, $piutang, 0];
		$kredit = [0, 0, $pj, 0, $piutang];
		$id = $postData['id'];

		$dataT = [];
		$index = 0;

		foreach ($akun as $datakd) {
			$dataT[] = [
				'id' => $id[$index],
				'kode_akun' => $kodeAkun[$index],
				'keterangan' => "Sewa  " . $keterangan,
				'tanggal_transaksi' => $tanggalTransaksi[$index],
				'pos_saldo' => $posSaldo[$index],
				'pos_laporan' => $posLaporan[$index],
				'bukti_transaksi' => $buktiTransaksi[$index],
				'akun' => $datakd,
				'debit' => $debit[$index],
				'kredit' => $kredit[$index],
				'pos_akun' => $posAkun[$index],
				'id_sewa' => $idSewa,
				'ref' => 'JU'
			];
			$index++;
		}

		return $this->db->table('transaksi')->updateBatch($dataT, 'id');
	}

	/**
	 * Generate next transaction proof number
	 *
	 * @return string
	 */
	public function buktiTransaksi(): string
	{
		$query = $this->db->table('transaksi')->orderBy('id', 'DESC')->limit(1)->get()->getRowArray();

		if ($query && isset($query['bukti_transaksi'])) {
			$kode = intval($query['bukti_transaksi']) + 1;
		} else {
			$kode = 1;
		}

		return str_pad($kode, 6, '0', STR_PAD_LEFT);
	}

	/**
	 * Generate next rental ID
	 *
	 * @return int
	 */
	public function idSewa(): int
	{
		$query = $this->orderBy('id_sewa', 'DESC')->limit(1)->first();

		if ($query && isset($query['id_sewa'])) {
			$kode = intval($query['id_sewa']) + 1;
		} else {
			$kode = 1;
		}

		return $kode;
	}

	/**
	 * Get field information by account name
	 *
	 * @param string $akun
	 * @return array
	 */
	public function isiFieldByKode(string $akun): array
	{
		$result = $this->db->table('daftar_akun')
			->where('akun', $akun)
			->get()
			->getRowArray();

		if ($result) {
			return [
				'akun' => $result['akun'],
				'kode_akun' => $result['kode_akun'],
				'pos_laporan' => $result['pos_laporan'],
				'pos_akun' => $result['pos_akun']
			];
		}

		return [];
	}

	/**
	 * Add vehicle data
	 *
	 * @param array $data
	 * @return bool|int
	 */
	public function tambahMobil(array $data)
	{
		return $this->db->table('data_kendaraan')->insert($data);
	}

	/**
	 * Display all vehicles
	 *
	 * @return array
	 */
	public function tampilMobil(): array
	{
		return $this->db->table('data_kendaraan')->get()->getResultArray();
	}

	/**
	 * Update vehicle data
	 *
	 * @param int $id
	 * @param array $data
	 * @return bool
	 */
	public function updateMobil(int $id, array $data): bool
	{
		return $this->db->table('data_kendaraan')->where('id', $id)->update($data);
	}

	/**
	 * Delete vehicle data
	 *
	 * @param int $id
	 * @return bool
	 */
	public function hapusMobil(int $id): bool
	{
		return $this->db->table('data_kendaraan')->where('id', $id)->delete();
	}
}
