<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingStatus;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EditLtcsBillingUseCaseMixin;
use Tests\Unit\Mixins\LtcsBillingBundleRepositoryMixin;
use Tests\Unit\Mixins\LtcsBillingStatementRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\ConfirmLtcsBillingStatementStatusInteractor;

/**
 * {@link \UseCase\Billing\ConfirmLtcsBillingStatementStatusInteractor} のテスト.
 */
final class ConfirmLtcsBillingStatementStatusInteractorTest extends Test
{
    use ContextMixin;
    use EditLtcsBillingUseCaseMixin;
    use ExamplesConsumer;
    use LtcsBillingBundleRepositoryMixin;
    use LtcsBillingStatementRepositoryMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private LtcsBilling $billing;
    private LtcsBillingBundle $bundle;
    private ConfirmLtcsBillingStatementStatusInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->billing = $self->examples->ltcsBillings[0];
            $self->bundle = $self->examples->ltcsBillingBundles[0];
        });
        self::beforeEachSpec(function (self $self): void {
            $self->ltcsBillingBundleRepository
                ->allows('lookupByBillingId')
                ->andReturn(Map::from([$self->billing->id => Seq::from($self->bundle)]))
                ->byDefault();

            $self->ltcsBillingStatementRepository
                ->allows('lookupByBundleId')
                ->andReturn(Map::from([$self->bundle->id => Seq::fromArray($self->examples->ltcsBillingStatements)]))
                ->byDefault();

            $self->interactor = app(ConfirmLtcsBillingStatementStatusInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('not call edit usecase', function (): void {
            $this->editLtcsBillingUseCase
                ->expects('handle')
                ->times(0);

            $this->interactor->handle($this->context, $this->billing);
        });
        $this->should('call edit usecase', function (): void {
            $statement = $this->examples->ltcsBillingStatements[2]->copy(['status' => LtcsBillingStatus::fixed()]);
            $this->ltcsBillingStatementRepository
                ->expects('lookupByBundleId')
                ->with($this->bundle->id)
                ->andReturn(Map::from([$this->bundle->id => Seq::from($statement)]));
            $this->editLtcsBillingUseCase
                ->expects('handle')
                ->with($this->context, $this->billing->id, equalTo(['status' => LtcsBillingStatus::ready()]))
                ->andReturn($this->billing);

            $this->interactor->handle($this->context, $this->billing);
        });
    }
}
