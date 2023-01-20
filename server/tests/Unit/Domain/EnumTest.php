<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain;

use Codeception\Test\Unit;
use InvalidArgumentException;
use Tests\Unit\Helpers\UnitSupport;

/**
 * Enum のテスト.
 */
class EnumTest extends Unit
{
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_length(): void
    {
        $this->should('return the length', function (): void {
            $this->assertSame(2, EnumFixture::length());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_isValid(): void
    {
        $this->should('return true when valid value given', function (): void {
            $this->assertTrue(EnumFixture::isValid('stringValue'));
            $this->assertTrue(EnumFixture::isValid(11));
        });
        $this->should('return false when invalid value given', function (): void {
            $this->assertFalse(EnumFixture::isValid('wrongValue'));
            $this->assertFalse(EnumFixture::isValid('11'));
            $this->assertFalse(EnumFixture::isValid(12));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $this->should('return an instance when valid value given', function (): void {
            $this->assertSame(EnumFixture::keyOfString(), EnumFixture::from('stringValue'));
            $this->assertSame(EnumFixture::keyOfInt(), EnumFixture::from(11));
        });
        $this->should('return same instance when method called more than once', function (): void {
            $this->assertSame(
                EnumFixture::from('stringValue'),
                EnumFixture::from('stringValue')
            );
        });
        $this->should('throw an InvalidArgumentException when invalid value given', function (): void {
            $this->assertThrows(
                InvalidArgumentException::class,
                function (): void {
                    EnumFixture::from('invalidValue');
                }
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_all(): void
    {
        $this->should('return an array of enum', function (): void {
            $this->assertSame(
                [EnumFixture::from('stringValue'), EnumFixture::from(11)],
                EnumFixture::all()
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_value(): void
    {
        $this->should('return the value', function (): void {
            $this->assertSame('stringValue', EnumFixture::keyOfString()->value());
            $this->assertSame(11, EnumFixture::keyOfInt()->value());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_key(): void
    {
        $this->should('return the key', function (): void {
            $this->assertSame('keyOfString', EnumFixture::keyOfString()->key());
            $this->assertSame('keyOfInt', EnumFixture::keyOfInt()->key());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_callStatic(): void
    {
        $this->should('return an instance when method name exists in static values', function (): void {
            $this->assertSame(EnumFixture::from('stringValue'), EnumFixture::keyOfString());
            $this->assertSame(EnumFixture::from(11), EnumFixture::keyOfInt());
        });
        $this->should('return same instance when method called more than once', function (): void {
            $this->assertSame(
                EnumFixture::keyOfString(),
                EnumFixture::keyOfString()
            );
        });
        $this->should('throw an InvalidArgumentException when method name not exists in static values', function (): void {
            $this->assertThrows(
                InvalidArgumentException::class,
                function (): void {
                    /** @noinspection PhpUndefinedMethodInspection */
                    EnumFixture::notExists();
                }
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_toString(): void
    {
        $this->should('return the string value', function (): void {
            $this->assertSame('stringValue', (string)EnumFixture::keyOfString());
            $this->assertSame('11', (string)EnumFixture::keyOfInt());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_jsonSerialize(): void
    {
        $this->should('return the value', function (): void {
            $this->assertSame('stringValue', EnumFixture::keyOfString()->jsonSerialize());
            $this->assertSame(11, EnumFixture::keyOfInt()->jsonSerialize());
        });
    }
}
