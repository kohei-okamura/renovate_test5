<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingRepositoryMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\LookupDwsBillingInteractor;

/**
 * {@link \UseCase\Billing\LookupDwsBillingInteractor} Test.
 */
class LookupDwsBillingInteractorTest extends Test
{
    use ContextMixin;
    use DwsBillingRepositoryMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use MockeryMixin;
    use UnitSupport;

    private DwsBilling $dwsBilling;
    private LookupDwsBillingInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupDwsBillingInteractorTest $self): void {
            $self->context
                ->allows('organization')
                ->andReturn($self->examples->organizations[0]);
            $self->context
                ->allows('isAccessibleTo')
                ->andReturn(true)
                ->byDefault();
            $self->dwsBillingRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->dwsBillings[0]))
                ->byDefault();

            $self->dwsBilling = $self->examples->dwsBillings[0];
            $self->interactor = app(LookupDwsBillingInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a Seq of DwsBilling', function (): void {
            $actual = $this->interactor
                ->handle(
                    $this->context,
                    Permission::viewBillings(),
                    $this->dwsBilling->id
                );

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $this->dwsBilling,
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
                    $this->dwsBilling->id
                );
            $this->assertCount(0, $actual);
        });
    }
}
