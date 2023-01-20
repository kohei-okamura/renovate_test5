<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\UserBilling;

use Domain\Billing\DwsBillingStatementItem;
use Domain\Billing\LtcsBillingStatementItem;
use Domain\Billing\LtcsBillingStatementItemSubsidy;
use Domain\Common\Carbon;
use Domain\Common\ConsumptionTaxRate;
use Domain\Common\Decimal;
use Domain\Permission\Permission;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingDwsItem;
use Domain\UserBilling\UserBillingLtcsItem;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupDwsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\LookupLtcsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\LookupLtcsBillingUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\ResolveDwsNameFromServiceCodesUseCaseMixin;
use Tests\Unit\Mixins\ResolveLtcsNameFromServiceCodesUseCaseMixin;
use Tests\Unit\Mixins\SimpleLookupDwsBillingStatementUseCaseMixin;
use Tests\Unit\Mixins\SimpleLookupLtcsBillingStatementUseCaseMixin;
use Tests\Unit\Test;
use UseCase\UserBilling\BuildUserBillingStatementPdfParamInteractor;

/**
 * {@link \UseCase\UserBilling\BuildUserBillingStatementPdfParamInteractor} のテスト.
 */
class BuildUserBillingStatementPdfParamInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;
    use CarbonMixin;
    use LookupUserUseCaseMixin;
    use LookupLtcsBillingUseCaseMixin;
    use LookupLtcsBillingBundleUseCaseMixin;
    use ResolveDwsNameFromServiceCodesUseCaseMixin;
    use ResolveLtcsNameFromServiceCodesUseCaseMixin;
    use SimpleLookupDwsBillingStatementUseCaseMixin;
    use SimpleLookupLtcsBillingStatementUseCaseMixin;
    use LookupDwsBillingBundleUseCaseMixin;
    use MatchesSnapshots;
    use LookupUserUseCaseMixin;

    private BuildUserBillingStatementPdfParamInteractor $interactor;
    private UserBilling $userBilling;
    private Carbon $issuedOn;

    private array $dwsDictionaryEntries;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (BuildUserBillingStatementPdfParamInteractorTest $self): void {
            $self->dwsDictionaryEntries = [
                $self->examples->dwsHomeHelpServiceDictionaryEntries[11],
                $self->examples->dwsHomeHelpServiceDictionaryEntries[12],
                $self->examples->dwsHomeHelpServiceDictionaryEntries[13],
                $self->examples->dwsHomeHelpServiceDictionaryEntries[14],
                $self->examples->dwsVisitingCareForPwsdDictionaryEntries[8],
                $self->examples->dwsVisitingCareForPwsdDictionaryEntries[9],
                $self->examples->dwsVisitingCareForPwsdDictionaryEntries[10],
                $self->examples->dwsVisitingCareForPwsdDictionaryEntries[11],
                $self->examples->dwsVisitingCareForPwsdDictionaryEntries[12],
                $self->examples->dwsVisitingCareForPwsdDictionaryEntries[13],
            ];

            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[0]))
                ->byDefault();
            $self->lookupLtcsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ltcsBillings[0]))
                ->byDefault();
            $self->lookupLtcsBillingBundleUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ltcsBillingBundles[0]))
                ->byDefault();
            $self->simpleLookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillingStatements[0]))
                ->byDefault();
            $self->simpleLookupLtcsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ltcsBillingStatements[0]))
                ->byDefault();
            $self->resolveDwsNameFromServiceCodesUseCase
                ->allows('handle')
                ->andReturn(
                    Map::from([
                        $self->dwsDictionaryEntries[0]->serviceCode->toString() => $self->dwsDictionaryEntries[0]->name,
                        $self->dwsDictionaryEntries[1]->serviceCode->toString() => $self->dwsDictionaryEntries[1]->name,
                        $self->dwsDictionaryEntries[2]->serviceCode->toString() => $self->dwsDictionaryEntries[2]->name,
                        $self->dwsDictionaryEntries[3]->serviceCode->toString() => $self->dwsDictionaryEntries[3]->name,
                        $self->dwsDictionaryEntries[4]->serviceCode->toString() => $self->dwsDictionaryEntries[4]->name,
                        $self->dwsDictionaryEntries[5]->serviceCode->toString() => $self->dwsDictionaryEntries[5]->name,
                        $self->dwsDictionaryEntries[6]->serviceCode->toString() => $self->dwsDictionaryEntries[6]->name,
                        $self->dwsDictionaryEntries[7]->serviceCode->toString() => $self->dwsDictionaryEntries[7]->name,
                        $self->dwsDictionaryEntries[8]->serviceCode->toString() => $self->dwsDictionaryEntries[8]->name,
                        $self->dwsDictionaryEntries[9]->serviceCode->toString() => $self->dwsDictionaryEntries[9]->name,
                    ])
                )
                ->byDefault();
            $self->resolveLtcsNameFromServiceCodesUseCase
                ->allows('handle')
                ->andReturn(
                    Seq::from(...$self->examples->ltcsHomeVisitLongTermCareDictionaryEntries)
                        ->toMap(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->serviceCode->toString())
                        ->mapValues(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->name)
                )
                ->byDefault();

            $self->userBilling = $self->examples->userBillings[0];
            $self->issuedOn = Carbon::now();
            $self->interactor = app(BuildUserBillingStatementPdfParamInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a array of params', function (): void {
            $user = $this->examples->users[16]->copy([
                'id' => $this->examples->userBillings[14]->userId,
                'birthday' => Carbon::now(),
                'isEnabled' => true,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]);
            $dwsStatement = $this->examples->dwsBillingStatements[0]->copy([
                'items' => [
                    new DwsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111000'),
                        serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                        unitScore: 2000,
                        count: 20,
                        totalScore: 2000,
                    ),
                    new DwsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111111'),
                        serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                        unitScore: 1000,
                        count: 20,
                        totalScore: 2000,
                    ),
                ],
            ]);
            $ltcsStatement = $this->examples->ltcsBillingStatements[0]->copy([
                'items' => [
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('125010'),
                        serviceCodeCategory: LtcsServiceCodeCategory::physicalCare(),
                        unitScore: 100,
                        count: 30,
                        totalScore: 3000,
                        subsidies: [
                            new LtcsBillingStatementItemSubsidy(
                                count: 10,
                                totalScore: 3000,
                            ),
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                        ],
                        note: 'test',
                    ),
                ],
            ]);
            $this->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($user));
            $this->simpleLookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::from($dwsStatement));
            $this->simpleLookupLtcsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::from($ltcsStatement));
            $actual = $this->interactor
                ->handle(
                    $this->context,
                    Seq::from($this->examples->userBillings[14]->copy([
                        'ltcsItem' => UserBillingLtcsItem::create([
                            'ltcsStatementId' => $this->examples->ltcsBillingStatements[0]->id,
                            'score' => 100,
                            'unitCost' => Decimal::fromInt(10_0000),
                            'subtotalCost' => 1000,
                            'tax' => ConsumptionTaxRate::ten(),
                            'medicalDeductionAmount' => 5000,
                            'benefitAmount' => 2000,
                            'subsidyAmount' => 1000,
                            'totalAmount' => 500,
                            'copayWithoutTax' => 2000,
                            'copayWithTax' => 2200,
                        ]),
                    ])),
                    $this->issuedOn
                );
            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should('use LookupUserUseCase', function (): void {
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewUserBillings(), $this->examples->users[0]->id)
                ->andReturn(Seq::from($this->examples->users[0]));

            $this->interactor
                ->handle(
                    $this->context,
                    Seq::from($this->examples->userBillings[0]),
                    $this->issuedOn
                );
        });
        $this->should('throw NotFoundException when LookupUserUseCase returns empty', function (): void {
            $this->lookupUserUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor
                    ->handle(
                        $this->context,
                        Seq::from($this->examples->userBillings[0]),
                        $this->issuedOn
                    );
            });
        });
        $this->should('use SimpleLookupDwsBillingStatementUseCase', function (): void {
            $this->simpleLookupDwsBillingStatementUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewUserBillings(),
                    $this->examples->userBillings[0]->dwsItem->dwsStatementId
                )
                ->andReturn(Seq::from($this->examples->dwsBillingStatements[0]));

            $this->interactor
                ->handle(
                    $this->context,
                    Seq::from($this->examples->userBillings[0]),
                    $this->issuedOn
                );
        });
        $this->should('use SimpleLookupLtcsBillingStatementUseCase', function (): void {
            $this->simpleLookupLtcsBillingStatementUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewUserBillings(),
                    $this->examples->userBillings[0]->ltcsItem->ltcsStatementId
                )
                ->andReturn(Seq::from($this->examples->ltcsBillingStatements[0]));

            $this->interactor
                ->handle(
                    $this->context,
                    Seq::from($this->examples->userBillings[0]),
                    $this->issuedOn
                );
        });
        $this->should(
            'throw NotFoundException when discrepancy in the number of cases returned by SimpleLookupDwsBillingStatementUseCase',
            function (): void {
                $this->simpleLookupDwsBillingStatementUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($this->examples->dwsBillingStatements[0]));

                $this->assertThrows(NotFoundException::class, function (): void {
                    $this->interactor->handle(
                        $this->context,
                        Seq::from(
                            $this->examples->userBillings[0]->copy([
                                'dwsItem' => UserBillingDwsItem::create([
                                    'dwsStatementId' => $this->examples->dwsBillingStatements[3]->id,
                                    'score' => 100,
                                    'unitCost' => Decimal::fromInt(10_0000),
                                    'subtotalCost' => 1000,
                                    'tax' => ConsumptionTaxRate::ten(),
                                    'medicalDeductionAmount' => 5000,
                                    'benefitAmount' => 2000,
                                    'subsidyAmount' => 1000,
                                    'totalAmount' => 500,
                                    'copayWithoutTax' => 2000,
                                    'copayWithTax' => 2200,
                                ]),
                            ]),
                            $this->examples->userBillings[1]
                        ),
                        $this->issuedOn
                    );
                });
            }
        );
        $this->should(
            'throw NotFoundException when discrepancy in the number of cases returned by SimpleLookupLtcsBillingStatementUseCase',
            function (): void {
                $this->simpleLookupDwsBillingStatementUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($this->examples->ltcsBillingStatements[0]));

                $this->assertThrows(NotFoundException::class, function (): void {
                    $this->interactor->handle(
                        $this->context,
                        Seq::from(
                            $this->examples->userBillings[0]->copy([
                                'ltcsItem' => UserBillingLtcsItem::create([
                                    'ltcsStatementId' => $this->examples->ltcsBillingStatements[1]->id,
                                    'score' => 100,
                                    'unitCost' => Decimal::fromInt(10_0000),
                                    'subtotalCost' => 1000,
                                    'tax' => ConsumptionTaxRate::ten(),
                                    'medicalDeductionAmount' => 5000,
                                    'benefitAmount' => 2000,
                                    'subsidyAmount' => 1000,
                                    'totalAmount' => 1000,
                                    'copayWithoutTax' => 2000,
                                    'copayWithTax' => 2200,
                                ]),
                            ]),
                            $this->examples->userBillings[1]
                        ),
                        $this->issuedOn
                    );
                });
            }
        );
        $this->should('use resolveDwsNameFromServiceCodesUseCase', function (): void {
            $this->simpleLookupDwsBillingStatementUseCase
                ->expects('handle')
                ->andReturn(Seq::from(
                    $this->examples->dwsBillingStatements[0]->copy([
                        'items' => [$this->examples->dwsBillingStatements[0]->items[0]->copy(['serviceCode' => ServiceCode::fromString('111111')])],
                    ]),
                ));
            $this->resolveDwsNameFromServiceCodesUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    equalTo(Seq::from(ServiceCode::fromString('111111')))
                )
                ->andReturn(
                    Map::from([
                        $this->dwsDictionaryEntries[0]->serviceCode->toString() => $this->dwsDictionaryEntries[0]->name,
                        $this->dwsDictionaryEntries[1]->serviceCode->toString() => $this->dwsDictionaryEntries[1]->name,
                        $this->dwsDictionaryEntries[2]->serviceCode->toString() => $this->dwsDictionaryEntries[2]->name,
                        $this->dwsDictionaryEntries[3]->serviceCode->toString() => $this->dwsDictionaryEntries[3]->name,
                        $this->dwsDictionaryEntries[4]->serviceCode->toString() => $this->dwsDictionaryEntries[4]->name,
                        $this->dwsDictionaryEntries[5]->serviceCode->toString() => $this->dwsDictionaryEntries[5]->name,
                        $this->dwsDictionaryEntries[6]->serviceCode->toString() => $this->dwsDictionaryEntries[6]->name,
                        $this->dwsDictionaryEntries[7]->serviceCode->toString() => $this->dwsDictionaryEntries[7]->name,
                        $this->dwsDictionaryEntries[8]->serviceCode->toString() => $this->dwsDictionaryEntries[8]->name,
                        $this->dwsDictionaryEntries[9]->serviceCode->toString() => $this->dwsDictionaryEntries[9]->name,
                    ])
                );

            $this->interactor->handle($this->context, Seq::from($this->examples->userBillings[0]), $this->issuedOn);
        });
        $this->specify('請求単位ごとにサービス名称の解決を行う', function (): void {
            $this->simpleLookupLtcsBillingStatementUseCase
                ->expects('handle')
                ->andReturn(Seq::from(
                    $this->examples->ltcsBillingStatements[5]->copy([
                        'items' => [$this->examples->ltcsBillingStatements[5]->items[0]->copy(['serviceCode' => ServiceCode::fromString('111111')])],
                    ]),
                ));
            $this->lookupLtcsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->ltcsBillings[2]));
            $this->lookupLtcsBillingBundleUseCase
                ->allows('handle')
                ->andReturn(Seq::from($this->examples->ltcsBillingBundles[5]));
            $this->resolveLtcsNameFromServiceCodesUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    equalTo(Seq::from(ServiceCode::fromString('111111'))),
                    equalTo($this->examples->ltcsBillingBundles[5]->providedIn)
                )
                ->andReturn(
                    Seq::from(...$this->examples->ltcsHomeVisitLongTermCareDictionaryEntries)
                        ->toMap(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->serviceCode->toString())
                        ->mapValues(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->name)
                );

            $this->interactor->handle($this->context, Seq::from($this->examples->userBillings[0]), $this->issuedOn);
        });
        $this->specify('請求単位が複数存在する場合に請求単位ごとにサービス名称の解決を行う', function (): void {
            $bundle1 = $this->examples->ltcsBillingBundles[0]->copy([
                'providedIn' => Carbon::parse('2021-02-01'),
            ]);
            $bundle2 = $this->examples->ltcsBillingBundles[1]->copy([
                'providedIn' => Carbon::parse('2021-03-01'),
            ]);
            $ltcsStatement1 = $this->examples->ltcsBillingStatements[0]->copy([
                'bundleId' => $bundle1->id,
                'items' => [
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('125010'),
                        serviceCodeCategory: LtcsServiceCodeCategory::physicalCare(),
                        unitScore: 100,
                        count: 30,
                        totalScore: 3000,
                        subsidies: [
                            new LtcsBillingStatementItemSubsidy(
                                count: 10,
                                totalScore: 3000,
                            ),
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                        ],
                        note: 'test',
                    ),
                ],
            ]);
            $ltcsStatement2 = $this->examples->ltcsBillingStatements[1]->copy([
                'bundleId' => $bundle2->id,
                'items' => [
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('121171'),
                        serviceCodeCategory: LtcsServiceCodeCategory::physicalCare(),
                        unitScore: 213,
                        count: 1,
                        totalScore: 213,
                        subsidies: [
                            new LtcsBillingStatementItemSubsidy(
                                count: 1,
                                totalScore: 213,
                            ),
                            LtcsBillingStatementItemSubsidy::empty(),
                            LtcsBillingStatementItemSubsidy::empty(),
                        ],
                        note: 'test',
                    ),
                ],
            ]);

            $userBilling1 = $this->examples->userBillings[0]->copy([
                'ltcsItem' => UserBillingLtcsItem::create([
                    'ltcsStatementId' => $ltcsStatement1->id,
                    'score' => 100,
                    'unitCost' => Decimal::fromInt(10_0000),
                    'subtotalCost' => 1000,
                    'tax' => ConsumptionTaxRate::ten(),
                    'medicalDeductionAmount' => 5000,
                    'benefitAmount' => 2000,
                    'subsidyAmount' => 1000,
                    'totalAmount' => 1000,
                    'copayWithoutTax' => 2000,
                    'copayWithTax' => 2200,
                ]),
            ]);
            $userBilling2 = $this->examples->userBillings[1]->copy([
                'ltcsItem' => UserBillingLtcsItem::create([
                    'ltcsStatementId' => $ltcsStatement2->id,
                    'score' => 100,
                    'unitCost' => Decimal::fromInt(10_0000),
                    'subtotalCost' => 1000,
                    'tax' => ConsumptionTaxRate::ten(),
                    'medicalDeductionAmount' => 5000,
                    'benefitAmount' => 2000,
                    'subsidyAmount' => 1000,
                    'totalAmount' => 1000,
                    'copayWithoutTax' => 2000,
                    'copayWithTax' => 2200,
                ]),
            ]);

            $this->simpleLookupLtcsBillingStatementUseCase
                ->expects('handle')
                ->andReturn(Seq::from(
                    $ltcsStatement1,
                    $ltcsStatement2
                ));
            $this->lookupLtcsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->ltcsBillings[0]));

            $this->lookupLtcsBillingBundleUseCase
                ->expects('handle')
                ->andReturn(Seq::from(
                    $bundle1,
                    $bundle2
                ));
            $this->resolveLtcsNameFromServiceCodesUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    equalTo(Seq::from(ServiceCode::fromString('125010'))),
                    equalTo($bundle1->providedIn)
                )
                ->andReturn(
                    Seq::from(...$this->examples->ltcsHomeVisitLongTermCareDictionaryEntries)
                        ->toMap(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->serviceCode->toString())
                        ->mapValues(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->name)
                );
            $this->resolveLtcsNameFromServiceCodesUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    equalTo(Seq::from(ServiceCode::fromString('121171'))),
                    equalTo($bundle2->providedIn)
                )
                ->andReturn(
                    Seq::from(...$this->examples->ltcsHomeVisitLongTermCareDictionaryEntries)
                        ->toMap(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->serviceCode->toString())
                        ->mapValues(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->name)
                );

            $this->interactor->handle($this->context, Seq::from($userBilling1, $userBilling2), $this->issuedOn);
        });
    }
}
