<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateDwsBillingStatementCopayCoordinationRequest;
use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBillingStatus;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\DwsBillingStatementRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\UpdateDwsBillingStatementCopayCoordinationRequest} のテスト.
 */
final class UpdateDwsBillingStatementCopayCoordinationRequestTest extends Test
{
    use ConfigMixin;
    use DwsBillingStatementRepositoryMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private UpdateDwsBillingStatementCopayCoordinationRequest $request;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->request = new UpdateDwsBillingStatementCopayCoordinationRequest();

            $self->dwsBillingStatementRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->dwsBillingStatements[0]))
                ->byDefault();

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
            'payload return array',
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
        $examples = [
            'when result is empty' => [
                ['result' => ['入力してください。']],
                ['result' => ''],
                ['result' => CopayCoordinationResult::appropriated()->value()],
            ],
            'when result is invalid' => [
                ['result' => ['上限管理結果を指定してください。']],
                ['result' => self::INVALID_ENUM_VALUE],
                ['result' => CopayCoordinationResult::appropriated()->value()],
            ],
            'when amount is empty' => [
                ['amount' => ['入力してください。']],
                ['amount' => ''],
                ['amount' => 1000],
            ],
            'when amount is string' => [
                ['amount' => ['整数で入力してください。']],
                ['amount' => 'string'],
                ['amount' => 1000],
            ],
            'when amount is nagative' => [
                ['amount' => ['0以上で入力してください。']],
                ['amount' => -1000],
                ['amount' => 1000],
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
                    $this->assertTrue($validator->passes(), $validator->errors()->toJson(\JSON_UNESCAPED_UNICODE));
                }
            },
            compact('examples')
        );
        $this->should('fails when Statement cannot update', function (): void {
            $this->dwsBillingStatementRepository
                ->allows('lookup')
                ->andReturn(Seq::from($this->examples->dwsBillingStatements[0]->copy([
                    'status' => DwsBillingStatus::fixed(),
                ])));
            $input = $this->defaultInput();
            $validator = $this->request->createValidatorInstance($input);
            $this->assertTrue($validator->fails());
            $this->assertSame($validator->errors()->toArray(), [
                'id' => ['明細書を更新できません。'],
            ]);
        });
    }

    /**
     * 入力値.
     *
     * @return array|array[][]
     */
    private function defaultInput(): array
    {
        return [
            'id' => $this->examples->dwsBillingStatements[0]->id,
            'result' => CopayCoordinationResult::appropriated()->value(),
            'amount' => 1000,
        ];
    }

    /**
     * payloadの期待値.
     *
     * @param array $input
     * @return array
     */
    private function expectedPayload(array $input): array
    {
        return [
            'result' => CopayCoordinationResult::from($input['result']),
            'amount' => $input['amount'],
        ];
    }
}
