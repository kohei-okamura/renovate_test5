<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\LtcsBilling as Billing;
use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingOffice;
use Domain\Billing\LtcsBillingStatus;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Pagination;
use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Office\Office;
use Domain\Permission\Permission;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Lib\Exceptions\NotFoundException;
use Mockery;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CreateLtcsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\CreateLtcsBillingInvoiceListUseCaseMixin;
use Tests\Unit\Mixins\CreateLtcsBillingStatementListUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LtcsBillingRepositoryMixin;
use Tests\Unit\Mixins\LtcsProvisionReportFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Billing\CreateLtcsBillingInteractor;

/**
 * {@link \UseCase\Billing\CreateLtcsBillingInteractor} のテスト.
 */
final class CreateLtcsBillingInteractorTest extends Test
{
    use BuildLtcsBillingInvoiceListInteractorTestData;
    use CarbonMixin;
    use ContextMixin;
    use CreateLtcsBillingBundleUseCaseMixin;
    use CreateLtcsBillingInvoiceListUseCaseMixin;
    use CreateLtcsBillingStatementListUseCaseMixin;
    use ExamplesConsumer;
    use LookupOfficeUseCaseMixin;
    use LtcsBillingRepositoryMixin;
    use LtcsBillingTestSupport;
    use LtcsProvisionReportFinderMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private const BILLING_ID = 24360679;

    private Carbon $transactedIn;
    private CarbonRange $fixedAt;

    private Billing $billing;
    private Seq $reports;
    private Seq $invoices;
    private Seq $statements;

    private CreateLtcsBillingInteractor $interactor;

    /**
     * 初期化処理.
     *
     * @noinspection PhpUnusedParameterInspection
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->transactedIn = Carbon::create(2008, 5, 17);
            $self->fixedAt = CarbonRange::create([
                'start' => Carbon::create(1993, 6, 21),
                'end' => Carbon::create(1996, 6, 11),
            ]);
            $self->setupTestData();
            $self->reports = Seq::from(...$self->examples->ltcsProvisionReports);
            $self->billing = $self->billing();
            $self->invoices = $self->invoices();
            $self->statements = $self->statements();
        });
        self::beforeEachSpec(function (self $self): void {
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->offices[0]))
                ->byDefault();

            $self->createLtcsBillingBundleUseCase
                ->allows('handle')
                ->andReturnUsing(
                    function (
                        Context $context,
                        Billing $billing,
                        Carbon $providedIn,
                        Seq $reports
                    ) use ($self): LtcsBillingBundle {
                        return $self->bundle->copy(['providedIn' => $providedIn]);
                    }
                )
                ->byDefault();

            $self->createLtcsBillingStatementListUseCase
                ->allows('handle')
                ->andReturn($self->statements)
                ->byDefault();

            $self->createLtcsBillingInvoiceListUseCase
                ->allows('handle')
                ->andReturn($self->invoices)
                ->byDefault();

            $self->ltcsBillingRepository
                ->allows('store')
                ->andReturnUsing(fn (Billing $x): Billing => $x->copy(['id' => self::BILLING_ID]))
                ->byDefault();

            $self->ltcsProvisionReportFinder
                ->allows('find')
                ->andReturn(FinderResult::from($self->reports, Pagination::create([])))
                ->byDefault();

            $self->interactor = app(CreateLtcsBillingInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('run in transaction', function (): void {
            $this->transactionManager->expects('run')->andReturn($this->billing);
            $this->ltcsBillingRepository->expects('store')->never();
            $this->createLtcsBillingBundleUseCase->expects('handle')->never();
            $this->createLtcsBillingStatementListUseCase->expects('handle')->never();
            $this->createLtcsBillingInvoiceListUseCase->expects('handle')->never();

            $this->interactor->handle($this->context, 1234, $this->transactedIn, $this->fixedAt);
        });
        $this->should('lookup the office using arguments', function (): void {
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::createBillings()], $this->office->id)
                ->andReturn(Seq::from($this->office));

            $this->interactor->handle($this->context, $this->office->id, $this->transactedIn, $this->fixedAt);
        });
        $this->should('throw NotFoundException when the office not found', function (): void {
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle($this->context, $this->office->id, $this->transactedIn, $this->fixedAt);
            });
        });
        $this->should('create and store the billing', function (): void {
            $expected = $this->billing->copy(['id' => null]);
            $this->ltcsBillingRepository
                ->expects('store')
                ->with(Mockery::capture($actual))
                ->andReturnUsing(fn (Billing $x): Billing => $x->copy(['id' => 1]));

            $this->interactor->handle($this->context, $this->office->id, $this->transactedIn, $this->fixedAt);

            $this->assertModelStrictEquals($expected, $actual);
        });
        $this->should('fetch a Seq of LtcsProvisionReports using LtcsProvisionReportFinder', function (): void {
            $expectedFilterParams = [
                'officeId' => $this->billing->office->officeId,
                'fixedAt' => $this->fixedAt,
                'status' => LtcsProvisionReportStatus::fixed(),
            ];
            $expectedPaginationParams = [
                'all' => true,
                'sortBy' => 'id',
            ];
            $this->ltcsProvisionReportFinder
                ->expects('find')
                ->with(Mockery::capture($actualFilterParams), Mockery::capture($actualPaginationParmas))
                ->andReturn(FinderResult::from($this->reports, Pagination::create([])));

            $this->interactor->handle($this->context, $this->office->id, $this->transactedIn, $this->fixedAt);

            $this->assertEquals($expectedFilterParams, $actualFilterParams);
            $this->assertEquals($expectedPaginationParams, $actualPaginationParmas);
        });
        $this->should('throw NotFoundException when LtcsProvisionReport not found', function (): void {
            $this->ltcsProvisionReportFinder
                ->expects('find')
                ->andReturn(FinderResult::from(Seq::emptySeq(), Pagination::create([])));

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle($this->context, $this->office->id, $this->transactedIn, $this->fixedAt);
            });
        });
        $this->should('create bundle for each months provided', function (): void {
            $providedInList = [
                Carbon::create(2020, 10),
                Carbon::create(2020, 11),
                Carbon::create(2021, 1),
            ];
            foreach ($providedInList as $expected) {
                $this->createLtcsBillingBundleUseCase
                    ->expects('handle')
                    ->withArgs(
                        function (
                            Context $context,
                            Billing $billing,
                            Carbon $providedIn,
                            Seq $reports
                        ) use ($expected): bool {
                            return $context === $this->context
                                && $billing->equals($this->billing)
                                && $providedIn->eq($expected)
                                && $reports->forAll(fn (LtcsProvisionReport $x): bool => $x->providedIn->eq($expected));
                        }
                    )
                    ->andReturn($this->bundle);
            }
            $this->createLtcsBillingBundleUseCase->expects('handle')->never();

            $this->interactor->handle($this->context, $this->office->id, $this->transactedIn, $this->fixedAt);
        });
        $this->should('create statements for each months provided', function (): void {
            $providedInList = [
                Carbon::create(2020, 10),
                Carbon::create(2020, 11),
                Carbon::create(2021, 1),
            ];
            foreach ($providedInList as $expected) {
                $this->createLtcsBillingStatementListUseCase
                    ->expects('handle')
                    ->withArgs(
                        function (Context $context, Office $office, LtcsBillingBundle $bundle) use ($expected): bool {
                            return $context === $this->context
                                && $office->equals($this->offices[0])
                                && $bundle->providedIn->eq($expected);
                        }
                    )
                    ->andReturn($this->statements);
            }
            $this->createLtcsBillingStatementListUseCase->expects('handle')->never();

            $this->interactor->handle($this->context, $this->office->id, $this->transactedIn, $this->fixedAt);
        });
        $this->should('create invoices for each months provided', function (): void {
            $providedInList = [
                Carbon::create(2020, 10),
                Carbon::create(2020, 11),
                Carbon::create(2021, 1),
            ];
            foreach ($providedInList as $expected) {
                $this->createLtcsBillingInvoiceListUseCase
                    ->expects('handle')
                    ->withArgs(
                        function (Context $context, LtcsBillingBundle $bundle, Seq $statements) use ($expected): bool {
                            return $context === $this->context
                                && $bundle->equals($this->bundle->copy(['providedIn' => $expected]))
                                && $statements === $this->statements;
                        }
                    )
                    ->andReturn($this->statements);
            }
            $this->createLtcsBillingInvoiceListUseCase->expects('handle')->never();

            $this->interactor->handle($this->context, $this->office->id, $this->transactedIn, $this->fixedAt);
        });
        $this->should('return the billing entity', function (): void {
            $expected = $this->billing->copy(['id' => self::BILLING_ID]);
            $this->ltcsBillingRepository->expects('store')->andReturn($expected);

            $actual = $this->interactor->handle($this->context, $this->office->id, $this->transactedIn, $this->fixedAt);

            $this->assertSame($expected, $actual);
        });
    }

    /**
     * テスト用の介護保険サービス：請求を生成する.
     */
    private function billing(): Billing
    {
        return Billing::create([
            'id' => self::BILLING_ID,
            'organizationId' => $this->examples->organizations[0]->id,
            'office' => LtcsBillingOffice::from($this->offices[0]),
            'transactedIn' => $this->transactedIn,
            'files' => [],
            'status' => LtcsBillingStatus::checking(),
            'fixedAt' => null,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]);
    }

    /**
     * テスト用の介護保険サービス：請求書の一覧を生成する.
     *
     * @return \Domain\Billing\LtcsBillingInvoice[]|\ScalikePHP\Seq
     */
    private function invoices(): Seq
    {
        return Seq::from();
    }
}
