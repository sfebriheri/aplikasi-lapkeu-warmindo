<?php

use App\Libraries\CurrencyHandler;

if (!function_exists('rupiah')) {
	/**
	 * Format currency amount as Indonesian Rupiah with HTML div wrapper
	 * Uses CurrencyHandler for decimal precision
	 *
	 * @param int|float|string $angka Amount to format
	 * @return string HTML formatted currency
	 */
	function rupiah($angka)
	{
		try {
			return CurrencyHandler::formatIDRHtml($angka);
		} catch (\Exception $e) {
			// Fallback to basic formatting if CurrencyHandler fails
			$value = is_numeric($angka) ? (float)$angka : 0;
			$formatted = number_format(abs($value), 2, ',', '.');
			if ($value < 0) {
				return "<div style='height: 100%;text-align: right;'>(Rp " . $formatted . ")</div>";
			}
			return "<div style='height: 100%;text-align: right;'>Rp " . $formatted . "</div>";
		}
	}
}

if (!function_exists('rupiah_cetak')) {
	/**
	 * Format currency amount as Indonesian Rupiah with rata-kanan class (for printing)
	 * Uses CurrencyHandler for decimal precision
	 *
	 * @param int|float|string $angka Amount to format
	 * @return string HTML formatted currency with rata-kanan class
	 */
	function rupiah_cetak($angka)
	{
		try {
			return CurrencyHandler::formatIDRCetak($angka);
		} catch (\Exception $e) {
			// Fallback to basic formatting if CurrencyHandler fails
			$value = is_numeric($angka) ? (float)$angka : 0;
			$formatted = number_format(abs($value), 2, ',', '.');
			return "<div class='rata-kanan'>Rp " . $formatted . "</div>";
		}
	}
}
