<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\DwsProvisionReport;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Billing\DwsBillingServiceReportAggregateGroup;
use Domain\Billing\DwsProvisionReportTimeSummaryItem;
use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\Shift\ServiceOption;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * DwsProvisionReport getTimeSummary のテスト.
 * POST /dws-provision-report-time-summary
 */
class GetDwsProvisionReportTimeSummaryCest extends DwsProvisionReportTest
{
    use ExamplesConsumer;

    // tests

    /**
     * API正常呼び出しテスト.
     *
     * @param \ApiTester $I
     * @throws \Codeception\Exception\ModuleException
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0];

        $I->sendPOST('dws-provision-report-time-summary', $this->createParameters($dwsProvisionReport));

        $I->seeResponseCodeIs(HttpCode::OK);

        $plan = DwsProvisionReportTimeSummaryItem::fromAssoc([
            DwsBillingServiceReportAggregateGroup::physicalCare()->value() => Decimal::fromInt(2_0000),
            DwsBillingServiceReportAggregateGroup::accompanyWithPhysicalCare()->value() => Decimal::zero(),
            DwsBillingServiceReportAggregateGroup::housework()->value() => Decimal::zero(),
            DwsBillingServiceReportAggregateGroup::accompany()->value() => Decimal::zero(),
            DwsBillingServiceReportAggregateGroup::visitingCareForPwsd()->value() => Decimal::zero(),
            DwsBillingServiceReportAggregateGroup::outingSupportForPwsd()->value() => Decimal::zero(),
        ]);
        $result = DwsProvisionReportTimeSummaryItem::fromAssoc([
            DwsBillingServiceReportAggregateGroup::physicalCare()->value() => Decimal::fromInt(2_0000),
            DwsBillingServiceReportAggregateGroup::accompanyWithPhysicalCare()->value() => Decimal::zero(),
            DwsBillingServiceReportAggregateGroup::housework()->value() => Decimal::zero(),
            DwsBillingServiceReportAggregateGroup::accompany()->value() => Decimal::zero(),
            DwsBillingServiceReportAggregateGroup::visitingCareForPwsd()->value() => Decimal::zero(),
            DwsBillingServiceReportAggregateGroup::outingSupportForPwsd()->value() => Decimal::zero(),
        ]);

        $expected = $this->domainToArray(compact('plan', 'result'));
        $I->seeResponseContainsJson($expected);
        $I->seeLogCount(0);
    }

    /**
     * Officeが事業者に存在していないと400が返るテスト.
     *
     * @param \ApiTester $I
     * @throws \Codeception\Exception\ModuleException
     */
    public function failWithBadRequestWhenOfficeIsNotInOrganization(ApiTester $I)
    {
        $I->wantTo('fail with Bad Request when Office is not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0];

        $I->sendPOST(
            'dws-provision-report-time-summary',
            $this->createParameters(
                $dwsProvisionReport,
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
     * @param \ApiTester $I
     * @throws \Codeception\Exception\ModuleException
     */
    public function failWithBadRequestWhenUserIsNotInOrganization(ApiTester $I)
    {
        $I->wantTo('fail with Bad Request when Office is not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0];

        $I->sendPOST(
            'dws-provision-report-time-summary',
            $this->createParameters(
                $dwsProvisionReport,
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
     * @param \ApiTester $I
     * @throws \Codeception\Exception\ModuleException
     */
    public function failWithBadRequestWhenProvidedInIsInvalidFormat(ApiTester $I)
    {
        $I->wantTo('fail with Bad Request when providedIn is invalid format.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0];

        $I->sendPOST(
            'dws-provision-report-time-summary',
            $this->createParameters($dwsProvisionReport, ['providedIn' => '2020-13'])
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['errors' => ['providedIn' => ['正しい日付を入力してください。']]]);
        $I->seeLogCount(0);
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param \ApiTester $I
     * @throws \Codeception\Exception\ModuleException
     */
    public function failWithForbiddenWhenNotHavingPermission(ApiTester $I)
    {
        $I->wantTo('fail with forbidden when not having permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0];

        $I->sendPOST('dws-provision-report-time-summary', $this->createParameters($dwsProvisionReport));

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * リクエスト用パラメータ生成.
     *
     * @param \Domain\ProvisionReport\DwsProvisionReport $dwsProvisionReport
     * @param array $others ['officeId' | 'userId' | 'providedIn']
     * @return array
     */
    private function createParameters(
        DwsProvisionReport $dwsProvisionReport,
        array $others = []
    ): array {
        $providedInString = $others['providedIn'] ?? $dwsProvisionReport->providedIn->format('Y-m');
        $providedIn = Carbon::canBeCreatedFromFormat($providedInString, 'Y-m')
            ? Carbon::parse($providedInString)
            : Carbon::now();
        return [
            'officeId' => $others['officeId'] ?? $dwsProvisionReport->officeId,
            'userId' => $others['userId'] ?? $dwsProvisionReport->userId,
            'providedIn' => $providedInString,
            'plans' => Seq::fromArray($dwsProvisionReport->plans)
                ->map(function (DwsProvisionReportItem $x) use ($providedIn): array {
                    $date = $x->schedule->date->setDate($providedIn->year, $providedIn->month, $x->schedule->date->day);
                    $start = $x->schedule->start->setDate($providedIn->year, $providedIn->month, $x->schedule->date->day);
                    $end = $x->schedule->end->setDate($providedIn->year, $providedIn->month, $x->schedule->date->day);
                    return [
                        'schedule' => [
                            'date' => $date->toDateString(),
                            'start' => $start,
                            'end' => $end,
                        ],
                        'category' => $x->category->value(),
                        'headcount' => $x->headcount,
                        'movingDurationMinutes' => $x->movingDurationMinutes,
                        'ownExpenseProgramId' => $x->ownExpenseProgramId,
                        'options' => Seq::fromArray($x->options)->map(fn (
                            ServiceOption $x
                        ): int => $x->value())->toArray(),
                        'note' => $x->note,
                    ];
                })
                ->toArray(),
            'results' => Seq::fromArray($dwsProvisionReport->results)
                ->map(function (DwsProvisionReportItem $x) use ($providedIn): array {
                    $date = $x->schedule->date->setDate($providedIn->year, $providedIn->month, $x->schedule->date->day);
                    $start = $x->schedule->start->setDate($providedIn->year, $providedIn->month, $x->schedule->date->day);
                    $end = $x->schedule->end->setDate($providedIn->year, $providedIn->month, $x->schedule->date->day);
                    return [
                        'schedule' => [
                            'date' => $date->toDateString(),
                            'start' => $start,
                            'end' => $end,
                        ],
                        'category' => $x->category->value(),
                        'headcount' => $x->headcount,
                        'movingDurationMinutes' => $x->movingDurationMinutes,
                        'ownExpenseProgramId' => $x->ownExpenseProgramId,
                        'options' => Seq::fromArray($x->options)->map(fn (
                            ServiceOption $x
                        ): int => $x->value())->toArray(),
                        'note' => $x->note,
                    ];
                })
                ->toArray(),
        ];
    }
}
