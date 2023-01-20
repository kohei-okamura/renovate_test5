<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Billing\LtcsBilling;

use BillingTester;
use Codeception\Util\HttpCode;
use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingServiceDetail;
use Domain\Billing\LtcsBillingServiceDetailDisposition;
use Domain\Billing\LtcsBillingStatement;
use Domain\Billing\LtcsBillingStatementSubsidy;
use Domain\Billing\LtcsExpiredReason;
use Domain\Common\Carbon;
use Domain\Common\TimeRange;
use Domain\Contract\Contract;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\Office\LtcsBaseIncreaseSupportAddition;
use Domain\Office\LtcsOfficeLocationAddition;
use Domain\Office\LtcsSpecifiedTreatmentImprovementAddition;
use Domain\Office\LtcsTreatmentImprovementAddition;
use Domain\Project\LtcsProjectAmount;
use Domain\Project\LtcsProjectAmountCategory;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\ProvisionReport\LtcsBuildingSubtraction;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportEntry;
use Domain\ProvisionReport\LtcsProvisionReportOverScore;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsNoteRequirement;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Domain\ServiceCodeDictionary\Timeframe;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * 介護保険 請求テスト.
 *
 * 公費負担（生活保護）の場合
 */
final class CreateLtcsBillingWithSubsidiesCest extends CreateLtcsBillingTest
{
    use ExamplesConsumer;

    /**
     * 請求生成テスト.
     *
     * @param \BillingTester $I
     */
    public function succeedAPICall(BillingTester $I)
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $office = $this->examples->offices[2];

        // 予実を準備
        $entries = [
            LtcsProvisionReportEntry::create([
                'ownExpenseProgramId' => null,
                'slot' => TimeRange::create(['start' => '13:00', 'end' => '14:00']),
                'timeframe' => Timeframe::daytime(),
                'category' => LtcsProjectServiceCategory::housework(),
                'amounts' => [
                    LtcsProjectAmount::create(['category' => LtcsProjectAmountCategory::housework(), 'amount' => 60]),
                ],
                'headcount' => 1,
                'serviceCode' => ServiceCode::fromString('117311'), // カイポケより 生活援助３
                'options' => [],
                'note' => 'SRさん 新宿',
                'plans' => [
                    Carbon::create(2020, 10, 1),
                    Carbon::create(2020, 10, 8),
                    Carbon::create(2020, 10, 15),
                    Carbon::create(2020, 10, 22),
                    Carbon::create(2020, 10, 29),
                ],
                'results' => [
                    Carbon::create(2020, 10, 15),
                ],
            ]),
            LtcsProvisionReportEntry::create([
                'ownExpenseProgramId' => null,
                'slot' => TimeRange::create(['start' => '14:00', 'end' => '15:00']),
                'timeframe' => Timeframe::daytime(),
                'category' => LtcsProjectServiceCategory::housework(),
                'amounts' => [
                    LtcsProjectAmount::create(['category' => LtcsProjectAmountCategory::housework(), 'amount' => 60]),
                ],
                'headcount' => 1,
                'serviceCode' => ServiceCode::fromString('117311'), // カイポケより 生活援助３
                'options' => [],
                'note' => 'SRさん 新宿',
                'plans' => [
                ],
                'results' => [
                    Carbon::create(2020, 10, 1),
                    Carbon::create(2020, 10, 8),
                    Carbon::create(2020, 10, 22),
                    Carbon::create(2020, 10, 29),
                ],
            ]),
            LtcsProvisionReportEntry::create([
                'ownExpenseProgramId' => null,
                'slot' => TimeRange::create(['start' => '14:30', 'end' => '15:30']),
                'timeframe' => Timeframe::daytime(),
                'category' => LtcsProjectServiceCategory::physicalCare(),
                'amounts' => [
                    LtcsProjectAmount::create([
                        'category' => LtcsProjectAmountCategory::physicalCare(),
                        'amount' => 60,
                    ]),
                ],
                'headcount' => 1,
                'serviceCode' => ServiceCode::fromString('111211'), // カイポケより 身体介護２
                'options' => [],
                'note' => 'SRさん 新宿',
                'plans' => [
                    Carbon::create(2020, 10, 5),
                    Carbon::create(2020, 10, 12),
                    Carbon::create(2020, 10, 19),
                    Carbon::create(2020, 10, 26),
                ],
                'results' => [
                    Carbon::create(2020, 10, 5),
                    Carbon::create(2020, 10, 12),
                    Carbon::create(2020, 10, 19),
                    Carbon::create(2020, 10, 26),
                ],
            ]),
        ];

        $repository = $this->getProvisionReportRepository();
        $repository->store(LtcsProvisionReport::create([
            'userId' => $this->examples->users[0]->id,
            'officeId' => $office->id,
            'contractId' => $this->examples->contracts[0]->id,
            'providedIn' => Carbon::create(2020, 10),
            'entries' => $entries,
            'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::none(),
            'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::none(),
            'specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::none(),
            'baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::none(),
            'locationAddition' => LtcsOfficeLocationAddition::none(),
            'plan' => new LtcsProvisionReportOverScore(
                maxBenefitExcessScore: 0,
                maxBenefitQuotaExcessScore: 0,
            ),
            'result' => new LtcsProvisionReportOverScore(
                maxBenefitExcessScore: 0,
                maxBenefitQuotaExcessScore: 0,
            ),
            'status' => LtcsProvisionReportStatus::fixed(),
            'fixedAt' => Carbon::create(2021, 5, 1),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ]));

        $I->sendPOST('ltcs-billings', ['officeId' => $office->id, 'transactedIn' => '2021-05']);

        $I->seeResponseCodeIs(HttpCode::ACCEPTED);
        $I->seeLogCount(3);

        // LtcsBilling
        $billingFinder = $this->getBillingFinder();
        $findList = $billingFinder->find([], ['sortBy' => 'id', 'desc' => true, 'itemsPerPage' => 1])->list;
        /** @var \Domain\Billing\LtcsBilling $ltcsBilling */
        $ltcsBilling = $findList->head();

        // LtcsBillingBundle
        $bundleRepository = $this->getBundleRepository();
        /** @var \Domain\Billing\LtcsBillingBundle[]|\ScalikePHP\Seq $bundles */
        $bundles = $bundleRepository->lookupByBillingId($ltcsBilling->id)->head()[1];
        $I->assertCount(1, $bundles);
        /** @var \Domain\Billing\LtcsBillingBundle $bundle */
        $bundle = $bundles->head();

        // 明細書
        $statementRepository = $this->getStatementRepository();
        /** @var \Domain\Billing\LtcsBillingStatement[]|\ScalikePHP\Seq $statements */
        $statements = $statementRepository->lookupByBundleId($bundle->id)->head()[1];
        $I->assertCount(1, $statements);
        /** @var \Domain\Billing\LtcsBillingStatement $statement */
        $statement = $statements->head();

        // パラメータの検証
        $this->checkServiceDetails($I, $bundle, $entries);
        $this->checkStatementParameters($I, $statement, $this->examples->contracts[25]);
        $this->checkStatementCarePlanAuthor($I, $statement);
        $this->checkStatementItems($I, $statement);
        $this->checkStatementAggregates($I, $statement);
        $this->checkStatementSubsidies($I, $statement);
    }

    /**
     * サービス詳細の検証.
     *
     * @param \BillingTester $I
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @param array $entries
     */
    private function checkServiceDetails(BillingTester $I, LtcsBillingBundle $bundle, array $entries): void
    {
        $details = Seq::fromArray($entries)->flatMap(function (LtcsProvisionReportEntry $entry): iterable {
            $score = $entry->serviceCode->serviceCategoryCode === '7311' ? 224 : 395;
            $category = $entry->serviceCode->serviceCategoryCode === '7311'
                ? LtcsServiceCodeCategory::housework()
                : LtcsServiceCodeCategory::physicalCare();
            $base = new LtcsBillingServiceDetail(
                userId: $this->examples->users[0]->id,
                disposition: LtcsBillingServiceDetailDisposition::result(),
                providedOn: Carbon::create(2020, 10, 5),
                serviceCode: ServiceCode::fromString('111211'),
                serviceCodeCategory: LtcsServiceCodeCategory::physicalCare(),
                buildingSubtraction: LtcsBuildingSubtraction::none(),
                noteRequirement: LtcsNoteRequirement::none(),
                isAddition: false,
                isLimited: true,
                durationMinutes: $entry->amounts[0]->amount,
                unitScore: $score,
                count: 1,
                wholeScore: $score,
                maxBenefitQuotaExcessScore: 0,
                maxBenefitExcessScore: 0,
                totalScore: $score,
            );
            return Seq::fromArray($entry->results)->map(fn (Carbon $x): LtcsBillingServiceDetail => $base->copy([
                'providedOn' => $x,
                'serviceCode' => $entry->serviceCode,
                'serviceCodeCategory' => $category,
            ]));
        });

        $I->assertArrayStrictEquals($details->toArray(), $bundle->details);
    }

    /**
     * 明細書の個別パラメータを検証する.
     *
     * @param \BillingTester $I
     * @param \Domain\Billing\LtcsBillingStatement $statement
     * @param \Domain\Contract\Contract $contract
     */
    private function checkStatementParameters(
        BillingTester $I,
        LtcsBillingStatement $statement,
        Contract $contract
    ): void {
        $I->assertEquals($contract->ltcsPeriod->start, $statement->agreedOn);
        $I->assertNull($statement->expiredOn); // 当月終了じゃないのでnull
        $I->assertEquals(LtcsExpiredReason::unspecified(), $statement->expiredReason);
    }

    /**
     * 明細書のケアプラン作成者を検証する.
     *
     * @param \BillingTester $I
     * @param \Domain\Billing\LtcsBillingStatement $statement
     */
    private function checkStatementCarePlanAuthor(BillingTester $I, LtcsBillingStatement $statement): void
    {
        $I->assertMatchesModelSnapshot($statement->carePlanAuthor);
    }

    /**
     * 明細書の明細を検証する.
     *
     * @param \BillingTester $I
     * @param \Domain\Billing\LtcsBillingStatement $statement
     */
    private function checkStatementItems(BillingTester $I, LtcsBillingStatement $statement): void
    {
        $I->assertMatchesModelSnapshot($statement->items);
    }

    /**
     * 明細書合計を検証する.
     *
     * @param \BillingTester $I
     * @param \Domain\Billing\LtcsBillingStatement $statement
     */
    private function checkStatementAggregates(BillingTester $I, LtcsBillingStatement $statement): void
    {
        $I->assertMatchesModelSnapshot($statement->aggregates);
    }

    /**
     * 公費請求情報を検証する.
     *
     * @param \BillingTester $I
     * @param \Domain\Billing\LtcsBillingStatement $statement
     */
    private function checkStatementSubsidies(BillingTester $I, LtcsBillingStatement $statement): void
    {
        $ltcsSubsidy = $this->examples->userLtcsSubsidies[0];
        $expected = [
            new LtcsBillingStatementSubsidy(
                defrayerCategory: $ltcsSubsidy->defrayerCategory,
                defrayerNumber: $ltcsSubsidy->defrayerNumber,
                recipientNumber: $ltcsSubsidy->recipientNumber,
                benefitRate: $ltcsSubsidy->benefitRate,
                totalScore: 2700, // 総スコア
                claimAmount: 9234, // 3割
                copayAmount: 0,
            ),
            LtcsBillingStatementSubsidy::empty(),
            LtcsBillingStatementSubsidy::empty(),
        ];

        $I->assertArrayStrictEquals($expected, $statement->subsidies);
    }
}
