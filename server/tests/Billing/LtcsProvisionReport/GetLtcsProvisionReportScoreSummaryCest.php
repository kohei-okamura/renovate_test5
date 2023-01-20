<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\LtcsProvisionReport;

use BillingTester;
use Codeception\Util\HttpCode;
use Domain\Common\Carbon;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\Office\LtcsBaseIncreaseSupportAddition;
use Domain\Office\LtcsOfficeLocationAddition;
use Domain\Office\LtcsSpecifiedTreatmentImprovementAddition;
use Domain\Office\LtcsTreatmentImprovementAddition;
use Domain\Project\LtcsProjectAmount;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportEntry;
use Domain\Shift\ServiceOption;
use ScalikePHP\Seq;
use Tests\Billing\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * ProvisionReport get のテスト.
 * GET /ltcs-provision-reports/{officeId}/{userId}/{providedIn}
 */
class GetLtcsProvisionReportScoreSummaryCest extends Test
{
    use ExamplesConsumer;

    // tests

    /**
     * API正常呼び出しテスト.
     *
     * @param \BillingTester $I
     * @throws \Codeception\Exception\ModuleException
     */
    public function succeedAPICall(BillingTester $I)
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0];

        $I->sendPOST('ltcs-provision-report-score-summary', $this->createParameters($ltcsProvisionReport));

        $I->seeResponseCodeIs(HttpCode::OK);

        // 111213 身体介護2・深 593 * 4 = 2372
        // 114001 訪問介護初回加算 200 * 1 = 200
        // 118100 訪問介護小規模事業所加算 * 1 = 237
        // 116274 訪問介護処遇改善加算Ⅱ * 1 = 281
        $expected = [
            'plan' => [
                'managedScore' => 2572,
                'unmanagedScore' => 518,
            ],
            'result' => [
                'managedScore' => 2572,
                'unmanagedScore' => 518,
            ],
        ];
        $I->seeResponseContainsJson($expected);
        $I->seeLogCount(0);
    }

    /**
     * Officeが事業者に存在していないと400が返るテスト.
     *
     * @param \BillingTester $I
     * @throws \Codeception\Exception\ModuleException
     */
    public function failWithBadRequestWhenOfficeIsNotInOrganization(BillingTester $I)
    {
        $I->wantTo('fail with Bad Request when Office is not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0];

        $I->sendPOST(
            'ltcs-provision-report-score-summary',
            $this->createParameters(
                $ltcsProvisionReport,
                ['officeId' => $this->examples->offices[1]->id]
            )
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['officeId' => ['正しい値を入力してください。']]]);
        $I->seeLogCount(0);
    }

    /**
     * Userが事業者に存在していないと400が返るテスト.
     *
     * @param \BillingTester $I
     * @throws \Codeception\Exception\ModuleException
     */
    public function failWithBadRequestWhenUserIsNotInOrganization(BillingTester $I)
    {
        $I->wantTo('fail with Bad Request when Office is not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0];

        $I->sendPOST(
            'ltcs-provision-report-score-summary',
            $this->createParameters(
                $ltcsProvisionReport,
                ['userId' => $this->examples->users[14]->id]
            )
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['userId' => ['正しい値を入力してください。']]]);
        $I->seeLogCount(0);
    }

    /**
     * サービス提供年月が不正な日付フォーマットの場合に400が返るテスト.
     *
     * @param \BillingTester $I
     * @throws \Codeception\Exception\ModuleException
     */
    public function failWithBadRequestWhenProvidedInIsInvalidFormat(BillingTester $I)
    {
        $I->wantTo('fail with Bad Request when providedIn is invalid format.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0];

        $I->sendPOST(
            'ltcs-provision-report-score-summary',
            $this->createParameters($ltcsProvisionReport, ['providedIn' => '2020-13'])
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['providedIn' => ['正しい日付を入力してください。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param \BillingTester $I
     * @throws \Codeception\Exception\ModuleException
     */
    public function failWithForbiddenWhenNotHavingPermission(BillingTester $I)
    {
        $I->wantTo('fail with forbidden when not having permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0];

        $I->sendPOST('ltcs-provision-report-score-summary', $this->createParameters($ltcsProvisionReport));

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * リクエスト用パラメータ生成.
     *
     * @param LtcsProvisionReport $ltcsProvisionReport
     * @param array $others ['officeId' | 'userId' | 'providedIn']
     * @return array
     */
    private function createParameters(
        LtcsProvisionReport $ltcsProvisionReport,
        array $others = []
    ): array {
        return [
            'entries' => Seq::fromArray($ltcsProvisionReport->entries)
                ->map(fn (LtcsProvisionReportEntry $entry): array => [
                    'ownExpenseProgramId' => $entry->ownExpenseProgramId,
                    'slot' => [
                        'start' => $entry->slot->start,
                        'end' => $entry->slot->end,
                    ],
                    'timeframe' => $entry->timeframe->value(),
                    'category' => $entry->category->value(),
                    'amounts' => Seq::fromArray($entry->amounts)
                        ->map(fn (LtcsProjectAmount $amount): array => [
                            'category' => $amount->category->value(),
                            'amount' => $amount->amount,
                        ])
                        ->toArray(),
                    'headcount' => $entry->headcount,
                    'serviceCode' => empty($entry->serviceCode) ? null : $entry->serviceCode->toString(),
                    'options' => Seq::fromArray($entry->options)
                        ->map(fn (ServiceOption $x): int => $x->value())
                        ->toArray(),
                    'note' => $entry->note,
                    'plans' => Seq::fromArray($entry->plans)
                        ->map(fn (Carbon $plan): string => $plan->toDateString())
                        ->toArray(),
                    'results' => Seq::fromArray($entry->results)
                        ->map(fn (Carbon $result): string => $result->toDateString())
                        ->toArray(),
                ])
                ->toArray(),
            'plan' => [
                'maxBenefitExcessScore' => 0,
                'maxBenefitQuotaExcessScore' => 0,
            ],
            'result' => [
                'maxBenefitExcessScore' => 0,
                'maxBenefitQuotaExcessScore' => 0,
            ],
            'officeId' => $others['officeId'] ?? $ltcsProvisionReport->officeId,
            'userId' => $others['userId'] ?? $ltcsProvisionReport->userId,
            'providedIn' => $others['providedIn'] ?? $ltcsProvisionReport->providedIn->format('Y-m'),
            'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::none()->value(),
            'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::addition2()->value(),
            'specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::none()->value(),
            'baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::none()->value(),
            'locationAddition' => LtcsOfficeLocationAddition::mountainousArea()->value(),
        ];
    }
}
