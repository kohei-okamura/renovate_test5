<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBillingBundle;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingBundleRepositoryMixin;
use Tests\Unit\Mixins\EnsureDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\LookupDwsBillingBundleInteractor;

/**
 * {@link \UseCase\Billing\LookupDwsBillingBundleInteractor} Test.
 */
class LookupDwsBillingBundleInteractorTest extends Test
{
    use ContextMixin;
    use DwsBillingBundleRepositoryMixin;
    use EnsureDwsBillingUseCaseMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use MockeryMixin;
    use UnitSupport;

    private DwsBillingBundle $dwsBillingBundle;
    private LookupDwsBillingBundleInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupDwsBillingBundleInteractorTest $self): void {
            $self->dwsBillingBundleRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->dwsBillingBundles[0]))
                ->byDefault();
            $self->ensureDwsBillingUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();

            $self->dwsBillingBundle = $self->examples->dwsBillingBundles[0];
            $self->interactor = app(LookupDwsBillingBundleInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a Seq of DwsBillingBundle', function (): void {
            $actual = $this->interactor
                ->handle(
                    $this->context,
                    Permission::viewBillings(),
                    $this->dwsBillingBundle->dwsBillingId,
                    $this->dwsBillingBundle->id
                );

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $this->dwsBillingBundle,
                $actual->head()
            );
        });
        $this->should('use EnsureDwsBillingUseCase', function (): void {
            $this->ensureDwsBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewBillings(), $this->dwsBillingBundle->dwsBillingId)
                ->andReturnNull();

            $this->interactor->handle(
                $this->context,
                Permission::viewBillings(),
                $this->dwsBillingBundle->dwsBillingId,
                $this->dwsBillingBundle->id
            );
        });
    }
}
