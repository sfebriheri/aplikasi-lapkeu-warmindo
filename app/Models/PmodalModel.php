<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Perubahan Modal Model
 * Handles Statement of Changes in Equity operations
 */
class PmodalModel extends Model
{
	protected $table = 'daftar_akun';
	protected $primaryKey = 'id';
	protected $allowedFields = [];
	protected $useTimestamps = false;

	/**
	 * Get equity position accounts
	 *
	 * @return array
	 */
	public function posEkuitas(): array
	{
		return $this->where('pos_akun', 'Ekuitas')->findAll();
	}

	/**
	 * Calculate total profit/loss based on period filters
	 * This is a complex method that handles various date range scenarios
	 *
	 * @param array $data Associative array with period information (tahun, bulan, dk_awal_k, etc.)
	 * @param array $postData POST data containing filter information
	 * @return float
	 */
	public function totalLabaRugi(array $data, array $postData = []): float
	{
		$posLabaRugi = [
			['pos_akun' => 'Pendapatan', 'saldo_normal' => 'Kredit'],
			['pos_akun' => 'Beban', 'saldo_normal' => 'Debit'],
			['pos_akun' => 'Pajak', 'saldo_normal' => 'Debit']
		];

		$jumlah = [];

		foreach ($posLabaRugi as $pl) {
			$debit = 0;
			$kredit = 0;

			// Filter by month and year posted
			if (isset($postData['bulan_post']) && isset($postData['tahun_post'])) {
				$dateAkhir = date($data['tahun'] . "-" . $data['bulan'] . "-d");
				$bulan = $data['bulan'];
				$dtAkhir = date("Y-m-d", strtotime("last day of $dateAkhir"));

				// Get initial balance
				$dateSa = $data['tahun'];
				$saD = $this->db->table('saldo_awal')
					->selectSum('debit', 'total')
					->where('YEAR(tanggal_transaksi)', $dateSa)
					->where('pos_akun', $pl['pos_akun'])
					->get()
					->getRow()
					->total ?? 0;

				$saK = $this->db->table('saldo_awal')
					->selectSum('kredit', 'total')
					->where('YEAR(tanggal_transaksi)', $dateSa)
					->where('pos_akun', $pl['pos_akun'])
					->get()
					->getRow()
					->total ?? 0;

				// Get transaction totals
				$deb = $this->db->table('transaksi')
					->selectSum('debit', 'total')
					->where('tanggal_transaksi >=', $data['dk_awal_k'])
					->where('tanggal_transaksi <=', $dtAkhir)
					->where('pos_akun', $pl['pos_akun'])
					->get()
					->getRow()
					->total ?? 0;

				$kre = $this->db->table('transaksi')
					->selectSum('kredit', 'total')
					->where('tanggal_transaksi >=', $data['dk_awal_k'])
					->where('tanggal_transaksi <=', $dtAkhir)
					->where('pos_akun', $pl['pos_akun'])
					->get()
					->getRow()
					->total ?? 0;

				$debit = $saD + $deb;
				$kredit = $saK + $kre;
			} // Filter by date range
			elseif (isset($postData['tanggal_awal'])) {
				$tglAwal = $postData['tanggal_awal'];
				$tglAkhir = $postData['tanggal_akhir'];
				$tahunJika = date("Y", strtotime($tglAwal));
				$bulan = date("m", strtotime($tglAwal));

				// If starting from January 1st
				if ($tglAwal == date($tahunJika . '-01-01')) {
					$tahun = date('Y', strtotime($tglAwal));

					// Get initial balance
					$saD = $this->db->table('saldo_awal')
						->selectSum('debit', 'total')
						->where('YEAR(tanggal_transaksi)', $tahun)
						->where('pos_akun', $pl['pos_akun'])
						->get()
						->getRow()
						->total ?? 0;

					$saK = $this->db->table('saldo_awal')
						->selectSum('kredit', 'total')
						->where('YEAR(tanggal_transaksi)', $tahun)
						->where('pos_akun', $pl['pos_akun'])
						->get()
						->getRow()
						->total ?? 0;

					// Get transaction totals
					$deb = $this->db->table('transaksi')
						->selectSum('debit', 'total')
						->where('tanggal_transaksi >=', $tglAwal)
						->where('tanggal_transaksi <=', $tglAkhir)
						->where('pos_akun', $pl['pos_akun'])
						->get()
						->getRow()
						->total ?? 0;

					$kre = $this->db->table('transaksi')
						->selectSum('kredit', 'total')
						->where('tanggal_transaksi >=', $tglAwal)
						->where('tanggal_transaksi <=', $tglAkhir)
						->where('pos_akun', $pl['pos_akun'])
						->get()
						->getRow()
						->total ?? 0;

					$debit = $saD + $deb;
					$kredit = $saK + $kre;
				} // If starting from January (not necessarily 1st)
				elseif (date("m", strtotime($tglAwal)) == '1') {
					$bulan = date('m', strtotime($tglAwal));
					$dataBulan = $tglAwal;
					$tahun = date('Y', strtotime($tglAwal));

					$dkAwalK = date("Y-m-d", strtotime("first day of $dataBulan"));
					$dkAkhirK = date("Y-m-d", strtotime("$tglAwal -1 day"));
					$dkAwalK1 = $tglAwal;
					$dkAkhirK1 = $tglAkhir;

					// Get initial balance
					$saD = $this->db->table('saldo_awal')
						->selectSum('debit', 'total')
						->where('YEAR(tanggal_transaksi)', $tahun)
						->where('pos_akun', $pl['pos_akun'])
						->get()
						->getRow()
						->total ?? 0;

					$saK = $this->db->table('saldo_awal')
						->selectSum('kredit', 'total')
						->where('YEAR(tanggal_transaksi)', $tahun)
						->where('pos_akun', $pl['pos_akun'])
						->get()
						->getRow()
						->total ?? 0;

					// Get transactions before posted period
					$deb = $this->db->table('transaksi')
						->selectSum('debit', 'total')
						->where('tanggal_transaksi >=', $dkAwalK)
						->where('tanggal_transaksi <=', $dkAkhirK)
						->where('pos_akun', $pl['pos_akun'])
						->get()
						->getRow()
						->total ?? 0;

					$kre = $this->db->table('transaksi')
						->selectSum('kredit', 'total')
						->where('tanggal_transaksi >=', $dkAwalK)
						->where('tanggal_transaksi <=', $dkAkhirK)
						->where('pos_akun', $pl['pos_akun'])
						->get()
						->getRow()
						->total ?? 0;

					// Get transactions in posted period
					$debB = $this->db->table('transaksi')
						->selectSum('debit', 'total')
						->where('tanggal_transaksi >=', $dkAwalK1)
						->where('tanggal_transaksi <=', $dkAkhirK1)
						->where('pos_akun', $pl['pos_akun'])
						->get()
						->getRow()
						->total ?? 0;

					$kreB = $this->db->table('transaksi')
						->selectSum('kredit', 'total')
						->where('tanggal_transaksi >=', $dkAwalK1)
						->where('tanggal_transaksi <=', $dkAkhirK1)
						->where('pos_akun', $pl['pos_akun'])
						->get()
						->getRow()
						->total ?? 0;

					$debit = $deb + $debB + $saD;
					$kredit = $kre + $kreB + $saK;
				} // Other months
				else {
					$bulan = date('m', strtotime($tglAwal));
					$dataBulan = $bulan;
					$dataKurang = $bulan - 1;
					$tahun = date('Y', strtotime($tglAwal));

					$dkAwalK = date("Y-m-d", strtotime("first day of $dataBulan-$dataKurang"));
					$dkAkhirK = date("Y-m-d", strtotime("$tglAwal -1 day"));
					$dkAwalK1 = $tglAwal;
					$dkAkhirK1 = $tglAkhir;

					// Get initial balance
					$saD = $this->db->table('saldo_awal')
						->selectSum('debit', 'total')
						->where('YEAR(tanggal_transaksi)', $tahun)
						->where('pos_akun', $pl['pos_akun'])
						->get()
						->getRow()
						->total ?? 0;

					$saK = $this->db->table('saldo_awal')
						->selectSum('kredit', 'total')
						->where('YEAR(tanggal_transaksi)', $tahun)
						->where('pos_akun', $pl['pos_akun'])
						->get()
						->getRow()
						->total ?? 0;

					// Get transactions before posted period
					$deb = $this->db->table('transaksi')
						->selectSum('debit', 'total')
						->where('tanggal_transaksi >=', $dkAwalK)
						->where('tanggal_transaksi <=', $dkAkhirK)
						->where('pos_akun', $pl['pos_akun'])
						->get()
						->getRow()
						->total ?? 0;

					$kre = $this->db->table('transaksi')
						->selectSum('kredit', 'total')
						->where('tanggal_transaksi >=', $dkAwalK)
						->where('tanggal_transaksi <=', $dkAkhirK)
						->where('pos_akun', $pl['pos_akun'])
						->get()
						->getRow()
						->total ?? 0;

					// Get transactions in posted period
					if (isset($postData['tanggal_awal'])) {
						$debB = $this->db->table('transaksi')
							->selectSum('debit', 'total')
							->where('tanggal_transaksi >=', $dkAwalK1)
							->where('tanggal_transaksi <=', $dkAkhirK1)
							->where('pos_akun', $pl['pos_akun'])
							->get()
							->getRow()
							->total ?? 0;

						$kreB = $this->db->table('transaksi')
							->selectSum('kredit', 'total')
							->where('tanggal_transaksi >=', $dkAwalK1)
							->where('tanggal_transaksi <=', $dkAkhirK1)
							->where('pos_akun', $pl['pos_akun'])
							->get()
							->getRow()
							->total ?? 0;
					} else {
						$debB = $this->db->table('transaksi')
							->selectSum('debit', 'total')
							->where('YEAR(tanggal_transaksi)', $tahun)
							->where('MONTH(tanggal_transaksi)', $bulan)
							->where('pos_akun', $pl['pos_akun'])
							->get()
							->getRow()
							->total ?? 0;

						$kreB = $this->db->table('transaksi')
							->selectSum('kredit', 'total')
							->where('YEAR(tanggal_transaksi)', $tahun)
							->where('MONTH(tanggal_transaksi)', $bulan)
							->where('pos_akun', $pl['pos_akun'])
							->get()
							->getRow()
							->total ?? 0;
					}

					$debit = $deb + $debB + $saD;
					$kredit = $kre + $kreB + $saK;
				}
			} // Filter by year only
			else {
				if (isset($postData['tahun_post'])) {
					$dateSa = $postData['tahun_post'];

					$saD = $this->db->table('saldo_awal')
						->selectSum('debit', 'total')
						->where('YEAR(tanggal_transaksi)', $dateSa)
						->where('pos_akun', $pl['pos_akun'])
						->get()
						->getRow()
						->total ?? 0;

					$saK = $this->db->table('saldo_awal')
						->selectSum('kredit', 'total')
						->where('YEAR(tanggal_transaksi)', $dateSa)
						->where('pos_akun', $pl['pos_akun'])
						->get()
						->getRow()
						->total ?? 0;

					$date = $postData['tahun_post'];
					$bulan = $data['bulan'];

					$deb = $this->db->table('transaksi')
						->selectSum('debit', 'total')
						->where('YEAR(tanggal_transaksi)', $date)
						->where('pos_akun', $pl['pos_akun'])
						->get()
						->getRow()
						->total ?? 0;

					$kre = $this->db->table('transaksi')
						->selectSum('kredit', 'total')
						->where('YEAR(tanggal_transaksi)', $date)
						->where('pos_akun', $pl['pos_akun'])
						->get()
						->getRow()
						->total ?? 0;

					$debit = $saD + $deb;
					$kredit = $saK + $kre;
				} // Default: current period
				else {
					if ($data['bulan'] != 1) {
						$dateSa = date('Y');

						$saD = $this->db->table('saldo_awal')
							->selectSum('debit', 'total')
							->where('YEAR(tanggal_transaksi)', $dateSa)
							->where('pos_akun', $pl['pos_akun'])
							->get()
							->getRow()
							->total ?? 0;

						$saK = $this->db->table('saldo_awal')
							->selectSum('kredit', 'total')
							->where('YEAR(tanggal_transaksi)', $dateSa)
							->where('pos_akun', $pl['pos_akun'])
							->get()
							->getRow()
							->total ?? 0;

						$date = date('Y');
						$bulan = $data['bulan'];
						$dateAkhir = date($data['tahun'] . "-" . $data['bulan'] . "-d");
						$dtAkhir = date("Y-m-d", strtotime("last day of $dateAkhir"));

						$deb = $this->db->table('transaksi')
							->selectSum('debit', 'total')
							->where('tanggal_transaksi >=', $data['dk_awal_k'])
							->where('tanggal_transaksi <=', $dtAkhir)
							->where('pos_akun', $pl['pos_akun'])
							->get()
							->getRow()
							->total ?? 0;

						$kre = $this->db->table('transaksi')
							->selectSum('kredit', 'total')
							->where('tanggal_transaksi >=', $data['dk_awal_k'])
							->where('tanggal_transaksi <=', $dtAkhir)
							->where('pos_akun', $pl['pos_akun'])
							->get()
							->getRow()
							->total ?? 0;

						$debit = $saD + $deb;
						$kredit = $saK + $kre;
					} else {
						$dateSa = date('Y');

						$saD = $this->db->table('saldo_awal')
							->selectSum('debit', 'total')
							->where('YEAR(tanggal_transaksi)', $dateSa)
							->where('pos_akun', $pl['pos_akun'])
							->get()
							->getRow()
							->total ?? 0;

						$saK = $this->db->table('saldo_awal')
							->selectSum('kredit', 'total')
							->where('YEAR(tanggal_transaksi)', $dateSa)
							->where('pos_akun', $pl['pos_akun'])
							->get()
							->getRow()
							->total ?? 0;

						$date = date('Y');
						$bulan = $data['bulan'];

						$deb = $this->db->table('transaksi')
							->selectSum('debit', 'total')
							->where('YEAR(tanggal_transaksi)', $date)
							->where('MONTH(tanggal_transaksi)', $bulan)
							->where('pos_akun', $pl['pos_akun'])
							->get()
							->getRow()
							->total ?? 0;

						$kre = $this->db->table('transaksi')
							->selectSum('kredit', 'total')
							->where('YEAR(tanggal_transaksi)', $date)
							->where('MONTH(tanggal_transaksi)', $bulan)
							->where('pos_akun', $pl['pos_akun'])
							->get()
							->getRow()
							->total ?? 0;

						$debit = $saD + $deb;
						$kredit = $saK + $kre;
					}
				}
			}

			// Calculate based on normal balance
			if ($pl['saldo_normal'] == 'Kredit') {
				$jumlah[$pl['pos_akun']] = $kredit - $debit;
			} else {
				$jumlah[$pl['pos_akun']] = $debit + $kredit;
			}
		}

		// Calculate total profit/loss
		$totalLabaRugi = $jumlah['Pendapatan'] - $jumlah['Beban'] - $jumlah['Pajak'];

		return $totalLabaRugi;
	}
}
