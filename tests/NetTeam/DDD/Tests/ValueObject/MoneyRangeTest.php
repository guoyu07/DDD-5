<?php

namespace NetTeam\DDD\Tests\ValueObject;

use NetTeam\DDD\ValueObject\Money;
use NetTeam\DDD\ValueObject\MoneyRange;
use NetTeam\DDD\ValueObject\Range;

/**
 * @author Paweł A. Wacławczyk <p.a.waclawczyk@gmail.com>
 *
 * @group Unit
 */
class MoneyRangeTest extends \PHPUnit_Framework_TestCase
{
    public function testStaticFactory()
    {
        $range = MoneyRange::USD(123.45, 678.90);

        $this->assertEquals(Money::USD(123.45), $range->min());
        $this->assertEquals(Money::USD(678.90), $range->max());
        $this->assertEquals('USD', $range->currency());
    }

    public function testStaticFactoryWhenDefaultArguments()
    {
        $range = MoneyRange::USD();

        $this->assertNull($range->min());
        $this->assertNull($range->max());
    }

    public function testStaticFactoryWhenNullArguments()
    {
        $range = MoneyRange::USD(null, null);

        $this->assertNull($range->min());
        $this->assertNull($range->max());
    }

    public function testCurrency()
    {
        $range = new MoneyRange(new Money(100, 'PLN'), new Money(1000, 'PLN'));
        $this->assertEquals('PLN', $range->currency());
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage Min and max objects must have same currencies, min is PLN and max is USD
     */
    public function testCurrencyWhenDifferentCurrencies()
    {
        $range = new MoneyRange(new Money(100, 'PLN'), new Money(100, 'USD'));
        $range->currency();
    }

    /**
     * @expectedException \DomainException
     */
    public function testCurrencyWhenMinIsNotMoney()
    {
        $range = new MoneyRange(new \stdClass(), new Money(100, 'USD'));
        $range->currency();
    }

    /**
     * @expectedException \DomainException
     */
    public function testCurrencyWhenMaxIsNotMoney()
    {
        $range = new MoneyRange(new Money(100, 'PLN'), new \stdClass());
        $range->currency();
    }

    public function testCurrencyWhenMinIsNull()
    {
        $range = new MoneyRange(null, Money::USD(123.45));

        $this->assertEquals('USD', $range->currency());
    }

    public function testCurrencyWhenMaxIsNull()
    {
        $range = new MoneyRange(Money::USD(123.45), null);

        $this->assertEquals('USD', $range->currency());
    }

    public function testCurrencyWhenBothAreNull()
    {
        $range = new MoneyRange(null, null);

        $this->assertNull($range->currency());
    }

    public function testContains()
    {
        $min = new Money(1000, 'PLN');
        $max = new Money(10000, 'PLN');
        $range = new MoneyRange($min, $max);

        $inRange = new Money(5000, 'PLN');
        $outOfRange = new Money(500000, 'PLN');

        $this->assertTrue($range->contains($inRange));
        $this->assertFalse($range->contains($outOfRange));
    }

    public function testLeftOpenedRangeDoesNotContainInLeftClosedRange()
    {
        $leftClosedRange = new MoneyRange(new Money(100, 'PLN'), null);
        $leftOpenedRange = new MoneyRange(null, null);

        $this->assertFalse($leftClosedRange->containsRange($leftOpenedRange));
    }

    public function testIfRightOpenedRangeDoesNotContainInRightCloseRange()
    {
        $rightClosedRange = new MoneyRange(null, new Money(100, 'PLN'));
        $rightOpenedRange = new MoneyRange(null, null);

        $this->assertFalse($rightClosedRange->containsRange($rightOpenedRange));
    }

    public function testIfClosedRangeContainsInClosedRange()
    {
        $range = new MoneyRange(new Money(100, 'PLN'), new Money(1000, 'PLN'));

        $leftExceedRange = new MoneyRange(new Money(10, 'PLN'), new Money(1000, 'PLN'));
        $rightExceedRange = new MoneyRange(new Money(100, 'PLN'), new Money(10000, 'PLN'));
        $exceedRange = new MoneyRange(new Money(10, 'PLN'), new Money(10000, 'PLN'));
        $innerRange = new MoneyRange(new Money(200, 'PLN'), new Money(900, 'PLN'));
        $sameRange = new MoneyRange(new Money(100, 'PLN'), new Money(1000, 'PLN'));

        $this->assertFalse($range->containsRange($leftExceedRange));
        $this->assertFalse($range->containsRange($rightExceedRange));
        $this->assertFalse($range->containsRange($exceedRange));
        $this->assertTrue($range->containsRange($innerRange));
        $this->assertTrue($range->containsRange($sameRange));
    }

    /**
     * @expectedException \DomainException
     */
    public function testContainsRangeWhenMinIsNotMoney()
    {
        $range = new MoneyRange(new \stdClass(), new Money(100, 'PLN'));
        $range->containsRange(MoneyRange::PLN());
    }

    /**
     * @expectedException \DomainException
     */
    public function testContainsRangeWhenMaxIsNotMoney()
    {
        $range = new MoneyRange(new Money(100, 'PLN'), new \stdClass());
        $range->containsRange(MoneyRange::PLN());
    }

    /**
     * @expectedException \DomainException
     * @expectedExceptionMessage Min and max objects must have same currencies, min is PLN and max is USD
     */
    public function testContainsRangeWhenDifferentCurrencies()
    {
        $range = new MoneyRange(new Money(100, 'PLN'), new Money(100, 'USD'));
        $range->containsRange(MoneyRange::PLN());
    }

    /**
     * @expectedException \DomainException
     */
    public function testContainsRangeWhenGivenIsNotMoneyRange()
    {
        $range = new MoneyRange(new Money(100, 'PLN'), new Money(100, 'PLN'));
        $range->containsRange(new Range());
    }

    /**
     * @expectedException \DomainException
     */
    public function testContainsRangeWhenGivenHasDifferentCurrency()
    {
        $range = new MoneyRange(new Money(100, 'PLN'), new Money(100, 'PLN'));
        $given = new MoneyRange(new Money(100, 'USD'), new Money(100, 'USD'));
        $range->containsRange($given);
    }
}
