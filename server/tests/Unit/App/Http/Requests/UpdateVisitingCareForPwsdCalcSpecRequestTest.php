<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateVisitingCareForPwsdCalcSpecRequest;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Office\DwsBaseIncreaseSupportAddition;
use Domain\Office\DwsSpecifiedTreatmentImprovementAddition;
use Domain\Office\DwsTreatmentImprovementAddition;
use Domain\Office\VisitingCareForPwsdCalcSpec;
use Domain\Office\VisitingCareForPwsdSpecifiedOfficeAddition;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Test;

/**
 * CreateVisitingCareForPwsdCalcSpecRequest のテスト.
 */
class UpdateVisitingCareForPwsdCalcSpecRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use UnitSupport;

    protected UpdateVisitingCareForPwsdCalcSpecRequest $request;
    protected VisitingCareForPwsdCalcSpec $visitingCareForPwsdCalcSpec;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UpdateVisitingCareForPwsdCalcSpecRequestTest $self): void {
            $self->visitingCareForPwsdCalcSpec = $self->examples->visitingCareForPwsdCalcSpecs[0];

            $self->request = new UpdateVisitingCareForPwsdCalcSpecRequest();
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
        $this->should('payload return VisitingCareForPwsdCalcSpec', function (): void {
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
                ['period.start' => ''],
                ['period.start' => $this->visitingCareForPwsdCalcSpec->period->start],
            ],
            'when period.start is not date' => [
                ['period.start' => ['正しい日付を入力してください。']],
                ['period.start' => 'date'],
                ['period.start' => $this->visitingCareForPwsdCalcSpec->period->start],
            ],
            'when period.end is empty' => [
                ['period.end' => ['入力してください。']],
                ['period.end' => ''],
                ['period.end' => $this->visitingCareForPwsdCalcSpec->period->end],
            ],
            'when period.end is not date' => [
                ['period.end' => ['正しい日付を入力してください。', '適用期間開始以降の日時を入力してください。']],
                ['period.end' => 'date'],
                ['period.end' => $this->visitingCareForPwsdCalcSpec->period->end],
            ],
            'when period.end is after period.start' => [
                ['period.end' => ['適用期間開始以降の日時を入力してください。']],
                ['period.start' => $input['period']['start'], 'period.end' => Carbon::parse($input['period']['start'])->subWeek()->format('Y-m-d')],
                ['period.start' => $input['period']['start'], 'period.end' => $input['period']['end']],
            ],
            'when specifiedOfficeAddition is empty' => [
                ['specifiedOfficeAddition' => ['入力してください。']],
                ['specifiedOfficeAddition' => ''],
                ['specifiedOfficeAddition' => $this->visitingCareForPwsdCalcSpec->specifiedOfficeAddition->value()],
            ],
            'when unknown specifiedOfficeAddition given' => [
                ['specifiedOfficeAddition' => ['特定事業所加算区分（障害・重度訪問介護）を指定してください。']],
                ['specifiedOfficeAddition' => self::NOT_EXISTING_ID],
                ['specifiedOfficeAddition' => VisitingCareForPwsdSpecifiedOfficeAddition::addition1()->value()],
            ],
            'when treatmentImprovementAddition is not string' => [
                ['treatmentImprovementAddition' => ['入力してください。']],
                ['treatmentImprovementAddition' => ''],
                ['treatmentImprovementAddition' => $this->visitingCareForPwsdCalcSpec->treatmentImprovementAddition->value()],
            ],
            'when unknown treatmentImprovementAddition given' => [
                ['treatmentImprovementAddition' => ['福祉・介護職員処遇改善加算（障害）を指定してください。']],
                ['treatmentImprovementAddition' => self::NOT_EXISTING_ID],
                ['treatmentImprovementAddition' => DwsTreatmentImprovementAddition::addition1()->value()],
            ],
            'when specifiedTreatmentImprovementAddition is not string' => [
                ['specifiedTreatmentImprovementAddition' => ['入力してください。']],
                ['specifiedTreatmentImprovementAddition' => ''],
                ['specifiedTreatmentImprovementAddition' => $this->visitingCareForPwsdCalcSpec->specifiedTreatmentImprovementAddition->value()],
            ],
            'when unknown specifiedTreatmentImprovementAddition given' => [
                ['specifiedTreatmentImprovementAddition' => ['福祉・介護職員等特定処遇改善加算（障害）を指定してください。']],
                ['specifiedTreatmentImprovementAddition' => self::NOT_EXISTING_ID],
                ['specifiedTreatmentImprovementAddition' => DwsSpecifiedTreatmentImprovementAddition::addition1()->value()],
            ],
            'when baseIncreaseSupportAddition is not empty' => [
                ['baseIncreaseSupportAddition' => ['入力してください。']],
                ['baseIncreaseSupportAddition' => ''],
                ['baseIncreaseSupportAddition' => $this->visitingCareForPwsdCalcSpec->baseIncreaseSupportAddition->value()],
            ],
            'when unknown baseIncreaseSupportAddition given' => [
                ['baseIncreaseSupportAddition' => ['ベースアップ等支援加算（障害）を指定してください。']],
                ['baseIncreaseSupportAddition' => self::NOT_EXISTING_ID],
                ['baseIncreaseSupportAddition' => DwsBaseIncreaseSupportAddition::addition1()->value()],
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
        $visitingCareForPwsdCalcSpec = $this->visitingCareForPwsdCalcSpec;
        return [
            'officeId' => $visitingCareForPwsdCalcSpec->officeId,
            'period' => [
                'start' => $visitingCareForPwsdCalcSpec->period->start->format('Y-m-d'),
                'end' => $visitingCareForPwsdCalcSpec->period->end->format('Y-m-d'),
            ],
            'specifiedOfficeAddition' => $visitingCareForPwsdCalcSpec->specifiedOfficeAddition->value(),
            'treatmentImprovementAddition' => $visitingCareForPwsdCalcSpec->treatmentImprovementAddition->value(),
            'specifiedTreatmentImprovementAddition' => $visitingCareForPwsdCalcSpec->specifiedTreatmentImprovementAddition->value(),
            'baseIncreaseSupportAddition' => $visitingCareForPwsdCalcSpec->baseIncreaseSupportAddition->value(),
        ];
    }

    /**
     * payload が返す値.
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
            'specifiedOfficeAddition' => VisitingCareForPwsdSpecifiedOfficeAddition::from($input['specifiedOfficeAddition']),
            'treatmentImprovementAddition' => DwsTreatmentImprovementAddition::from($input['treatmentImprovementAddition']),
            'specifiedTreatmentImprovementAddition' => DwsSpecifiedTreatmentImprovementAddition::from($input['specifiedTreatmentImprovementAddition']),
            'baseIncreaseSupportAddition' => DwsBaseIncreaseSupportAddition::from($input['baseIncreaseSupportAddition']),
        ];
    }
}
