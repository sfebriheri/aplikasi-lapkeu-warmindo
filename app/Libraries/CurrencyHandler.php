<?php

namespace App\Libraries;

use Brick\Money\Money;
use Brick\Money\Currency;
use Brick\Math\BigDecimal;

/**
 * CurrencyHandler - Decimal-based currency handling for accounting
 *
 * Uses Brick\Money for precise decimal arithmetic on currency values.
 * Specifically configured for Indonesian Rupiah (IDR).
 */
class CurrencyHandler
{
    /**
     * Default currency: Indonesian Rupiah
     */
    private const DEFAULT_CURRENCY = 'IDR';

    /**
     * Create a Money object from a numeric value
     *
     * @param int|float|string $amount The amount value
     * @param string $currencyCode Currency code (default: IDR)
     * @return Money
     */
    public static function create($amount, string $currencyCode = self::DEFAULT_CURRENCY): Money
    {
        // Convert to string
        $decimalAmount = (string)$amount;

        // If the string contains a comma, treat it as formatted (IDR style: 1.000.000,50)
        // Otherwise, just use it as-is
        if (strpos($decimalAmount, ',') !== false) {
            // Formatted string: remove thousand separators (periods) and convert decimal comma to period
            $decimalAmount = str_replace('.', '', $decimalAmount);
            $decimalAmount = str_replace(',', '.', $decimalAmount);
        }
        // If it's a regular float/int/numeric string (1000.50), just use it as-is
        // Money::of() will parse it correctly

        // Money::of() accepts string directly and parses it properly
        return Money::of($decimalAmount, Currency::of($currencyCode));
    }

    /**
     * Create Money from a string with decimal separator (comma for IDR)
     *
     * @param string $amount Amount as string (e.g., "1.500.000,50")
     * @param string $currencyCode Currency code (default: IDR)
     * @return Money
     */
    public static function createFromFormatted(string $amount, string $currencyCode = self::DEFAULT_CURRENCY): Money
    {
        // Remove thousand separators (periods) and convert decimal comma to period
        $cleanAmount = str_replace('.', '', $amount);
        $cleanAmount = str_replace(',', '.', $cleanAmount);

        // Money::of() accepts string directly and parses it properly
        return Money::of($cleanAmount, Currency::of($currencyCode));
    }

    /**
     * Add two monetary amounts
     *
     * @param Money|int|float|string $amount1 First amount
     * @param Money|int|float|string $amount2 Second amount
     * @param string $currencyCode Currency code (default: IDR)
     * @return Money
     */
    public static function add($amount1, $amount2, string $currencyCode = self::DEFAULT_CURRENCY): Money
    {
        $money1 = $amount1 instanceof Money ? $amount1 : self::create($amount1, $currencyCode);
        $money2 = $amount2 instanceof Money ? $amount2 : self::create($amount2, $currencyCode);

        return $money1->plus($money2);
    }

    /**
     * Subtract two monetary amounts
     *
     * @param Money|int|float|string $amount1 First amount (minuend)
     * @param Money|int|float|string $amount2 Second amount (subtrahend)
     * @param string $currencyCode Currency code (default: IDR)
     * @return Money
     */
    public static function subtract($amount1, $amount2, string $currencyCode = self::DEFAULT_CURRENCY): Money
    {
        $money1 = $amount1 instanceof Money ? $amount1 : self::create($amount1, $currencyCode);
        $money2 = $amount2 instanceof Money ? $amount2 : self::create($amount2, $currencyCode);

        return $money1->minus($money2);
    }

    /**
     * Multiply monetary amount by a multiplier
     *
     * @param Money|int|float|string $amount Amount to multiply
     * @param int|float|string $multiplier Multiplier value
     * @param string $currencyCode Currency code (default: IDR)
     * @return Money
     */
    public static function multiply($amount, $multiplier, string $currencyCode = self::DEFAULT_CURRENCY): Money
    {
        $money = $amount instanceof Money ? $amount : self::create($amount, $currencyCode);

        return $money->multipliedBy($multiplier);
    }

    /**
     * Divide monetary amount by a divisor
     *
     * @param Money|int|float|string $amount Amount to divide
     * @param int|float|string $divisor Divisor value
     * @param string $currencyCode Currency code (default: IDR)
     * @return Money
     */
    public static function divide($amount, $divisor, string $currencyCode = self::DEFAULT_CURRENCY): Money
    {
        $money = $amount instanceof Money ? $amount : self::create($amount, $currencyCode);

        return $money->dividedBy($divisor, \Brick\Math\RoundingMode::HALF_UP);
    }

    /**
     * Get the numeric value of Money as string (for database storage)
     *
     * @param Money|int|float|string $amount Amount to convert
     * @param string $currencyCode Currency code (default: IDR)
     * @return string Numeric string (e.g., "1500000.50")
     */
    public static function toNumericString($amount, string $currencyCode = self::DEFAULT_CURRENCY): string
    {
        $money = $amount instanceof Money ? $amount : self::create($amount, $currencyCode);

        return $money->getAmount()->toScale(2)->__toString();
    }

    /**
     * Format Money as Indonesian Rupiah string
     *
     * @param Money|int|float|string $amount Amount to format
     * @param string $currencyCode Currency code (default: IDR)
     * @param bool $negative Whether to show as negative with parentheses
     * @return string Formatted currency (e.g., "Rp 1.500.000,00")
     */
    public static function formatIDR($amount, string $currencyCode = self::DEFAULT_CURRENCY, bool $negative = false): string
    {
        $money = $amount instanceof Money ? $amount : self::create($amount, $currencyCode);

        $numericValue = $money->getAmount()->toScale(2, \Brick\Math\RoundingMode::HALF_UP);

        // Convert to float for number_format
        $floatValue = (float)$numericValue->__toString();

        // Format with Indonesian locale (comma for decimal, period for thousands)
        $formatted = number_format($floatValue, 2, ',', '.');

        if ($negative && $floatValue < 0) {
            return "(Rp " . substr($formatted, 1) . ")";
        }

        return "Rp " . $formatted;
    }

    /**
     * Format Money as HTML with Rupiah formatting
     *
     * @param Money|int|float|string $amount Amount to format
     * @param string $currencyCode Currency code (default: IDR)
     * @param bool $negative Whether to show as negative with parentheses
     * @return string HTML formatted currency
     */
    public static function formatIDRHtml($amount, string $currencyCode = self::DEFAULT_CURRENCY, bool $negative = false): string
    {
        $formatted = self::formatIDR($amount, $currencyCode, $negative);

        return "<div style='height: 100%;text-align: right;'>" . htmlspecialchars($formatted) . "</div>";
    }

    /**
     * Format Money as HTML with Rupiah formatting (for print/cetak)
     *
     * @param Money|int|float|string $amount Amount to format
     * @param string $currencyCode Currency code (default: IDR)
     * @return string HTML formatted currency with rata-kanan class
     */
    public static function formatIDRCetak($amount, string $currencyCode = self::DEFAULT_CURRENCY): string
    {
        $formatted = self::formatIDR($amount, $currencyCode);

        return "<div class='rata-kanan'>" . htmlspecialchars($formatted) . "</div>";
    }

    /**
     * Compare two monetary amounts
     *
     * @param Money|int|float|string $amount1 First amount
     * @param Money|int|float|string $amount2 Second amount
     * @param string $currencyCode Currency code (default: IDR)
     * @return int -1 if amount1 < amount2, 0 if equal, 1 if amount1 > amount2
     */
    public static function compare($amount1, $amount2, string $currencyCode = self::DEFAULT_CURRENCY): int
    {
        $money1 = $amount1 instanceof Money ? $amount1 : self::create($amount1, $currencyCode);
        $money2 = $amount2 instanceof Money ? $amount2 : self::create($amount2, $currencyCode);

        if ($money1->isLessThan($money2)) {
            return -1;
        } elseif ($money1->isGreaterThan($money2)) {
            return 1;
        }

        return 0;
    }

    /**
     * Check if two amounts are equal
     *
     * @param Money|int|float|string $amount1 First amount
     * @param Money|int|float|string $amount2 Second amount
     * @param string $currencyCode Currency code (default: IDR)
     * @return bool
     */
    public static function equals($amount1, $amount2, string $currencyCode = self::DEFAULT_CURRENCY): bool
    {
        $money1 = $amount1 instanceof Money ? $amount1 : self::create($amount1, $currencyCode);
        $money2 = $amount2 instanceof Money ? $amount2 : self::create($amount2, $currencyCode);

        return $money1->isEqualTo($money2);
    }

    /**
     * Get the absolute value (remove negative sign)
     *
     * @param Money|int|float|string $amount Amount to process
     * @param string $currencyCode Currency code (default: IDR)
     * @return Money
     */
    public static function abs($amount, string $currencyCode = self::DEFAULT_CURRENCY): Money
    {
        $money = $amount instanceof Money ? $amount : self::create($amount, $currencyCode);

        return Money::of($money->getAmount()->abs(), Currency::of($currencyCode));
    }

    /**
     * Get zero Money object
     *
     * @param string $currencyCode Currency code (default: IDR)
     * @return Money
     */
    public static function zero(string $currencyCode = self::DEFAULT_CURRENCY): Money
    {
        return Money::zero(Currency::of($currencyCode));
    }

    /**
     * Check if amount is zero
     *
     * @param Money|int|float|string $amount Amount to check
     * @param string $currencyCode Currency code (default: IDR)
     * @return bool
     */
    public static function isZero($amount, string $currencyCode = self::DEFAULT_CURRENCY): bool
    {
        $money = $amount instanceof Money ? $amount : self::create($amount, $currencyCode);

        return $money->isZero();
    }

    /**
     * Check if amount is negative
     *
     * @param Money|int|float|string $amount Amount to check
     * @param string $currencyCode Currency code (default: IDR)
     * @return bool
     */
    public static function isNegative($amount, string $currencyCode = self::DEFAULT_CURRENCY): bool
    {
        $money = $amount instanceof Money ? $amount : self::create($amount, $currencyCode);

        return $money->isNegative();
    }

    /**
     * Check if amount is positive
     *
     * @param Money|int|float|string $amount Amount to check
     * @param string $currencyCode Currency code (default: IDR)
     * @return bool
     */
    public static function isPositive($amount, string $currencyCode = self::DEFAULT_CURRENCY): bool
    {
        $money = $amount instanceof Money ? $amount : self::create($amount, $currencyCode);

        return $money->isPositive();
    }
}
