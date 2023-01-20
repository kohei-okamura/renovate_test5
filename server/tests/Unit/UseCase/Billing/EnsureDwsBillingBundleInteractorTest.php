<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupDwsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\EnsureDwsBillingBundleInteractor;

/**
 * {@link \UseCase\Billing\EnsureDwsBillingBundleInteractor} Test.
 */
class EnsureDwsBillingBundleInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use LookupDwsBillingBundleUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private EnsureDwsBillingBundleInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (EnsureDwsBillingBundleInteractorTest $self): void {
            $self->lookupDwsBillingBundleUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillingBundles[0]))
                ->byDefault();

            $self->interactor = app(EnsureDwsBillingBundleInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use LookupDwsBillingBundleUseCase', function (): void {
            $this->lookupDwsBillingBundleUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewBillings(), $this->examples->dwsBillings[0]->id, $this->examples->dwsBillingBundles[0]->id)
                ->andReturn(Seq::from($this->examples->dwsBillingBundles[0]));

            $this->interactor
                ->handle($this->context, Permission::viewBillings(), $this->examples->dwsBillings[0]->id, $this->examples->dwsBillingBundles[0]->id);
        });
        $this->should(
            'throw NotFoundException when handle on LookupDwsBillingBundleUseCase return empty seq',
            function (): void {
                $this->lookupDwsBillingBundleUseCase
                    ->expects('handle')
                    ->with($this->context, Permission::viewBillings(), $this->examples->dwsBillings[0]->id, $this->examples->dwsBillingBundles[0]->id)
                    ->andReturn(Seq::emptySeq());

                $this->assertThrows(NotFoundException::class, function (): void {
                    $this->interactor
                        ->handle($this->context, Permission::viewBillings(), $this->examples->dwsBillings[0]->id, $this->examples->dwsBillingBundles[0]->id);
                });
            }
        );
    }
}
