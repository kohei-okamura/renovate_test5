<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FileStorageMixin;
use Tests\Unit\Mixins\LookupLtcsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\GetLtcsBillingFileInfoInteractor;

/**
 * {@link \UseCase\Billing\GetLtcsBillingFileInfoInteractor} のテスト.
 */
final class GetLtcsBillingFileInfoInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use LookupLtcsBillingUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use FileStorageMixin;
    use UnitSupport;

    public const TEMPORARY_URL = 'temporary-url';

    private LtcsBilling $billing;
    private GetLtcsBillingFileInfoInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->billing = $self->examples->ltcsBillings[0];

            $self->lookupLtcsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->billing))
                ->byDefault();
            $self->fileStorage
                ->allows('getTemporaryUrl')
                ->andReturn(self::TEMPORARY_URL)
                ->byDefault();

            $self->interactor = app(GetLtcsBillingFileInfoInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return url string via FileStorage', function (): void {
            $this->assertSame(
                self::TEMPORARY_URL,
                $this->interactor->handle($this->context, $this->billing->id, $this->billing->files[0]->token)
            );
        });
        $this->should('use LookupLtcsBillingUseCase', function (): void {
            $this->lookupLtcsBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewBillings(), $this->billing->id)
                ->andReturn(Seq::from($this->billing));
            $this->interactor->handle($this->context, $this->billing->id, $this->billing->files[0]->token);
        });
        $this->should('throw NotFoundException when LookupLtcsBillingUseCase return empty', function (): void {
            $this->lookupLtcsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, $this->billing->id, $this->billing->files[0]->token);
                }
            );
        });
        $this->should('throw NotFoundException when token unmatched any elements', function (): void {
            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, $this->billing->id, '');
                }
            );
        });
    }
}
