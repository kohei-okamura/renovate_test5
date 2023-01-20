<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Common;

use Domain\Common\Password;
use Lib\Exceptions\RuntimeException;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Common\Password} のテスト.
 */
final class PasswordTest extends Test
{
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    protected Password $password;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->password = Password::fromString('password');
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_fromHashString(): void
    {
        $this->should('build object', function (): void {
            $actual = Password::fromHashString($this->password->hashString());
            $this->assertTrue($actual->equals($this->password));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_value(): void
    {
        $this->should('return value', function (): void {
            $value = 'eustylelab';
            $actual = Password::fromString($value);
            $this->assertSame($value, $actual->value());
        });
        $this->should('throw Exception when no value', function (): void {
            $actual = Password::fromHashString($this->password->hashString());
            $this->assertThrows(
                RuntimeException::class,
                function () use ($actual): void {
                    $actual->value();
                }
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_equals(): void
    {
        $this->should('return false when not same as hash', function (): void {
            $actual = Password::fromHashString('Same');
            $this->assertFalse($this->password->equals($actual));
        });
        $this->should('return false when not same as rawValue', function (): void {
            $actual = Password::fromString('Same');
            $this->assertFalse($this->password->equals($actual));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_check(): void
    {
        $this->should('return true when password matched', function (): void {
            $this->assertTrue($this->password->check('password'));
        });
        $this->should('return false when password unmatched', function (): void {
            $this->assertFalse($this->password->check('Same'));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot(json_encode($this->password));
        });
    }
}
