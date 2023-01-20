<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Office\LtcsBaseIncreaseSupportAddition;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\ComputeLtcsBillingBaseIncreaseSupportAdditionInteractor;

/**
 * {@link \UseCase\Billing\ComputeLtcsBillingBaseIncreaseSupportAdditionInteractor} のテスト.
 */
final class ComputeLtcsBillingBaseIncreaseSupportAdditionInteractorTest extends Test
{
    use CarbonMixin;
    use ComputeLtcsBillingAdditionTestSupport;
    use DummyContextMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private Seq $entries;
    private ComputeLtcsBillingBaseIncreaseSupportAdditionInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->entries = self::dictionaryEntries();
        });
        self::beforeEachSpec(function (self $self): void {
            $self->interactor = app(ComputeLtcsBillingBaseIncreaseSupportAdditionInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should(
            'return a Some of a LtcsBillingServiceDetail containing baseIncreaseSupportAddition when the report contains addition1',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    self::provisionReport(['baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::addition1()]),
                    $this->entries,
                    2000,
                    0,
                    0,
                );
                $this->assertMatchesModelSnapshot($actual);
            }
        );
        $this->should('return a None when the report contains only none', function (): void {
            $actual = $this->interactor->handle(
                $this->context,
                self::provisionReport(['baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::none()]),
                $this->entries,
                3000,
                0,
                0,
            );
            $this->assertSame(Option::none(), $actual);
        });
        $this->should('return a None when the report does not contain baseIncreaseSupportAddition', function (): void {
            $actual = $this->interactor->handle(
                $this->context,
                self::provisionReport(),
                $this->entries,
                4000,
                0,
                0,
            );
            $this->assertSame(Option::none(), $actual);
        });
    }
}
