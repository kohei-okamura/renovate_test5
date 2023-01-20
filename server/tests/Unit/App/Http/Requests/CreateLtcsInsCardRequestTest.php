<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\CreateLtcsInsCardRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\LtcsInsCard\LtcsCarePlanAuthorType;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\LtcsInsCard\LtcsInsCardMaxBenefitQuota;
use Domain\LtcsInsCard\LtcsInsCardServiceType;
use Domain\LtcsInsCard\LtcsInsCardStatus;
use Domain\LtcsInsCard\LtcsLevel;
use Illuminate\Support\Arr;
use Lib\Json;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\GetOfficeListUseCaseMixin;
use Tests\Unit\Mixins\IdentifyLtcsInsCardUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * CreateLtcsInsCardRequest のテスト
 */
class CreateLtcsInsCardRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use GetOfficeListUseCaseMixin;
    use LookupUserUseCaseMixin;
    use IdentifyLtcsInsCardUseCaseMixin;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use StaffResolverMixin;
    use UnitSupport;

    protected CreateLtcsInsCardRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateLtcsInsCardRequestTest $self): void {
            $self->request = new CreateLtcsInsCardRequest();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::emptySeq(),
            );
            $self->getOfficeListUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->getOfficeListUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty())
                ->byDefault();
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[0]))
                ->byDefault();
            $self->identifyLtcsInsCardUseCase
                ->allows('handle')
                ->with(Mockery::any(), Mockery::any(), equalTo(Carbon::parse('2020-01-01')->startOfMonth()))
                ->andReturn(Option::from($self->examples->ltcsInsCards[0]));
            $self->identifyLtcsInsCardUseCase
                ->allows('handle')
                ->with(Mockery::any(), Mockery::any(), equalTo(Carbon::parse('2020-01-01')->endOfMonth()))
                ->andReturn(Option::from($self->examples->ltcsInsCards[1]));
            $self->identifyLtcsInsCardUseCase
                ->allows('handle')
                ->andReturn(Option::none())
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_payload(): void
    {
        $this->should('payload return LtcsInsCard', function (): void {
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

            $this->assertModelStrictEquals($this->expectedPayload($this->defaultInput()), $this->request->payload());
        });
        $this->should(
            'return LtcsInsCard when non-required param is null',
            function ($key): void {
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
                $this->assertModelStrictEquals(
                    $this->expectedPayload($input),
                    $this->request->payload()
                );
            },
            [
                'examples' => [
                    'when carePlanAuthorOfficeId' => [
                        'carePlanAuthorOfficeId',
                    ],
                    'when careManagerName' => [
                        'careManagerName',
                    ],
                ],
            ]
        );
        $this->should(
            'return LtcsInsCard when non-required param is undefined',
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
                $this->assertModelStrictEquals(
                    $this->expectedPayload($forgetInput),
                    $this->request->payload()
                );
            },
            [
                'examples' => [
                    'when carePlanAuthorOfficeId' => [
                        'carePlanAuthorOfficeId',
                    ],
                    'when careManagerName' => [
                        'careManagerName',
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
            'when status is empty' => [
                ['status' => ['入力してください。']],
                ['status' => ''],
                ['status' => LtcsInsCardStatus::applied()->value()],
            ],
            'when invalid status given' => [
                ['status' => ['介護保険被保険者証を指定してください。']],
                ['status' => 999],
                ['status' => LtcsInsCardStatus::applied()->value()],
            ],
            'when insNumber is empty' => [
                ['insNumber' => ['入力してください。']],
                ['insNumber' => ''],
                ['insNumber' => '0123456789'],
            ],
            'when insNumber is longer than 10' => [
                ['insNumber' => ['10文字以内で入力してください。']],
                ['insNumber' => '12345678901234567890'],
                ['insNumber' => '0123456789'],
            ],
            'when insurerNumber is empty' => [
                ['insurerNumber' => ['入力してください。']],
                ['insurerNumber' => ''],
                ['insurerNumber' => '012345'],
            ],
            'when insurerNumber is longer than 6' => [
                ['insurerNumber' => ['6文字以内で入力してください。']],
                ['insurerNumber' => '12345678901234567890'],
                ['insurerNumber' => '012345'],
            ],
            'when insurerName is empty' => [
                ['insurerName' => ['入力してください。']],
                ['insurerName' => ''],
                ['insurerName' => str_repeat('山', 100)],
            ],
            'when insurerName is longer than 100' => [
                ['insurerName' => ['100文字以内で入力してください。']],
                ['insurerName' => str_repeat('山', 101)],
                ['insurerName' => str_repeat('山', 100)],
            ],
            'when ltcsLevel is empty' => [
                ['ltcsLevel' => ['入力してください。']],
                ['ltcsLevel' => ''],
                ['ltcsLevel' => LtcsLevel::careLevel1()->value()],
            ],
            'when invalid ltcsLevel given' => [
                ['ltcsLevel' => ['要介護度を指定してください。']],
                ['ltcsLevel' => 999],
                ['ltcsLevel' => LtcsLevel::careLevel1()->value()],
            ],
            'when ltcsInsCardServiceType is empty' => [
                ['maxBenefitQuotas.0.ltcsInsCardServiceType' => ['入力してください。']],
                [
                    'maxBenefitQuotas' => [
                        [
                            'ltcsInsCardServiceType' => '',
                            'maxBenefitQuota' => LtcsInsCardServiceType::serviceType1()->value(),
                        ],
                    ],
                ],
                [
                    'maxBenefitQuotas' => [
                        [
                            'ltcsInsCardServiceType' => LtcsInsCardServiceType::serviceType1()->value(),
                            'maxBenefitQuota' => LtcsInsCardServiceType::serviceType1()->value(),
                        ],
                    ],
                ],
            ],
            'when invalid ltcsInsCardServiceType given' => [
                ['maxBenefitQuotas.0.ltcsInsCardServiceType' => ['サービス種別を指定してください。']],
                [
                    'maxBenefitQuotas' => [
                        [
                            'ltcsInsCardServiceType' => 999,
                            'maxBenefitQuota' => LtcsInsCardServiceType::serviceType1()->value(),
                        ],
                    ],
                ],
                [
                    'maxBenefitQuotas' => [
                        [
                            'ltcsInsCardServiceType' => LtcsInsCardServiceType::serviceType1()->value(),
                            'maxBenefitQuota' => LtcsInsCardServiceType::serviceType1()->value(),
                        ],
                    ],
                ],
            ],
            'when maxBenefitQuota is empty' => [
                ['maxBenefitQuotas.0.maxBenefitQuota' => ['入力してください。']],
                [
                    'maxBenefitQuotas' => [
                        [
                            'ltcsInsCardServiceType' => LtcsInsCardServiceType::serviceType1()->value(),
                            'maxBenefitQuota' => '',
                        ],
                    ],
                ],
                [
                    'maxBenefitQuotas' => [
                        [
                            'ltcsInsCardServiceType' => LtcsInsCardServiceType::serviceType1()->value(),
                            'maxBenefitQuota' => 12345678900,
                        ],
                    ],
                ],
            ],
            'when maxBenefitQuota contains non-integer' => [
                ['maxBenefitQuotas.0.maxBenefitQuota' => ['整数で入力してください。']],
                [
                    'maxBenefitQuotas' => [
                        [
                            'ltcsInsCardServiceType' => LtcsInsCardServiceType::serviceType1()->value(),
                            'maxBenefitQuota' => 'あいうえおかきくけこ',
                        ],
                    ],
                ],
                [
                    'maxBenefitQuotas' => [
                        [
                            'ltcsInsCardServiceType' => LtcsInsCardServiceType::serviceType1()->value(),
                            'maxBenefitQuota' => 12345678900,
                        ],
                    ],
                ],
            ],
            'when copayRate is empty' => [
                ['copayRate' => ['入力してください。']],
                ['copayRate' => ''],
                ['copayRate' => 12345678900],
            ],
            'when copayRate contains non-integer' => [
                ['copayRate' => ['整数で入力してください。']],
                ['copayRate' => 'あいうえおかきくけこ'],
                ['copayRate' => 12345678900],
            ],
            'when effectivatedOn is empty' => [
                ['effectivatedOn' => ['入力してください。']],
                ['effectivatedOn' => ''],
                ['effectivatedOn' => '2000-02-29'],
            ],
            'when invalid effectivatedOn given' => [
                ['effectivatedOn' => ['正しい日付を入力してください。']],
                ['effectivatedOn' => '1999-02-29'],
                ['effectivatedOn' => '2000-02-29'],
            ],
            'when issuedOn is empty' => [
                ['issuedOn' => ['入力してください。']],
                ['issuedOn' => ''],
                ['issuedOn' => '2000-02-29'],
            ],
            'when invalid issuedOn given' => [
                ['issuedOn' => ['正しい日付を入力してください。']],
                ['issuedOn' => '1999-02-29'],
                ['issuedOn' => '2000-02-29'],
            ],
            'when duplicated effectivatedOn given' => [
                ['effectivatedOn' => ['同一月内に適用される被保険者証が既に2つ登録されています。登録内容などに間違いがないか、ご確認ください。']],
                ['effectivatedOn' => '2020-01-01'],
                ['effectivatedOn' => '1999-02-28'],
            ],
            'when certificatedOn is empty' => [
                ['certificatedOn' => ['入力してください。']],
                ['certificatedOn' => ''],
                ['certificatedOn' => '2000-02-29'],
            ],
            'when invalid certificatedOn given' => [
                ['certificatedOn' => ['正しい日付を入力してください。']],
                ['certificatedOn' => '1999-02-29'],
                ['certificatedOn' => '2000-02-29'],
            ],
            'when activatedOn is empty' => [
                ['activatedOn' => ['入力してください。']],
                ['activatedOn' => ''],
                ['activatedOn' => '2000-02-29'],
            ],
            'when invalid activatedOn given' => [
                ['activatedOn' => ['正しい日付を入力してください。']],
                ['activatedOn' => '1999-02-29'],
                ['activatedOn' => '2000-02-29'],
            ],
            'when deactivatedOn is empty' => [
                ['deactivatedOn' => ['入力してください。']],
                ['deactivatedOn' => ''],
                ['deactivatedOn' => '2000-02-29'],
            ],
            'when invalid deactivatedOn given' => [
                ['deactivatedOn' => ['正しい日付を入力してください。']],
                ['deactivatedOn' => '1999-02-29'],
                ['deactivatedOn' => '2000-02-29'],
            ],
            'when copayActivatedOn is empty' => [
                ['copayActivatedOn' => ['入力してください。']],
                ['copayActivatedOn' => ''],
                ['copayActivatedOn' => '2000-02-29'],
            ],
            'when invalid copayActivatedOn given' => [
                ['copayActivatedOn' => ['正しい日付を入力してください。']],
                ['copayActivatedOn' => '1999-02-29'],
                ['copayActivatedOn' => '2000-02-29'],
            ],
            'when copayDeactivatedOn is empty' => [
                ['copayDeactivatedOn' => ['入力してください。']],
                ['copayDeactivatedOn' => ''],
                ['copayDeactivatedOn' => '2000-02-29'],
            ],
            'when invalid copayDeactivatedOn given' => [
                ['copayDeactivatedOn' => ['正しい日付を入力してください。']],
                ['copayDeactivatedOn' => '1999-02-29'],
                ['copayDeactivatedOn' => '2000-02-29'],
            ],
            'when careManagerName is empty although LtcsCarePlanAuthorType is careManagerOffice' => [
                ['careManagerName' => ['居宅サービス計画作成区分が1の時、居宅介護支援事業所：担当者は必ず入力してください。']],
                [
                    'careManagerName' => '',
                    'carePlanAuthorType' => LtcsCarePlanAuthorType::careManagerOffice()->value(),
                ],
                [
                    'careManagerName' => '名前',
                    'carePlanAuthorType' => LtcsCarePlanAuthorType::careManagerOffice()->value(),
                ],
            ],
            'when careManagerName is not string' => [
                ['careManagerName' => ['文字列で入力してください。']],
                [
                    'careManagerName' => 12345,
                    'carePlanAuthorType' => LtcsCarePlanAuthorType::careManagerOffice()->value(),
                ],
                [
                    'careManagerName' => '名前',
                    'carePlanAuthorType' => LtcsCarePlanAuthorType::careManagerOffice()->value(),
                ],
            ],
            'when careManagerName is longer than 100' => [
                ['careManagerName' => ['100文字以内で入力してください。']],
                [
                    'careManagerName' => str_repeat('山', 101),
                    'carePlanAuthorType' => LtcsCarePlanAuthorType::careManagerOffice()->value(),
                ],
                [
                    'careManagerName' => '名前',
                    'carePlanAuthorType' => LtcsCarePlanAuthorType::careManagerOffice()->value(),
                ],
            ],
            'when carePlanAuthorType is empty' => [
                ['carePlanAuthorType' => ['入力してください。']],
                ['carePlanAuthorType' => ''],
                ['carePlanAuthorType' => LtcsCarePlanAuthorType::self()->value()],
            ],
            'when invalid carePlanAuthorType given' => [
                ['carePlanAuthorType' => ['居宅サービス計画作成区分を指定してください。']],
                ['carePlanAuthorType' => self::INVALID_ENUM_VALUE],
                ['carePlanAuthorType' => LtcsCarePlanAuthorType::self()->value()],
            ],
            'when communityGeneralSupportCenterId is empty and ltcsLevel is not careLevel' => [
                ['communityGeneralSupportCenterId' => ['入力してください。']],
                [
                    'communityGeneralSupportCenterId' => '',
                    'ltcsLevel' => LtcsLevel::supportLevel1()->value(),
                ],
                [
                    'communityGeneralSupportCenterId' => $this->examples->ltcsInsCards[0]->communityGeneralSupportCenterId,
                    'ltcsLevel' => LtcsLevel::careLevel1()->value(),
                ],
            ],
            'when unknown communityGeneralSupportCenterId given' => [
                ['communityGeneralSupportCenterId' => ['正しい値を入力してください。']],
                ['communityGeneralSupportCenterId' => self::NOT_EXISTING_ID],
                ['communityGeneralSupportCenterId' => $this->examples->ltcsInsCards[0]->communityGeneralSupportCenterId],
            ],
            'when carePlanAuthorOfficeId is empty and carePlanAuthorType is not self' => [
                ['carePlanAuthorOfficeId' => ['居宅サービス計画作成区分が1の時、居宅介護支援事業所IDは必ず入力してください。']],
                [
                    'carePlanAuthorOfficeId' => '',
                    'carePlanAuthorType' => LtcsCarePlanAuthorType::careManagerOffice()->value(),
                ],
                [
                    'carePlanAuthorOfficeId' => $this->examples->ltcsInsCards[0]->carePlanAuthorOfficeId,
                    'carePlanAuthorType' => LtcsCarePlanAuthorType::careManagerOffice()->value(),
                ],
            ],
            'when unknown carePlanAuthorOfficeId given' => [
                ['carePlanAuthorOfficeId' => ['正しい値を入力してください。']],
                ['carePlanAuthorOfficeId' => self::NOT_EXISTING_ID],
                ['carePlanAuthorOfficeId' => $this->examples->ltcsInsCards[0]->carePlanAuthorOfficeId],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $validator = $this->request->createValidatorInstance(['userId' => $this->examples->users[0]->id] + $invalid + $this->defaultInput());
                $this->assertTrue($validator->fails());
                $this->assertSame($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    $validator = $this->request->createValidatorInstance($valid + $this->defaultInput());
                    $this->assertTrue($validator->passes(), $validator->errors()->toJson(\JSON_UNESCAPED_UNICODE));
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
        $x = $this->examples->ltcsInsCards[0];
        $values = [
            'status' => $x->status->value(),
            'insNumber' => $x->insNumber,
            'insurerNumber' => $x->insurerNumber,
            'insurerName' => $x->insurerName,
            'ltcsLevel' => $x->ltcsLevel->value(),
            'copayRate' => $x->copayRate,
            'effectivatedOn' => $x->effectivatedOn->toDateString(),
            'issuedOn' => $x->issuedOn->toDateString(),
            'certificatedOn' => $x->certificatedOn->toDateString(),
            'activatedOn' => $x->activatedOn->toDateString(),
            'deactivatedOn' => $x->deactivatedOn->toDateString(),
            'copayActivatedOn' => $x->copayActivatedOn->toDateString(),
            'copayDeactivatedOn' => $x->copayDeactivatedOn->toDateString(),
            'careManagerName' => $x->careManagerName,
            'carePlanAuthorType' => LtcsCarePlanAuthorType::self()->value(),
            'communityGeneralSupportCenterId' => $x->communityGeneralSupportCenterId,
            'carePlanAuthorOfficeId' => $x->carePlanAuthorOfficeId,
        ];
        $maxBenefitQuotas = Seq::fromArray($x->maxBenefitQuotas)
            ->map(fn (LtcsInsCardMaxBenefitQuota $maxBenefitQuota) => [
                'ltcsInsCardServiceType' => $maxBenefitQuota->ltcsInsCardServiceType->value(),
                'maxBenefitQuota' => $maxBenefitQuota->maxBenefitQuota,
            ]);

        return $values + ['maxBenefitQuotas' => $maxBenefitQuotas->toArray()];
    }

    /**
     * payload が返すドメインモデル.
     *
     * @param array $input
     * @return \Domain\LtcsInsCard\LtcsInsCard
     */
    private function expectedPayload(array $input): LtcsInsCard
    {
        $overwrites = [
            'status' => LtcsInsCardStatus::from($input['status']),
            'ltcsLevel' => LtcsLevel::from($input['ltcsLevel']),
            'issuedOn' => Carbon::parse($input['issuedOn']),
            'effectivatedOn' => Carbon::parse($input['effectivatedOn']),
            'certificatedOn' => Carbon::parse($input['certificatedOn']),
            'activatedOn' => Carbon::parse($input['activatedOn']),
            'deactivatedOn' => Carbon::parse($input['deactivatedOn']),
            'copayActivatedOn' => Carbon::parse($input['copayActivatedOn']),
            'copayDeactivatedOn' => Carbon::parse($input['copayDeactivatedOn']),
            'careManagerName' => $input['careManagerName'] ?? '',
            'carePlanAuthorType' => LtcsCarePlanAuthorType::from($input['carePlanAuthorType']),
            'communityGeneralSupportCenterId' => Seq::from(
                LtcsLevel::supportLevel1(),
                LtcsLevel::supportLevel2(),
                LtcsLevel::target()
            )->contains(LtcsLevel::from($input['ltcsLevel']))
                ? $input['communityGeneralSupportCenterId']
                : null,
            'carePlanAuthorOfficeId' => LtcsCarePlanAuthorType::from($input['carePlanAuthorType']) === LtcsCarePlanAuthorType::self()
                ? null
                : $input['carePlanAuthorOfficeId'],
            'isEnabled' => true,
        ];
        $maxBenefitQuotas = Seq::fromArray($input['maxBenefitQuotas'])
            ->map(fn ($x): LtcsInsCardMaxBenefitQuota => LtcsInsCardMaxBenefitQuota::create([
                'ltcsInsCardServiceType' => LtcsInsCardServiceType::from($x['ltcsInsCardServiceType']),
                'maxBenefitQuota' => $x['maxBenefitQuota'],
            ]));

        return LtcsInsCard::create($overwrites + ['maxBenefitQuotas' => $maxBenefitQuotas->toArray()] + $input);
    }
}
