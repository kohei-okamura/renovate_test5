<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateDwsBillingStatementRequest;
use Domain\Billing\DwsBillingStatementAggregate;
use Domain\Billing\DwsBillingStatus;
use Domain\Billing\DwsServiceDivisionCode;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\DwsBillingStatementRepositoryMixin;
use Tests\Unit\Mixins\LookupDwsBillingStatementUseCaseMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\UpdateDwsBillingStatementRequest} Test.
 */
final class UpdateDwsBillingStatementRequestTest extends Test
{
    use ConfigMixin;
    use DwsBillingStatementRepositoryMixin;
    use ExamplesConsumer;
    use LookupDwsBillingStatementUseCaseMixin;
    use OrganizationResolverMixin;
    use UnitSupport;

    protected UpdateDwsBillingStatementRequest $request;

    private array $aggregates;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (UpdateDwsBillingStatementRequestTest $self): void {
            $self->request = new UpdateDwsBillingStatementRequest();

            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::emptySeq(),
            );

            $self->lookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillingStatements[0]))
                ->byDefault();

            $self->dwsBillingStatementRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->examples->dwsBillingStatements[0]))
                ->byDefault();

            $self->aggregates = Seq::fromArray($self->examples->dwsBillingStatements[0]->aggregates)
                ->map(fn (DwsBillingStatementAggregate $x): array => [
                    'serviceDivisionCode' => $x->serviceDivisionCode->value(),
                    'managedCopay' => $x->managedCopay,
                    'subtotalSubsidy' => $x->subtotalSubsidy,
                ])
                ->toArray();
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
        $this->should('return array when subtotalSubsidy is undefined', function (): void {
            $input = tap($this->defaultInput(), function (array &$x): void {
                Arr::forget($x, 'subtotalSubsidy');
            });
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
            'when aggregates is empty' => [
                ['aggregates' => ['入力してください。']],
                ['aggregates' => []],
                ['aggregates' => $this->aggregates],
            ],
            'when aggregate is string' => [
                ['aggregates' => ['配列にしてください。']],
                ['aggregates' => 'string'],
                ['aggregates' => $this->aggregates],
            ],
            'when serviceDivision code is missing in DB' => [
                ['aggregates' => ['障害福祉サービス：請求：サービス種類コード が存在しません。']],
                ['aggregates' => [
                    [
                        'serviceDivisionCode' => DwsServiceDivisionCode::visitingCareForPwsd()->value(),
                        'managedCopay' => 0,
                    ],
                ],
                ],
                ['aggregates' => $this->aggregates],
            ],
            'when serviceDivision is empty' => [
                ['aggregates.0.serviceDivisionCode' => ['入力してください。']],
                ['aggregates' => [
                    [
                        'serviceDivisionCode' => '',
                        'managedCopay' => 0,
                    ],
                ],
                ],
                ['aggregates' => $this->aggregates],
            ],
            'when serviceDivision is invalid' => [
                ['aggregates.0.serviceDivisionCode' => ['障害福祉サービス：請求：サービス種類コード を指定してください。']],
                ['aggregates' => [
                    [
                        'serviceDivisionCode' => (string)self::INVALID_ENUM_VALUE,
                        'managedCopay' => 0,
                    ],
                ],
                ],
                ['aggregates' => $this->aggregates],
            ],
            'when managedCopay is empty' => [
                ['aggregates.0.managedCopay' => ['入力してください。']],
                ['aggregates' => [
                    [
                        'serviceDivisionCode' => DwsServiceDivisionCode::homeHelpService()->value(),
                        'managedCopay' => '',
                    ],
                ],
                ],
                ['aggregates' => $this->aggregates],
            ],
            'when managedCopay is not integer' => [
                ['aggregates.0.managedCopay' => ['整数で入力してください。']],
                ['aggregates' => [
                    [
                        'serviceDivisionCode' => DwsServiceDivisionCode::homeHelpService()->value(),
                        'managedCopay' => 'string',
                    ],
                ],
                ],
                ['aggregates' => $this->aggregates],
            ],
            'when subtotalSubsidy is not integer' => [
                ['aggregates.0.subtotalSubsidy' => ['整数で入力してください。']],
                [
                    'aggregates' => [
                        [
                            'serviceDivisionCode' => DwsServiceDivisionCode::homeHelpService()->value(),
                            'managedCopay' => 0,
                            'subtotalSubsidy' => 'error',
                        ],
                    ],
                ],
                ['aggregates' => $this->aggregates],
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
                $this->assertSame($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    $validator = $this->request->createValidatorInstance($valid + $this->defaultInput());
                    $this->assertTrue($validator->passes());
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
            'billingBundleId' => $this->examples->dwsBillingBundles[0]->id,
            'id' => $this->examples->dwsBillingStatements[0]->id,
            'aggregates' => $this->aggregates,
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
        return Seq::fromArray($input['aggregates'])->map(fn (array $x): array => [
            'serviceDivisionCode' => DwsServiceDivisionCode::from($x['serviceDivisionCode']),
            'managedCopay' => $x['managedCopay'],
            'subtotalSubsidy' => $x['subtotalSubsidy'],
        ])->toArray();
    }
}
