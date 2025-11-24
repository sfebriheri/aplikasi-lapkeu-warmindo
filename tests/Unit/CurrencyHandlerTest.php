<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Libraries\CurrencyHandler;
use Brick\Money\Money;

class CurrencyHandlerTest extends TestCase
{
	/**
	 * Test creating Money object from various numeric formats
	 */
	public function testCreateFromVariousFormats()
	{
		// From integer
		$money = CurrencyHandler::create(1000);
		$this->assertEquals('1000.00', CurrencyHandler::toNumericString($money));

		// From float
		$money = CurrencyHandler::create(1000.50);
		$this->assertEquals('1000.50', CurrencyHandler::toNumericString($money));

		// From string
		$money = CurrencyHandler::create('1000.50');
		$this->assertEquals('1000.50', CurrencyHandler::toNumericString($money));

		// From formatted string (IDR format)
		$money = CurrencyHandler::createFromFormatted('1.500.000,50');
		$this->assertEquals('1500000.50', CurrencyHandler::toNumericString($money));
	}

	/**
	 * Test addition with decimal precision
	 */
	public function testAddition()
	{
		$result = CurrencyHandler::add('0.1', '0.2');
		// Float would give 0.30000000000000004, decimal should give exactly 0.30
		$this->assertEquals('0.30', CurrencyHandler::toNumericString($result));

		$result = CurrencyHandler::add('1000.50', '500.25');
		$this->assertEquals('1500.75', CurrencyHandler::toNumericString($result));

		$result = CurrencyHandler::add(1000, 500);
		$this->assertEquals('1500.00', CurrencyHandler::toNumericString($result));
	}

	/**
	 * Test subtraction with decimal precision
	 */
	public function testSubtraction()
	{
		$result = CurrencyHandler::subtract('1.00', '0.30');
		// Float would give 0.6999999999999999, decimal should give exactly 0.70
		$this->assertEquals('0.70', CurrencyHandler::toNumericString($result));

		$result = CurrencyHandler::subtract('1500.75', '500.25');
		$this->assertEquals('1000.50', CurrencyHandler::toNumericString($result));

		// Test with Money objects
		$money1 = CurrencyHandler::create(2000);
		$money2 = CurrencyHandler::create(500);
		$result = CurrencyHandler::subtract($money1, $money2);
		$this->assertEquals('1500.00', CurrencyHandler::toNumericString($result));
	}

	/**
	 * Test multiplication
	 */
	public function testMultiplication()
	{
		$result = CurrencyHandler::multiply('100.50', 2);
		$this->assertEquals('201.00', CurrencyHandler::toNumericString($result));

		$result = CurrencyHandler::multiply('10.50', '2.5');
		$this->assertEquals('26.25', CurrencyHandler::toNumericString($result));
	}

	/**
	 * Test division
	 */
	public function testDivision()
	{
		$result = CurrencyHandler::divide('100.00', 4);
		$this->assertEquals('25.00', CurrencyHandler::toNumericString($result));

		$result = CurrencyHandler::divide('10.00', 3);
		// Should round properly
		$numeric = CurrencyHandler::toNumericString($result);
		$this->assertTrue(in_array($numeric, ['3.33', '3.34'])); // Depending on rounding mode
	}

	/**
	 * Test formatting as Indonesian Rupiah
	 */
	public function testFormatIDR()
	{
		$formatted = CurrencyHandler::formatIDR(1500000.50);
		$this->assertStringContainsString('1.500.000,50', $formatted);
		$this->assertStringContainsString('Rp', $formatted);

		$formatted = CurrencyHandler::formatIDR('1500000.50');
		$this->assertStringContainsString('1.500.000,50', $formatted);
	}

	/**
	 * Test comparison operations
	 */
	public function testComparison()
	{
		// Less than
		$this->assertEquals(-1, CurrencyHandler::compare(100, 200));

		// Greater than
		$this->assertEquals(1, CurrencyHandler::compare(200, 100));

		// Equal
		$this->assertEquals(0, CurrencyHandler::compare(100, 100));

		// Test equals method
		$this->assertTrue(CurrencyHandler::equals('100.50', '100.50'));
		$this->assertFalse(CurrencyHandler::equals('100.50', '100.51'));
	}

	/**
	 * Test zero and negative checks
	 */
	public function testZeroAndNegative()
	{
		$this->assertTrue(CurrencyHandler::isZero(0));
		$this->assertTrue(CurrencyHandler::isZero('0.00'));
		$this->assertFalse(CurrencyHandler::isZero(0.01));

		$this->assertTrue(CurrencyHandler::isNegative(-100));
		$this->assertFalse(CurrencyHandler::isNegative(100));
		$this->assertFalse(CurrencyHandler::isNegative(0));

		$this->assertTrue(CurrencyHandler::isPositive(100));
		$this->assertFalse(CurrencyHandler::isPositive(-100));
		$this->assertFalse(CurrencyHandler::isPositive(0));
	}

	/**
	 * Test absolute value
	 */
	public function testAbsoluteValue()
	{
		$result = CurrencyHandler::abs(-100.50);
		$this->assertEquals('100.50', CurrencyHandler::toNumericString($result));

		$result = CurrencyHandler::abs(100.50);
		$this->assertEquals('100.50', CurrencyHandler::toNumericString($result));
	}

	/**
	 * Test money object creation
	 */
	public function testZeroMoney()
	{
		$zero = CurrencyHandler::zero();
		$this->assertTrue(CurrencyHandler::isZero($zero));
		$this->assertEquals('0.00', CurrencyHandler::toNumericString($zero));
	}

	/**
	 * Test precision preservation in accounting calculation
	 * (common issue: 0.1 + 0.2 = 0.30000000000000004 in float)
	 */
	public function testAccountingPrecision()
	{
		// Simulate common accounting problem: multiple small decimal amounts
		$amounts = ['100.10', '200.20', '300.30', '400.40'];

		$total = CurrencyHandler::create(0);
		foreach ($amounts as $amount) {
			$total = CurrencyHandler::add($total, $amount);
		}

		// Should be exactly 1001.00
		$this->assertEquals('1001.00', CurrencyHandler::toNumericString($total));

		// Test journal entry calculation
		$debit = CurrencyHandler::create('1500.75');
		$credit = CurrencyHandler::create('1000.50');
		$balance = CurrencyHandler::subtract($debit, $credit);

		$this->assertEquals('500.25', CurrencyHandler::toNumericString($balance));
	}
}
