<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\ProvisionReport;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Common\Carbon;
use Domain\Common\TimeRange;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\Office\LtcsBaseIncreaseSupportAddition;
use Domain\Office\LtcsOfficeLocationAddition;
use Domain\Office\LtcsSpecifiedTreatmentImprovementAddition;
use Domain\Office\LtcsTreatmentImprovementAddition;
use Domain\Project\LtcsProjectAmount;
use Domain\Project\LtcsProjectAmountCategory;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportEntry;
use Domain\ProvisionReport\LtcsProvisionReportOverScore;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\Timeframe;
use Domain\Shift\ServiceOption;
use Infrastructure\ProvisionReport\LtcsProvisionReportRepositoryEloquentImpl;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\DatabaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * LtcsProvisionReportRepositoryEloquentImpl のテスト.
 */
class LtcsProvisionReportRepositoryEloquentImplTest extends Test
{
    use CarbonMixin;
    use DatabaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private LtcsProvisionReportRepositoryEloquentImpl $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LtcsProvisionReportRepositoryEloquentImplTest $self): void {
            $self->repository = app(LtcsProvisionReportRepositoryEloquentImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_transactionManager(): void
    {
        $this->should('return a class name of DatabaseTransactionManager', function (): void {
            $this->assertSame(PermanentDatabaseTransactionManager::class, $this->repository->transactionManager());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_lookup(): void
    {
        $this->should('return an entity when the id exists in db', function (): void {
            $expected = $this->examples->ltcsProvisionReports[0];
            $actual = $this->repository->lookup($this->examples->ltcsProvisionReports[0]->id);

            $this->assertEquals(1, $actual->size());
            $this->assertModelStrictEquals($expected, $actual->head());
        });
        $this->should('return empty seq when the id not exists in db', function (): void {
            $actual = $this->repository->lookup(self::NOT_EXISTING_ID);
            $this->assertCount(0, $actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_store()
    {
        $this->should('add the entity', function (): void {
            $attrs = [
                'userId' => $this->examples->users[0]->id,
                'officeId' => $this->examples->offices[0]->id,
                'contractId' => $this->examples->contracts[0]->id,
                'providedIn' => Carbon::parse('2020-10'),
                'entries' => [
                    LtcsProvisionReportEntry::create([
                        'ownExpenseProgramId' => $this->examples->ownExpensePrograms[0]->id,
                        'slot' => TimeRange::create([
                            'start' => Carbon::now()->format('H:i'),
                            'end' => Carbon::now()->addHours(8)->format('H:i'),
                        ]),
                        'timeframe' => Timeframe::daytime(),
                        'category' => LtcsProjectServiceCategory::ownExpense(),
                        'amounts' => [
                            LtcsProjectAmount::create([
                                'category' => LtcsProjectAmountCategory::ownExpense(),
                                'amount' => 100,
                            ]),
                            LtcsProjectAmount::create([
                                'category' => LtcsProjectAmountCategory::housework(),
                                'amount' => 30,
                            ]),
                        ],
                        'headcount' => 5,
                        'serviceCode' => ServiceCode::fromString('111213'),
                        'options' => [
                            ServiceOption::oneOff(),
                        ],
                        'note' => '備考',
                        'plans' => [
                            Carbon::parse('2020-10-10'),
                            Carbon::parse('2020-10-11'),
                        ],
                        'results' => [
                            Carbon::parse('2020-10-10'),
                            Carbon::parse('2020-10-11'),
                        ],
                    ]),
                ],
                'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::addition1(),
                'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::addition1(),
                'specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::addition1(),
                'baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::addition1(),
                'locationAddition' => LtcsOfficeLocationAddition::none(),
                'plan' => new LtcsProvisionReportOverScore(
                    maxBenefitExcessScore: 100,
                    maxBenefitQuotaExcessScore: 200
                ),
                'result' => new LtcsProvisionReportOverScore(
                    maxBenefitExcessScore: 100,
                    maxBenefitQuotaExcessScore: 200
                ),
                'status' => LtcsProvisionReportStatus::inProgress(),
                'fixedAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
                'createdAt' => Carbon::now(),
            ];
            $entity = LtcsProvisionReport::create($attrs);
            $stored = $this->repository->store($entity);
            $actual = $this->repository->lookup($stored->id);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $entity->copy(['id' => $stored->id]),
                $actual->head()
            );
        });
        $this->should('update the entity', function (): void {
            $newStatus = LtcsProvisionReportStatus::fixed();
            $this->assertNotEquals($newStatus, $this->examples->ltcsProvisionReports[0]->status);
            $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0]->copy(['status' => $newStatus]);
            $this->repository->store($ltcsProvisionReport);
            $actual = $this->repository->lookup($this->examples->ltcsProvisionReports[0]->id);

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $ltcsProvisionReport,
                $actual->head()
            );
        });
        $this->should('return stored entity', function (): void {
            $attrs = [
                'userId' => $this->examples->users[0]->id,
                'officeId' => $this->examples->offices[0]->id,
                'contractId' => $this->examples->contracts[0]->id,
                'providedIn' => Carbon::parse('2020-10'),
                'entries' => [
                    LtcsProvisionReportEntry::create([
                        'ownExpenseProgramId' => $this->examples->ownExpensePrograms[0]->id,
                        'slot' => TimeRange::create([
                            'start' => Carbon::now()->format('H:i'),
                            'end' => Carbon::now()->addHours(8)->format('H:i'),
                        ]),
                        'timeframe' => Timeframe::daytime(),
                        'category' => LtcsProjectServiceCategory::ownExpense(),
                        'amounts' => [
                            LtcsProjectAmount::create([
                                'category' => LtcsProjectAmountCategory::ownExpense(),
                                'amount' => 100,
                            ]),
                            LtcsProjectAmount::create([
                                'category' => LtcsProjectAmountCategory::housework(),
                                'amount' => 30,
                            ]),
                        ],
                        'headcount' => 5,
                        'serviceCode' => ServiceCode::fromString('111213'),
                        'options' => [
                            ServiceOption::oneOff(),
                        ],
                        'note' => '備考',
                        'plans' => [
                            Carbon::parse('2020-10-10'),
                            Carbon::parse('2020-10-11'),
                        ],
                        'results' => [
                            Carbon::parse('2020-10-10'),
                            Carbon::parse('2020-10-11'),
                        ],
                    ]),
                ],
                'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::addition1(),
                'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::addition1(),
                'specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::addition1(),
                'baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::addition1(),
                'locationAddition' => LtcsOfficeLocationAddition::none(),
                'plan' => new LtcsProvisionReportOverScore(
                    maxBenefitExcessScore: 100,
                    maxBenefitQuotaExcessScore: 200
                ),
                'result' => new LtcsProvisionReportOverScore(
                    maxBenefitExcessScore: 100,
                    maxBenefitQuotaExcessScore: 200
                ),
                'status' => LtcsProvisionReportStatus::inProgress(),
                'fixedAt' => null,
                'updatedAt' => Carbon::now(),
                'createdAt' => Carbon::now(),
            ];
            $entity = LtcsProvisionReport::create($attrs);

            $stored = $this->repository->store($entity);
            $this->assertModelStrictEquals(
                $entity->copy(['id' => $stored->id]),
                $stored
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_removeById(): void
    {
        $this->should('remove entities', function (): void {
            $this->repository->removeById($this->examples->ltcsProvisionReports[2]->id, $this->examples->ltcsProvisionReports[3]->id);
            $report0 = $this->repository->lookup($this->examples->ltcsProvisionReports[2]->id);
            $this->assertCount(0, $report0);
            $report1 = $this->repository->lookup($this->examples->ltcsProvisionReports[3]->id);
            $this->assertCount(0, $report1);
        });
        $this->should('not remove other entities', function (): void {
            $this->repository->removeById($this->examples->ltcsProvisionReports[0]->id);
            $report0 = $this->repository->lookup($this->examples->ltcsProvisionReports[0]->id);
            $this->assertCount(0, $report0);
            $report1 = $this->repository->lookup($this->examples->ltcsProvisionReports[1]->id);
            $report2 = $this->repository->lookup($this->examples->ltcsProvisionReports[2]->id);
            $this->assertCount(1, $report1);
            $this->assertModelStrictEquals($this->examples->ltcsProvisionReports[1], $report1->head());
            $this->assertCount(1, $report2);
            $this->assertModelStrictEquals($this->examples->ltcsProvisionReports[2], $report2->head());
        });
    }
}
