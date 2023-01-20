<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateDwsBillingCopayCoordinationRequest;
use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingCopayCoordinationExchangeAim;
use Domain\Billing\DwsBillingCopayCoordinationItem;
use Domain\Billing\DwsBillingCopayCoordinationPayment;
use Domain\Billing\DwsBillingStatementCopayCoordinationStatus;
use Domain\Common\Pagination;
use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Illuminate\Support\Arr;
use Lib\Arrays;
use Lib\Json;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\DwsBillingStatementFinderMixin;
use Tests\Unit\Mixins\GetOfficeListUseCaseMixin;
use Tests\Unit\Mixins\IdentifyDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\ValidateCopayCoordinationItemUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\UpdateDwsBillingCopayCoordinationRequest} のテスト
 */
class UpdateDwsBillingCopayCoordinationRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use DwsBillingStatementFinderMixin;
    use ExamplesConsumer;
    use GetOfficeListUseCaseMixin;
    use IdentifyDwsCertificationUseCaseMixin;
    use LookupDwsBillingUseCaseMixin;
    use LookupDwsBillingBundleUseCaseMixin;
    use LookupDwsCertificationUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use LookupUserUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;
    use ValidateCopayCoordinationItemUseCaseMixin;

    protected UpdateDwsBillingCopayCoordinationRequest $request;
    protected DwsBillingCopayCoordination $dwsBillingCopayCoordination;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UpdateDwsBillingCopayCoordinationRequestTest $self): void {
            $self->dwsBillingCopayCoordination = $self->examples->dwsBillingCopayCoordinations[0]->copy([
                'exchangeAim' => DwsBillingCopayCoordinationExchangeAim::declaration(),
            ]);

            $self->request = new UpdateDwsBillingCopayCoordinationRequest();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::emptySeq(),
            );

            $self->dwsBillingStatementFinder
                ->allows('find')
                ->andReturn(FinderResult::from(
                    [
                        $self->examples->dwsBillingStatements[0]
                            ->copy(['copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::uncreated()]),
                    ],
                    Pagination::create()
                ))
                ->byDefault();
            $self->identifyDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->dwsCertifications[0]))
                ->byDefault();
            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillings[0]))
                ->byDefault();
            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->with(
                    anInstanceOf(Context::class),
                    Permission::updateBillings(),
                    $self->examples->dwsBillings[1]->id
                )
                ->andReturn(Seq::from($self->examples->dwsBillings[1]))
                ->byDefault();
            $self->lookupDwsBillingBundleUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillingBundles[0]))
                ->byDefault();
            $self->lookupDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsCertifications[0]))
                ->byDefault();
            $self->lookupDwsCertificationUseCase
                ->allows('handle')
                ->with(
                    anInstanceOf(Context::class),
                    Permission::updateBillings(),
                    $self->examples->dwsBillingCopayCoordinations[0]->user->userId,
                    self::NOT_EXISTING_ID
                )
                ->andReturn(Seq::emptySeq())
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateBillings(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq())
                ->byDefault();
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[0]))
                ->byDefault();
            $self->lookupUserUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::updateBillings(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq())
                ->byDefault();
            $self->validateCopayCoordinationItemUseCase
                ->allows('handle')
                ->andReturn(true)
                ->byDefault();
            $self->getOfficeListUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_payload(): void
    {
        $this->should('payload return DwsBillingCopayCoordination', function (): void {
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

        $examples = [
            'when userId is empty' => [
                ['userId' => ['入力してください。']],
                ['userId' => ''],
                ['userId' => $this->dwsBillingCopayCoordination->user->userId],
            ],
            'when unknown userId given' => [
                ['userId' => ['正しい値を入力してください。']],
                ['userId' => self::NOT_EXISTING_ID],
                ['userId' => $this->dwsBillingCopayCoordination->user->userId],
            ],
            'when exchangeAim is empty' => [
                ['exchangeAim' => ['入力してください。']],
                ['exchangeAim' => ''],
                ['exchangeAim' => $this->dwsBillingCopayCoordination->exchangeAim->value()],
            ],
            'when unknown exchangeAim given' => [
                ['exchangeAim' => ['作成区分を指定してください。']],
                ['exchangeAim' => 'error'],
                ['exchangeAim' => $this->dwsBillingCopayCoordination->exchangeAim->value()],
            ],
            'when result is empty' => [
                ['result' => ['入力してください。']],
                ['result' => ''],
                ['result' => $this->dwsBillingCopayCoordination->result->value()],
            ],
            'when unknown result given' => [
                ['result' => ['上限管理結果を指定してください。']],
                ['result' => 'error'],
                ['result' => $this->dwsBillingCopayCoordination->result->value()],
            ],
            'when items is empty' => [
                ['items' => ['入力してください。']],
                ['items' => null],
                [
                    'items' => Seq::fromArray($this->dwsBillingCopayCoordination->items)
                        ->map(fn (DwsBillingCopayCoordinationItem $item) => [
                            'itemNumber' => $item->itemNumber,
                            'officeId' => $item->office->officeId,
                            'subtotal' => $item->subtotal->toAssoc(),
                        ])
                        ->toArray(),
                ],
            ],
            'when items is not array' => [
                ['items' => ['配列にしてください。']],
                ['items' => 'error'],
                [
                    'items' => Seq::fromArray($this->dwsBillingCopayCoordination->items)
                        ->map(fn (DwsBillingCopayCoordinationItem $item) => [
                            'itemNumber' => $item->itemNumber,
                            'officeId' => $item->office->officeId,
                            'subtotal' => $item->subtotal->toAssoc(),
                        ])
                        ->toArray(),
                ],
            ],
            'when items is copay coordination office only' => [
                ['items' => ['上限額管理を行う場合は利用者負担額集計・調整欄を2件以上登録してください。']],
                [
                    'dwsBillingId' => $this->examples->dwsBillings[1]->id,
                    'items' => [
                        [
                            'itemNumber' => $this->dwsBillingCopayCoordination->items[0]->itemNumber,
                            'officeId' => $this->examples->dwsBillings[1]->office->officeId,
                            'subtotal' => $this->dwsBillingCopayCoordination->items[0]->subtotal->toAssoc(),
                        ],
                    ],
                    'total' => [
                        'fee' => 30000,
                        'copay' => 3000,
                        'coordinatedCopay' => 3000,
                    ],
                ],
                [
                    'items' => Seq::fromArray($this->dwsBillingCopayCoordination->items)
                        ->map(fn (DwsBillingCopayCoordinationItem $item) => [
                            'itemNumber' => $item->itemNumber,
                            'officeId' => $item->office->officeId,
                            'subtotal' => $item->subtotal->toAssoc(),
                        ])
                        ->toArray(),
                ],
            ],
            'when items is copay coordination office not only' => [
                ['items' => ['他事業所におけるサービス提供が無い場合は、利用者負担額集計・調整欄を1件のみ登録してください。']],
                [
                    'dwsBillingId' => $this->examples->dwsBillings[1]->id,
                    'items' => Seq::fromArray($this->dwsBillingCopayCoordination->items)
                        ->map(fn (DwsBillingCopayCoordinationItem $item) => [
                            'itemNumber' => $item->itemNumber,
                            'officeId' => $item->office->officeId,
                            'subtotal' => $item->subtotal->toAssoc(),
                        ])
                        ->toArray(),
                    'total' => [
                        'fee' => 30000,
                        'copay' => 3000,
                        'coordinatedCopay' => 3000,
                    ],
                    'isProvided' => false,
                ],
                [
                    'dwsBillingId' => $this->examples->dwsBillings[1]->id,
                    'items' => [
                        [
                            'itemNumber' => $this->dwsBillingCopayCoordination->items[0]->itemNumber,
                            'officeId' => $this->examples->dwsBillings[1]->office->officeId,
                            'subtotal' => $this->dwsBillingCopayCoordination->items[0]->subtotal->toAssoc(),
                        ],
                    ],
                    'isProvided' => false,
                ],
            ],
            'when items.*.officeId is empty' => [
                ['items.0.officeId' => ['入力してください。']],
                ['items.0.officeId' => ''],
                ['items.0.officeId' => $this->dwsBillingCopayCoordination->items[0]->office->officeId],
            ],
            'when unknown items.*.officeId given' => [
                ['items.0.officeId' => ['正しい値を入力してください。']],
                ['items.0.officeId' => self::NOT_EXISTING_ID],
                ['items.0.officeId' => $this->dwsBillingCopayCoordination->items[0]->office->officeId],
            ],
            'when subtotal is empty' => [
                [
                    'items.0.subtotal' => ['入力してください。'],
                    'items.0.subtotal.fee' => ['入力してください。'],
                    'items.0.subtotal.copay' => ['入力してください。'],
                    'items.0.subtotal.coordinatedCopay' => ['入力してください。'],
                ],
                ['items.0.subtotal' => ''],
                ['items.0.subtotal' => $this->dwsBillingCopayCoordination->items[0]->subtotal->toAssoc()],
            ],
            'when items.*.subtotal is not array' => [
                [
                    'items.0.subtotal' => ['配列にしてください。'],
                    'items.0.subtotal.fee' => ['入力してください。'],
                    'items.0.subtotal.copay' => ['入力してください。'],
                    'items.0.subtotal.coordinatedCopay' => ['入力してください。'],
                ],
                ['items.0.subtotal' => 'error'],
                ['items.0.subtotal' => $this->dwsBillingCopayCoordination->items[0]->subtotal->toAssoc()],
            ],
            'when items.*.subtotal.fee is empty' => [
                ['items.0.subtotal.fee' => ['入力してください。']],
                ['items.0.subtotal.fee' => ''],
                ['items.0.subtotal.fee' => $this->dwsBillingCopayCoordination->items[0]->subtotal->fee],
            ],
            'when items.*.subtotal.fee is not integer' => [
                ['items.0.subtotal.fee' => ['整数で入力してください。']],
                ['items.0.subtotal.fee' => 'error'],
                ['items.0.subtotal.fee' => $this->dwsBillingCopayCoordination->items[0]->subtotal->fee],
            ],
            'when items.*.subtotal.copay is empty' => [
                ['items.0.subtotal.copay' => ['入力してください。']],
                ['items.0.subtotal.copay' => ''],
                ['items.0.subtotal.copay' => $this->dwsBillingCopayCoordination->items[0]->subtotal->copay],
            ],
            'when items.*.subtotal.copay is not integer' => [
                ['items.0.subtotal.copay' => ['整数で入力してください。']],
                ['items.0.subtotal.copay' => 'error'],
                ['items.0.subtotal.copay' => $this->dwsBillingCopayCoordination->items[0]->subtotal->copay],
            ],
            'when items.*.subtotal.coordinatedCopay is empty' => [
                ['items.0.subtotal.coordinatedCopay' => ['入力してください。']],
                ['items.0.subtotal.coordinatedCopay' => ''],
                ['items.0.subtotal.coordinatedCopay' => $this->dwsBillingCopayCoordination->items[0]->subtotal->coordinatedCopay],
            ],
            'when items.*.subtotal.coordinatedCopay is not integer' => [
                ['items.0.subtotal.coordinatedCopay' => ['整数で入力してください。']],
                ['items.0.subtotal.coordinatedCopay' => 'error'],
                ['items.0.subtotal.coordinatedCopay' => $this->dwsBillingCopayCoordination->items[0]->subtotal->coordinatedCopay],
            ],
        ];
        $this->should(
            'fail when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $this->getOfficeListUseCase
                    ->allows('handle')
                    ->with(anInstanceOf(Context::class), self::NOT_EXISTING_ID)
                    ->andReturn(Seq::empty());

                $input = $this->defaultInput();
                foreach ($invalid as $key => $value) {
                    Arr::set($input, $key, $value);
                }
                $validator = $this->request->createValidatorInstance($input);
                $this->assertTrue($validator->fails());
                $this->assertSame($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    $validator = $this->request->createValidatorInstance($valid + $this->defaultInput());
                    $this->assertSame([], $validator->errors()->toArray());
                    $this->assertTrue($validator->passes());
                }
            },
            compact('examples')
        );
        $this->should('fail when validateCopayCoordinationItemUseCase return false', function (): void {
            $this->validateCopayCoordinationItemUseCase
                ->expects('handle')
                ->andReturn(false);

            $validator = $this->request->createValidatorInstance($this->defaultInput());
            $this->assertTrue($validator->fails());
        });
        $this->should('fails when dws billing copay coordination cannot update', function (): void {
            $this->dwsBillingStatementFinder
                ->expects('find')
                ->andReturn(
                    FinderResult::from(
                        [
                            $this->examples->dwsBillingStatements[0]->copy([
                                'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::unapplicable(),
                            ]),
                        ],
                        Pagination::create()
                    )
                )
                ->times(3);
            $input = $this->defaultInput();
            $validator = $this->request->createValidatorInstance($input);
            $this->assertTrue($validator->fails());
            $this->assertSame($validator->errors()->toArray(), [
                'id' => ['利用者負担上限額管理結果票を更新できません。'],
            ]);
        });
    }

    /**
     * リクエストクラスが受け取る入力のデフォルト値.
     *
     * @return array
     */
    private function defaultInput(): array
    {
        $dwsBillingCopayCoordination = $this->dwsBillingCopayCoordination;
        return [
            'id' => $this->examples->dwsBillingStatements[0]->id,
            'userId' => $dwsBillingCopayCoordination->user->userId,
            'exchangeAim' => DwsBillingCopayCoordinationExchangeAim::declaration()->value(),
            'result' => $dwsBillingCopayCoordination->result->value(),
            'isProvided' => true,
            'items' => Seq::fromArray($dwsBillingCopayCoordination->items)
                ->map(fn (DwsBillingCopayCoordinationItem $item) => [
                    'officeId' => $item->office->officeId,
                    'subtotal' => [
                        'fee' => $item->subtotal->fee,
                        'copay' => $item->subtotal->copay,
                        'coordinatedCopay' => $item->subtotal->coordinatedCopay,
                    ],
                ])
                ->toArray(),
        ];
    }

    /**
     * payload が返す配列.
     *
     * @return array
     */
    private function expectedPayload(): array
    {
        $input = $this->defaultInput();
        return [
            'userId' => $input['userId'],
            'exchangeAim' => DwsBillingCopayCoordinationExchangeAim::from($input['exchangeAim']),
            'result' => CopayCoordinationResult::from($input['result']),
            'items' => Arrays::generate(function () use ($input): iterable {
                foreach ($input['items'] as $itemNumber => $item) {
                    yield [
                        'itemNumber' => $itemNumber + 1,
                        'officeId' => $item['officeId'],
                        'subtotal' => DwsBillingCopayCoordinationPayment::create([
                            'fee' => $item['subtotal']['fee'],
                            'copay' => $item['subtotal']['copay'],
                            'coordinatedCopay' => $item['subtotal']['coordinatedCopay'],
                        ]),
                    ];
                }
            }),
        ];
    }
}
