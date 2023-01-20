<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\UserBilling;

use Lib\Exceptions\InvalidArgumentException;
use Spatie\Snapshots\MatchesSnapshots;
use SplFileInfo;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Test;
use UseCase\UserBilling\ParseZenginFormatInteractor;

/**
 * {@link \UseCase\UserBilling\ParseZenginFormatInteractor} のテスト.
 */
final class ParseZenginFormatInteractorTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use UnitSupport;

    private ParseZenginFormatInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ParseZenginFormatInteractorTest $self): void {
            $self->interactor = app(ParseZenginFormatInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('Case1 success', function (): void {
            $file = new SplFileInfo(codecept_data_dir('UserBilling/zengin_case1.txt'));
            $actual = $this->interactor->handle($this->context, $file);
            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should('Case2 throw InvalidArgumentException when clientNumber is not numeric', function (): void {
            $this->assertThrows(InvalidArgumentException::class, function (): void {
                $file = new SplFileInfo(codecept_data_dir('UserBilling/zengin_case2.txt'));
                $this->interactor->handle($this->context, $file);
            });
        });
        $this->should('Case3 throw InvalidArgumentException when deductedOn is date format.', function (): void {
            $this->assertThrows(InvalidArgumentException::class, function (): void {
                $file = new SplFileInfo(codecept_data_dir('UserBilling/zengin_case3.txt'));
                $this->interactor->handle($this->context, $file);
            });
        });
        $this->should(
            'Case4 throw InvalidArgumentException when file length is not a multiple of 120.',
            function (): void {
                $this->assertThrows(InvalidArgumentException::class, function (): void {
                    $file = new SplFileInfo(codecept_data_dir('UserBilling/zengin_case4.txt'));
                    $this->interactor->handle($this->context, $file);
                });
            }
        );
        $this->should('Case5 throw InvalidArgumentException when dataType is an unknown value.', function (): void {
            $this->assertThrows(InvalidArgumentException::class, function (): void {
                $file = new SplFileInfo(codecept_data_dir('UserBilling/zengin_case5.txt'));
                $this->interactor->handle($this->context, $file);
            });
        });
        $this->should('Case6 success', function (): void {
            $file = new SplFileInfo(codecept_data_dir('UserBilling/zengin_case6.txt'));
            $actual = $this->interactor->handle($this->context, $file);
            $this->assertMatchesModelSnapshot($actual);
        });
    }
}
