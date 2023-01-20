<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LtcsBillingRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\LookupLtcsBillingInteractor;

/**
 * {@link \UseCase\Billing\LookupLtcsBillingInteractor} のテスト.
 */
final class LookupLtcsBillingInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use LtcsBillingRepositoryMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private LtcsBilling $billing;
    private LookupLtcsBillingInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->billing = $self->examples->ltcsBillings[0];

            $self->context
                ->allows('organization')
                ->andReturn($self->examples->organizations[0]);
            $self->context
                ->allows('isAccessibleTo')
                ->andReturn(true)
                ->byDefault();
            $self->ltcsBillingRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->billing));

            $self->interactor = app(LookupLtcsBillingInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a Seq of LtcsBilling', function (): void {
            $actual = $this->interactor
                ->handle(
                    $this->context,
                    Permission::viewBillings(),
                    $this->billing->id
                );

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $this->billing,
                $actual->head()
            );
        });
        $this->should('return empty seq when accessibleTo of Context return false', function (): void {
            $this->context
                ->expects('isAccessibleTo')
                ->andReturn(false);

            $actual = $this->interactor
                ->handle(
                    $this->context,
                    Permission::viewBillings(),
                    $this->billing->id
                );
            $this->assertCount(0, $actual);
        });
    }
}
