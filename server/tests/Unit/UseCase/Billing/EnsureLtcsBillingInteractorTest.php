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
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupLtcsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\EnsureLtcsBillingInteractor;

/**
 * {@link \UseCase\Billing\EnsureLtcsBillingInteractor} のテスト.
 */
final class EnsureLtcsBillingInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use LookupLtcsBillingUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private EnsureLtcsBillingInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->lookupLtcsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ltcsBillings[0]))
                ->byDefault();

            $self->interactor = app(EnsureLtcsBillingInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use LookupDwsBillingUseCase', function (): void {
            $this->lookupLtcsBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewBillings(), $this->examples->ltcsBillings[0]->id)
                ->andReturn(Seq::from($this->examples->ltcsBillings[0]));

            $this->interactor
                ->handle($this->context, Permission::viewBillings(), $this->examples->ltcsBillings[0]->id);
        });
        $this->should(
            'throw NotFoundException when handle on LookupDwsBillingUseCase return empty seq',
            function (): void {
                $this->lookupLtcsBillingUseCase
                    ->expects('handle')
                    ->with($this->context, Permission::viewBillings(), $this->examples->ltcsBillings[0]->id)
                    ->andReturn(Seq::emptySeq());

                $this->assertThrows(
                    NotFoundException::class,
                    function (): void {
                        $this->interactor->handle(
                            $this->context,
                            Permission::viewBillings(),
                            $this->examples->ltcsBillings[0]->id
                        );
                    }
                );
            }
        );
    }
}
