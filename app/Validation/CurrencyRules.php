<?php

namespace App\Validation;

use App\Libraries\CurrencyHandler;

/**
 * Custom validation rules for currency input
 * Uses Brick\Money for decimal precision validation
 */
class CurrencyRules
{
	/**
	 * Validate that input is a valid currency amount
	 * Accepts: "1234.56", "1,234.56", "1234,56", "1234", etc.
	 *
	 * @param string $str The value being validated
	 * @param string $field The field name for error messages
	 * @param array $data The full data array
	 * @return bool
	 */
	public static function validCurrency(?string $str, ?string $field = null, ?array $data = null): bool
	{
		if ($str === null || $str === '') {
			return true; // Let required rule handle empty values
		}

		try {
			// Try to create a Money object
			CurrencyHandler::createFromFormatted($str);
			return true;
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * Validate that input is a valid positive currency amount
	 *
	 * @param string $str The value being validated
	 * @param string $field The field name for error messages
	 * @param array $data The full data array
	 * @return bool
	 */
	public static function validPositiveCurrency(?string $str, ?string $field = null, ?array $data = null): bool
	{
		if ($str === null || $str === '') {
			return true; // Let required rule handle empty values
		}

		try {
			$money = CurrencyHandler::createFromFormatted($str);
			return $money->isPositive() || $money->isZero();
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * Validate debit/kredit pair (at least one must be filled, but not both)
	 *
	 * @param string $str The value being validated
	 * @param string $field The field name for error messages
	 * @param array $data The full data array
	 * @return bool
	 */
	public static function validDebitKredit(?string $str, ?string $field = null, ?array $data = null): bool
	{
		if (!$data) {
			return false;
		}

		$debit = $data['debit'] ?? null;
		$kredit = $data['kredit'] ?? null;

		try {
			$debitMoney = CurrencyHandler::create($debit ?? 0);
			$kreditMoney = CurrencyHandler::create($kredit ?? 0);

			$debitZero = $debitMoney->isZero();
			$kreditZero = $kreditMoney->isZero();

			// Must have exactly one of debit or kredit (not both, not neither)
			return ($debitZero && !$kreditZero) || (!$debitZero && $kreditZero);
		} catch (\Exception $e) {
			return false;
		}
	}
}
