<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\CreateShiftTemplateRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\Shift\Shift;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Test;

/**
 * CreateShiftTemplateRequest のテスト
 */
class CreateShiftTemplateRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupOfficeUseCaseMixin;
    use UnitSupport;

    protected CreateShiftTemplateRequest $request;
    private Shift $shift;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateShiftTemplateRequestTest $self): void {
            $self->request = new CreateShiftTemplateRequest();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            $self->shift = $self->examples->shifts[0]->copy(['organizationId' => $self->examples->organizations[0]->id]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::emptySeq(),
            );

            $self->lookupOfficeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), [Permission::createShifts()], $self->shift->officeId)
                ->andReturn(Seq::from($self->examples->offices[0]));
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), [Permission::createShifts()], self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());
        });
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
                ['officeId' => $this->examples->shifts[0]->officeId],
            ],
            'when unknown officeId given' => [
                ['officeId' => ['正しい値を入力してください。']],
                ['officeId' => self::NOT_EXISTING_ID],
                ['officeId' => $this->examples->shifts[0]->officeId],
            ],
            'when `range` is empty' => [
                [
                    'range' => ['入力してください。'],
                    'range.start' => ['入力してください。'],
                    'range.end' => ['入力してください。'],
                ],
                ['range' => []],
                ['range' => [
                    'start' => $this->examples->shifts[0]->schedule->date->addWeek()->toDateString(),
                    'end' => $this->examples->shifts[0]->schedule->date->addDays(5)->addWeek()->toDateString(),
                ]],
            ],
            'when `range` is not array' => [
                [
                    'range' => ['配列にしてください。'],
                    'range.start' => ['入力してください。'],
                    'range.end' => ['入力してください。'],
                ],
                ['range' => 'dummy'],
                ['range' => [
                    'start' => $this->examples->shifts[0]->schedule->date->addWeek()->toDateString(),
                    'end' => $this->examples->shifts[0]->schedule->date->addDays(5)->addWeek()->toDateString(),
                ]],
            ],
            'when `range.start` is null' => [
                ['range.start' => ['入力してください。']],
                ['range.start' => null],
                ['range.start' => $this->examples->shifts[0]->schedule->date->addWeek()->toDateString()],
            ],
            'when `range.start` is non-date' => [
                ['range.start' => ['正しい日付を入力してください。']],
                ['range.start' => 'TEXT'],
                ['range.start' => $this->examples->shifts[0]->schedule->date->addWeek()->toDateString()],
            ],
            'when `range.end` is null' => [
                ['range.end' => ['入力してください。']],
                ['range.end' => null],
                ['range.end' => $this->examples->shifts[0]->schedule->date->addDays(5)->addWeek()->toDateString()],
            ],
            'when `range.end` is non-date' => [
                ['range.end' => ['正しい日付を入力してください。', 'range.start以降の日付を入力してください。']],
                ['range.end' => 'TEXT'],
                ['range.end' => $this->examples->shifts[0]->schedule->date->addDays(5)->addWeek()->toDateString()],
            ],
            'when `range.end` is before range.start' => [
                ['range.end' => ['range.start以降の日付を入力してください。']],
                ['range.end' => $this->examples->shifts[0]->schedule->date->subDays(5)->addWeek()->toDateString()],
                ['range.end' => $this->examples->shifts[0]->schedule->date->addDays(5)->addWeek()->toDateString()],
            ],
            'when `source` is empty with isCopy is true' => [
                [
                    'source' => ['is copyがtrueの時、sourceは必ず入力してください。'],
                    'source.start' => ['is copyがtrueの時、source.startは必ず入力してください。'],
                    'source.end' => ['is copyがtrueの時、source.endは必ず入力してください。'],
                ],
                ['source' => []],
                ['source' => [
                    'start' => $this->examples->shifts[0]->schedule->date->toDateString(),
                    'end' => $this->examples->shifts[0]->schedule->date->addDays(5)->toDateString(),
                ]],
            ],
            'when `source` is not array' => [
                [
                    'source' => ['配列にしてください。'],
                    'source.start' => ['is copyがtrueの時、source.startは必ず入力してください。'],
                    'source.end' => ['is copyがtrueの時、source.endは必ず入力してください。'],
                ],
                ['source' => 'dummy'],
                ['source' => [
                    'start' => $this->examples->shifts[0]->schedule->date->toDateString(),
                    'end' => $this->examples->shifts[0]->schedule->date->addDays(5)->toDateString(),
                ]],
            ],
            'when `source` is not same range as `range`' => [
                [
                    'source' => ['rangeと同じ期間を指定してください。'],
                ],
                ['source' => [
                    'start' => $this->examples->shifts[0]->schedule->date->toDateString(),
                    'end' => $this->examples->shifts[0]->schedule->date->addDays(7)->toDateString(),
                ]],
                ['source' => [
                    'start' => $this->examples->shifts[0]->schedule->date->toDateString(),
                    'end' => $this->examples->shifts[0]->schedule->date->addDays(5)->toDateString(),
                ]],
            ],
            'when `source.start` is empty with isCopy is true' => [
                ['source.start' => ['is copyがtrueの時、source.startは必ず入力してください。']],
                ['source.start' => null],
                ['source.start' => $this->examples->shifts[0]->schedule->date->toDateString()],
            ],
            'when `source.start` is string' => [
                ['source.start' => ['正しい日付を入力してください。']],
                ['source.start' => 'TEXT'],
                ['source.start' => $this->examples->shifts[0]->schedule->date->toDateString()],
            ],
            'when `source.start` is not same weekday as `range.start`' => [
                [
                    'source' => ['rangeと同じ期間を指定してください。'],
                    'source.start' => ['range.startと同じ曜日である必要があります。'],
                ],
                ['source.start' => $this->examples->shifts[0]->schedule->date->addDay()->toDateString()],
                ['source.start' => $this->examples->shifts[0]->schedule->date->toDateString()],
            ],
            'when `source.end` is empty with isCopy is true' => [
                ['source.end' => ['is copyがtrueの時、source.endは必ず入力してください。']],
                ['source.end' => null],
                ['source.end' => $this->examples->shifts[0]->schedule->date->addDays(5)->toDateString()],
            ],
            'when `source.end` is string' => [
                [
                    'source.end' => ['正しい日付を入力してください。', 'source.start以降の日付を入力してください。'],
                ],
                ['source.end' => 'TEXT'],
                ['source.end' => $this->examples->shifts[0]->schedule->date->addDays(5)->toDateString()],
            ],
            'when `source.end` is not same weekday as `range.start`' => [
                [
                    'source' => ['rangeと同じ期間を指定してください。'],
                    'source.end' => ['source.start以降の日付を入力してください。'],
                ],
                ['source.end' => $this->examples->shifts[0]->schedule->date->subDay()->toDateString()],
                ['source.end' => $this->examples->shifts[0]->schedule->date->addDays(5)->toDateString()],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $failedInput = $this->defaultInput();
                foreach ($invalid as $key => $value) {
                    Arr::set($failedInput, $key, $value);
                }
                $validator = $this->request->createValidatorInstance($failedInput);
                $this->assertTrue($validator->fails());
                $this->assertSame($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    $normalInput = $this->defaultInput();
                    foreach ($valid as $key => $value) {
                        Arr::set($normalInput, $key, $value);
                    }
                    $validator = $this->request->createValidatorInstance($normalInput);
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
            'officeId' => $this->examples->shifts[0]->officeId,
            'isCopy' => true,
            'source' => [
                'start' => $this->examples->shifts[0]->schedule->date->toDateString(),
                'end' => $this->examples->shifts[0]->schedule->date->addDays(5)->toDateString(),
            ],
            'range' => [
                'start' => $this->examples->shifts[0]->schedule->date->addWeek()->toDateString(),
                'end' => $this->examples->shifts[0]->schedule->date->addDays(5)->addWeek()->toDateString(),
            ],
        ];
    }
}
