<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\GetDwsProvisionReportTimeSummaryRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Common\Carbon;
use Domain\Common\Schedule;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\Project\DwsProjectServiceCategory;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\Shift\ServiceOption;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\GetDwsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupOwnExpenseProgramUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\SessionMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\GetDwsProvisionReportTimeSummaryRequest} のテスト.
 */
final class GetDwsProvisionReportTimeSummaryRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use GetDwsProvisionReportUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use LookupOwnExpenseProgramUseCaseMixin;
    use LookupUserUseCaseMixin;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use SessionMixin;
    use UnitSupport;

    private GetDwsProvisionReportTimeSummaryRequest $request;
    private DwsProvisionReport $dwsProvisionReport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (self $self): void {
            $self->dwsProvisionReport = $self->examples->dwsProvisionReports[0];

            $requiredPermission = Permission::updateDwsProvisionReports();
            $self->getDwsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->dwsProvisionReport))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), [$requiredPermission], self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->lookupOwnExpenseProgramUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ownExpensePrograms[0]))
                ->byDefault();
            $self->lookupOwnExpenseProgramUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateDwsProvisionReports(), $self->examples->ownExpensePrograms[2]->id)
                ->andReturn(Seq::from($self->examples->ownExpensePrograms[2]))
                ->byDefault();
            $self->lookupOwnExpenseProgramUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateDwsProvisionReports(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty())
                ->byDefault();
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[0]))
                ->byDefault();
            $self->lookupUserUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), $requiredPermission, self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq())
                ->byDefault();

            $self->request = new GetDwsProvisionReportTimeSummaryRequest();

            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::empty(),
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_payload(): void
    {
        $this->should('payload return DwsProvisionReport', function (): void {
            // リクエスト内容を反映させるために initialize() を利用する
            $this->request->initialize(
                [],
                [],
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode($this->defaultInput())
            );
            $this->assertEquals(
                $this->expectedPayload($this->defaultInput()),
                $this->request->payload()
            );
        });
        $this->should(
            'return DwsProvisionReport when non-required param is null or empty',
            function ($key): void {
                foreach (['', null] as $value) {
                    $input = $this->defaultInput();
                    Arr::set($input, $key, $value);
                    // リクエスト内容を反映させるために initialize() を利用する
                    $this->request->initialize(
                        [],
                        [],
                        [],
                        [],
                        [],
                        ['CONTENT_TYPE' => 'application/json'],
                        Json::encode($input)
                    );
                    $this->assertEquals(
                        $this->expectedPayload($input),
                        $this->request->payload()
                    );
                }
            },
            [
                'examples' => [
                    'when note' => [
                        'plans.0.note',
                    ],
                ],
            ]
        );
        $this->should(
            'return DwsProvisionReport when non-required param is undefined',
            function ($key): void {
                $forgetInput = $this->defaultInput();
                Arr::forget($forgetInput, $key);
                // リクエスト内容を反映させるために initialize() を利用する
                $this->request->initialize(
                    [],
                    [],
                    [],
                    [],
                    [],
                    ['CONTENT_TYPE' => 'application/json'],
                    Json::encode($forgetInput)
                );
                $this->assertEquals(
                    $this->expectedPayload($forgetInput),
                    $this->request->payload()
                );
            },
            [
                'examples' => [
                    'when note' => [
                        'plans.0.note',
                    ],
                    'when plans' => [
                        'plans',
                    ],
                    'when results' => [
                        'results',
                    ],
                ],
            ]
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_validation(): void
    {
        $this->should('succeed when the data passes the validation rules', function (): void {
            $validator = $this->request->createValidatorInstance($this->defaultInput());
            $this->assertTrue($validator->passes());
        });
        $examples = [
            'when officeId is empty' => [
                ['officeId' => ['入力してください。']],
                ['officeId' => ''],
                ['officeId' => $this->dwsProvisionReport->officeId],
            ],
            'when unknown officeId given' => [
                ['officeId' => ['正しい値を入力してください。']],
                ['officeId' => self::NOT_EXISTING_ID],
                ['officeId' => $this->dwsProvisionReport->officeId],
            ],
            'when providedIn is empty' => [
                ['providedIn' => ['入力してください。']],
                ['providedIn' => ''],
                ['providedIn' => $this->dwsProvisionReport->providedIn->format('Y-m')],
            ],
            'when userId is empty' => [
                ['userId' => ['入力してください。']],
                ['userId' => ''],
                ['userId' => $this->dwsProvisionReport->userId],
            ],
            'when unknown userId given' => [
                ['userId' => ['正しい値を入力してください。']],
                ['userId' => self::NOT_EXISTING_ID],
                ['userId' => $this->dwsProvisionReport->userId],
            ],
            'when providedIn is invalid date format' => [
                ['providedIn' => ['正しい日付を入力してください。']],
                ['providedIn' => '2020-10-10'],
                ['providedIn' => $this->dwsProvisionReport->providedIn->format('Y-m')],
            ],
            'when both `plans` and `results` are empty' => [
                ['plans' => ['予定または実績のいずれかは入力する必要があります。']],
                ['plans' => [], 'results' => []],
                ['plans' => $this->defaultInput()['plans']],
            ],
            'when plans contain duplicated schedule' => [
                [
                    'plans.0.schedule' => ['時間帯が完全に一致する予定が存在します。'],
                    'plans.1.schedule' => ['時間帯が完全に一致する予定が存在します。'],
                ],
                [
                    'plans.0.schedule' => [
                        'date' => '2020-10-10',
                        'start' => '2020-10-10T12:00:00+0900',
                        'end' => '2020-10-10T17:00:00+0900',
                    ],
                    'plans.1.schedule' => [
                        'date' => '2020-10-10',
                        'start' => '2020-10-10T12:00:00+0900',
                        'end' => '2020-10-10T17:00:00+0900',
                    ],
                ],
                [
                    'plans.0.schedule' => [
                        'date' => '2020-10-10',
                        'start' => '2020-10-10T12:00:00+0900',
                        'end' => '2020-10-10T17:00:00+0900',
                    ],
                ],
            ],
            'when plans contain overlapped schedule' => [
                [
                    'plans.0.schedule' => ['時間帯が重複する予定が存在します。'],
                    'plans.1.schedule' => ['時間帯が重複する予定が存在します。'],
                ],
                [
                    'plans.0.schedule' => [
                        'date' => '2020-10-10',
                        'start' => '2020-10-10T12:00:00+0900',
                        'end' => '2020-10-10T17:00:00+0900',
                    ],
                    'plans.0.headcount' => 1,
                    'plans.1.schedule' => [
                        'date' => '2020-10-10',
                        'start' => '2020-10-10T12:00:00+0900',
                        'end' => '2020-10-10T18:00:00+0900',
                    ],
                    'plans.1.headcount' => 2,
                ],
                [
                    'plans.0.schedule' => [
                        'date' => '2020-10-10',
                        'start' => '2020-10-10T12:00:00+0900',
                        'end' => '2020-10-10T17:00:00+0900',
                    ],
                ],
            ],
            'when plans.0.schedule.date is invalid' => [
                ['plans.0.schedule.date' => ['正しい日付を入力してください。']],
                ['plans.0.schedule.date' => ['2099-02-30']],
                ['plans.0.schedule.date' => ['2099-02-28']],
            ],
            'when plans.0.schedule.start is empty' => [
                ['plans.0.schedule.start' => ['入力してください。']],
                ['plans.0.schedule.start' => ''],
                ['plans.0.schedule.start' => $this->defaultInput()['plans'][0]['schedule']['start']],
            ],
            'when plans.0.schedule.start is not date' => [
                ['plans.0.schedule.start' => ['正しい日付を入力してください。']],
                ['plans.0.schedule.start' => '12:30:00'],
                ['plans.0.schedule.start' => $this->defaultInput()['plans'][0]['schedule']['start']],
            ],
            'when plans.0.schedule.end is empty' => [
                ['plans.0.schedule.end' => ['入力してください。']],
                ['plans.0.schedule.end' => ''],
                ['plans.0.schedule.end' => $this->defaultInput()['plans'][0]['schedule']['end']],
            ],
            'when plans.0.schedule.end is not date' => [
                ['plans.0.schedule.end' => ['正しい日付を入力してください。']],
                ['plans.0.schedule.end' => '12:30:00'],
                ['plans.0.schedule.end' => $this->defaultInput()['plans'][0]['schedule']['end']],
            ],
            'when plans.*.category is empty' => [
                ['plans.0.category' => ['入力してください。']],
                ['plans.0.category' => ''],
                ['plans.0.category' => $this->dwsProvisionReport->plans[0]->category->value()],
            ],
            'when unknown plans.*.category given' => [
                ['plans.0.category' => ['障害福祉サービス：計画：サービス区分を指定してください。']],
                ['plans.0.category' => self::INVALID_ENUM_VALUE],
                ['plans.0.category' => $this->dwsProvisionReport->plans[0]->category->value()],
            ],
            'when plans.0.headcount is empty' => [
                ['plans.0.headcount' => ['入力してください。']],
                ['plans.0.headcount' => ''],
                ['plans.0.headcount' => $this->dwsProvisionReport->plans[0]->headcount],
            ],
            'when plans.0.headcount is not integer' => [
                ['plans.0.headcount' => ['整数で入力してください。']],
                ['plans.0.headcount' => 'error'],
                ['plans.0.headcount' => $this->dwsProvisionReport->plans[0]->headcount],
            ],
            'when plans.0.options is empty' => [
                ['plans.0.options.0' => ['入力してください。']],
                ['plans.0.options.0' => ''],
                ['plans.0.options.0' => $this->dwsProvisionReport->plans[0]->options[0]->value()],
            ],
            'when plans.0.ownExpenseProgramId given even though plans.0.category is not "OwnExpense"' => [
                ['plans.0.ownExpenseProgramId' => ['入力しないでください。']],
                [
                    'plans.0.category' => DwsProjectServiceCategory::physicalCare()->value(),
                    'plans.0.ownExpenseProgramId' => 1,
                ],
                [
                    'plans.0.category' => DwsProjectServiceCategory::physicalCare()->value(),
                    'plans.0.ownExpenseProgramId' => '',
                ],
            ],
            'when plans.0.ownExpenseProgramId is empty even though plans.0.category is "OwnExpense"' => [
                ['plans.0.ownExpenseProgramId' => ['入力してください。']],
                [
                    'plans.0.category' => DwsProjectServiceCategory::ownExpense()->value(),
                    'plans.0.ownExpenseProgramId' => '',
                    'plans.0.options' => [],
                ],
                [
                    'plans.0.category' => DwsProjectServiceCategory::ownExpense()->value(),
                    'plans.0.ownExpenseProgramId' => 1,
                    'plans.0.options' => [],
                ],
            ],
            'when plans.0.ownExpenseProgramId is not exists' => [
                ['plans.0.ownExpenseProgramId' => ['正しい値を入力してください。']],
                [
                    'plans.0.category' => DwsProjectServiceCategory::ownExpense()->value(),
                    'plans.0.ownExpenseProgramId' => self::NOT_EXISTING_ID,
                    'plans.0.options' => [],
                ],
                [
                    'plans.0.category' => DwsProjectServiceCategory::ownExpense()->value(),
                    'plans.0.ownExpenseProgramId' => 1,
                    'plans.0.options' => [],
                ],
            ],
            'when other office plans.0.ownExpenseProgramId given' => [
                ['plans.0.ownExpenseProgramId' => ['異なる事業所の自費サービスが含まれています。']],
                ['plans.0.category' => DwsProjectServiceCategory::ownExpense()->value(), 'plans.0.options' => [], 'plans.0.ownExpenseProgramId' => $this->examples->ownExpensePrograms[2]->id],
                ['plans.0.category' => DwsProjectServiceCategory::ownExpense()->value(), 'plans.0.options' => [], 'plans.0.ownExpenseProgramId' => $this->examples->ownExpensePrograms[0]->id],
            ],
            'when unknown plans.0.options given' => [
                ['plans.0.options.0' => ['サービスオプションを指定してください。']],
                ['plans.0.options' => [self::INVALID_ENUM_VALUE]],
                ['plans.0.options' => [$this->dwsProvisionReport->plans[0]->options[0]->value()]],
            ],
            'when plans.0.options is invalid' => [
                ['plans.0.options.0' => ['正しいサービスオプションを指定してください。']],
                [
                    'plans.0.options' => [ServiceOption::plannedByNovice()->value()],
                    'plans.0.category' => DwsProjectServiceCategory::visitingCareForPwsd()->value(),
                ],
                [
                    'plans.0.options' => [ServiceOption::firstTime()->value()],
                    'plans.0.category' => DwsProjectServiceCategory::visitingCareForPwsd()->value(),
                ],
            ],
            'when plans.0.note is not string' => [
                ['plans.0.note' => ['文字列で入力してください。']],
                ['plans.0.note' => 123456],
                ['plans.0.note' => $this->dwsProvisionReport->plans[0]->note],
            ],
            'when results contain duplicated schedule' => [
                [
                    'results.0.schedule' => ['時間帯が完全に一致する実績が存在します。'],
                    'results.1.schedule' => ['時間帯が完全に一致する実績が存在します。'],
                ],
                [
                    'results.0.schedule' => [
                        'date' => '2020-10-10',
                        'start' => '2020-10-10T12:00:00+0900',
                        'end' => '2020-10-10T17:00:00+0900',
                    ],
                    'results.1.schedule' => [
                        'date' => '2020-10-10',
                        'start' => '2020-10-10T12:00:00+0900',
                        'end' => '2020-10-10T17:00:00+0900',
                    ],
                ],
                [
                    'results.0.schedule' => [
                        'date' => '2020-10-10',
                        'start' => '2020-10-10T12:00:00+0900',
                        'end' => '2020-10-10T17:00:00+0900',
                    ],
                ],
            ],
            'when results contain overlapped schedule' => [
                [
                    'results.0.schedule' => ['時間帯が重複する実績が存在します。'],
                    'results.1.schedule' => ['時間帯が重複する実績が存在します。'],
                ],
                [
                    'results.0.schedule' => [
                        'date' => '2020-10-10',
                        'start' => '2020-10-10T12:00:00+0900',
                        'end' => '2020-10-10T17:00:00+0900',
                    ],
                    'results.0.headcount' => 1,
                    'results.1.schedule' => [
                        'date' => '2020-10-10',
                        'start' => '2020-10-10T12:00:00+0900',
                        'end' => '2020-10-10T18:00:00+0900',
                    ],
                    'results.1.headcount' => 2,
                ],
                [
                    'results.0.schedule' => [
                        'date' => '2020-10-10',
                        'start' => '2020-10-10T12:00:00+0900',
                        'end' => '2020-10-10T17:00:00+0900',
                    ],
                ],
            ],
            'when results.0.schedule.date is invalid' => [
                ['results.0.schedule.date' => ['正しい日付を入力してください。']],
                ['results.0.schedule.date' => ['2099-02-30']],
                ['results.0.schedule.date' => ['2099-02-28']],
            ],
            'when results.0.schedule.start is empty' => [
                ['results.0.schedule.start' => ['入力してください。']],
                ['results.0.schedule.start' => ''],
                ['results.0.schedule.start' => $this->defaultInput()['results'][0]['schedule']['start']],
            ],
            'when results.0.schedule.start is not date' => [
                ['results.0.schedule.start' => ['正しい日付を入力してください。']],
                ['results.0.schedule.start' => '12:30:00'],
                ['results.0.schedule.start' => $this->defaultInput()['results'][0]['schedule']['start']],
            ],
            'when results.0.schedule.end is empty' => [
                ['results.0.schedule.end' => ['入力してください。']],
                ['results.0.schedule.end' => ''],
                ['results.0.schedule.end' => $this->defaultInput()['results'][0]['schedule']['end']],
            ],
            'when results.0.schedule.end is not date' => [
                ['results.0.schedule.end' => ['正しい日付を入力してください。']],
                ['results.0.schedule.end' => '12:30:00'],
                ['results.0.schedule.end' => $this->defaultInput()['results'][0]['schedule']['end']],
            ],
            'when results.*.category is empty' => [
                ['results.0.category' => ['入力してください。']],
                ['results.0.category' => ''],
                ['results.0.category' => $this->dwsProvisionReport->results[0]->category->value()],
            ],
            'when unknown results.*.category given' => [
                ['results.0.category' => ['障害福祉サービス：計画：サービス区分を指定してください。']],
                ['results.0.category' => self::INVALID_ENUM_VALUE],
                ['results.0.category' => $this->dwsProvisionReport->results[0]->category->value()],
            ],
            'when results.0.headcount is empty' => [
                ['results.0.headcount' => ['入力してください。']],
                ['results.0.headcount' => ''],
                ['results.0.headcount' => $this->dwsProvisionReport->results[0]->headcount],
            ],
            'when results.0.headcount is not integer' => [
                ['results.0.headcount' => ['整数で入力してください。']],
                ['results.0.headcount' => 'error'],
                ['results.0.headcount' => $this->dwsProvisionReport->results[0]->headcount],
            ],
            'when results.0.options is empty' => [
                ['results.0.options.0' => ['入力してください。']],
                ['results.0.options.0' => ''],
                ['results.0.options.0' => $this->dwsProvisionReport->results[0]->options[0]->value()],
            ],
            'when unknown results.0.options given' => [
                ['results.0.options.0' => ['サービスオプションを指定してください。']],
                ['results.0.options' => [self::INVALID_ENUM_VALUE]],
                ['results.0.options' => [$this->dwsProvisionReport->results[0]->options[0]->value()]],
            ],
            'when results.0.options is invalid' => [
                ['results.0.options.0' => ['正しいサービスオプションを指定してください。']],
                [
                    'results.0.options' => [ServiceOption::plannedByNovice()->value()],
                    'results.0.category' => DwsProjectServiceCategory::visitingCareForPwsd()->value(),
                ],
                [
                    'results.0.options' => [ServiceOption::firstTime()->value()],
                    'results.0.category' => DwsProjectServiceCategory::visitingCareForPwsd()->value(),
                ],
            ],
            'when results.0.ownExpenseProgramId given even though results.0.category is not "OwnExpense"' => [
                ['results.0.ownExpenseProgramId' => ['入力しないでください。']],
                [
                    'results.0.category' => DwsProjectServiceCategory::physicalCare()->value(),
                    'results.0.ownExpenseProgramId' => 1,
                ],
                [
                    'results.0.category' => DwsProjectServiceCategory::physicalCare()->value(),
                    'results.0.ownExpenseProgramId' => '',
                ],
            ],
            'when results.0.ownExpenseProgramId is empty even though results.0.category is "OwnExpense"' => [
                ['results.0.ownExpenseProgramId' => ['入力してください。']],
                [
                    'results.0.category' => DwsProjectServiceCategory::ownExpense()->value(),
                    'results.0.ownExpenseProgramId' => '',
                    'results.0.options' => [],
                ],
                [
                    'results.0.category' => DwsProjectServiceCategory::ownExpense()->value(),
                    'results.0.ownExpenseProgramId' => 1,
                    'results.0.options' => [],
                ],
            ],
            'when results.0.ownExpenseProgramId is not exists' => [
                ['results.0.ownExpenseProgramId' => ['正しい値を入力してください。']],
                [
                    'results.0.category' => DwsProjectServiceCategory::ownExpense()->value(),
                    'results.0.ownExpenseProgramId' => self::NOT_EXISTING_ID,
                    'results.0.options' => [],
                ],
                [
                    'results.0.category' => DwsProjectServiceCategory::ownExpense()->value(),
                    'results.0.ownExpenseProgramId' => 1,
                    'results.0.options' => [],
                ],
            ],
            'when other office results.0.ownExpenseProgramId given' => [
                ['results.0.ownExpenseProgramId' => ['異なる事業所の自費サービスが含まれています。']],
                ['results.0.category' => DwsProjectServiceCategory::ownExpense()->value(), 'results.0.options' => [], 'results.0.ownExpenseProgramId' => $this->examples->ownExpensePrograms[2]->id],
                ['results.0.category' => DwsProjectServiceCategory::ownExpense()->value(), 'results.0.options' => [], 'results.0.ownExpenseProgramId' => $this->examples->ownExpensePrograms[0]->id],
            ],
            'when results.0.note is not string' => [
                ['results.0.note' => ['文字列で入力してください。']],
                ['results.0.note' => 123456],
                ['results.0.note' => $this->dwsProvisionReport->plans[0]->note],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $input = $this->defaultInput();
                foreach ($invalid as $key => $value) {
                    Arr::set($input, $key, $value);
                }
                $validator = $this->request->createValidatorInstance($invalid + $input);
                $this->assertTrue($validator->fails());
                $this->assertSame($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    $validator = $this->request->createValidatorInstance($valid + $this->defaultInput());
                    $this->assertTrue($validator->passes());
                }
            },
            compact('examples')
        );
    }

    /**
     * リクエストクラスが受け取る入力のデフォルト値.
     *
     * @return array
     */
    private function defaultInput(): array
    {
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0];
        return [
            'plans' => Seq::fromArray($dwsProvisionReport->plans)
                ->map(fn (DwsProvisionReportItem $x): array => [
                    'schedule' => [
                        'date' => $x->schedule->date->toDateString(),
                        'start' => $x->schedule->start->toDateTimeString(),
                        'end' => $x->schedule->end->toDateTimeString(),
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
                        'start' => $x->schedule->start->toDateTimeString(),
                        'end' => $x->schedule->end->toDateTimeString(),
                    ],
                    'category' => $x->category->value(),
                    'headcount' => $x->headcount,
                    'movingDurationMinutes' => $x->movingDurationMinutes,
                    'ownExpenseProgramId' => $x->ownExpenseProgramId,
                    'options' => Seq::fromArray($x->options)->map(fn (ServiceOption $x): int => $x->value())->toArray(),
                    'note' => $x->note,
                ])
                ->toArray(),
            // ルートパラメーター
            'officeId' => $dwsProvisionReport->officeId,
            'userId' => $dwsProvisionReport->userId,
            'providedIn' => $dwsProvisionReport->providedIn->format('Y-m'),
        ];
    }

    /**
     * payload が返すドメインモデル.
     *
     * @param array $input
     * @return array
     */
    private function expectedPayload(array $input): array
    {
        $plans = Seq::fromArray($input['plans'] ?? [])
            ->map(fn (array $plan): DwsProvisionReportItem => DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::parse($plan['schedule']['date']),
                    'start' => Carbon::parse($plan['schedule']['start']),
                    'end' => Carbon::parse($plan['schedule']['end']),
                ]),
                'category' => DwsProjectServiceCategory::from($plan['category']),
                'headcount' => $plan['headcount'],
                'movingDurationMinutes' => $plan['movingDurationMinutes'],
                'ownExpenseProgramId' => $plan['ownExpenseProgramId'],
                'options' => Seq::fromArray($plan['options'])
                    ->map(fn (int $option): ServiceOption => ServiceOption::from($option))
                    ->toArray(),
                'note' => $plan['note'] ?? '',
            ]));
        $results = Seq::fromArray($input['results'] ?? [])
            ->map(fn (array $result): DwsProvisionReportItem => DwsProvisionReportItem::create([
                'schedule' => Schedule::create([
                    'date' => Carbon::parse($result['schedule']['date']),
                    'start' => Carbon::parse($result['schedule']['start']),
                    'end' => Carbon::parse($result['schedule']['end']),
                ]),
                'category' => DwsProjectServiceCategory::from($result['category']),
                'headcount' => $result['headcount'],
                'movingDurationMinutes' => $result['movingDurationMinutes'],
                'ownExpenseProgramId' => $result['ownExpenseProgramId'],
                'options' => Seq::fromArray($result['options'])
                    ->map(fn (int $option): ServiceOption => ServiceOption::from($option))
                    ->toArray(),
                'note' => $result['note'] ?? '',
            ]));

        return [
            'plans' => $plans->toArray(),
            'results' => $results->toArray(),
            'officeId' => $input['officeId'],
            'userId' => $input['userId'],
            'providedIn' => Carbon::parse($input['providedIn']),
        ];
    }
}
