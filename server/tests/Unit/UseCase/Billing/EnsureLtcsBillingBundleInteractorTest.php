<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
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
use Tests\Unit\Mixins\LookupLtcsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\EnsureLtcsBillingBundleInteractor;

/**
 * {@link \UseCase\Billing\EnsureLtcsBillingBundleInteractor} のテスト.
 */
final class EnsureLtcsBillingBundleInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use LookupLtcsBillingBundleUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private EnsureLtcsBillingBundleInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->lookupLtcsBillingBundleUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ltcsBillingBundles[0]))
                ->byDefault();

            $self->interactor = app(EnsureLtcsBillingBundleInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use LookupLtcsBillingBundleUseCase', function (): void {
            $this->lookupLtcsBillingBundleUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewBillings(), $this->examples->ltcsBillings[0]->id, $this->examples->ltcsBillingBundles[0]->id)
                ->andReturn(Seq::from($this->examples->ltcsBillingBundles[0]));

            $this->interactor
                ->handle($this->context, Permission::viewBillings(), $this->examples->ltcsBillings[0]->id, $this->examples->ltcsBillingBundles[0]->id);
        });
        $this->should(
            'throw NotFoundException when handle on LookupLtcsBillingBundleUseCase return empty seq',
            function (): void {
                $this->lookupLtcsBillingBundleUseCase
                    ->expects('handle')
                    ->with($this->context, Permission::viewBillings(), $this->examples->ltcsBillings[0]->id, $this->examples->ltcsBillingBundles[0]->id)
                    ->andReturn(Seq::emptySeq());

                $this->assertThrows(NotFoundException::class, function (): void {
                    $this->interactor
                        ->handle($this->context, Permission::viewBillings(), $this->examples->ltcsBillings[0]->id, $this->examples->ltcsBillingBundles[0]->id);
                });
            }
        );
    }
}
