<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingFile;
use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Mockery;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\Examples;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateDwsBillingCopayCoordinationCsvUseCaseMixin;
use Tests\Unit\Mixins\CreateDwsBillingCopayCoordinationPdfUseCaseMixin;
use Tests\Unit\Mixins\CreateDwsBillingInvoicePdfUseCaseMixin;
use Tests\Unit\Mixins\CreateDwsBillingServiceReportCsvUseCaseMixin;
use Tests\Unit\Mixins\CreateDwsBillingServiceReportPdfUseCaseMixin;
use Tests\Unit\Mixins\CreateDwsBillingStatementAndInvoiceCsvUseCaseMixin;
use Tests\Unit\Mixins\DwsBillingBundleRepositoryMixin;
use Tests\Unit\Mixins\DwsBillingRepositoryMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\UpdateDwsBillingFilesInteractor;

/**
 * {@link \UseCase\Billing\UpdateDwsBillingFilesInteractor} のテスト.
 */
final class UpdateDwsBillingFilesInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use CreateDwsBillingStatementAndInvoiceCsvUseCaseMixin;
    use CreateDwsBillingInvoicePdfUseCaseMixin;
    use CreateDwsBillingServiceReportCsvUseCaseMixin;
    use CreateDwsBillingServiceReportPdfUseCaseMixin;
    use CreateDwsBillingCopayCoordinationCsvUseCaseMixin;
    use CreateDwsBillingCopayCoordinationPdfUseCaseMixin;
    use DwsBillingRepositoryMixin;
    use DwsBillingBundleRepositoryMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupDwsBillingUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private DwsBilling $dwsBilling;
    /** @var \Domain\Billing\DwsBillingBundle[][]|\ScalikePHP\Seq */
    private Seq $dwsBillingBundlesArray;
    private DwsBillingFile $statementInvoiceFile;
    private DwsBillingFile $serviceReportFile;
    private DwsBillingFile $copayCoordinationFile;

    private UpdateDwsBillingFilesInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->statementInvoiceFile = $self->examples->dwsBillings[1]->files[0];
            $self->serviceReportFile = $self->examples->dwsBillings[0]->files[0];
            $self->copayCoordinationFile = $self->examples->dwsBillings[4]->files[0];
        });
        self::beforeEachSpec(function (self $self): void {
            $self->dwsBilling = $self->examples->dwsBillings[0];
            $self->dwsBillingBundlesArray = $self->createDwsBillingBundles($self->examples);

            $self->createDwsBillingStatementAndInvoiceCsvUseCase
                ->allows('handle')
                ->andReturn($self->statementInvoiceFile)
                ->byDefault();
            $self->createDwsBillingInvoicePdfUseCase
                ->allows('handle')
                ->andReturn($self->statementInvoiceFile)
                ->byDefault();
            $self->createDwsBillingServiceReportCsvUseCase
                ->allows('handle')
                ->andReturn($self->serviceReportFile)
                ->byDefault();
            $self->createDwsBillingServiceReportPdfUseCase
                ->allows('handle')
                ->andReturn($self->serviceReportFile)
                ->byDefault();
            $self->createDwsBillingCopayCoordinationCsvUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->copayCoordinationFile))
                ->byDefault();
            $self->createDwsBillingCopayCoordinationPdfUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->copayCoordinationFile))
                ->byDefault();

            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->dwsBilling))
                ->byDefault();
            $self->dwsBillingBundleRepository
                ->allows('lookupByBillingId')
                ->andReturn(Map::from([$self->dwsBilling->id => $self->dwsBillingBundlesArray->flatten()]))
                ->byDefault();

            $self->dwsBillingRepository
                ->allows('store')
                ->andReturn($self->dwsBilling)
                ->byDefault();

            $self->logger
                ->allows('info')
                ->andReturnNull()
                ->byDefault();

            $self->interactor = app(UpdateDwsBillingFilesInteractor::class);
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
                $this->dwsBilling,
                $this->interactor->handle($this->context, $this->dwsBilling->id)
            );
        });
        $this->should('use CreateDwsBillingStatementAndInvoiceCsvUseCase', function (): void {
            /** @var \ScalikePHP\Seq[] $actual */
            $actual = [null, null, null];
            foreach ($actual as &$value) {
                $this->createDwsBillingStatementAndInvoiceCsvUseCase
                    ->expects('handle')
                    ->with($this->context, $this->dwsBilling, Mockery::capture($value))
                    ->andReturn($this->statementInvoiceFile);
            }

            $this->interactor->handle($this->context, $this->dwsBilling->id);

            $this->assertBillingBundles($actual);
        });
        $this->should('use CreateDwsBillingInvoicePdfUseCase', function (): void {
            /** @var \ScalikePHP\Seq[] $actual */
            $actual = [null, null, null];
            foreach ($actual as &$value) {
                $this->createDwsBillingInvoicePdfUseCase
                    ->expects('handle')
                    ->with($this->context, $this->dwsBilling, Mockery::capture($value))
                    ->andReturn($this->statementInvoiceFile);
            }

            $this->interactor->handle($this->context, $this->dwsBilling->id);

            $this->assertBillingBundles($actual);
        });
        $this->should('use CreateDwsBillingServiceReportCsvUseCase', function (): void {
            /** @var \ScalikePHP\Seq[] $actual */
            $actual = [null, null, null];
            foreach ($actual as &$value) {
                $this->createDwsBillingServiceReportCsvUseCase
                    ->expects('handle')
                    ->with($this->context, $this->dwsBilling, Mockery::capture($value))
                    ->andReturn($this->serviceReportFile);
            }

            $this->interactor->handle($this->context, $this->dwsBilling->id);

            $this->assertBillingBundles($actual);
        });
        $this->should('use CreateDwsBillingServiceReportPdfUseCase', function (): void {
            /** @var \ScalikePHP\Seq[] $actual */
            $actual = [null, null, null];
            foreach ($actual as &$value) {
                $this->createDwsBillingServiceReportPdfUseCase
                    ->expects('handle')
                    ->with($this->context, $this->dwsBilling, Mockery::capture($value))
                    ->andReturn($this->serviceReportFile);
            }

            $this->interactor->handle($this->context, $this->dwsBilling->id);

            $this->assertBillingBundles($actual);
        });
        $this->should('use CreateDwsBillingCopayCoordinationCsvUseCase', function (): void {
            /** @var \ScalikePHP\Seq[] $actual */
            $actual = [null, null, null];
            foreach ($actual as &$value) {
                $this->createDwsBillingCopayCoordinationCsvUseCase
                    ->expects('handle')
                    ->with($this->context, $this->dwsBilling, Mockery::capture($value))
                    ->andReturn(Option::from($this->copayCoordinationFile));
            }

            $this->interactor->handle($this->context, $this->dwsBilling->id);

            $this->assertBillingBundles($actual);
        });
        $this->should('use CreateDwsBillingCopayCoordinationPdfUseCase', function (): void {
            /** @var \ScalikePHP\Seq[] $actual */
            $actual = [null, null, null];
            foreach ($actual as &$value) {
                $this->createDwsBillingCopayCoordinationPdfUseCase
                    ->expects('handle')
                    ->with($this->context, $this->dwsBilling, Mockery::capture($value))
                    ->andReturn(Option::from($this->copayCoordinationFile));
            }

            $this->interactor->handle($this->context, $this->dwsBilling->id);

            $this->assertBillingBundles($actual);
        });
        $this->should('use LookupDwsBillingUseCase', function (): void {
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateBillings(), $this->dwsBilling->id)
                ->andReturn(Seq::from($this->dwsBilling));

            $this->interactor->handle($this->context, $this->dwsBilling->id);
        });
        $this->should('throw Exception LookupDwsBilling returns empty', function (): void {
            $this->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::empty());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, $this->dwsBilling->id);
                }
            );
        });
        $this->should('use DwsBillingBundleRepository', function (): void {
            $this->dwsBillingBundleRepository
                ->expects('lookupByBillingId')
                ->with($this->dwsBilling->id)
                ->andReturn(Map::from([$this->dwsBilling->id => $this->dwsBillingBundlesArray->flatten()]));

            $this->interactor->handle($this->context, $this->dwsBilling->id);
        });
        $this->should('store entity via Repository', function (): void {
            // サービス提供年月ごとに必要なファイルが作られる
            $files = $this->dwsBillingBundlesArray->map(fn () => [
                $this->statementInvoiceFile,
                $this->statementInvoiceFile,
                $this->serviceReportFile,
                $this->serviceReportFile,
                $this->copayCoordinationFile,
                $this->copayCoordinationFile,
            ])->flatten()->toArray();

            $this->dwsBillingRepository
                ->expects('store')
                ->andReturnUsing(function (DwsBilling $actual) use ($files): DwsBilling {
                    $expect = $this->dwsBilling->copy([
                        'files' => $files,
                        'updatedAt' => Carbon::now(),
                    ]);
                    $this->assertModelStrictEquals($expect, $actual);
                    return $actual;
                });

            $this->interactor->handle($this->context, $this->dwsBilling->id);
        });
        $this->should(
            'store entity via Repository except copayCoordination CSV and PDF when CreateCopayCoordinationCsvUseCase return none',
            function (): void {
                // サービス提供年月ごとに必要なファイルが作られる
                $files = $this->dwsBillingBundlesArray->map(fn () => [
                    $this->statementInvoiceFile,
                    $this->statementInvoiceFile,
                    $this->serviceReportFile,
                    $this->serviceReportFile,
                ])->flatten()->toArray();

                // このテストの目的は Option::none() が返った後の処理の確認なので any で通す
                $this->createDwsBillingCopayCoordinationCsvUseCase
                    ->expects('handle')
                    ->times($this->dwsBillingBundlesArray->size())
                    ->with($this->context, $this->dwsBilling, Mockery::any())
                    ->andReturn(Option::none());
                $this->dwsBillingRepository
                    ->expects('store')
                    ->andReturnUsing(function (DwsBilling $actual) use ($files): DwsBilling {
                        $expect = $this->dwsBilling->copy([
                            'files' => $files,
                            'updatedAt' => Carbon::now(),
                        ]);
                        $this->assertModelStrictEquals($expect, $actual);
                        return $actual;
                    });

                $this->interactor->handle($this->context, $this->dwsBilling->id);
            }
        );
        $this->should('use logger', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('障害福祉サービス請求が更新されました', equalTo(
                    ['id' => $this->dwsBilling->id] + $context
                ));

            $this->interactor->handle($this->context, $this->dwsBilling->id);
        });
    }

    /**
     * テスト用に下記のような請求単位の配列を返す
     * [ 0 => 請求単位の配列, 1 => サービス提供年月が 0 + 1ヶ月, 2 => サービス提供年月が 0 + 2ヶ月]
     *
     * @param \Tests\Unit\Examples\Examples $examples
     * @return \Domain\Billing\DwsBillingBundle[][]|\ScalikePHP\Seq
     */
    private function createDwsBillingBundles(Examples $examples): Seq
    {
        $base = Seq::fromArray($examples->dwsBillingBundles)->take(2);
        return Seq::fromArray([
            $base,
            ...Seq::fromArray([[10000, 1], [20000, 2]])
                ->map(function (array $x) use ($base): Seq {
                    return $base->map(fn (DwsBillingBundle $y) => $y->copy([
                        'id' => $y->id + $x[0],
                        'providedIn' => $y->providedIn->addMonths($x[1]),
                    ]));
                })
                ->toArray(),
        ]);
    }

    /**
     * 請求単位の一致を検証する
     *
     * @param \Domain\Billing\DwsBillingBundle[][]|\ScalikePHP\Seq[] $actual
     * @return void
     */
    private function assertBillingBundles(array $actual): void
    {
        $this->dwsBillingBundlesArray->each(function (Seq $bundles, int $key) use ($actual) {
            $this->assertEach(
                function (DwsBillingBundle $expected, DwsBillingBundle $actual): void {
                    $this->assertModelStrictEquals($expected, $actual);
                },
                $bundles->toArray(),
                $actual[$key]->toArray()
            );
        });
    }
}
