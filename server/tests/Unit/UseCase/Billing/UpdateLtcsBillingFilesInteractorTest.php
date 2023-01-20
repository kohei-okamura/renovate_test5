<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingFile;
use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\Examples;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateLtcsBillingInvoiceCsvUseCaseMixin;
use Tests\Unit\Mixins\CreateLtcsBillingInvoicePdfUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupLtcsBillingUseCaseMixin;
use Tests\Unit\Mixins\LtcsBillingBundleRepositoryMixin;
use Tests\Unit\Mixins\LtcsBillingRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\UpdateLtcsBillingFilesInteractor;

/**
 * {@link \Usecase\Billing\UpdateLtcsBillingFilesInteractor} のテスト.
 */
final class UpdateLtcsBillingFilesInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use CreateLtcsBillingInvoiceCsvUseCaseMixin;
    use CreateLtcsBillingInvoicePdfUseCaseMixin;
    use LtcsBillingBundleRepositoryMixin;
    use LtcsBillingRepositoryMixin;
    use ExamplesConsumer;
    use LookupLtcsBillingUseCaseMixin;
    use LoggerMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private LtcsBilling $ltcsBilling;
    /** @var \Domain\Billing\LtcsBillingBundle[]|\ScalikePHP\Seq */
    private Seq $ltcsBillingBundles;
    private LtcsBillingFile $statementInvoiceFile;

    private UpdateLtcsBillingFilesInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->ltcsBilling = $self->examples->ltcsBillings[0];
            $self->ltcsBillingBundles = $self->createLtcsBillingBundles($self->examples);
            $self->statementInvoiceFile = $self->examples->ltcsBillings[1]->files[0];
        });
        self::beforeEachSpec(function (self $self): void {
            $self->lookupLtcsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->ltcsBilling))
                ->byDefault();

            $self->ltcsBillingBundleRepository
                ->allows('lookupByBillingId')
                ->andReturn(Map::from([$self->ltcsBilling->id => $self->ltcsBillingBundles]))
                ->byDefault();

            $self->createLtcsBillingInvoiceCsvUseCase
                ->allows('handle')
                ->andReturn($self->statementInvoiceFile)
                ->byDefault();

            $self->createLtcsBillingInvoicePdfUseCase
                ->allows('handle')
                ->andReturn($self->statementInvoiceFile)
                ->byDefault();

            $self->ltcsBillingRepository
                ->allows('store')
                ->andReturn($self->ltcsBilling)
                ->byDefault();

            $self->logger
                ->allows('info')
                ->andReturnNull()
                ->byDefault();

            $self->interactor = app(UpdateLtcsBillingFilesInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('run normally return entity', function (): void {
            $this->assertModelStrictEquals(
                $this->ltcsBilling,
                $this->interactor->handle($this->context, $this->ltcsBilling->id)
            );
        });
        $this->should('use LookupLtcsBillingUseCase', function (): void {
            $this->lookupLtcsBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateBillings(), $this->ltcsBilling->id)
                ->andReturn(Seq::from($this->ltcsBilling));

            $this->interactor->handle($this->context, $this->ltcsBilling->id);
        });
        $this->should('throw Exception LookupLtcsBilling returns empty Seq', function (): void {
            $this->lookupLtcsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::empty());
            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle($this->context, $this->ltcsBilling->id);
            });
        });
        $this->should('find LtcsBillingBundle by billing id', function (): void {
            $billingId = $this->ltcsBilling->id;
            $this->ltcsBillingBundleRepository
                ->expects('lookupByBillingId')
                ->with($billingId)
                ->andReturn(Map::from([$billingId => $this->ltcsBillingBundles]));

            $this->interactor->handle($this->context, $billingId);
        });
        $this->should('use CreateLtcsBillingInvoiceCsvUseCase', function (): void {
            foreach ($this->ltcsBillingBundles as $bundle) {
                $this->createLtcsBillingInvoiceCsvUseCase
                    ->expects('handle')
                    ->with($this->context, $this->ltcsBilling, $bundle)
                    ->andReturn($this->statementInvoiceFile);
            }

            $this->interactor->handle($this->context, $this->ltcsBilling->id);
        });
        $this->should('use CreateLtcsBillingInvoicePdfUseCase', function (): void {
            foreach ($this->ltcsBillingBundles as $bundle) {
                $this->createLtcsBillingInvoicePdfUseCase
                    ->expects('handle')
                    ->with($this->context, $this->ltcsBilling, $bundle)
                    ->andReturn($this->statementInvoiceFile);
            }

            $this->interactor->handle($this->context, $this->ltcsBilling->id);
        });
        $this->should('store entity via Repository', function (): void {
            // サービス提供年月ごとに必要なファイルが作られる
            $files = $this->ltcsBillingBundles->map(fn () => [
                $this->statementInvoiceFile,
                $this->statementInvoiceFile,
            ])->flatten()->toArray();
            $this->ltcsBillingRepository
                ->expects('store')
                ->andReturnUsing(function (LtcsBilling $actual) use ($files): LtcsBilling {
                    $expect = $this->ltcsBilling->copy([
                        'files' => $files,
                        'updatedAt' => Carbon::now(),
                    ]);
                    $this->assertModelStrictEquals($expect, $actual);
                    return $actual;
                });

            $this->interactor->handle($this->context, $this->ltcsBilling->id);
        });
        $this->should('use logger', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('介護保険サービス請求が更新されました', equalTo(
                    ['id' => $this->ltcsBilling->id] + $context
                ));

            $this->interactor->handle($this->context, $this->ltcsBilling->id);
        });
    }

    /**
     * テスト用に下記のような請求単位の配列を返す
     * [ 0 => 請求単位, 1 => サービス提供年月が 0 + 1ヶ月, 2 => サービス提供年月が 0 + 2ヶ月]
     *
     * @param \Tests\Unit\Examples\Examples $examples
     * @return \Domain\Billing\LtcsBillingBundle[][]|\ScalikePHP\Seq
     */
    private function createLtcsBillingBundles(Examples $examples): Seq
    {
        $base = $examples->ltcsBillingBundles[0];
        return Seq::fromArray([
            $base,
            $base->copy([
                'id' => $base->id + 10000,
                'providedIn' => $base->providedIn->addMonths(1),
            ]),
            $base->copy([
                'id' => $base->id + 20000,
                'providedIn' => $base->providedIn->addMonths(2),
            ]),
        ]);
    }
}
