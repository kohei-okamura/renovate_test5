<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Api\DwsProvisionReport;

use ApiTester;
use Codeception\Util\HttpCode;
use Domain\Common\Carbon;
use Domain\Common\Schedule;
use Domain\Project\DwsProjectServiceCategory;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Domain\Shift\ServiceOption;
use Psr\Log\LogLevel;
use ScalikePHP\Seq;
use Tests\Api\Mixins\TransactionMixin;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * DwsProvisionReport update のテスト.
 * PUT /dws-provision-reports/{officeId}/{userId}/{providedIn}
 */
class UpdateDwsProvisionReportCest extends DwsProvisionReportTest
{
    use ExamplesConsumer;
    use TransactionMixin;

    // tests

    /**
     * API正常呼び出しテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICall(ApiTester $I)
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsProvisionReport = $this->copyEntity($this->examples->dwsProvisionReports[0]);

        $I->sendPut(
            "dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($dwsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '障害福祉サービス：予実が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $dwsProvisionReport->id,
        ]);

        $returned = $I->grabResponseArray();
        $I->assertSame(
            ['dwsProvisionReport' => $this->domainToArray($dwsProvisionReport->copy(['updatedAt' => Carbon::now()]))],
            $returned
        );

        $I->sendGet("dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $stored = $I->grabResponseArray();
        $I->assertSame(
            ['dwsProvisionReport' => $this->domainToArray($dwsProvisionReport->copy(['updatedAt' => Carbon::now()]))],
            $stored
        );
    }

    /**
     * ルートパラメーターに一致するデータが存在しない場合に新規登録されるテスト.
     *
     * @param ApiTester $I
     */
    public function createDataWhenDataDoesNotExistInDB(ApiTester $I)
    {
        $I->wantTo('create data when data does not exist in db');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $providedIn = '2099-01';
        $dwsProvisionReport = $this->copyEntity($this->examples->dwsProvisionReports[0]);

        $I->sendPUT(
            "dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$providedIn}",
            $this->defaultParam($dwsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '障害福祉サービス：予実が登録されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => '*',
        ]);
        $actual = $I->grabResponseArray();
        $I->assertSame(DwsProvisionReportStatus::inProgress()->value(), $actual['dwsProvisionReport']['status']);
    }

    /**
     * 自費サービスを設定できるテスト.
     *
     * @param ApiTester $I
     */
    public function succeedAPICallWhenOwnExpenseProgramIsSet(ApiTester $I)
    {
        $I->wantTo('succeed API call when OwnExpenseProgram is set');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0]->copy([
            'plans' => [$this->examples->dwsProvisionReports[0]->plans[0]->copy([
                'category' => DwsProjectServiceCategory::ownExpense(),
                'ownExpenseProgramId' => $this->examples->ownExpensePrograms[0]->id,
                'options' => [],
            ])],
            'results' => [$this->examples->dwsProvisionReports[0]->results[0]->copy([
                'category' => DwsProjectServiceCategory::ownExpense(),
                'ownExpenseProgramId' => $this->examples->ownExpensePrograms[1]->id,
                'options' => [],
            ])],
        ]);

        $I->sendPUT(
            "dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($dwsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::INFO, '障害福祉サービス：予実が更新されました', [
            'organizationId' => $staff->organizationId,
            'staffId' => $staff->id,
            'id' => $dwsProvisionReport->id,
        ]);

        $returned = $I->grabResponseArray();
        $I->assertSame(
            ['dwsProvisionReport' => $this->domainToArray($dwsProvisionReport->copy(['updatedAt' => Carbon::now()]))],
            $returned
        );

        $I->sendGet("dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}");
        $I->seeResponseCodeIs(HttpCode::OK);
        $stored = $I->grabResponseArray();
        $I->assertSame(
            ['dwsProvisionReport' => $this->domainToArray($dwsProvisionReport->copy(['updatedAt' => Carbon::now()]))],
            $stored
        );
    }

    /**
     * 他事業所の自費サービスを指定した場合に400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestWhenOwnExpenseProgramIdBelongsToOtherOffice(ApiTester $I)
    {
        $I->wantTo('fail with Bad Request when ownExpenseProgramId belongs to other office');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0]->copy([
            'plans' => [$this->examples->dwsProvisionReports[0]->plans[0]->copy([
                'category' => DwsProjectServiceCategory::ownExpense(),
                'ownExpenseProgramId' => $this->examples->ownExpensePrograms[2]->id,
                'options' => [],
            ])],
        ]);

        $I->sendPut(
            "dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($dwsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson([
            'errors' => [
                'plans.0.ownExpenseProgramId' => ['異なる事業所の自費サービスが含まれています。'],
            ],
        ]);
        $I->seeLogCount(0);
    }

    /**
     * 予実が確定済みの場合に400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWhenEntityIsFixed(ApiTester $I)
    {
        $I->wantTo('fail when entity is fixed.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsProvisionReport = $this->copyEntity($this->examples->dwsProvisionReports[3]);

        $I->sendPUT(
            "dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($dwsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeLogCount(0);
        $I->seeResponseContainsJson(['errors' => ['plans' => ['確定済みの予実は編集できません。']]]);
    }

    /**
     * 「サービスオプション」が「障害福祉サービス：予実」の「サービスオプション」として不正の場合に400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestWhenServiceOptionIsInvalidForDwsProvisionReport(ApiTester $I)
    {
        $I->wantTo('fail with BadRequest when ServiceOption is invalid for DwsProvisionReport');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsProvisionReport = $this->copyEntity($this->examples->dwsProvisionReports[0])->copy([
            'plans' => [
                $this->examples->dwsProvisionReports[0]->plans[0]->copy([
                    'category' => DwsProjectServiceCategory::physicalCare(),
                    'options' => [ServiceOption::notificationEnabled()],
                ]),
            ],
            'results' => [
                $this->examples->dwsProvisionReports[0]->plans[0]->copy([
                    'category' => DwsProjectServiceCategory::physicalCare(),
                    'options' => [ServiceOption::notificationEnabled()],
                ]),
            ],
        ]);

        $I->sendPUT(
            "dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($dwsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson([
            'errors' => [
                'plans.0.options.0' => ['正しいサービスオプションを指定してください。'],
                'results.0.options.0' => ['正しいサービスオプションを指定してください。'],
            ],
        ]);
        $I->seeLogCount(0);
    }

    /**
     * 時間帯が完全に一致する予定が存在する場合に400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestWhenPlansDuplicated(ApiTester $I)
    {
        $I->wantTo('fail with bad request when plans duplicated');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0]
            ->copy([
                'plans' => [
                    DwsProvisionReportItem::create([
                        'schedule' => Schedule::create([
                            'date' => Carbon::parse('2020-10-10'),
                            'start' => Carbon::parse('2020-10-10T12:00:00+0900'),
                            'end' => Carbon::parse('2020-10-10T17:00:00+0900'),
                        ]),
                        'category' => $this->examples->dwsProvisionReports[0]->plans[0]->category,
                        'headcount' => 1,
                        'movingDurationMinutes' => 0,
                        'options' => $this->examples->dwsProvisionReports[0]->plans[0]->options,
                        'note' => '',
                    ]),
                    DwsProvisionReportItem::create([
                        'schedule' => Schedule::create([
                            'date' => Carbon::parse('2020-10-10'),
                            'start' => Carbon::parse('2020-10-10T12:00:00+0900'),
                            'end' => Carbon::parse('2020-10-10T17:00:00+0900'),
                        ]),
                        'category' => $this->examples->dwsProvisionReports[0]->plans[0]->category,
                        'headcount' => 1,
                        'movingDurationMinutes' => 0,
                        'options' => $this->examples->dwsProvisionReports[0]->plans[0]->options,
                        'note' => '',
                    ]),
                ],
            ]);

        $I->sendPUT(
            "dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($dwsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson([
            'errors' => [
                'plans.0.schedule' => ['時間帯が完全に一致する予定が存在します。'],
                'plans.1.schedule' => ['時間帯が完全に一致する予定が存在します。'],
            ],
        ]);
        $I->seeLogCount(0);
    }

    /**
     * 時間帯が完全に一致する実績が存在する場合に400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestWhenResultsDuplicated(ApiTester $I)
    {
        $I->wantTo('fail with bad request when results duplicated');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0]
            ->copy([
                'results' => [
                    DwsProvisionReportItem::create([
                        'schedule' => Schedule::create([
                            'date' => Carbon::parse('2020-10-10'),
                            'start' => Carbon::parse('2020-10-10T12:00:00+0900'),
                            'end' => Carbon::parse('2020-10-10T17:00:00+0900'),
                        ]),
                        'category' => $this->examples->dwsProvisionReports[0]->results[0]->category,
                        'headcount' => 1,
                        'movingDurationMinutes' => 0,
                        'options' => $this->examples->dwsProvisionReports[0]->results[0]->options,
                        'note' => '',
                    ]),
                    DwsProvisionReportItem::create([
                        'schedule' => Schedule::create([
                            'date' => Carbon::parse('2020-10-10'),
                            'start' => Carbon::parse('2020-10-10T12:00:00+0900'),
                            'end' => Carbon::parse('2020-10-10T17:00:00+0900'),
                        ]),
                        'category' => $this->examples->dwsProvisionReports[0]->results[0]->category,
                        'headcount' => 1,
                        'movingDurationMinutes' => 0,
                        'options' => $this->examples->dwsProvisionReports[0]->results[0]->options,
                        'note' => '',
                    ]),
                ],
            ]);

        $I->sendPUT(
            "dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($dwsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson([
            'errors' => [
                'results.0.schedule' => ['時間帯が完全に一致する実績が存在します。'],
                'results.1.schedule' => ['時間帯が完全に一致する実績が存在します。'],
            ],
        ]);
        $I->seeLogCount(0);
    }

    /**
     * 提供人数が3人以上となるような時間帯の重複が予定に存在する場合に400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestWhenPlansOverlapped(ApiTester $I)
    {
        $I->wantTo('fail with bad request when plans overlapped');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0]
            ->copy([
                'plans' => [
                    DwsProvisionReportItem::create([
                        'schedule' => Schedule::create([
                            'date' => Carbon::parse('2020-10-10'),
                            'start' => Carbon::parse('2020-10-10T12:00:00+0900'),
                            'end' => Carbon::parse('2020-10-10T17:00:00+0900'),
                        ]),
                        'category' => $this->examples->dwsProvisionReports[0]->plans[0]->category,
                        'headcount' => 1,
                        'movingDurationMinutes' => 0,
                        'options' => $this->examples->dwsProvisionReports[0]->plans[0]->options,
                        'note' => '',
                    ]),
                    DwsProvisionReportItem::create([
                        'schedule' => Schedule::create([
                            'date' => Carbon::parse('2020-10-10'),
                            'start' => Carbon::parse('2020-10-10T12:00:00+0900'),
                            'end' => Carbon::parse('2020-10-10T18:00:00+0900'),
                        ]),
                        'category' => $this->examples->dwsProvisionReports[0]->plans[0]->category,
                        'headcount' => 2,
                        'movingDurationMinutes' => 0,
                        'options' => $this->examples->dwsProvisionReports[0]->plans[0]->options,
                        'note' => '',
                    ]),
                ],
            ]);

        $I->sendPUT(
            "dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($dwsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson([
            'errors' => [
                'plans.0.schedule' => ['時間帯が重複する予定が存在します。'],
                'plans.1.schedule' => ['時間帯が重複する予定が存在します。'],
            ],
        ]);
        $I->seeLogCount(0);
    }

    /**
     * 提供人数が3人以上となるような時間帯の重複が実績に存在する場合に400が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithBadRequestWhenResultsOverlapped(ApiTester $I)
    {
        $I->wantTo('fail with bad request when results overlapped');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0]
            ->copy([
                'results' => [
                    DwsProvisionReportItem::create([
                        'schedule' => Schedule::create([
                            'date' => Carbon::parse('2020-10-10'),
                            'start' => Carbon::parse('2020-10-10T12:00:00+0900'),
                            'end' => Carbon::parse('2020-10-10T17:00:00+0900'),
                        ]),
                        'category' => $this->examples->dwsProvisionReports[0]->results[0]->category,
                        'headcount' => 1,
                        'movingDurationMinutes' => 0,
                        'options' => $this->examples->dwsProvisionReports[0]->results[0]->options,
                        'note' => '',
                    ]),
                    DwsProvisionReportItem::create([
                        'schedule' => Schedule::create([
                            'date' => Carbon::parse('2020-10-10'),
                            'start' => Carbon::parse('2020-10-10T12:00:00+0900'),
                            'end' => Carbon::parse('2020-10-10T18:00:00+0900'),
                        ]),
                        'category' => $this->examples->dwsProvisionReports[0]->results[0]->category,
                        'headcount' => 2,
                        'movingDurationMinutes' => 0,
                        'options' => $this->examples->dwsProvisionReports[0]->results[0]->options,
                        'note' => '',
                    ]),
                ],
            ]);

        $I->sendPUT(
            "dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($dwsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson([
            'errors' => [
                'results.0.schedule' => ['時間帯が重複する実績が存在します。'],
                'results.1.schedule' => ['時間帯が重複する実績が存在します。'],
            ],
        ]);
        $I->seeLogCount(0);
    }

    /**
     * 事業所IDが一致しない場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundWhenTheDataDoesNotMatchOfficeId(ApiTester $I)
    {
        $I->wantTo('fail with Not Found when the data does not match officeId.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $officeId = self::NOT_EXISTING_ID;
        $dwsProvisionReport = $this->copyEntity($this->examples->dwsProvisionReports[0]);

        $I->sendPUT(
            "dws-provision-reports/{$officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($dwsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Office[{$officeId}] is not found");
    }

    /**
     * 事業所IDが文字列の場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundWhenOfficeIdIsString(ApiTester $I)
    {
        $I->wantTo('fail with Not Found when officeId is string.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $officeId = 'error';
        $dwsProvisionReport = $this->copyEntity($this->examples->dwsProvisionReports[0]);

        $I->sendPUT(
            "dws-provision-reports/{$officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($dwsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(0);
    }

    /**
     * 利用者IDが一致しない場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundWhenTheDataDoesNotMatchUserId(ApiTester $I)
    {
        $I->wantTo('fail with Not Found when the data does not match userId.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $userId = self::NOT_EXISTING_ID;
        $dwsProvisionReport = $this->copyEntity($this->examples->dwsProvisionReports[0]);

        $I->sendPUT(
            "dws-provision-reports/{$dwsProvisionReport->officeId}/{$userId}/{$dwsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($dwsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$userId}] is not found");
    }

    /**
     * 利用者IDが文字列の場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundWhenUserIdIsString(ApiTester $I)
    {
        $I->wantTo('fail with Not Found when userId is string.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $userId = 'error';
        $dwsProvisionReport = $this->copyEntity($this->examples->dwsProvisionReports[0]);

        $I->sendPUT(
            "dws-provision-reports/{$dwsProvisionReport->officeId}/{$userId}/{$dwsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($dwsProvisionReport)
        );
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(0);
    }

    /**
     * サービス提供年月が不正な日付フォーマットの場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundWhenProvidedInIsInvalidFormat(ApiTester $I)
    {
        $I->wantTo('fail with Not Found when providedIn is invalid format.');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $providedIn = '2020-13';
        $dwsProvisionReport = $this->copyEntity($this->examples->dwsProvisionReports[0]);

        $I->sendPUT(
            "dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$providedIn}",
            $this->defaultParam($dwsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(0);
    }

    /**
     * アクセス可能な事業所でない場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundWhenSpecifyNotAccessibleOffice(ApiTester $I)
    {
        $I->wantTo('fail with Not Found when specify not accessible office.');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $dwsProvisionReport = $this->copyEntity($this->examples->dwsProvisionReports[5]);

        $I->sendPUT(
            "dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($dwsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Office[{$dwsProvisionReport->officeId}] is not found");
    }

    /**
     * アクセス可能な利用者でない場合に404が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithNotFoundWhenSpecifyNotAccessibleUser(ApiTester $I)
    {
        $I->wantTo('fail with Not Found when specify not accessible user.');

        $staff = $this->examples->staffs[28];
        $I->actingAs($staff);
        $dwsProvisionReport = $this->copyEntity($this->examples->dwsProvisionReports[0]);

        $I->sendPUT(
            "dws-provision-reports/{$dwsProvisionReport->officeId}/{$this->examples->users[1]->id}/{$dwsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($dwsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$this->examples->users[1]->id}] is not found");
    }

    /**
     * 事業所が事業者に存在していないと404が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithNotFoundWhenOfficeIsNotInOrganization(ApiTester $I)
    {
        $I->wantTo('fail with NotFound when Office is not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsProvisionReport = $this->copyEntity($this->examples->dwsProvisionReports[0]);

        $I->sendPUT(
            "dws-provision-reports/{$this->examples->offices[1]->id}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($dwsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "Office[{$this->examples->offices[1]->id}] is not found");
    }

    /**
     * 利用者が事業者に存在していないと404が返るテスト.
     *
     * @param \ApiTester $I
     */
    public function failWithNotFoundWhenUserIsNotInOrganization(ApiTester $I)
    {
        $I->wantTo('fail with NotFound when User is not in Organization');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $dwsProvisionReport = $this->copyEntity($this->examples->dwsProvisionReports[0]);

        $I->sendPUT(
            "dws-provision-reports/{$dwsProvisionReport->officeId}/{$this->examples->users[14]->id}/{$dwsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($dwsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, "User[{$this->examples->users[14]->id}] is not found");
    }

    /**
     * 権限のないスタッフによる操作で403が返るテスト.
     *
     * @param ApiTester $I
     */
    public function failWithForbiddenWhenNotHavingPermission(ApiTester $I)
    {
        $I->wantTo('fail with forbidden when not having permission');

        $staff = $this->examples->staffs[29];
        $I->actingAs($staff);
        $dwsProvisionReport = $this->copyEntity($this->examples->dwsProvisionReports[0]);

        $I->sendPUT(
            "dws-provision-reports/{$dwsProvisionReport->officeId}/{$dwsProvisionReport->userId}/{$dwsProvisionReport->providedIn->format('Y-m')}",
            $this->defaultParam($dwsProvisionReport)
        );

        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeLogCount(0);
    }

    /**
     * リクエストパラメータ組み立て.
     *
     * @param \Domain\ProvisionReport\DwsProvisionReport $dwsProvisionReport
     * @return array
     */
    private function defaultParam(DwsProvisionReport $dwsProvisionReport): array
    {
        return [
            'plans' => Seq::fromArray($dwsProvisionReport->plans)
                ->map(fn (DwsProvisionReportItem $x): array => [
                    'schedule' => [
                        'date' => $x->schedule->date->toDateString(),
                        'start' => $x->schedule->start,
                        'end' => $x->schedule->end,
                    ],
                    'category' => $x->category->value(),
                    'headcount' => $x->headcount,
                    'movingDurationMinutes' => $x->movingDurationMinutes,
                    'ownExpenseProgramId' => $x->ownExpenseProgramId,
                    'options' => Seq::fromArray($x->options)->map(fn (ServiceOption $x): int => $x->value())->toArray(),
                    'note' => $x->note,
                ])
                ->toArray(),
            'results' => Seq::fromArray($dwsProvisionReport->results)
                ->map(fn (DwsProvisionReportItem $x): array => [
                    'schedule' => [
                        'date' => $x->schedule->date->toDateString(),
                        'start' => $x->schedule->start,
                        'end' => $x->schedule->end,
                    ],
                    'category' => $x->category->value(),
                    'headcount' => $x->headcount,
                    'movingDurationMinutes' => $x->movingDurationMinutes,
                    'ownExpenseProgramId' => $x->ownExpenseProgramId,
                    'options' => Seq::fromArray($x->options)->map(fn (ServiceOption $x): int => $x->value())->toArray(),
                    'note' => $x->note,
                ])
                ->toArray(),
        ];
    }

    /**
     * 更新用Entityを生成.
     *
     * @param \Domain\ProvisionReport\DwsProvisionReport $dwsProvisionReport
     * @return \Domain\ProvisionReport\DwsProvisionReport
     */
    private function copyEntity(DwsProvisionReport $dwsProvisionReport): DwsProvisionReport
    {
        return $dwsProvisionReport->copy([
            'plans' => [
                DwsProvisionReportItem::create([
                    'schedule' => $this->examples->dwsProvisionReports[0]->plans[0]->schedule,
                    'category' => $this->examples->dwsProvisionReports[0]->plans[0]->category,
                    'headcount' => $this->examples->dwsProvisionReports[0]->plans[0]->headcount,
                    'movingDurationMinutes' => 0,
                    'options' => $this->examples->dwsProvisionReports[0]->plans[0]->options,
                    'note' => 'update',
                ]),
            ],
        ]);
    }
}
