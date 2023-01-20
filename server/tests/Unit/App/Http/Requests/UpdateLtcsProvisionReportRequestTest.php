<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\Delegates\LtcsProvisionReportFormDelegateImpl;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateLtcsProvisionReportRequest;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\Common\TimeRange;
use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Office\HomeHelpServiceSpecifiedOfficeAddition;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\Office\LtcsBaseIncreaseSupportAddition;
use Domain\Office\LtcsOfficeLocationAddition;
use Domain\Office\LtcsSpecifiedTreatmentImprovementAddition;
use Domain\Office\LtcsTreatmentImprovementAddition;
use Domain\Permission\Permission;
use Domain\Project\LtcsProjectAmount;
use Domain\Project\LtcsProjectAmountCategory;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportEntry;
use Domain\ProvisionReport\LtcsProvisionReportOverScore;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\Timeframe;
use Domain\Shift\ServiceOption;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\GetLtcsProvisionReportScoreSummaryUseCaseMixin;
use Tests\Unit\Mixins\GetLtcsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\IdentifyLtcsHomeVisitLongTermCareDictionaryUseCaseMixin;
use Tests\Unit\Mixins\LookupOwnExpenseProgramUseCaseMixin;
use Tests\Unit\Mixins\LtcsHomeVisitLongTermCareDictionaryEntryFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\UpdateLtcsProvisionReportRequest} のテスト.
 */
class UpdateLtcsProvisionReportRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use GetLtcsProvisionReportUseCaseMixin;
    use GetLtcsProvisionReportScoreSummaryUseCaseMixin;
    use LookupOwnExpenseProgramUseCaseMixin;
    use IdentifyLtcsHomeVisitLongTermCareDictionaryUseCaseMixin;
    use LtcsHomeVisitLongTermCareDictionaryEntryFinderMixin;
    use MockeryMixin;
    use UnitSupport;

    protected UpdateLtcsProvisionReportRequest $request;
    private LtcsProvisionReport $ltcsProvisionReport;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UpdateLtcsProvisionReportRequestTest $self): void {
            $self->ltcsProvisionReport = $self->examples->ltcsProvisionReports[0];
            $self->getLtcsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->ltcsProvisionReport))
                ->byDefault();
            $self->getLtcsProvisionReportScoreSummaryUseCase
                ->allows('handle')
                ->andReturn([
                    'plan' => ['managedScore' => 25000, 'unmanagedScore' => 25000],
                    'result' => ['managedScore' => 25000, 'unmanagedScore' => 25000],
                ])
                ->byDefault();
            $self->request = new UpdateLtcsProvisionReportRequest(new LtcsProvisionReportFormDelegateImpl());
            $self->lookupOwnExpenseProgramUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ownExpensePrograms[0]))
                ->byDefault();
            $self->lookupOwnExpenseProgramUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateLtcsProvisionReports(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty())
                ->byDefault();
            $self->lookupOwnExpenseProgramUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateLtcsProvisionReports(), $self->examples->ownExpensePrograms[2]->id)
                ->andReturn(Seq::from($self->examples->ownExpensePrograms[2]))
                ->byDefault();
            $self->identifyLtcsHomeVisitLongTermCareDictionary
                ->allows('handle')
                ->andReturn(Option::some($self->examples->ltcsHomeVisitLongTermCareDictionaries[0]))
                ->byDefault();
            $self->ltcsHomeVisitLongTermCareDictionaryEntryFinder
                ->allows('find')
                ->andReturn(FinderResult::from(Seq::from($self->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0]), Pagination::create([])))
                ->byDefault();
            $filterParams = ['specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::addition1(), 'serviceCodes' => ['111111'], 'dictionaryId' => $self->examples->ltcsHomeVisitLongTermCareDictionaries[0]->id];
            $paginationParams = ['all' => true, 'sortBy' => 'id'];
            $self->ltcsHomeVisitLongTermCareDictionaryEntryFinder
                ->allows('find')
                ->with($filterParams, $paginationParams)
                ->andReturn(FinderResult::from(Seq::empty(), Pagination::create([])))
                ->byDefault();
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
        $this->should('payload return LtcsProvisionReport', function (): void {
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
            'return LtcsProvisionReport when non-required param is null or empty',
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
                    'when entries.0.note' => [
                        'entries.0.note',
                    ],
                    'when entries.0.plans' => [
                        'entries.0.plans',
                    ],
                    'when entries.0.results' => [
                        'entries.0.results',
                    ],
                ],
            ]
        );
        $this->should(
            'return LtcsProvisionReport when non-required param is undefined',
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
                    'when entries.0.ownExpenseProgramId' => [
                        'entries.0.ownExpenseProgramId',
                    ],
                    'when entries.0.note' => [
                        'entries.0.note',
                    ],
                    'when entries.0.plans' => [
                        'entries.0.plans',
                    ],
                    'when entries.0.results' => [
                        'entries.0.results',
                    ],
                    'when entries.0.serviceCode' => [
                        'entries.0.serviceCode',
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
            'when entries serviceCode is not match HomeVisitLongTermCareSpecifiedOfficeAddition' => [
                ['entries' => ['介護保険サービス：予実：特定事業所加算区分が異なるサービスコードが含まれています。']],
                [
                    'entries.0.category' => LtcsProjectServiceCategory::housework()->value(),
                    'entries.0.options' => [],
                    'entries.0.ownExpenseProgramId' => '',
                    'entries.0.serviceCode' => '111111',
                    'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::addition1()->value(),
                ],
                ['entries' => $this->defaultInput()['entries']],
            ],
            'when unknown ownExpenseProgramId given' => [
                ['entries.0.ownExpenseProgramId' => ['正しい値を入力してください。']],
                [
                    'entries.0.category' => LtcsProjectServiceCategory::ownExpense()->value(),
                    'entries.0.ownExpenseProgramId' => self::NOT_EXISTING_ID,
                    'entries.0.options' => [],
                    'entries.0.serviceCode' => null,
                    'entries.0.amounts' => [],
                ],
                [
                    'entries.0.category' => LtcsProjectServiceCategory::ownExpense()->value(),
                    'entries.0.ownExpenseProgramId' => 1,
                    'entries.0.options' => [],
                ],
            ],
            'when other office ownExpenseProgramId given' => [
                ['entries.0.ownExpenseProgramId' => ['異なる事業所の自費サービスが含まれています。']],
                [
                    'entries.0.category' => LtcsProjectServiceCategory::ownExpense()->value(),
                    'entries.0.ownExpenseProgramId' => $this->examples->ownExpensePrograms[2]->id,
                    'entries.0.options' => [],
                    'entries.0.serviceCode' => null,
                    'entries.0.amounts' => [],
                ],
                [
                    'entries.0.category' => LtcsProjectServiceCategory::ownExpense()->value(),
                    'entries.0.ownExpenseProgramId' => $this->examples->ownExpensePrograms[0]->id,
                    'entries.0.options' => [],
                ],
            ],
            'when entries.0.ownExpenseProgramId given even though entries.0.category is not "OwnExpense"' => [
                ['entries.0.ownExpenseProgramId' => ['入力しないでください。']],
                [
                    'entries.0.category' => LtcsProjectServiceCategory::physicalCare()->value(),
                    'entries.0.ownExpenseProgramId' => 1,
                ],
                [
                    'entries.0.category' => LtcsProjectServiceCategory::physicalCare()->value(),
                    'entries.0.ownExpenseProgramId' => '',
                ],
            ],
            'when entries.0.ownExpenseProgramId is empty even though entries.0.category is "OwnExpense"' => [
                ['entries.0.ownExpenseProgramId' => ['入力してください。']],
                [
                    'entries.0.category' => LtcsProjectServiceCategory::ownExpense()->value(),
                    'entries.0.ownExpenseProgramId' => '',
                    'entries.0.options' => [],
                    'entries.0.serviceCode' => null,
                    'entries.0.amounts' => [],
                ],
                [
                    'entries.0.category' => LtcsProjectServiceCategory::ownExpense()->value(),
                    'entries.0.ownExpenseProgramId' => 1,
                    'entries.0.options' => [],
                ],
            ],
            'when entries.0.ownExpenseProgramId is not exists' => [
                ['entries.0.ownExpenseProgramId' => ['正しい値を入力してください。']],
                [
                    'entries.0.category' => LtcsProjectServiceCategory::ownExpense()->value(),
                    'entries.0.ownExpenseProgramId' => self::NOT_EXISTING_ID,
                    'entries.0.options' => [],
                    'entries.0.serviceCode' => null,
                    'entries.0.amounts' => [],
                ],
                [
                    'entries.0.category' => LtcsProjectServiceCategory::ownExpense()->value(),
                    'entries.0.ownExpenseProgramId' => 1,
                    'entries.0.options' => [],
                ],
            ],
            'when slot.start is empty' => [
                ['entries.0.slot.start' => ['入力してください。']],
                ['entries.0.slot.start' => ''],
                ['entries.0.slot.start' => $this->ltcsProvisionReport->entries[0]->slot->start],
            ],
            'when slot.start is not date format H:i' => [
                ['entries.0.slot.start' => ['正しい日付を入力してください。']],
                ['entries.0.slot.start' => '12:30:00'],
                ['entries.0.slot.start' => $this->ltcsProvisionReport->entries[0]->slot->start],
            ],
            'when slot.end is empty' => [
                ['entries.0.slot.end' => ['入力してください。']],
                ['entries.0.slot.end' => ''],
                ['entries.0.slot.end' => $this->ltcsProvisionReport->entries[0]->slot->end],
            ],
            'when slot.end is not date format H:i' => [
                ['entries.0.slot.end' => ['正しい日付を入力してください。']],
                ['entries.0.slot.end' => '12:30:00'],
                ['entries.0.slot.end' => $this->ltcsProvisionReport->entries[0]->slot->end],
            ],
            'when timeframe is empty' => [
                ['entries.0.timeframe' => ['入力してください。']],
                ['entries.0.timeframe' => ''],
                ['entries.0.timeframe' => $this->ltcsProvisionReport->entries[0]->timeframe->value()],
            ],
            'when unknown timeframe given' => [
                ['entries.0.timeframe' => ['時間帯を指定してください。']],
                ['entries.0.timeframe' => self::INVALID_ENUM_VALUE],
                ['entries.0.timeframe' => $this->ltcsProvisionReport->entries[0]->timeframe->value()],
            ],
            'when entries.*.category is empty' => [
                ['entries.0.category' => ['入力してください。']],
                ['entries.0.category' => ''],
                ['entries.0.category' => $this->ltcsProvisionReport->entries[0]->category->value()],
            ],
            'when unknown entries.*.category given' => [
                ['entries.0.category' => ['介護保険サービス：計画：サービス区分を指定してください。']],
                ['entries.0.category' => self::INVALID_ENUM_VALUE],
                ['entries.0.category' => $this->ltcsProvisionReport->entries[0]->category->value()],
            ],
            'when entries.*.amounts.*.category is empty' => [
                ['entries.0.amounts.0.category' => ['入力してください。']],
                [
                    'entries.0.category' => LtcsProjectServiceCategory::physicalCare()->value(),
                    'entries.0.amounts.0.category' => '',
                ],
                ['entries.0.amounts.0.category' => $this->ltcsProvisionReport->entries[0]->amounts[0]->category->value()],
            ],
            'when unknown entries.*.amounts.*.category given' => [
                ['entries.0.amounts.0.category' => ['介護保険サービス：計画：サービス提供量を指定してください。']],
                [
                    'entries.0.category' => LtcsProjectServiceCategory::physicalCare()->value(),
                    'entries.0.amounts.0.category' => self::INVALID_ENUM_VALUE,
                ],
                ['entries.0.amounts.0.category' => $this->ltcsProvisionReport->entries[0]->amounts[0]->category->value()],
            ],
            'when amount is empty' => [
                ['entries.0.amounts.0.amount' => ['入力してください。']],
                [
                    'entries.0.category' => LtcsProjectServiceCategory::physicalCare()->value(),
                    'entries.0.amounts.0.amount' => '',
                ],
                ['entries.0.amounts.0.amount' => $this->ltcsProvisionReport->entries[0]->amounts[0]->amount],
            ],
            'when amount is not integer' => [
                ['entries.0.amounts.0.amount' => ['整数で入力してください。']],
                [
                    'entries.0.category' => LtcsProjectServiceCategory::physicalCare()->value(),
                    'entries.0.amounts.0.amount' => 'error',
                ],
                ['entries.0.amounts.0.amount' => $this->ltcsProvisionReport->entries[0]->amounts[0]->amount],
            ],
            'when headcount is empty' => [
                ['entries.0.headcount' => ['入力してください。']],
                ['entries.0.headcount' => ''],
                ['entries.0.headcount' => $this->ltcsProvisionReport->entries[0]->headcount],
            ],
            'when headcount is not integer' => [
                ['entries.0.headcount' => ['整数で入力してください。']],
                ['entries.0.headcount' => 'error'],
                ['entries.0.headcount' => $this->ltcsProvisionReport->entries[0]->headcount],
            ],
            'when serviceCode is empty' => [
                ['entries.0.serviceCode' => ['入力してください。']],
                ['entries.0.serviceCode' => ''],
                ['entries.0.serviceCode' => $this->ltcsProvisionReport->entries[0]->serviceCode->toString()],
            ],
            'when serviceCode is not string' => [
                ['entries.0.serviceCode' => ['文字列で入力してください。']],
                ['entries.0.serviceCode' => 123456],
                ['entries.0.serviceCode' => $this->ltcsProvisionReport->entries[0]->serviceCode->toString()],
            ],
            'when serviceCode is over 6 letters' => [
                ['entries.0.serviceCode' => ['6文字以内で入力してください。']],
                ['entries.0.serviceCode' => '1234567'],
                ['entries.0.serviceCode' => $this->ltcsProvisionReport->entries[0]->serviceCode->toString()],
            ],

            'when options is empty' => [
                ['entries.0.options.0' => ['入力してください。']],
                ['entries.0.options' => ['']],
                ['entries.0.options' => [$this->ltcsProvisionReport->entries[0]->options[0]->value()]],
            ],
            'when unknown options given' => [
                ['entries.0.options.0' => ['サービスオプションを指定してください。']],
                ['entries.0.options' => [self::INVALID_ENUM_VALUE]],
                ['entries.0.options' => [$this->ltcsProvisionReport->entries[0]->options[0]->value()]],
            ],
            'when option is invalid' => [
                ['entries.0.options.0' => ['正しいサービスオプションを指定してください。']],
                ['entries.0.category' => LtcsProjectServiceCategory::physicalCare()->value(), 'entries.0.options' => [ServiceOption::notificationEnabled()->value()]],
                ['entries.0.category' => LtcsProjectServiceCategory::physicalCare()->value(), 'entries.0.options' => [ServiceOption::over20()->value()]],
            ],
            'when note is not string' => [
                ['entries.0.note' => ['文字列で入力してください。']],
                ['entries.0.note' => 123456],
                ['entries.0.note' => $this->ltcsProvisionReport->entries[0]->note],
            ],
            'when plans is not date' => [
                ['entries.0.plans.0' => ['正しい日付を入力してください。']],
                ['entries.0.plans.0' => 'error'],
                ['entries.0.plans.0' => $this->ltcsProvisionReport->entries[0]->plans[0]],
            ],
            'when plans is overlapped' => [
                ['entries.0.plans.0' => ['予定が重複しています。'], 'entries.1.plans.0' => ['予定が重複しています。']],
                [
                    'entries.0.slot' => [
                        'start' => '12:00',
                        'end' => '18:00',
                    ],
                    'entries.0.plans' => ['2020-12-11'],
                    'entries.1.slot' => [
                        'start' => '12:00',
                        'end' => '18:00',
                    ],
                    'entries.1.plans' => ['2020-12-11'],
                ],
                ['entries.0.plans' => [$this->ltcsProvisionReport->entries[0]->plans[0]->toDateString(), $this->ltcsProvisionReport->entries[0]->plans[1]->toDateString()]],
            ],
            'when results is empty and plans is empty' => [
                ['entries.0.results' => ['予定年月日が存在しない時、実績年月日は必ず入力してください。']],
                ['entries.0.results' => '', 'entries.0.plans' => ''],
                ['entries.0.results' => [$this->ltcsProvisionReport->entries[0]->results[0]]],
            ],
            'when results is not date' => [
                ['entries.0.results.0' => ['正しい日付を入力してください。']],
                ['entries.0.results.0' => 'error'],
                ['entries.0.results.0' => $this->ltcsProvisionReport->entries[0]->results[0]],
            ],
            'when results is overlapped' => [
                ['entries.0.results.0' => ['実績が重複しています。'], 'entries.1.results.0' => ['実績が重複しています。']],
                [
                    'entries.0.slot' => [
                        'start' => '12:00',
                        'end' => '18:00',
                    ],
                    'entries.0.results' => ['2020-12-11'],
                    'entries.1.slot' => [
                        'start' => '12:00',
                        'end' => '18:00',
                    ],
                    'entries.1.results' => ['2020-12-11'],
                ],
                ['entries.0.results' => [$this->ltcsProvisionReport->entries[0]->results[0]->toDateString(), $this->ltcsProvisionReport->entries[0]->results[1]->toDateString()]],
            ],
            'when serviceCode is given if the category is ownExpense' => [
                ['entries.0.serviceCode' => ['入力しないでください。']],
                [
                    'entries.0.category' => LtcsProjectServiceCategory::ownExpense()->value(),
                    'entries.0.ownExpenseProgramId' => 1,
                    'entries.0.options' => [],
                    'entries.0.serviceCode' => '123456',
                    'entries.0.amounts' => [],
                ],
                ['entries.0.serviceCode' => null],
            ],
            'when amounts is given if the category is ownExpense' => [
                ['entries.0.amounts' => ['入力しないでください。']],
                [
                    'entries.0.category' => LtcsProjectServiceCategory::ownExpense()->value(),
                    'entries.0.ownExpenseProgramId' => 1,
                    'entries.0.options' => [],
                    'entries.0.serviceCode' => null,
                    'entries.0.amounts' => [['category' => 91, 'amount' => 10000]],
                ],
                ['entries.0.amounts' => []],
            ],
            'when serviceCode is not given if the category is not ownExpense' => [
                ['entries.0.serviceCode' => ['入力してください。']],
                [
                    'entries.0.serviceCode' => null,
                ],
                ['entries' => $this->defaultInput()['entries']],
            ],
            'when amounts is not given if the category is not ownExpense' => [
                ['entries.0.amounts' => ['入力してください。']],
                [
                    'entries.0.amounts' => [],
                ],
                ['entries.0.amounts' => [['category' => 91, 'amount' => 10000]]],
            ],
            'when specifiedOfficeAddition is empty' => [
                ['specifiedOfficeAddition' => ['入力してください。']],
                ['specifiedOfficeAddition' => ''],
                ['specifiedOfficeAddition' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->specifiedOfficeAddition->value()],
            ],
            'when unknown specifiedOfficeAddition given' => [
                ['specifiedOfficeAddition' => ['特定事業所加算区分（介保・訪問介護）を指定してください。']],
                ['specifiedOfficeAddition' => self::NOT_EXISTING_ID],
                ['specifiedOfficeAddition' => HomeHelpServiceSpecifiedOfficeAddition::addition1()->value()],
            ],
            'when treatmentImprovementAddition is not string' => [
                ['treatmentImprovementAddition' => ['入力してください。']],
                ['treatmentImprovementAddition' => ''],
                ['treatmentImprovementAddition' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->treatmentImprovementAddition->value()],
            ],
            'when unknown treatmentImprovementAddition given' => [
                ['treatmentImprovementAddition' => ['介護職員処遇改善加算（介保）を指定してください。']],
                ['treatmentImprovementAddition' => self::NOT_EXISTING_ID],
                ['treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::addition1()->value()],
            ],
            'when specifiedTreatmentImprovementAddition is not string' => [
                ['specifiedTreatmentImprovementAddition' => ['入力してください。']],
                ['specifiedTreatmentImprovementAddition' => ''],
                ['specifiedTreatmentImprovementAddition' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->specifiedTreatmentImprovementAddition->value()],
            ],
            'when unknown specifiedTreatmentImprovementAddition given' => [
                ['specifiedTreatmentImprovementAddition' => ['介護職員等特定処遇改善加算（介保）を指定してください。']],
                ['specifiedTreatmentImprovementAddition' => self::NOT_EXISTING_ID],
                ['specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::addition1()->value()],
            ],
            'when baseIncreaseSupportAddition is not string' => [
                ['baseIncreaseSupportAddition' => ['入力してください。']],
                ['baseIncreaseSupportAddition' => ''],
                ['baseIncreaseSupportAddition' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->baseIncreaseSupportAddition->value()],
            ],
            'when unknown baseIncreaseSupportAddition given' => [
                ['baseIncreaseSupportAddition' => ['ベースアップ等支援加算（介保）を指定してください。']],
                ['baseIncreaseSupportAddition' => self::NOT_EXISTING_ID],
                ['baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::addition1()->value()],
            ],
            'when locationAddition is not string' => [
                ['locationAddition' => ['入力してください。']],
                ['locationAddition' => ''],
                ['locationAddition' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->locationAddition->value()],
            ],
            'when unknown locationAddition given' => [
                ['locationAddition' => ['地域加算（介保）を指定してください。']],
                ['locationAddition' => self::NOT_EXISTING_ID],
                ['locationAddition' => LtcsOfficeLocationAddition::specifiedArea()->value()],
            ],
            'when plan maxBenefitExcessScore not given' => [
                ['plan.maxBenefitExcessScore' => ['入力してください。']],
                ['plan' => [
                    'maxBenefitQuotaExcessScore' => $this->ltcsProvisionReport->plan->maxBenefitQuotaExcessScore,
                ]],
                ['plan' => $this->defaultInput()['plan']],
            ],
            'when plan maxBenefitExcessScore is not integer' => [
                ['plan.maxBenefitExcessScore' => ['整数で入力してください。']],
                ['plan' => [
                    'maxBenefitExcessScore' => 'error',
                    'maxBenefitQuotaExcessScore' => $this->ltcsProvisionReport->plan->maxBenefitQuotaExcessScore,
                ]],
                ['plan' => $this->defaultInput()['plan']],
            ],
            'when plan maxBenefitExcessScore is negative number' => [
                ['plan.maxBenefitExcessScore' => ['0以上で入力してください。']],
                ['plan' => [
                    'maxBenefitExcessScore' => -1,
                    'maxBenefitQuotaExcessScore' => $this->ltcsProvisionReport->plan->maxBenefitQuotaExcessScore,
                ]],
                ['plan' => $this->defaultInput()['plan']],
            ],
            // 'when plan maxBenefitExcessScore over managedScore' => [
            //     ['plan.maxBenefitExcessScore' => ['「種類支給限度基準を超える単位数」は0以上、「限度額管理対象単位数」以下の半角数字で入力してください。']],
            //     ['plan' => [
            //         'maxBenefitExcessScore' => 25001,
            //         'maxBenefitQuotaExcessScore' => 1000,
            //     ]],
            //     ['plan' => $this->defaultInput()['plan']],
            // ],
            'when result maxBenefitExcessScore not given' => [
                ['result.maxBenefitExcessScore' => ['入力してください。']],
                ['result' => [
                    'maxBenefitQuotaExcessScore' => $this->ltcsProvisionReport->result->maxBenefitQuotaExcessScore,
                ]],
                ['result' => $this->defaultInput()['result']],
            ],
            'when result maxBenefitExcessScore is not integer' => [
                ['result.maxBenefitExcessScore' => ['整数で入力してください。']],
                ['result' => [
                    'maxBenefitExcessScore' => 'error',
                    'maxBenefitQuotaExcessScore' => $this->ltcsProvisionReport->result->maxBenefitQuotaExcessScore,
                ]],
                ['result' => $this->defaultInput()['result']],
            ],
            'when result maxBenefitExcessScore is negative number' => [
                ['result.maxBenefitExcessScore' => ['0以上で入力してください。']],
                ['result' => [
                    'maxBenefitExcessScore' => -1,
                    'maxBenefitQuotaExcessScore' => $this->ltcsProvisionReport->result->maxBenefitQuotaExcessScore,
                ]],
                ['result' => $this->defaultInput()['result']],
            ],
            // 'when result maxBenefitExcessScore over managedScore' => [
            //     ['result.maxBenefitExcessScore' => ['「種類支給限度基準を超える単位数」は0以上、「限度額管理対象単位数」以下の半角数字で入力してください。']],
            //     ['result' => [
            //         'maxBenefitExcessScore' => 25001,
            //         'maxBenefitQuotaExcessScore' => 1000,
            //     ]],
            //     ['result' => $this->defaultInput()['result']],
            // ],
            'when plan maxBenefitQuotaExcessScore not given' => [
                ['plan.maxBenefitQuotaExcessScore' => ['入力してください。']],
                ['plan' => [
                    'maxBenefitExcessScore' => $this->ltcsProvisionReport->plan->maxBenefitExcessScore,
                ]],
                ['plan' => $this->defaultInput()['plan']],
            ],
            'when plan maxBenefitQuotaExcessScore is not integer' => [
                ['plan.maxBenefitQuotaExcessScore' => ['整数で入力してください。']],
                ['plan' => [
                    'maxBenefitExcessScore' => $this->ltcsProvisionReport->plan->maxBenefitExcessScore,
                    'maxBenefitQuotaExcessScore' => 'error',
                ]],
                ['plan' => $this->defaultInput()['plan']],
            ],
            'when plan maxBenefitQuotaExcessScore is negative number' => [
                ['plan.maxBenefitQuotaExcessScore' => ['0以上で入力してください。']],
                ['plan' => [
                    'maxBenefitExcessScore' => $this->ltcsProvisionReport->plan->maxBenefitExcessScore,
                    'maxBenefitQuotaExcessScore' => -1,
                ]],
                ['plan' => $this->defaultInput()['plan']],
            ],
            // 'when plan maxBenefitQuotaExcessScore over managedScore' => [
            //     ['plan.maxBenefitQuotaExcessScore' => ['「区分支給限度基準を超える単位数」は0以上、「種類支給限度基準内単位数」以下の半角数字で入力してください。']],
            //     ['plan' => [
            //         'maxBenefitExcessScore' => 24500,
            //         'maxBenefitQuotaExcessScore' => 1000,
            //     ]],
            //     ['plan' => $this->defaultInput()['plan']],
            // ],
            'when result maxBenefitQuotaExcessScore not given' => [
                ['result.maxBenefitQuotaExcessScore' => ['入力してください。']],
                ['result' => [
                    'maxBenefitExcessScore' => $this->ltcsProvisionReport->plan->maxBenefitExcessScore,
                    'maxBenefitQuotaExcessScore' => '',
                ]],
                ['result' => $this->defaultInput()['result']],
            ],
            'when result maxBenefitQuotaExcessScore is not integer' => [
                ['result.maxBenefitQuotaExcessScore' => ['整数で入力してください。']],
                ['result' => [
                    'maxBenefitExcessScore' => $this->ltcsProvisionReport->result->maxBenefitExcessScore,
                    'maxBenefitQuotaExcessScore' => 'error',
                ]],
                ['result' => $this->defaultInput()['result']],
            ],
            'when result maxBenefitQuotaExcessScore is negative number' => [
                ['result.maxBenefitQuotaExcessScore' => ['0以上で入力してください。']],
                ['result' => [
                    'maxBenefitExcessScore' => $this->ltcsProvisionReport->result->maxBenefitQuotaExcessScore,
                    'maxBenefitQuotaExcessScore' => -1,
                ]],
                ['result' => $this->defaultInput()['result']],
            ],
            // 'when result maxBenefitQuotaExcessScore over managedScore' => [
            //     ['result.maxBenefitQuotaExcessScore' => ['「区分支給限度基準を超える単位数」は0以上、「種類支給限度基準内単位数」以下の半角数字で入力してください。']],
            //     ['result' => [
            //         'maxBenefitExcessScore' => 24500,
            //         'maxBenefitQuotaExcessScore' => 1000,
            //     ]],
            //     ['result' => $this->defaultInput()['result']],
            // ],
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
                $this->assertEquals($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    foreach ($valid as $key => $value) {
                        Arr::set($input, $key, $value);
                    }
                    $validator = $this->request->createValidatorInstance($valid + $input);
                    $this->assertTrue($validator->passes(), $validator->errors()->toJson(\JSON_UNESCAPED_UNICODE));
                    $this->assertSame([], $validator->errors()->toArray());
                }
            },
            compact('examples')
        );
        $this->should(
            'fails when the status is fixed',
            function (): void {
                $input = $this->defaultInput();
                $this->getLtcsProvisionReportUseCase
                    ->expects('handle')
                    ->with(
                        anInstanceOf(Context::class),
                        Permission::updateLtcsProvisionReports(),
                        $input['officeId'],
                        $input['userId'],
                        equalTo(Carbon::parse($input['providedIn']))
                    )
                    ->andReturn(Option::from($this->examples->ltcsProvisionReports[3]));
                $validator = $this->request->createValidatorInstance($input);
                $this->assertTrue($validator->fails());
                $this->assertSame(['entries' => ['確定済みの予実は編集できません。']], $validator->errors()->toArray());
            },
        );
    }

    /**
     * リクエストクラスが受け取る入力のデフォルト値.
     *
     * @return array
     */
    private function defaultInput(): array
    {
        return [
            'entries' => Seq::fromArray($this->ltcsProvisionReport->entries)
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
                    'serviceCode' => $entry->serviceCode->toString(),
                    'options' => Seq::fromArray($entry->options)
                        ->map(fn (ServiceOption $option): int => $option->value())
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
            'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::addition1()->value(),
            'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::addition1()->value(),
            'specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::addition1()->value(),
            'baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::addition1()->value(),
            'locationAddition' => LtcsOfficeLocationAddition::specifiedArea()->value(),
            'plan' => [
                'maxBenefitExcessScore' => $this->ltcsProvisionReport->plan->maxBenefitExcessScore,
                'maxBenefitQuotaExcessScore' => $this->ltcsProvisionReport->plan->maxBenefitQuotaExcessScore,
            ],
            'result' => [
                'maxBenefitExcessScore' => $this->ltcsProvisionReport->result->maxBenefitExcessScore,
                'maxBenefitQuotaExcessScore' => $this->ltcsProvisionReport->result->maxBenefitQuotaExcessScore,
            ],

            // ルートパラメーター
            'officeId' => $this->ltcsProvisionReport->officeId,
            'userId' => $this->ltcsProvisionReport->userId,
            'providedIn' => $this->ltcsProvisionReport->providedIn->format('Y-m'),
        ];
    }

    /**
     * payload が返す連想配列.
     *
     * @param array $input
     * @return array
     */
    private function expectedPayload(array $input): array
    {
        return [
            'entries' => Seq::fromArray($input['entries'])
                ->map(fn (array $entry): LtcsProvisionReportEntry => LtcsProvisionReportEntry::create([
                    'ownExpenseProgramId' => $entry['ownExpenseProgramId'] ?? null,
                    'slot' => TimeRange::create([
                        'start' => $entry['slot']['start'],
                        'end' => $entry['slot']['end'],
                    ]),
                    'timeframe' => Timeframe::from($entry['timeframe']),
                    'category' => LtcsProjectServiceCategory::from($entry['category']),
                    'amounts' => Seq::fromArray($entry['amounts'])
                        ->map(fn (array $amount): LtcsProjectAmount => LtcsProjectAmount::create([
                            'category' => LtcsProjectAmountCategory::from($amount['category']),
                            'amount' => $amount['amount'],
                        ]))
                        ->toArray(),
                    'headcount' => $entry['headcount'],
                    'serviceCode' => isset($entry['serviceCode']) ? ServiceCode::fromString($entry['serviceCode']) : null,
                    'options' => Seq::fromArray($entry['options'])
                        ->map(fn (int $option): ServiceOption => ServiceOption::from($option))
                        ->toArray(),
                    'note' => $entry['note'] ?? '',
                    'plans' => empty($entry['plans'])
                        ? []
                        : Seq::fromArray($entry['plans'] ?: [])
                            ->map(fn (string $plan): Carbon => Carbon::parse($plan))
                            ->toArray(),
                    'results' => empty($entry['results'])
                        ? []
                        : Seq::fromArray($entry['results'] ?: [])
                            ->map(fn (string $result): Carbon => Carbon::parse($result))
                            ->toArray(),
                ]))
                ->toArray(),
            'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::addition1(),
            'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::addition1(),
            'specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::addition1(),
            'baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::addition1(),
            'locationAddition' => LtcsOfficeLocationAddition::specifiedArea(),
            'plan' => new LtcsProvisionReportOverScore(
                maxBenefitExcessScore: $input['plan']['maxBenefitExcessScore'],
                maxBenefitQuotaExcessScore: $input['plan']['maxBenefitQuotaExcessScore'],
            ),
            'result' => new LtcsProvisionReportOverScore(
                maxBenefitExcessScore: $input['result']['maxBenefitExcessScore'],
                maxBenefitQuotaExcessScore: $input['result']['maxBenefitQuotaExcessScore'],
            ),
        ];
    }
}
