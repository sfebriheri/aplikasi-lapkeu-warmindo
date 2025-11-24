<?php

use App\Libraries\CurrencyHandler;

/**
 * Currency Helper for CodeIgniter 4
 * Provides formatting functions for Indonesian Rupiah currency
 * Uses CurrencyHandler library for decimal precision
 */

if (!function_exists('rupiah')) {
	/**
	 * Format currency amount as Indonesian Rupiah with HTML div wrapper
	 * Uses CurrencyHandler for decimal precision
	 *
	 * @param int|float|string $amount Amount to format
	 * @return string HTML formatted currency
	 */
	function rupiah($amount): string
	{
		try {
			return CurrencyHandler::formatIDRHtml($amount);
		} catch (\Exception $e) {
			// Fallback to basic formatting if CurrencyHandler fails
			$value = is_numeric($amount) ? (float)$amount : 0;
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
	 * @param int|float|string $amount Amount to format
	 * @return string HTML formatted currency with rata-kanan class
	 */
	function rupiah_cetak($amount): string
	{
		try {
			return CurrencyHandler::formatIDRCetak($amount);
		} catch (\Exception $e) {
			// Fallback to basic formatting if CurrencyHandler fails
			$value = is_numeric($amount) ? (float)$amount : 0;
			$formatted = number_format(abs($value), 2, ',', '.');
			return "<div class='rata-kanan'>Rp " . $formatted . "</div>";
		}
	}
}

if (!function_exists('rupiah_plain')) {
	/**
	 * Format currency amount as Indonesian Rupiah string (no HTML)
	 * Uses CurrencyHandler for decimal precision
	 *
	 * @param int|float|string $amount Amount to format
	 * @return string Formatted currency (e.g., "Rp 1.500.000,00")
	 */
	function rupiah_plain($amount): string
	{
		try {
			return CurrencyHandler::formatIDR($amount);
		} catch (\Exception $e) {
			// Fallback to basic formatting if CurrencyHandler fails
			$value = is_numeric($amount) ? (float)$amount : 0;
			return "Rp " . number_format(abs($value), 2, ',', '.');
		}
	}
}

if (!function_exists('currency_to_numeric')) {
	/**
	 * Convert currency value to numeric string for database storage
	 * Uses CurrencyHandler for decimal precision
	 *
	 * @param int|float|string $amount Amount to convert
	 * @return string Numeric string (e.g., "1500000.50")
	 */
	function currency_to_numeric($amount): string
	{
		try {
			return CurrencyHandler::toNumericString($amount);
		} catch (\Exception $e) {
			// Fallback to basic conversion if CurrencyHandler fails
			return (string)(float)$amount;
		}
	}
}

if (!function_exists('currency_create')) {
	/**
	 * Create a Money object from a value
	 * Uses CurrencyHandler wrapper around Brick\Money
	 *
	 * @param int|float|string $amount Amount value
	 * @param string $currency Currency code (default: IDR)
	 * @return \Brick\Money\Money
	 */
	function currency_create($amount, string $currency = 'IDR')
	{
		return CurrencyHandler::create($amount, $currency);
	}
}
