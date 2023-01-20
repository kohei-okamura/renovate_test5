<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateDwsProjectRequest;
use Domain\Common\Carbon;
use Domain\Common\DayOfWeek;
use Domain\Common\Recurrence;
use Domain\Common\TimeRange;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\Project\DwsProject;
use Domain\Project\DwsProjectContent;
use Domain\Project\DwsProjectProgram;
use Domain\Project\DwsProjectServiceCategory;
use Domain\Shift\ServiceOption;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupDwsProjectServiceMenuUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupOwnExpenseProgramUseCaseMixin;
use Tests\Unit\Mixins\LookupStaffUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\UpdateDwsProjectRequest} のテスト.
 */
class UpdateDwsProjectRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupOfficeUseCaseMixin;
    use LookupStaffUseCaseMixin;
    use LookupDwsProjectServiceMenuUseCaseMixin;
    use LookupOwnExpenseProgramUseCaseMixin;
    use LookupUserUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    protected UpdateDwsProjectRequest $request;
    protected DwsProject $dwsProject;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UpdateDwsProjectRequestTest $self): void {
            $self->dwsProject = $self->examples->dwsProjects[0];
            $self->request = new UpdateDwsProjectRequest();
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
                ->andReturn(Seq::from($self->dwsProject->officeId))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), [Permission::updateDwsProjects()], self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq())
                ->byDefault();
            $self->lookupDwsProjectServiceMenuUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsProjectServiceMenus[0]))
                ->byDefault();
            $self->lookupDwsProjectServiceMenuUseCase
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
                ->with(anInstanceOf(Context::class), Permission::updateDwsProjects(), $self->examples->ownExpensePrograms[2]->id)
                ->andReturn(Seq::from($self->examples->ownExpensePrograms[2]))
                ->byDefault();
            $self->lookupOwnExpenseProgramUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateDwsProjects(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq())
                ->byDefault();
            $self->lookupStaffUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->dwsProject->staffId))
                ->byDefault();
            $self->lookupStaffUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateDwsProjects(), self::NOT_EXISTING_ID)
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
        $this->should('return DwsProject', function (): void {
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
        });
        $this->should(
            'return DwsProject when nullable property is empty',
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
                    'when memo' => [
                        'programs.0.contents.0.memo',
                    ],
                    'when programs.0.note' => [
                        'programs.0.note',
                    ],
                ],
            ]
        );
        $this->should(
            'return DwsProject when nullable property is null',
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
        $this->should('return DwsProject when nullable param is undefined', function (string $forgetKey) {
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
                ['officeId' => $this->dwsProject->officeId],
            ],
            'when unknown officeId given' => [
                ['officeId' => ['正しい値を入力してください。'], 'programs.1.ownExpenseProgramId' => ['異なる事業所の自費サービスが含まれています。']],
                ['officeId' => self::NOT_EXISTING_ID],
                ['officeId' => $this->dwsProject->officeId],
            ],
            'when staffId is empty' => [
                ['staffId' => ['入力してください。']],
                ['staffId' => ''],
                ['staffId' => $this->dwsProject->staffId],
            ],
            'when unknown staffId given' => [
                ['staffId' => ['正しい値を入力してください。']],
                ['staffId' => self::NOT_EXISTING_ID],
                ['staffId' => $this->dwsProject->staffId],
            ],
            'when writtenOn is empty' => [
                ['writtenOn' => ['入力してください。']],
                ['writtenOn' => ''],
                ['writtenOn' => $this->dwsProject->writtenOn->toDateString()],
            ],
            'when writtenOn is not date' => [
                ['writtenOn' => ['正しい日付を入力してください。']],
                ['writtenOn' => '2021-02-30'],
                ['writtenOn' => $this->dwsProject->writtenOn->toDateString()],
            ],
            'when effectivatedOn is empty' => [
                ['effectivatedOn' => ['入力してください。']],
                ['effectivatedOn' => ''],
                ['effectivatedOn' => $this->dwsProject->effectivatedOn->toDateString()],
            ],
            'when effectivatedOn is not date' => [
                ['effectivatedOn' => ['正しい日付を入力してください。']],
                ['effectivatedOn' => '2021-02-30'],
                ['effectivatedOn' => $this->dwsProject->effectivatedOn->toDateString()],
            ],
            'when requestFromUser is empty' => [
                ['requestFromUser' => ['入力してください。']],
                ['requestFromUser' => ''],
                ['requestFromUser' => $this->dwsProject->requestFromUser],
            ],
            'when requestFromUser is not string' => [
                ['requestFromUser' => ['文字列で入力してください。']],
                ['requestFromUser' => 123],
                ['requestFromUser' => $this->dwsProject->requestFromUser],
            ],
            'when requestFromFamily is empty' => [
                ['requestFromFamily' => ['入力してください。']],
                ['requestFromFamily' => ''],
                ['requestFromFamily' => $this->dwsProject->requestFromFamily],
            ],
            'when requestFromFamily is not string' => [
                ['requestFromFamily' => ['文字列で入力してください。']],
                ['requestFromFamily' => 123],
                ['requestFromFamily' => $this->dwsProject->requestFromFamily],
            ],
            'when objective is empty' => [
                ['objective' => ['入力してください。']],
                ['objective' => ''],
                ['objective' => $this->dwsProject->objective],
            ],
            'when objective is not string' => [
                ['objective' => ['文字列で入力してください。']],
                ['objective' => 123],
                ['objective' => $this->dwsProject->objective],
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
            'when summaryIndex is empty' => [
                ['programs.0.summaryIndex' => ['入力してください。']],
                ['programs.0.summaryIndex' => ''],
                ['programs.0.summaryIndex' => $this->dwsProject->programs[0]->summaryIndex],
            ],
            'when summaryIndex is integer' => [
                ['programs.0.summaryIndex' => ['整数で入力してください。']],
                ['programs.0.summaryIndex' => 'error'],
                ['programs.0.summaryIndex' => $this->dwsProject->programs[0]->summaryIndex],
            ],
            'when unknown category is empty' => [
                ['programs.0.category' => ['入力してください。']],
                ['programs.0.category' => ''],
                ['programs.0.category' => $this->dwsProject->programs[0]->category->value()],
            ],
            'when unknown category given' => [
                ['programs.0.category' => ['障害福祉サービス：計画：サービス区分を指定してください。']],
                ['programs.0.category' => self::INVALID_ENUM_VALUE],
                ['programs.0.category' => $this->dwsProject->programs[0]->category->value()],
            ],
            'when unknown recurrence is empty' => [
                ['programs.0.recurrence' => ['入力してください。']],
                ['programs.0.recurrence' => ''],
                ['programs.0.recurrence' => $this->dwsProject->programs[0]->recurrence->value()],
            ],
            'when unknown recurrence given' => [
                ['programs.0.recurrence' => ['繰り返し周期を指定してください。']],
                ['programs.0.recurrence' => self::INVALID_ENUM_VALUE],
                ['programs.0.recurrence' => $this->dwsProject->programs[0]->recurrence->value()],
            ],
            'when dayOfWeeks is empty' => [
                ['programs.0.dayOfWeeks' => ['入力してください。']],
                ['programs.0.dayOfWeeks' => []],
                ['programs.0.dayOfWeeks' => [
                    $this->dwsProject->programs[0]->dayOfWeeks[0]->value(),
                ]],
            ],
            'when dayOfWeeks is not array' => [
                ['programs.0.dayOfWeeks' => ['配列にしてください。']],
                ['programs.0.dayOfWeeks' => 'error'],
                ['programs.0.dayOfWeeks' => [
                    $this->dwsProject->programs[0]->dayOfWeeks[0]->value(),
                ]],
            ],
            'when unknown dayOfWeek given' => [
                ['programs.0.dayOfWeeks.0' => ['曜日を指定してください。']],
                ['programs.0.dayOfWeeks.0' => 'error'],
                ['programs.0.dayOfWeeks.0' => $this->dwsProject->programs[0]->dayOfWeeks[0]->value()],
            ],
            'when slot.start is empty' => [
                [
                    'programs.0.slot.start' => ['入力してください。'],
                    'programs.0.slot.end' => ['時間帯 開始時刻以降の日時を入力してください。'],
                ],
                ['programs.0.slot.start' => ''],
                ['programs.0.slot.start' => $this->dwsProject->programs[0]->slot->start],
            ],
            'when slot.start is not date format H:i' => [
                [
                    'programs.0.slot.start' => ['正しい日付を入力してください。'],
                    'programs.0.slot.end' => ['時間帯 開始時刻以降の日時を入力してください。'],
                ],
                ['programs.0.slot.start' => '12:30:00'],
                ['programs.0.slot.start' => $this->dwsProject->programs[0]->slot->start],
            ],
            'when slot.end is empty' => [
                ['programs.0.slot.end' => ['入力してください。']],
                ['programs.0.slot.end' => ''],
                ['programs.0.slot.end' => $this->dwsProject->programs[0]->slot->end],
            ],
            'when slot.end is not date format H:i' => [
                ['programs.0.slot.end' => ['正しい日付を入力してください。']],
                ['programs.0.slot.end' => '12:30:00'],
                ['programs.0.slot.end' => $this->dwsProject->programs[0]->slot->end],
            ],
            'when slot.end is before slot.start' => [
                ['programs.0.slot.end' => ['時間帯 開始時刻以降の日時を入力してください。']],
                ['programs.0.slot.end' => '00:00'],
                ['programs.0.slot.end' => $this->dwsProject->programs[0]->slot->end],
            ],
            'when headcount is empty' => [
                ['programs.0.headcount' => ['入力してください。']],
                ['programs.0.headcount' => ''],
                ['programs.0.headcount' => $this->dwsProject->programs[0]->headcount],
            ],
            'when headcount is not integer' => [
                ['programs.0.headcount' => ['整数で入力してください。']],
                ['programs.0.headcount' => 'error'],
                ['programs.0.headcount' => $this->dwsProject->programs[0]->headcount],
            ],
            'when unknown ownExpenseProgramId given' => [
                ['programs.0.ownExpenseProgramId' => ['正しい値を入力してください。']],
                ['programs.0.ownExpenseProgramId' => self::NOT_EXISTING_ID],
                ['programs.0.ownExpenseProgramId' => $this->dwsProject->programs[1]->ownExpenseProgramId],
            ],
            'when other office ownExpenseProgramId given' => [
                ['programs.0.ownExpenseProgramId' => ['異なる事業所の自費サービスが含まれています。']],
                ['programs.0.ownExpenseProgramId' => $this->examples->ownExpensePrograms[2]->id],
                ['programs.0.ownExpenseProgramId' => $this->dwsProject->programs[1]->ownExpenseProgramId],
            ],
            'when unknown options given' => [
                ['programs.0.options.0' => ['サービスオプションを指定してください。']],
                ['programs.0.options.0' => self::INVALID_ENUM_VALUE],
                ['programs.0.options.0' => $this->dwsProject->programs[0]->options[0]->value()],
            ],
            'when options contain invalid service option for DwsProject' => [
                ['programs.0.options.0' => ['正しいサービスオプションを指定してください。']],
                ['programs.0.options.0' => ServiceOption::notificationEnabled()->value(), 'programs.0.category' => DwsProjectServiceCategory::physicalCare()->value()],
                ['programs.0.options.0' => ServiceOption::sucking()->value(), 'programs.0.category' => DwsProjectServiceCategory::physicalCare()->value()],
            ],
            'when menuId is empty' => [
                ['programs.0.contents.0.menuId' => ['入力してください。']],
                ['programs.0.contents.0.menuId' => ''],
                ['programs.0.contents.0.menuId' => $this->dwsProject->programs[0]->contents[0]->menuId],
            ],
            'when unknown menuId given' => [
                ['programs.0.contents.0.menuId' => ['正しい値を入力してください。']],
                ['programs.0.contents.0.menuId' => self::NOT_EXISTING_ID],
                ['programs.0.contents.0.menuId' => $this->dwsProject->programs[0]->contents[0]->menuId],
            ],
            'when duration is empty' => [
                ['programs.0.contents.0.duration' => ['入力してください。']],
                ['programs.0.contents.0.duration' => ''],
                ['programs.0.contents.0.duration' => $this->dwsProject->programs[0]->contents[0]->duration],
            ],
            'when duration is not integer' => [
                ['programs.0.contents.0.duration' => ['整数で入力してください。']],
                ['programs.0.contents.0.duration' => 'error'],
                ['programs.0.contents.0.duration' => $this->dwsProject->programs[0]->contents[0]->duration],
            ],
            'when memo is not string' => [
                ['programs.0.contents.0.memo' => ['文字列で入力してください。']],
                ['programs.0.contents.0.memo' => 123],
                ['programs.0.contents.0.memo' => $this->dwsProject->programs[0]->contents[0]->memo],
            ],
            'when programs.0.note' => [
                ['programs.0.note' => ['文字列で入力してください。']],
                ['programs.0.note' => 123],
                ['programs.0.note' => $this->dwsProject->programs[0]->note],
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
            'officeId' => $this->dwsProject->officeId,
            'staffId' => $this->dwsProject->staffId,
            'writtenOn' => $this->dwsProject->writtenOn->toDateString(),
            'effectivatedOn' => $this->dwsProject->effectivatedOn->toDateString(),
            'requestFromUser' => $this->dwsProject->requestFromUser,
            'requestFromFamily' => $this->dwsProject->requestFromFamily,
            'objective' => $this->dwsProject->objective,
            'programs' => Seq::fromArray($this->dwsProject->programs)
                ->map(fn (DwsProjectProgram $program): array => [
                    'summaryIndex' => $program->summaryIndex,
                    'category' => $program->category->value(),
                    'recurrence' => $program->recurrence->value(),
                    'dayOfWeeks' => Seq::fromArray($program->dayOfWeeks)
                        ->map(fn (DayOfWeek $x): int => $x->value())
                        ->toArray(),
                    'slot' => [
                        'start' => $program->slot->start,
                        'end' => $program->slot->end,
                    ],
                    'headcount' => $program->headcount,
                    'ownExpenseProgramId' => $program->ownExpenseProgramId,
                    'options' => Seq::fromArray($program->options)
                        ->map(fn (ServiceOption $option): int => $option->value())
                        ->toArray(),
                    'contents' => Seq::fromArray($program->contents)
                        ->map(fn (DwsProjectContent $content): array => [
                            'menuId' => $content->menuId,
                            'duration' => $content->duration,
                            'content' => $content->content,
                            'memo' => $content->memo,
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
            'objective' => $input['objective'],
            'programs' => Seq::fromArray($input['programs'])
                ->map(fn (array $program): DwsProjectProgram => DwsProjectProgram::create([
                    'summaryIndex' => $program['summaryIndex'],
                    'category' => DwsProjectServiceCategory::from($program['category']),
                    'recurrence' => Recurrence::from($program['recurrence']),
                    'dayOfWeeks' => Seq::fromArray($program['dayOfWeeks'])
                        ->map(fn (int $dayOfWeek): DayOfWeek => DayOfWeek::from($dayOfWeek))
                        ->toArray(),
                    'slot' => TimeRange::create([
                        'start' => $program['slot']['start'],
                        'end' => $program['slot']['end'],
                    ]),
                    'headcount' => $program['headcount'],
                    'ownExpenseProgramId' => $program['ownExpenseProgramId'] ?? null,
                    'options' => Seq::fromArray($program['options'])
                        ->map(fn (int $option): ServiceOption => ServiceOption::from($option))
                        ->toArray(),
                    'contents' => Seq::fromArray($program['contents'])
                        ->map(fn (array $content): DwsProjectContent => DwsProjectContent::create([
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
