<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBillingServiceReport;
use Domain\ProvisionReport\DwsProvisionReport;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildDwsBillingServiceReportListUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\DwsBillingServiceReportRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\CreateDwsBillingServiceReportListInteractor;

/**
 * {@link \UseCase\Billing\CreateDwsBillingServiceReportListInteractor} のテスト.
 */
final class CreateDwsBillingServiceReportListInteractorTest extends Test
{
    use BuildDwsBillingServiceReportListUseCaseMixin;
    use CarbonMixin;
    use DummyContextMixin;
    use DwsBillingServiceReportRepositoryMixin;
    use DwsBillingTestSupport;
    use MatchesSnapshots;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private CreateDwsBillingServiceReportListInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->setupTestData();
        });

        self::beforeEachSpec(function (self $self): void {
            $self->buildDwsBillingServiceReportListUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->serviceReport))
                ->byDefault();
            $self->dwsBillingServiceReportRepository
                ->allows('store')
                ->andReturn($self->serviceReport)
                ->byDefault();
            $self->interactor = app(CreateDwsBillingServiceReportListInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle()
    {
        $this->should('run in transaction', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturn(Seq::empty());
            $this->buildDwsBillingServiceReportListUseCase->expects('handle')->never();

            $this->interactor->handle($this->context, $this->bundle, $this->reports, $this->previousReports);
        });
        $this->should('use BuildDwsBillingServiceReportListUseCase', function (): void {
            $previousReportsByUserId = $this->previousReports
                ->toMap(fn (DwsProvisionReport $x): int => $x->userId);
            Seq::from(...$this->reports)->each(function (DwsProvisionReport $report) use ($previousReportsByUserId): void {
                $this->buildDwsBillingServiceReportListUseCase
                    ->expects('handle')
                    ->with($this->context, $this->bundle, $report, equalTo($previousReportsByUserId->get($report->userId)))
                    ->andReturn(Seq::from($this->serviceReport));
            });

            $this->interactor->handle($this->context, $this->bundle, $this->reports, $this->previousReports);
        });
        $this->should(
            'store each serviceReports to repository',
            function (DwsProvisionReport $report, DwsBillingServiceReport $serviceReport): void {
                $this->buildDwsBillingServiceReportListUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($serviceReport));
                $this->dwsBillingServiceReportRepository
                    ->expects('store')
                    ->with($serviceReport)
                    ->andReturn($serviceReport);

                $this->interactor->handle($this->context, $this->bundle, Seq::from($report), $this->previousReports);
            },
            [
                'examples' => [
                    [$this->reports[0], $this->serviceReports[0]],
                    [$this->reports[1], $this->serviceReports[1]],
                    [$this->reports[2], $this->serviceReports[2]],
                ],
            ]
        );
        $this->should('return a Seq of DwsBillingServiceReports', function (): void {
            $actual = $this->interactor->handle($this->context, $this->bundle, $this->reports, $this->previousReports);

            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertForAll($actual, fn ($x): bool => $x instanceof DwsBillingServiceReport);
            $this->assertMatchesModelSnapshot($actual);
        });
    }
}
