<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateHomeVisitLongTermCareCalcSpecRequest;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Office\HomeHelpServiceSpecifiedOfficeAddition;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\Office\LtcsBaseIncreaseSupportAddition;
use Domain\Office\LtcsOfficeLocationAddition;
use Domain\Office\LtcsSpecifiedTreatmentImprovementAddition;
use Domain\Office\LtcsTreatmentImprovementAddition;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Test;

/**
 * UpdateHomeVisitLongTermCareCalcSpecRequest のテスト
 */
class UpdateHomeVisitLongTermCareCalcSpecRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use UnitSupport;

    protected UpdateHomeVisitLongTermCareCalcSpecRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UpdateHomeVisitLongTermCareCalcSpecRequestTest $self): void {
            $self->request = new UpdateHomeVisitLongTermCareCalcSpecRequest();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::emptySeq(),
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_payload(): void
    {
        $this->should('return HomeVisitLongTermCareCalcSpec', function (): void {
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
                $this->expectedPayload(),
                $this->request->payload()
            );
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
        $input = $this->defaultInput();
        $examples = [
            'when period.start is empty' => [
                ['period.start' => ['入力してください。']],
                [
                    'period' => [
                        'start' => '',
                        'end' => $input['period']['end'],
                    ],
                ],
                [
                    'period' => [
                        'start' => $input['period']['start'],
                        'end' => $input['period']['end'],
                    ],
                ],
            ],
            'when period.start is not date' => [
                ['period.start' => ['正しい日付を入力してください。']],
                [
                    'period' => [
                        'start' => 'date',
                        'end' => $input['period']['end'],
                    ],
                ],
                [
                    'period' => [
                        'start' => $input['period']['start'],
                        'end' => $input['period']['end'],
                    ],
                ],
            ],
            'when period.end is empty' => [
                ['period.end' => ['入力してください。']],
                [
                    'period' => [
                        'start' => $input['period']['start'],
                        'end' => '',
                    ],
                ],
                [
                    'period' => [
                        'start' => $input['period']['start'],
                        'end' => $input['period']['end'],
                    ],
                ],
            ],
            'when period.end is not date' => [
                ['period.end' => ['正しい日付を入力してください。', '適用期間開始以降の日時を入力してください。']],
                [
                    'period' => [
                        'start' => $input['period']['start'],
                        'end' => 'date',
                    ],
                ],
                [
                    'period' => [
                        'start' => $input['period']['start'],
                        'end' => $input['period']['end'],
                    ],
                ],
            ],
            'when period.end is after period.start' => [
                ['period.end' => ['適用期間開始以降の日時を入力してください。']],
                [
                    'period' => [
                        'start' => $input['period']['start'],
                        'end' => Carbon::parse($input['period']['start'])->subWeek()->format('Y-m-d'),
                    ],
                ],
                [
                    'period' => [
                        'start' => $input['period']['start'],
                        'end' => $input['period']['end'],
                    ],
                ],
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
            'when treatmentImprovementAddition is empty' => [
                ['treatmentImprovementAddition' => ['入力してください。']],
                ['treatmentImprovementAddition' => ''],
                ['treatmentImprovementAddition' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->treatmentImprovementAddition->value()],
            ],
            'when unknown treatmentImprovementAddition given' => [
                ['treatmentImprovementAddition' => ['介護職員処遇改善加算（介保）を指定してください。']],
                ['treatmentImprovementAddition' => self::NOT_EXISTING_ID],
                ['treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::addition1()->value()],
            ],
            'when specifiedTreatmentImprovementAddition is empty' => [
                ['specifiedTreatmentImprovementAddition' => ['入力してください。']],
                ['specifiedTreatmentImprovementAddition' => ''],
                ['specifiedTreatmentImprovementAddition' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->specifiedTreatmentImprovementAddition->value()],
            ],
            'when unknown specifiedTreatmentImprovementAddition given' => [
                ['specifiedTreatmentImprovementAddition' => ['介護職員等特定処遇改善加算（介保）を指定してください。']],
                ['specifiedTreatmentImprovementAddition' => self::NOT_EXISTING_ID],
                ['specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::addition1()->value()],
            ],
            'when locationAddition is empty' => [
                ['locationAddition' => ['入力してください。']],
                ['locationAddition' => ''],
                ['locationAddition' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->locationAddition->value()],
            ],
            'when unknown locationAddition given' => [
                ['locationAddition' => ['地域加算（介保）を指定してください。']],
                ['locationAddition' => self::NOT_EXISTING_ID],
                ['locationAddition' => LtcsOfficeLocationAddition::specifiedArea()->value()],
            ],
            'when baseIncreaseSupportAddition is not string' => [
                ['baseIncreaseSupportAddition' => ['入力してください。']],
                ['baseIncreaseSupportAddition' => ''],
                ['baseIncreaseSupportAddition' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->baseIncreaseSupportAddition->value()],
            ],
            'when unknown baseIncreaseSupportAddition given' => [
                ['baseIncreaseSupportAddition' => ['ベースアップ等支援加算（介保）を指定してください。']],
                ['baseIncreaseSupportAddition' => self::NOT_EXISTING_ID],
                ['baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::none()->value()],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $input = $this->defaultInput();
                foreach ($invalid as $key => $value) {
                    Arr::set($input, $key, $value);
                }
                $validator = $this->request->createValidatorInstance($input);
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
        $homeVisitLongTermCareCalcSpec = $this->examples->homeVisitLongTermCareCalcSpecs[0];
        return [
            'period' => [
                'start' => $homeVisitLongTermCareCalcSpec->period->start->format('Y-m-d'),
                'end' => $homeVisitLongTermCareCalcSpec->period->end->format('Y-m-d'),
            ],
            'specifiedOfficeAddition' => $homeVisitLongTermCareCalcSpec->specifiedOfficeAddition->value(),
            'treatmentImprovementAddition' => $homeVisitLongTermCareCalcSpec->treatmentImprovementAddition->value(),
            'specifiedTreatmentImprovementAddition' => $homeVisitLongTermCareCalcSpec->specifiedTreatmentImprovementAddition->value(),
            'locationAddition' => $homeVisitLongTermCareCalcSpec->locationAddition->value(),
            'baseIncreaseSupportAddition' => $homeVisitLongTermCareCalcSpec->baseIncreaseSupportAddition->value(),
        ];
    }

    /**
     * payload が返す値
     *
     * @return array
     */
    private function expectedPayload(): array
    {
        $input = $this->defaultInput();
        return [
            'period' => CarbonRange::create([
                'start' => Carbon::parse($input['period']['start']),
                'end' => Carbon::parse($input['period']['end']),
            ]),
            'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::from($input['specifiedOfficeAddition']),
            'treatmentImprovementAddition' => LtcsTreatmentImprovementAddition::from($input['treatmentImprovementAddition']),
            'specifiedTreatmentImprovementAddition' => LtcsSpecifiedTreatmentImprovementAddition::from($input['specifiedTreatmentImprovementAddition']),
            'locationAddition' => LtcsOfficeLocationAddition::from($input['locationAddition']),
            'baseIncreaseSupportAddition' => LtcsBaseIncreaseSupportAddition::from($input['baseIncreaseSupportAddition']),
        ];
    }
}
