<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateLtcsBillingStatementRequest;
use Domain\Billing\LtcsServiceDivisionCode;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\UpdateLtcsBillingStatementRequest} のテスト.
 */
final class UpdateLtcsBillingStatementRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private UpdateLtcsBillingStatementRequest $request;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
        });
        self::beforeEachSpec(function (self $self): void {
            $self->request = new UpdateLtcsBillingStatementRequest();

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
        $this->should(
            'payload return LtcsCertification',
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
            },
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
            $this->assertTrue($validator->passes(), $validator->errors()->toJson(\JSON_UNESCAPED_UNICODE));
        });
        $input = $this->defaultInput();
        $examples = [
            'when aggregates is empty' => [
                ['aggregates' => ['入力してください。']],
                ['aggregates' => ''],
                ['aggregates' => $input['aggregates']],
            ],
            'when aggregates is string' => [
                ['aggregates' => ['配列にしてください。']],
                ['aggregates' => 'INVALID'],
                ['aggregates' => $input['aggregates']],
            ],
            'when serviceDivisionCode is invalid' => [
                ['aggregates.0.serviceDivisionCode' => ['介護保険サービス：請求：サービス種類コードを指定してください。']],
                ['aggregates.0.serviceDivisionCode' => self::INVALID_ENUM_VALUE],
                ['aggregates.0.serviceDivisionCode' => LtcsServiceDivisionCode::homeVisitLongTermCare()->value()],
            ],
            'when plannedScore is string' => [
                ['aggregates.0.plannedScore' => ['整数で入力してください。']],
                ['aggregates.0.plannedScore' => 'string'],
                ['aggregates.0.plannedScore' => 0],
            ],
            'when plannedScore is negative' => [
                ['aggregates.0.plannedScore' => ['0以上で入力してください。']],
                ['aggregates.0.plannedScore' => -1],
                ['aggregates.0.plannedScore' => 0],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $input = $this->defaultInput();
                foreach ($invalid as $key => $val) {
                    Arr::set($input, $key, $val);
                }
                $validator = $this->request->createValidatorInstance($input);
                $this->assertTrue($validator->fails());
                $this->assertSame($validator->errors()->toArray(), $expected);
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
        return [
            'aggregates' => [
                [
                    'serviceDivisionCode' => LtcsServiceDivisionCode::homeVisitLongTermCare()->value(),
                    'plannedScore' => 0,
                ],
            ],

            // URL パラメータ
            'id' => $this->examples->ltcsBillingStatements[0]->id,
            'billingId' => $this->examples->ltcsBillingStatements[0]->billingId,
            'billingBundleId' => $this->examples->ltcsBillingStatements[0]->bundleId,
        ];
    }

    /**
     * payload が返す配列.
     *
     * @param array $input リクエストパラメータ
     * @return array
     */
    private function expectedPayload(array $input): array
    {
        return Seq::fromArray($input['aggregates'])
            ->map(fn (array $x): array => [
                'serviceDivisionCode' => LtcsServiceDivisionCode::from($x['serviceDivisionCode']),
                'plannedScore' => $x['plannedScore'],
            ])
            ->toArray();
    }
}
