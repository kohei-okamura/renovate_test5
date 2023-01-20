<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Billing\LtcsProvisionReport;

use BillingTester;
use Codeception\Util\HttpCode;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\Office\LtcsBaseIncreaseSupportAddition;
use Domain\Office\LtcsOfficeLocationAddition;
use Domain\Office\LtcsSpecifiedTreatmentImprovementAddition;
use Domain\Office\LtcsTreatmentImprovementAddition;
use Domain\Project\LtcsProjectAmount;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportEntry;
use Domain\ServiceCode\ServiceCode;
use Domain\Shift\ServiceOption;
use Psr\Log\LogLevel;
use ScalikePHP\Seq;
use Tests\Billing\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * LtcsProvisionReport update のテスト.
 * PUT /ltcs-provision-reports/{officeId}/{userId}/{providedIn}
 */
class UpdateLtcsProvisionReportCest extends Test
{
    use ExamplesConsumer;

    // tests

    /**
     * API正常呼び出しテスト.
     *
     * @param BillingTester $I
     */
    public function succeedAPICall(BillingTester $I)
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0]->copy([
            'entries' => Seq::fromArray($this->examples->ltcsProvisionReports[0]->entries)
                ->map(fn (LtcsProvisionReportEntry $x): LtcsProvisionReportEntry => $x->copy([
                    'note' => '備考を更新',
                ]))
                ->toArray(),
        ]);

        $I->sendPut(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($ltcsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '介護保険サービス：予実が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $ltcsProvisionReport->id,
        ]);

        $returned = $I->grabResponseArray();
        $I->assertSame(
            $this->domainToArray($ltcsProvisionReport)['entries'],
            $returned['ltcsProvisionReport']['entries']
        );

        $I->sendGet("ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $stored = $I->grabResponseArray();
        $I->assertSame(
            $this->domainToArray($ltcsProvisionReport)['entries'],
            $stored['ltcsProvisionReport']['entries']
        );
    }

    /**
     * 事業所 ID、利用者 ID、サービス提供年月 が一致するデータが存在しない場合に新規登録されるテスト.
     *
     * @param BillingTester $I
     */
    public function createDataWhenDataDoesNotExistInDB(BillingTester $I)
    {
        $I->wantTo('create data when data does not exist in db');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $providedIn = '2021-11';
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0]->copy([
            'entries' => Seq::fromArray($this->examples->ltcsProvisionReports[0]->entries)
                ->map(fn (LtcsProvisionReportEntry $x): LtcsProvisionReportEntry => $x->copy([
                    'note' => '新規登録',
                ]))
                ->toArray(),
        ]);
        $params = $this->defaultParam($ltcsProvisionReport);

        $I->sendPUT(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$providedIn}",
            $params
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '介護保険サービス：予実が登録されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => '*',
        ]);
        $actual = $I->grabResponseArray();
        foreach ($actual['ltcsProvisionReport']['entries'] as $entry) {
            $I->assertSame('新規登録', $entry['note']);
        }
        $I->assertSame($params['specifiedOfficeAddition'], $actual['ltcsProvisionReport']['specifiedOfficeAddition']);
        $I->assertSame($params['treatmentImprovementAddition'], $actual['ltcsProvisionReport']['treatmentImprovementAddition']);
        $I->assertSame($params['specifiedTreatmentImprovementAddition'], $actual['ltcsProvisionReport']['specifiedTreatmentImprovementAddition']);
        $I->assertSame($params['baseIncreaseSupportAddition'], $actual['ltcsProvisionReport']['baseIncreaseSupportAddition']);
        $I->assertSame($params['locationAddition'], $actual['ltcsProvisionReport']['locationAddition']);
    }

    /**
     * 予定年月日なし実績年月日ありで更新できるテスト.
     *
     * @param BillingTester $I
     */
    public function succeedAPICallWhenSpecifyPlansEmpty(BillingTester $I)
    {
        $I->wantTo('succeed API call when specify plans empty');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0]->copy([
            'entries' => Seq::fromArray($this->examples->ltcsProvisionReports[0]->entries)
                ->map(fn (LtcsProvisionReportEntry $x): LtcsProvisionReportEntry => $x->copy([
                    'plans' => null,
                ]))
                ->toArray(),
        ]);

        $I->sendPUT(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($ltcsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '介護保険サービス：予実が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $ltcsProvisionReport->id,
        ]);

        $actual = $I->grabResponseArray();

        $I->sendGET("ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();
        $I->assertSame($expected, $actual);
    }

    /**
     * 予定年月日あり実績年月日なしで更新できるテスト.
     *
     * @param BillingTester $I
     */
    public function succeedAPICallWhenSpecifyResultsEmpty(BillingTester $I)
    {
        $I->wantTo('succeed API call when specify results empty');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0]->copy([
            'entries' => Seq::fromArray($this->examples->ltcsProvisionReports[0]->entries)
                ->map(fn (LtcsProvisionReportEntry $x): LtcsProvisionReportEntry => $x->copy([
                    'results' => null,
                ]))
                ->toArray(),
        ]);

        $I->sendPUT(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($ltcsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '介護保険サービス：予実が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $ltcsProvisionReport->id,
        ]);

        $actual = $I->grabResponseArray();

        $I->sendGET("ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $expected = $I->grabResponseArray();
        $I->assertSame($expected, $actual);
    }

    /**
     * 自費サービスを設定できるテスト.
     *
     * @param BillingTester $I
     */
    public function succeedAPICallWhenOwnExpenseProgramIsSet(BillingTester $I)
    {
        $I->wantTo('succeed API call when OwnExpenseProgram is set');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0]->copy([
            'entries' => Seq::fromArray($this->examples->ltcsProvisionReports[0]->entries)
                ->map(fn (LtcsProvisionReportEntry $x): LtcsProvisionReportEntry => $x->copy([
                    'ownExpenseProgramId' => $this->examples->ownExpensePrograms[0]->id,
                    'category' => LtcsProjectServiceCategory::ownExpense(),
                    'options' => [],
                    'serviceCode' => null,
                    'amounts' => [],
                ]))
                ->toArray(),
        ]);

        $I->sendPut(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($ltcsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '介護保険サービス：予実が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $ltcsProvisionReport->id,
        ]);

        $returned = $I->grabResponseArray();
        $I->assertSame(
            $this->domainToArray($ltcsProvisionReport)['entries'],
            $returned['ltcsProvisionReport']['entries']
        );

        $I->sendGet("ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $stored = $I->grabResponseArray();
        $I->assertSame(
            $this->domainToArray($ltcsProvisionReport)['entries'],
            $stored['ltcsProvisionReport']['entries']
        );
    }

    /**
     * 他事業所の自費サービスを指定した場合に400が返るテスト.
     *
     * @param BillingTester $I
     */
    public function failWithBadRequestWhenOwnExpenseProgramIdBelongsToOtherOffice(BillingTester $I)
    {
        $I->wantTo('fail with Bad Request when ownExpenseProgramId belongs to other office');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0]->copy([
            'entries' => Seq::fromArray($this->examples->ltcsProvisionReports[0]->entries)
                ->map(fn (LtcsProvisionReportEntry $x): LtcsProvisionReportEntry => $x->copy([
                    'ownExpenseProgramId' => $this->examples->ownExpensePrograms[2]->id,
                    'category' => LtcsProjectServiceCategory::ownExpense(),
                    'options' => [],
                ]))
                ->toArray(),
        ]);

        $I->sendPut(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($ltcsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson([
            'errors' => [
                'entries.0.ownExpenseProgramId' => ['異なる事業所の自費サービスが含まれています。'],
                'entries.1.ownExpenseProgramId' => ['異なる事業所の自費サービスが含まれています。'],
            ],
        ]);
        $I->seeLogCount(0);
    }

    /**
     * 「サービスオプション」が「介護保険サービス：予実」の「サービスオプション」として不正の場合400が返るテスト.
     *
     * @param BillingTester $I
     */
    public function failWithBadRequestWhenServiceOptionIsInvalid(BillingTester $I)
    {
        $I->wantTo('fail with BadRequest when service option is invalid');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0]->copy([
            'entries' => [$this->examples->ltcsProvisionReports[0]->entries[0]->copy([
                'category' => LtcsProjectServiceCategory::physicalCare(),
                'options' => [ServiceOption::notificationEnabled()],
            ])],
        ]);

        $I->sendPUT(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($ltcsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
        $I->seeResponseContainsJson(['errors' => ['entries.0.options.0' => ['正しいサービスオプションを指定してください。']]]);
    }

    /**
     * 予定年月日なし実績年月日なしで400が返るテスト.
     *
     * @param BillingTester $I
     */
    public function failWithBadRequestWhenSpecifyPlansEmptyAndResultsEmpty(BillingTester $I)
    {
        $I->wantTo('fail with BadRequest API call when specify results empty');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0]->copy([
            'entries' => Seq::fromArray($this->examples->ltcsProvisionReports[0]->entries)
                ->map(fn (LtcsProvisionReportEntry $x): LtcsProvisionReportEntry => $x->copy([
                    'plans' => null,
                    'results' => null,
                ]))
                ->toArray(),
        ]);

        $I->sendPUT(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($ltcsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
        $I->seeResponseContainsJson(['errors' => ['entries.0.results' => ['予定年月日が存在しない時、実績年月日は必ず入力してください。']]]);
    }

    /**
     * 予実が確定済みの場合に400が返るテスト.
     *
     * @param BillingTester $I
     */
    public function failWhenEntityIsFixed(BillingTester $I)
    {
        $I->wantTo('fail when entity is fixed.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[3]->copy([
            'entries' => Seq::fromArray($this->examples->ltcsProvisionReports[0]->entries)
                ->map(fn (LtcsProvisionReportEntry $x): LtcsProvisionReportEntry => $x->copy([
                    'note' => '備考を更新',
                ]))
                ->toArray(),
        ]);

        $I->sendPUT(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($ltcsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
        $I->seeResponseContainsJson(['errors' => ['entries' => ['確定済みの予実は編集できません。']]]);
    }

    /**
     * 予定が重複する場合に400が返るテスト.
     *
     * @param BillingTester $I
     */
    public function failWithBadRequestWhenPlansAreOverlapped(BillingTester $I)
    {
        $I->wantTo('fail with bad request when plans are overlapped');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0]->copy([
            'entries' => [
                $this->examples->ltcsProvisionReports[0]->entries[0]->copy([
                    'slot' => CarbonRange::create([
                        'start' => '12:00',
                        'end' => '18:00',
                    ]),
                    'plans' => [Carbon::parse('2020-10-10')],
                ]),
                $this->examples->ltcsProvisionReports[0]->entries[0]->copy([
                    'slot' => CarbonRange::create([
                        'start' => '15:00',
                        'end' => '19:00',
                    ]),
                    'plans' => [Carbon::parse('2020-10-10')],
                ]),
            ],
        ]);

        $I->sendPUT(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($ltcsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
        $I->seeResponseContainsJson(['errors' => ['entries.0.plans.0' => ['予定が重複しています。'], 'entries.1.plans.0' => ['予定が重複しています。']]]);
    }

    /**
     * 実績が重複する場合に400が返るテスト.
     *
     * @param BillingTester $I
     */
    public function failWithBadRequestWhenResultsAreOverlapped(BillingTester $I)
    {
        $I->wantTo('fail with bad request when results are overlapped');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0]->copy([
            'entries' => [
                $this->examples->ltcsProvisionReports[0]->entries[0]->copy([
                    'slot' => CarbonRange::create([
                        'start' => '12:00',
                        'end' => '18:00',
                    ]),
                    'results' => [Carbon::parse('2020-10-10')],
                ]),
                $this->examples->ltcsProvisionReports[0]->entries[0]->copy([
                    'slot' => CarbonRange::create([
                        'start' => '15:00',
                        'end' => '19:00',
                    ]),
                    'results' => [Carbon::parse('2020-10-10')],
                ]),
            ],
        ]);

        $I->sendPUT(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($ltcsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
        $I->seeResponseContainsJson(['errors' => ['entries.0.results.0' => ['実績が重複しています。'], 'entries.1.results.0' => ['実績が重複しています。']]]);
    }

    /**
     * サービス区分が「自費サービス」以外で「サービスコード」「サービス提供量」が未指定の場合400が返るテスト.
     *
     * @param BillingTester $I
     */
    public function failWithBadRequestWhenRequiredParameterForServicesIsNotPassed(BillingTester $I)
    {
        $I->wantTo('fail with bad request when required parameter for services is not passed');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0]->copy([
            'entries' => Seq::fromArray($this->examples->ltcsProvisionReports[0]->entries)
                ->map(fn (LtcsProvisionReportEntry $x): LtcsProvisionReportEntry => $x->copy([
                    'category' => LtcsProjectServiceCategory::physicalCare(),
                    'serviceCode' => null,
                    'amounts' => [],
                ]))
                ->toArray(),
        ]);

        $I->sendPUT(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($ltcsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
        $I->seeResponseContainsJson(
            [
                'errors' => [
                    'entries.0.serviceCode' => ['入力してください。'],
                    'entries.0.amounts' => ['入力してください。'],
                ],
            ]
        );
    }

    /**
     * サービス区分が「自費サービス」で「サービスコード」「サービス提供量」が指定されている場合400が返るテスト.
     *
     * @param BillingTester $I
     */
    public function failWithBadRequestWhenProhibitionParameterOfOwnExpenseIsPassed(BillingTester $I)
    {
        $I->wantTo('fail with bad request when prohibition parameter of own expense is passed');

        $staff = $this->examples->staffs[0];
        $category = LtcsProjectServiceCategory::ownExpense();
        $I->actingAs($staff);
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0]->copy([
            'entries' => Seq::fromArray($this->examples->ltcsProvisionReports[0]->entries)
                ->map(fn (LtcsProvisionReportEntry $x): LtcsProvisionReportEntry => $x->copy([
                    'ownExpenseProgramId' => $this->examples->ownExpensePrograms[0]->id,
                    'category' => $category,
                    'options' => [],
                    'serviceCode' => ServiceCode::fromString('123456'),
                    'amounts' => [LtcsProjectAmount::create(['category' => $category, 'amount' => 10000])],
                ]))
                ->toArray(),
        ]);

        $I->sendPUT(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($ltcsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
        $I->seeResponseContainsJson(
            [
                'errors' => [
                    'entries.0.serviceCode' => ['入力しないでください。'],
                    'entries.0.amounts' => ['入力しないでください。'],
                ],
            ]
        );
    }

    /**
     * 事業所IDが文字列の場合に404が返るテスト.
     *
     * @param BillingTester $I
     */
    public function failWithNotFoundWhenOfficeIdIsString(BillingTester $I)
    {
        $I->wantTo('fail with Not Found when officeId is string.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $officeId = 'error';
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0]->copy([
            'entries' => Seq::fromArray($this->examples->ltcsProvisionReports[0]->entries)
                ->map(fn (LtcsProvisionReportEntry $x): LtcsProvisionReportEntry => $x->copy([
                    'note' => '備考を更新',
                ]))
                ->toArray(),
        ]);
        $I->sendPUT(
            "ltcs-provision-reports/{$officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($ltcsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(0);
    }

    /**
     * 利用者IDが文字列の場合に404が返るテスト.
     *
     * @param BillingTester $I
     */
    public function failWithNotFoundWhenUserIdIsString(BillingTester $I)
    {
        $I->wantTo('fail with Not Found when userId is string.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $userId = 'error';
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0]->copy([
            'entries' => Seq::fromArray($this->examples->ltcsProvisionReports[0]->entries)
                ->map(fn (LtcsProvisionReportEntry $x): LtcsProvisionReportEntry => $x->copy([
                    'note' => '備考を更新',
                ]))
                ->toArray(),
        ]);
        $I->sendPUT(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($ltcsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(0);
    }

    /**
     * サービス提供年月が不正な日付フォーマットの場合に404が返るテスト.
     *
     * @param BillingTester $I
     */
    public function failWithNotFoundWhenProvidedInIsInvalidFormat(BillingTester $I)
    {
        $I->wantTo('fail with Not Found when providedIn is invalid format.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $providedIn = '2020-13';
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0]->copy([
            'entries' => Seq::fromArray($this->examples->ltcsProvisionReports[0]->entries)
                ->map(fn (LtcsProvisionReportEntry $x): LtcsProvisionReportEntry => $x->copy([
                    'note' => '備考を更新',
                ]))
                ->toArray(),
        ]);
        $I->sendPUT(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$providedIn}",
            $this->defaultParam($ltcsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(0);
    }

    /**
     * アクセス可能な事業所でない場合に404が返るテスト.
     *
     * @param BillingTester $I
     */
    public function failWithNotFoundWhenSpecifyNotAccessibleOffice(BillingTester $I)
    {
        $I->wantTo('fail with Not Found when specify not accessible office.');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[5]->copy([
            'entries' => Seq::fromArray($this->examples->ltcsProvisionReports[0]->entries)
                ->map(fn (LtcsProvisionReportEntry $x): LtcsProvisionReportEntry => $x->copy([
                    'note' => '備考を更新',
                ]))
                ->toArray(),
        ]);

        $I->sendPUT(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($ltcsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Office[{$ltcsProvisionReport->officeId}] is not found");
    }

    /**
     * アクセス可能な利用者でない場合に404が返るテスト.
     *
     * @param BillingTester $I
     */
    public function failWithNotFoundWhenSpecifyNotAccessibleUser(BillingTester $I)
    {
        $I->wantTo('fail with Not Found when specify not accessible user.');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0]->copy([
            'entries' => Seq::fromArray($this->examples->ltcsProvisionReports[0]->entries)
                ->map(fn (LtcsProvisionReportEntry $x): LtcsProvisionReportEntry => $x->copy([
                    'note' => '備考を更新',
                ]))
                ->toArray(),
        ]);

        $I->sendPUT(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$this->examples->users[1]->id}/{$ltcsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($ltcsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$this->examples->users[1]->id}] is not found");
    }

    /**
     * 事業所が事業者に存在していないと404が返るテスト.
     *
     * @param \BillingTester $I
     */
    public function failWithNotFoundWhenOfficeIsNotInOrganization(BillingTester $I)
    {
        $I->wantTo('fail with NotFound when Office is not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0]->copy([
            'entries' => Seq::fromArray($this->examples->ltcsProvisionReports[0]->entries)
                ->map(fn (LtcsProvisionReportEntry $x): LtcsProvisionReportEntry => $x->copy([
                    'note' => '備考を更新',
                ]))
                ->toArray(),
        ]);

        $I->sendPUT(
            "ltcs-provision-reports/{$this->examples->offices[1]->id}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($ltcsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Office[{$this->examples->offices[1]->id}] is not found");
    }

    /**
     * 利用者が事業者に存在していないと404が返るテスト.
     *
     * @param \BillingTester $I
     */
    public function failWithNotFoundWhenUserIsNotInOrganization(BillingTester $I)
    {
        $I->wantTo('fail with NotFound when User is not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0]->copy([
            'entries' => Seq::fromArray($this->examples->ltcsProvisionReports[0]->entries)
                ->map(fn (LtcsProvisionReportEntry $x): LtcsProvisionReportEntry => $x->copy([
                    'note' => '備考を更新',
                ]))
                ->toArray(),
        ]);

        $I->sendPUT(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$this->examples->users[14]->id}/{$ltcsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($ltcsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$this->examples->users[14]->id}] is not found");
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param BillingTester $I
     */
    public function failWithForbiddenWhenNotHavingPermission(BillingTester $I)
    {
        $I->wantTo('fail with forbidden when not having permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0]->copy([
            'entries' => Seq::fromArray($this->examples->ltcsProvisionReports[0]->entries)
                ->map(fn (LtcsProvisionReportEntry $x): LtcsProvisionReportEntry => $x->copy([
                    'note' => '備考を更新',
                ]))
                ->toArray(),
        ]);
        $I->sendPUT(
            "ltcs-provision-reports/{$ltcsProvisionReport->officeId}/{$ltcsProvisionReport->userId}/{$ltcsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($ltcsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * リクエスト用パラメータ生成.
     *
     * @param LtcsProvisionReport $ltcsProvisionReport
     * @return array
     */
    private function defaultParam(LtcsProvisionReport $ltcsProvisionReport): array
    {
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
            'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::none()->value(),
            'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::none()->value(),
            'specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::none()->value(),
            'baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::none()->value(),
            'locationAddition' => LtcsOfficeLocationAddition::none()->value(),
            'plan' => [
                'maxBenefitExcessScore' => 0,
                'maxBenefitQuotaExcessScore' => 0,
            ],
            'result' => [
                'maxBenefitExcessScore' => 0,
                'maxBenefitQuotaExcessScore' => 0,
            ],
        ];
    }
}
