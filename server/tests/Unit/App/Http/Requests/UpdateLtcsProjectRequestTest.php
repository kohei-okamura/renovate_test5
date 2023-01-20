<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateLtcsProjectRequest;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\DayOfWeek;
use Domain\Common\Recurrence;
use Domain\Common\TimeRange;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\Project\LtcsProject;
use Domain\Project\LtcsProjectAmount;
use Domain\Project\LtcsProjectAmountCategory;
use Domain\Project\LtcsProjectContent;
use Domain\Project\LtcsProjectProgram;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\Project\Objective;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\Timeframe;
use Domain\Shift\ServiceOption;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupLtcsProjectServiceMenuUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupOwnExpenseProgramUseCaseMixin;
use Tests\Unit\Mixins\LookupStaffUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * UpdateLtcsProjectRequest のテスト.
 */
class UpdateLtcsProjectRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupOfficeUseCaseMixin;
    use LookupStaffUseCaseMixin;
    use LookupLtcsProjectServiceMenuUseCaseMixin;
    use LookupUserUseCaseMixin;
    use MockeryMixin;
    use LookupOwnExpenseProgramUseCaseMixin;
    use UnitSupport;

    protected UpdateLtcsProjectRequest $request;
    protected LtcsProject $ltcsProject;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UpdateLtcsProjectRequestTest $self): void {
            $self->ltcsProject = $self->examples->ltcsProjects[0];
            $self->request = new UpdateLtcsProjectRequest();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::emptySeq()
            );

            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->ltcsProject->officeId))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), [Permission::updateLtcsProjects()], self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq())
                ->byDefault();
            $self->lookupLtcsProjectServiceMenuUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ltcsProjectServiceMenus[0]))
                ->byDefault();
            $self->lookupLtcsProjectServiceMenuUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq())
                ->byDefault();
            $self->lookupOwnExpenseProgramUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ownExpensePrograms[0]))
                ->byDefault();
            $self->lookupOwnExpenseProgramUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateLtcsProjects(), $self->examples->ownExpensePrograms[2]->id)
                ->andReturn(Seq::from($self->examples->ownExpensePrograms[2]))
                ->byDefault();
            $self->lookupOwnExpenseProgramUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateLtcsProjects(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq())
                ->byDefault();
            $self->lookupStaffUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->ltcsProject->staffId))
                ->byDefault();
            $self->lookupStaffUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateLtcsProjects(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq())
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_payload(): void
    {
        $this->should(
            'return LtcsProject',
            function (): void {
                $input = $this->defaultInput();
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
        );
        $this->should(
            'return LtcsProject when nullable property is empty',
            function (string $key): void {
                $input = $this->defaultInput();
                Arr::set($input, $key, '');

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
            },
            [
                'examples' => [
                    'when memo is empty' => [
                        'programs.0.contents.0.memo',
                    ],
                    'when programs.0.note' => [
                        'programs.0.note',
                    ],
                ],
            ]
        );
        $this->should(
            'return LtcsProject when nullable property is null',
            function (string $key): void {
                $input = $this->defaultInput();
                Arr::set($input, $key, null);

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
            },
            [
                'examples' => [
                    'when memo' => [
                        'programs.0.contents.0.memo',
                    ],
                    'when programs.0.ownExpenseProgramId' => [
                        'programs.0.ownExpenseProgramId',
                    ],
                    'when programs.0.note' => [
                        'programs.0.note',
                    ],
                ],
            ]
        );
        $this->should('return LtcsProject when nullable param is undefined', function (string $forgetKey) {
            $overwriteInput = $this->defaultInput();
            Arr::forget($overwriteInput, $forgetKey);
            // リクエスト内容を反映させるために initialize() を利用する
            $this->request->initialize(
                [],
                [],
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode($overwriteInput)
            );
            $this->assertEquals(
                $this->expectedPayload($overwriteInput),
                $this->request->payload()
            );
        }, [
            'examples' => [
                'when memo' => [
                    'programs.0.contents.0.memo',
                ],
                'when programs.0.ownExpenseProgramId' => [
                    'programs.0.ownExpenseProgramId',
                ],
                'when programs.0.note' => [
                    'programs.0.note',
                ],
            ],
        ]);
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
                ['officeId' => $this->ltcsProject->officeId],
            ],
            'when unknown officeId given' => [
                ['officeId' => ['正しい値を入力してください。'], 'programs.1.ownExpenseProgramId' => ['異なる事業所の自費サービスが含まれています。']],
                ['officeId' => self::NOT_EXISTING_ID],
                ['officeId' => $this->ltcsProject->officeId],
            ],
            'when staffId is empty' => [
                ['staffId' => ['入力してください。']],
                ['staffId' => ''],
                ['staffId' => $this->ltcsProject->staffId],
            ],
            'when unknown staffId given' => [
                ['staffId' => ['正しい値を入力してください。']],
                ['staffId' => self::NOT_EXISTING_ID],
                ['staffId' => $this->ltcsProject->staffId],
            ],
            'when writtenOn is empty' => [
                ['writtenOn' => ['入力してください。']],
                ['writtenOn' => ''],
                ['writtenOn' => $this->ltcsProject->writtenOn->toDateString()],
            ],
            'when writtenOn is not date' => [
                ['writtenOn' => ['正しい日付を入力してください。']],
                ['writtenOn' => '2021-02-30'],
                ['writtenOn' => $this->ltcsProject->writtenOn->toDateString()],
            ],
            'when effectivatedOn is empty' => [
                ['effectivatedOn' => ['入力してください。']],
                ['effectivatedOn' => ''],
                ['effectivatedOn' => $this->ltcsProject->effectivatedOn->toDateString()],
            ],
            'when effectivatedOn is not date' => [
                ['effectivatedOn' => ['正しい日付を入力してください。']],
                ['effectivatedOn' => '2021-02-30'],
                ['effectivatedOn' => $this->ltcsProject->effectivatedOn->toDateString()],
            ],
            'when requestFromUser is empty' => [
                ['requestFromUser' => ['入力してください。']],
                ['requestFromUser' => ''],
                ['requestFromUser' => $this->ltcsProject->requestFromUser],
            ],
            'when requestFromUser is not string' => [
                ['requestFromUser' => ['文字列で入力してください。']],
                ['requestFromUser' => 123],
                ['requestFromUser' => $this->ltcsProject->requestFromUser],
            ],
            'when requestFromFamily is empty' => [
                ['requestFromFamily' => ['入力してください。']],
                ['requestFromFamily' => ''],
                ['requestFromFamily' => $this->ltcsProject->requestFromFamily],
            ],
            'when requestFromFamily is not string' => [
                ['requestFromFamily' => ['文字列で入力してください。']],
                ['requestFromFamily' => 123],
                ['requestFromFamily' => $this->ltcsProject->requestFromFamily],
            ],
            'when problem is empty' => [
                ['problem' => ['入力してください。']],
                ['problem' => ''],
                ['problem' => $this->ltcsProject->problem],
            ],
            'when problem is not string' => [
                ['problem' => ['文字列で入力してください。']],
                ['problem' => 123],
                ['problem' => $this->ltcsProject->problem],
            ],
            'when longTermObjective.term.start is empty' => [
                ['longTermObjective.term.start' => ['入力してください。']],
                ['longTermObjective.term.start' => ''],
                ['longTermObjective.term.start' => $this->ltcsProject->longTermObjective->term->start],
            ],
            'when longTermObjective.term.start is not date' => [
                [
                    'longTermObjective.term.start' => ['正しい日付を入力してください。'],
                    'longTermObjective.term.end' => ['長期目標 開始日以降の日時を入力してください。'],
                ],
                ['longTermObjective.term.start' => '2021-02-30'],
                ['longTermObjective.term.start' => $this->ltcsProject->longTermObjective->term->start],
            ],
            'when longTermObjective.term.end is empty' => [
                ['longTermObjective.term.end' => ['入力してください。']],
                ['longTermObjective.term.end' => ''],
                ['longTermObjective.term.end' => $this->ltcsProject->longTermObjective->term->end],
            ],
            'when longTermObjective.term.end is not date' => [
                ['longTermObjective.term.end' => ['正しい日付を入力してください。']],
                ['longTermObjective.term.end' => '2021-02-30'],
                ['longTermObjective.term.end' => $this->ltcsProject->longTermObjective->term->end],
            ],
            'when longTermObjective.term.end is before longTermObjective.term.start' => [
                ['longTermObjective.term.end' => ['長期目標 開始日以降の日時を入力してください。']],
                ['longTermObjective.term.end' => '2001-02-01'],
                ['longTermObjective.term.end' => $this->ltcsProject->longTermObjective->term->end],
            ],
            'when longTermObjective.text is empty' => [
                ['longTermObjective.text' => ['入力してください。']],
                ['longTermObjective.text' => ''],
                ['longTermObjective.text' => $this->ltcsProject->longTermObjective->text],
            ],
            'when longTermObjective.text is not string' => [
                ['longTermObjective.text' => ['文字列で入力してください。']],
                ['longTermObjective.text' => 123],
                ['longTermObjective.text' => $this->ltcsProject->longTermObjective->text],
            ],
            'when shortTermObjective.term.start is empty' => [
                ['shortTermObjective.term.start' => ['入力してください。']],
                ['shortTermObjective.term.start' => ''],
                ['shortTermObjective.term.start' => $this->ltcsProject->shortTermObjective->term->start],
            ],
            'when shortTermObjective.term.start is not date' => [
                [
                    'shortTermObjective.term.start' => ['正しい日付を入力してください。'],
                    'shortTermObjective.term.end' => ['短期目標 開始日以降の日時を入力してください。'],
                ],
                ['shortTermObjective.term.start' => '2021-02-30'],
                ['shortTermObjective.term.start' => $this->ltcsProject->shortTermObjective->term->start],
            ],
            'when shortTermObjective.term.end is empty' => [
                ['shortTermObjective.term.end' => ['入力してください。']],
                ['shortTermObjective.term.end' => ''],
                ['shortTermObjective.term.end' => $this->ltcsProject->shortTermObjective->term->end],
            ],
            'when shortTermObjective.term.end is not date' => [
                ['shortTermObjective.term.end' => ['正しい日付を入力してください。']],
                ['shortTermObjective.term.end' => '2099-02-30'],
                ['shortTermObjective.term.end' => $this->ltcsProject->shortTermObjective->term->end],
            ],
            'when shortTermObjective.term.end is before shortTermObjective.term.start' => [
                ['shortTermObjective.term.end' => ['短期目標 開始日以降の日時を入力してください。']],
                ['shortTermObjective.term.end' => '2001-02-01'],
                ['shortTermObjective.term.end' => $this->ltcsProject->shortTermObjective->term->end],
            ],
            'when shortTermObjective.text is empty' => [
                ['shortTermObjective.text' => ['入力してください。']],
                ['shortTermObjective.text' => ''],
                ['shortTermObjective.text' => $this->ltcsProject->shortTermObjective->text],
            ],
            'when shortTermObjective.text is not string' => [
                ['shortTermObjective.text' => ['文字列で入力してください。']],
                ['shortTermObjective.text' => 123],
                ['shortTermObjective.text' => $this->ltcsProject->shortTermObjective->text],
            ],
            'when programs is empty' => [
                ['programs' => ['入力してください。']],
                ['programs' => []],
                ['programs' => $this->defaultInput()['programs']],
            ],
            'when programs is not array' => [
                ['programs' => ['配列にしてください。']],
                ['programs' => 'error'],
                ['programs' => $this->defaultInput()['programs']],
            ],
            'when programIndex is empty' => [
                ['programs.0.programIndex' => ['入力してください。']],
                ['programs.0.programIndex' => ''],
                ['programs.0.programIndex' => $this->ltcsProject->programs[0]->programIndex],
            ],
            'when programIndex is integer' => [
                ['programs.0.programIndex' => ['整数で入力してください。']],
                ['programs.0.programIndex' => 'error'],
                ['programs.0.programIndex' => $this->ltcsProject->programs[0]->programIndex],
            ],
            'when unknown category is empty' => [
                ['programs.0.category' => ['入力してください。']],
                ['programs.0.category' => ''],
                ['programs.0.category' => $this->ltcsProject->programs[0]->category->value()],
            ],
            'when unknown category given' => [
                ['programs.0.category' => ['介護保険サービス：計画：サービス区分を指定してください。']],
                ['programs.0.category' => self::INVALID_ENUM_VALUE],
                ['programs.0.category' => $this->ltcsProject->programs[0]->category->value()],
            ],
            'when unknown recurrence is empty' => [
                ['programs.0.recurrence' => ['入力してください。']],
                ['programs.0.recurrence' => ''],
                ['programs.0.recurrence' => $this->ltcsProject->programs[0]->recurrence->value()],
            ],
            'when unknown recurrence given' => [
                ['programs.0.recurrence' => ['繰り返し周期を指定してください。']],
                ['programs.0.recurrence' => self::INVALID_ENUM_VALUE],
                ['programs.0.recurrence' => $this->ltcsProject->programs[0]->recurrence->value()],
            ],
            'when dayOfWeeks is empty' => [
                ['programs.0.dayOfWeeks' => ['入力してください。']],
                ['programs.0.dayOfWeeks' => []],
                ['programs.0.dayOfWeeks' => [
                    $this->ltcsProject->programs[0]->dayOfWeeks[0]->value(),
                ]],
            ],
            'when dayOfWeeks is not array' => [
                ['programs.0.dayOfWeeks' => ['配列にしてください。']],
                ['programs.0.dayOfWeeks' => 'error'],
                ['programs.0.dayOfWeeks' => [
                    $this->ltcsProject->programs[0]->dayOfWeeks[0]->value(),
                ]],
            ],
            'when unknown dayOfWeek given' => [
                ['programs.0.dayOfWeeks.0' => ['曜日を指定してください。']],
                ['programs.0.dayOfWeeks.0' => 'error'],
                ['programs.0.dayOfWeeks.0' => $this->ltcsProject->programs[0]->dayOfWeeks[0]->value()],
            ],
            'when slot.start is empty' => [
                [
                    'programs.0.slot.start' => ['入力してください。'],
                    'programs.0.slot.end' => ['時間帯 開始時刻以降の日時を入力してください。'],
                ],
                ['programs.0.slot.start' => ''],
                ['programs.0.slot.start' => $this->ltcsProject->programs[0]->slot->start],
            ],
            'when slot.start is not date format H:i' => [
                [
                    'programs.0.slot.start' => ['正しい日付を入力してください。'],
                    'programs.0.slot.end' => ['時間帯 開始時刻以降の日時を入力してください。'],
                ],
                ['programs.0.slot.start' => '12:30:00'],
                ['programs.0.slot.start' => $this->ltcsProject->programs[0]->slot->start],
            ],
            'when slot.end is empty' => [
                ['programs.0.slot.end' => ['入力してください。']],
                ['programs.0.slot.end' => ''],
                ['programs.0.slot.end' => $this->ltcsProject->programs[0]->slot->end],
            ],
            'when slot.end is not date format H:i' => [
                ['programs.0.slot.end' => ['正しい日付を入力してください。']],
                ['programs.0.slot.end' => '12:30:00'],
                ['programs.0.slot.end' => $this->ltcsProject->programs[0]->slot->end],
            ],
            'when slot.end is before slot.start' => [
                ['programs.0.slot.end' => ['時間帯 開始時刻以降の日時を入力してください。']],
                ['programs.0.slot.end' => '00:00'],
                ['programs.0.slot.end' => $this->ltcsProject->programs[0]->slot->end],
            ],
            'when timeframe is empty' => [
                ['programs.0.timeframe' => ['入力してください。']],
                ['programs.0.timeframe' => []],
                ['programs.0.timeframe' => $this->ltcsProject->programs[0]->timeframe->value()],
            ],
            'when unknown timeframe given' => [
                ['programs.0.timeframe' => ['時間帯を指定してください。']],
                ['programs.0.timeframe' => self::INVALID_ENUM_VALUE],
                ['programs.0.timeframe' => $this->ltcsProject->programs[0]->timeframe->value()],
            ],
            'when amounts is empty' => [
                ['programs.0.amounts' => ['入力してください。']],
                ['programs.0.amounts' => []],
                ['programs.0.amounts' => $this->defaultInput()['programs'][0]['amounts']],
            ],
            'when amounts is not array' => [
                ['programs.0.amounts' => ['配列にしてください。']],
                ['programs.0.amounts' => 'error'],
                ['programs.0.amounts' => $this->defaultInput()['programs'][0]['amounts']],
            ],
            'when programs.0.amounts.0.category is empty' => [
                ['programs.0.amounts.0.category' => ['入力してください。']],
                ['programs.0.amounts.0.category' => []],
                ['programs.0.amounts.0.category' => $this->ltcsProject->programs[0]->amounts[0]->category->value()],
            ],
            'when unknown programs.0.amounts.0.category given' => [
                ['programs.0.amounts.0.category' => ['介護保険サービス：計画：サービス提供量を指定してください。']],
                ['programs.0.amounts.0.category' => self::INVALID_ENUM_VALUE],
                ['programs.0.amounts.0.category' => $this->ltcsProject->programs[0]->amounts[0]->category->value()],
            ],
            'when amount is empty' => [
                ['programs.0.amounts.0.amount' => ['入力してください。']],
                ['programs.0.amounts.0.amount' => []],
                ['programs.0.amounts.0.amount' => $this->ltcsProject->programs[0]->amounts[0]->amount],
            ],
            'when amount is not integer' => [
                ['programs.0.amounts.0.amount' => ['整数で入力してください。']],
                ['programs.0.amounts.0.amount' => 'error'],
                ['programs.0.amounts.0.amount' => $this->ltcsProject->programs[0]->amounts[0]->amount],
            ],
            'when headcount is empty' => [
                ['programs.0.headcount' => ['入力してください。']],
                ['programs.0.headcount' => ''],
                ['programs.0.headcount' => $this->ltcsProject->programs[0]->headcount],
            ],
            'when headcount is not integer' => [
                ['programs.0.headcount' => ['整数で入力してください。']],
                ['programs.0.headcount' => 'error'],
                ['programs.0.headcount' => $this->ltcsProject->programs[0]->headcount],
            ],
            'when unknown ownExpenseProgramId given' => [
                ['programs.0.ownExpenseProgramId' => ['正しい値を入力してください。']],
                ['programs.0.ownExpenseProgramId' => self::NOT_EXISTING_ID],
                ['programs.0.ownExpenseProgramId' => $this->ltcsProject->programs[0]->ownExpenseProgramId],
            ],
            'when other office ownExpenseProgramId given' => [
                ['programs.0.ownExpenseProgramId' => ['異なる事業所の自費サービスが含まれています。']],
                ['programs.0.ownExpenseProgramId' => $this->examples->ownExpensePrograms[2]->id],
                ['programs.0.ownExpenseProgramId' => $this->ltcsProject->programs[1]->ownExpenseProgramId],
            ],
            'when serviceCode is not string' => [
                ['programs.0.serviceCode' => ['文字列で入力してください。']],
                ['programs.0.serviceCode' => 123456],
                ['programs.0.serviceCode' => '123456'],
            ],
            'when serviceCode is longer than 6' => [
                ['programs.0.serviceCode' => ['6文字以内で入力してください。']],
                ['programs.0.serviceCode' => '1234567'],
                ['programs.0.serviceCode' => '123456'],
            ],
            'when unknown options given' => [
                ['programs.0.options.0' => ['サービスオプションを指定してください。']],
                ['programs.0.options.0' => self::INVALID_ENUM_VALUE],
                ['programs.0.options.0' => $this->ltcsProject->programs[0]->options[0]->value()],
            ],
            'when options contain invalid service option for LtcsProject' => [
                ['programs.0.options.0' => ['正しいサービスオプションを指定してください。']],
                ['programs.0.options.0' => ServiceOption::notificationEnabled()->value(), 'programs.0.category' => LtcsProjectServiceCategory::physicalCare()->value()],
                ['programs.0.options.0' => ServiceOption::over20()->value(), 'programs.0.category' => LtcsProjectServiceCategory::physicalCare()->value()],
            ],
            'when menuId is empty' => [
                ['programs.0.contents.0.menuId' => ['入力してください。']],
                ['programs.0.contents.0.menuId' => ''],
                ['programs.0.contents.0.menuId' => $this->ltcsProject->programs[0]->contents[0]->menuId],
            ],
            'when unknown menuId given' => [
                ['programs.0.contents.0.menuId' => ['正しい値を入力してください。']],
                ['programs.0.contents.0.menuId' => self::NOT_EXISTING_ID],
                ['programs.0.contents.0.menuId' => $this->ltcsProject->programs[0]->contents[0]->menuId],
            ],
            'when duration is empty' => [
                ['programs.0.contents.0.duration' => ['入力してください。']],
                ['programs.0.contents.0.duration' => ''],
                ['programs.0.contents.0.duration' => $this->ltcsProject->programs[0]->contents[0]->duration],
            ],
            'when duration is not integer' => [
                ['programs.0.contents.0.duration' => ['整数で入力してください。']],
                ['programs.0.contents.0.duration' => 'error'],
                ['programs.0.contents.0.duration' => $this->ltcsProject->programs[0]->contents[0]->duration],
            ],
            'when content is empty' => [
                ['programs.0.contents.0.content' => ['入力してください。']],
                ['programs.0.contents.0.content' => ''],
                ['programs.0.contents.0.content' => $this->ltcsProject->programs[0]->contents[0]->content],
            ],
            'when content is not string' => [
                ['programs.0.contents.0.content' => ['文字列で入力してください。']],
                ['programs.0.contents.0.content' => 123],
                ['programs.0.contents.0.content' => $this->ltcsProject->programs[0]->contents[0]->content],
            ],
            'when memo is not string' => [
                ['programs.0.contents.0.memo' => ['文字列で入力してください。']],
                ['programs.0.contents.0.memo' => 123],
                ['programs.0.contents.0.memo' => $this->ltcsProject->programs[0]->contents[0]->memo],
            ],
            'when programs.0.note' => [
                ['programs.0.note' => ['文字列で入力してください。']],
                ['programs.0.note' => 123],
                ['programs.0.note' => $this->ltcsProject->programs[0]->note],
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
    }

    /**
     * リクエストクラスが受け取る入力のデフォルト値.
     *
     * @return array
     */
    private function defaultInput(): array
    {
        return [
            'officeId' => $this->ltcsProject->officeId,
            'staffId' => $this->ltcsProject->staffId,
            'writtenOn' => $this->ltcsProject->writtenOn->toDateString(),
            'effectivatedOn' => $this->ltcsProject->effectivatedOn->toDateString(),
            'requestFromUser' => $this->ltcsProject->requestFromUser,
            'requestFromFamily' => $this->ltcsProject->requestFromFamily,
            'problem' => $this->ltcsProject->problem,
            'longTermObjective' => [
                'term' => [
                    'start' => $this->ltcsProject->longTermObjective->term->start->toDateString(),
                    'end' => $this->ltcsProject->longTermObjective->term->end->toDateString(),
                ],
                'text' => $this->ltcsProject->longTermObjective->text,
            ],
            'shortTermObjective' => [
                'term' => [
                    'start' => $this->ltcsProject->shortTermObjective->term->start->toDateString(),
                    'end' => $this->ltcsProject->shortTermObjective->term->end->toDateString(),
                ],
                'text' => $this->ltcsProject->shortTermObjective->text,
            ],
            'programs' => Seq::fromArray($this->ltcsProject->programs)
                ->map(fn (LtcsProjectProgram $program): array => [
                    'programIndex' => $program->programIndex,
                    'category' => $program->category->value(),
                    'recurrence' => $program->recurrence->value(),
                    'dayOfWeeks' => Seq::fromArray($program->dayOfWeeks)
                        ->map(fn (DayOfWeek $x): int => $x->value())
                        ->toArray(),
                    'slot' => [
                        'start' => $program->slot->start,
                        'end' => $program->slot->end,
                    ],
                    'timeframe' => $program->timeframe->value(),
                    'amounts' => Seq::fromArray($program->amounts)
                        ->map(fn (LtcsProjectAmount $amount): array => [
                            'category' => $amount->category->value(),
                            'amount' => $amount->amount,
                        ])
                        ->toArray(),
                    'headcount' => $program->headcount,
                    'ownExpenseProgramId' => $program->ownExpenseProgramId,
                    'serviceCode' => $program->serviceCode->toString(),
                    'options' => Seq::fromArray($program->options)
                        ->map(fn (ServiceOption $option): int => $option->value())
                        ->toArray(),
                    'contents' => Seq::fromArray($program->contents)
                        ->map(fn (LtcsProjectContent $content): array => [
                            'menuId' => $content->menuId,
                            'duration' => $content->duration,
                            'content' => $content->content,
                            'memo' => $content->memo ?? '',
                        ])
                        ->toArray(),
                    'note' => $program->note,
                ])
                ->toArray(),
        ];
    }

    /**
     * payload が返す配列.
     *
     * @param array $input
     * @return array
     */
    private function expectedPayload(array $input): array
    {
        return [
            'officeId' => $input['officeId'],
            'staffId' => $input['staffId'],
            'writtenOn' => Carbon::parse($input['writtenOn']),
            'effectivatedOn' => Carbon::parse($input['effectivatedOn']),
            'requestFromUser' => $input['requestFromUser'],
            'requestFromFamily' => $input['requestFromFamily'],
            'problem' => $input['problem'],
            'longTermObjective' => Objective::create([
                'term' => CarbonRange::create([
                    'start' => Carbon::parse($input['longTermObjective']['term']['start']),
                    'end' => Carbon::parse($input['longTermObjective']['term']['end']),
                ]),
                'text' => $input['longTermObjective']['text'],
            ]),
            'shortTermObjective' => Objective::create([
                'term' => CarbonRange::create([
                    'start' => Carbon::parse($input['shortTermObjective']['term']['start']),
                    'end' => Carbon::parse($input['shortTermObjective']['term']['end']),
                ]),
                'text' => $input['shortTermObjective']['text'],
            ]),
            'programs' => Seq::fromArray($input['programs'])
                ->map(fn (array $program): LtcsProjectProgram => LtcsProjectProgram::create([
                    'programIndex' => $program['programIndex'],
                    'category' => LtcsProjectServiceCategory::from($program['category']),
                    'recurrence' => Recurrence::from($program['recurrence']),
                    'dayOfWeeks' => Seq::fromArray($program['dayOfWeeks'])
                        ->map(fn (int $dayOfWeek): DayOfWeek => DayOfWeek::from($dayOfWeek))
                        ->toArray(),
                    'slot' => TimeRange::create([
                        'start' => $program['slot']['start'],
                        'end' => $program['slot']['end'],
                    ]),
                    'timeframe' => Timeframe::from($program['timeframe']),
                    'amounts' => Seq::fromArray($program['amounts'])
                        ->map(fn (array $amount): LtcsProjectAmount => LtcsProjectAmount::create([
                            'category' => LtcsProjectAmountCategory::from($amount['category']),
                            'amount' => $amount['amount'],
                        ]))
                        ->toArray(),
                    'headcount' => $program['headcount'],
                    'ownExpenseProgramId' => $program['ownExpenseProgramId'] ?? null,
                    'serviceCode' => ServiceCode::fromString($program['serviceCode']),
                    'options' => Seq::fromArray($program['options'])
                        ->map(fn (int $option): ServiceOption => ServiceOption::from($option))
                        ->toArray(),
                    'contents' => Seq::fromArray($program['contents'])
                        ->map(fn (array $content): LtcsProjectContent => LtcsProjectContent::create([
                            'menuId' => $content['menuId'],
                            'duration' => $content['duration'],
                            'content' => $content['content'],
                            'memo' => $content['memo'] ?? '',
                        ]))
                        ->toArray(),
                    'note' => $program['note'] ?? '',
                ]))
                ->toArray(),
        ];
    }
}
