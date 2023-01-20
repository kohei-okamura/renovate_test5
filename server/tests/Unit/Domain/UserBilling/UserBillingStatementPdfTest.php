<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\UserBilling;

use Domain\Billing\DwsBillingStatementItem;
use Domain\Billing\LtcsBillingStatementItem;
use Domain\Billing\LtcsBillingStatementItemSubsidy;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\ConsumptionTaxRate;
use Domain\Common\Decimal;
use Domain\Common\Location;
use Domain\Common\Prefecture;
use Domain\Common\Sex;
use Domain\Common\StructuredName;
use Domain\Pdf\PdfSupport;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Domain\User\User;
use Domain\User\UserBillingDestination;
use Domain\UserBilling\UserBillingLtcsItem;
use Domain\UserBilling\UserBillingOffice;
use Domain\UserBilling\UserBillingPdfSupport;
use Domain\UserBilling\UserBillingStatementPdf;
use Domain\UserBilling\UserBillingStatementPdfAmount;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\UserBilling\UserBillingStatementPdf} のテスト.
 */
final class UserBillingStatementPdfTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use UnitSupport;
    use MatchesSnapshots;
    use PdfSupport;
    use UserBillingPdfSupport;

    private const ITEMS_PER_PAGE = 25;

    protected UserBillingStatementPdf $userBillingStatementPdf;

    private Carbon $issuedOn;
    private Map $dwsServiceCodeMap;
    private Map $ltcsServiceCodeMap;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->dwsServiceCodeMap = Seq::from(
                $self->examples->dwsHomeHelpServiceDictionaryEntries[11],
                $self->examples->dwsHomeHelpServiceDictionaryEntries[12],
                $self->examples->dwsHomeHelpServiceDictionaryEntries[13],
                $self->examples->dwsHomeHelpServiceDictionaryEntries[14],
            )
                ->append(Seq::from(...$self->examples->dwsVisitingCareForPwsdDictionaryEntries))
                ->groupBy(
                    function (DwsVisitingCareForPwsdDictionaryEntry|DwsHomeHelpServiceDictionaryEntry $x): string {
                        return $x->serviceCode->toString();
                    }
                )
                ->mapValues(fn (Seq $x): string => $x->head()->name);
            $self->ltcsServiceCodeMap = Seq::from(...$self->examples->ltcsHomeVisitLongTermCareDictionaryEntries)
                ->groupBy(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->serviceCode->toString())
                ->mapValues(fn (Seq $x): string => $x->head()->name);

            $self->issuedOn = Carbon::now();

            $self->userBillingStatementPdf = new UserBillingStatementPdf(
                issuedOn: '令和3年5月1日',
                office: UserBillingOffice::create(),
                period: CarbonRange::create([
                    'start' => Carbon::create(2020, 5, 01),
                    'end' => Carbon::create(2020, 5, 31),
                ]),
                user: User::create(),
                billingItems: [$self->examples->userBillings[0]->ltcsItem],
                itemsAmounts: UserBillingStatementPdfAmount::fromLTcs($self->examples->userBillings[0]->ltcsItem),
                page: 1,
                maxPage: 1,
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $this->should('return userBillingStatementPdf', function (): void {
            $user = $this->examples->users[16]->copy([
                'birthday' => Carbon::now(),
                'isEnabled' => true,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]);
            $dwsBillingStatement = $this->examples->dwsBillingStatements[0]->copy([
                'items' => [
                    new DwsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('110901'),
                        serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                        unitScore: 2000,
                        count: 20,
                        totalScore: 2000,
                    ),
                    new DwsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('115010'),
                        serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                        unitScore: 1000,
                        count: 20,
                        totalScore: 2000,
                    ),
                ],
            ]);
            $ltcsBillingStatement = $this->examples->ltcsBillingStatements[0]->copy([
                'items' => [
                    new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('112444'),
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
            $userBilling = $this->examples->userBillings[14]->copy([
                'ltcsItem' => UserBillingLtcsItem::create([
                    'ltcsStatementId' => $ltcsBillingStatement->id,
                    'score' => 100,
                    'unitCost' => Decimal::fromInt(10_0000),
                    'subtotalCost' => 1000,
                    'tax' => ConsumptionTaxRate::ten(),
                    'medicalDeductionAmount' => 5000,
                    'benefitAmount' => 2000,
                    'subsidyAmount' => 1000,
                    'copayWithoutTax' => 2000,
                    'copayWithTax' => 2200,
                ]),
            ]);
            $actual = UserBillingStatementPdf::from(
                $user,
                $userBilling,
                $this->issuedOn,
                Option::some($dwsBillingStatement),
                Option::some($ltcsBillingStatement),
                $this->dwsServiceCodeMap,
                $this->ltcsServiceCodeMap,
            );
            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should(
            'throw InvalidArgumentException when the dwsBillingStatement and the ltcsBillingStatement are empty',
            function (): void {
                $user = User::create([
                    'id' => 1,
                    'organizationId' => 1,
                    'name' => new StructuredName(
                        familyName: '土屋',
                        givenName: '花子',
                        phoneticFamilyName: 'ツチヤ',
                        phoneticGivenName: 'ハナコ',
                    ),
                    'sex' => Sex::male(),
                    'birthday' => Carbon::now(),
                    'addr' => new Addr(
                        postcode: '164-0011',
                        prefecture: Prefecture::tokyo(),
                        city: '中野区',
                        street: '中央1-35-6',
                        apartment: 'レッチフィールド中野坂上ビル6F',
                    ),
                    'location' => Location::create(),
                    'contacts' => [],
                    'bankAccountId' => 1,
                    'billingDestination' => UserBillingDestination::create(),
                    'isEnabled' => true,
                    'version' => 1,
                    'createdAt' => Carbon::now(),
                    'updatedAt' => Carbon::now(),
                ]);

                $this->assertThrows(InvalidArgumentException::class, function () use ($user): void {
                    UserBillingStatementPdf::from(
                        $user,
                        $this->examples->userBillings[0],
                        $this->issuedOn,
                        Option::none(),
                        Option::none(),
                        $this->dwsServiceCodeMap,
                        $this->ltcsServiceCodeMap,
                    );
                });
            }
        );
        $this->should('create new page for every time items count over ITEMS_PER_PAGE', function (): void {
            $user = User::create([
                'id' => 1,
                'organizationId' => 1,
                'name' => new StructuredName(
                    familyName: '土屋',
                    givenName: '花子',
                    phoneticFamilyName: 'ツチヤ',
                    phoneticGivenName: 'ハナコ',
                ),
                'sex' => Sex::male(),
                'birthday' => Carbon::now(),
                'addr' => new Addr(
                    postcode: '164-0011',
                    prefecture: Prefecture::tokyo(),
                    city: '中野区',
                    street: '中央1-35-6',
                    apartment: 'レッチフィールド中野坂上ビル6F',
                ),
                'location' => Location::create(),
                'contacts' => [],
                'bankAccountId' => 1,
                'billingDestination' => UserBillingDestination::create(),
                'isEnabled' => true,
                'version' => 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ]);
            $dwsBillingStatement = $this->examples->dwsBillingStatements[0]->copy([
                'items' => Seq::from(...array_fill(0, self::ITEMS_PER_PAGE + 1, null))
                    ->map(fn (): DwsBillingStatementItem => new DwsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111111'),
                        serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                        unitScore: 2000,
                        count: 20,
                        totalScore: 2000,
                    ))
                    ->toArray(),
            ]);
            $ltcsBillingStatement = $this->examples->ltcsBillingStatements[0]->copy([
                'items' => Seq::from(...array_fill(0, self::ITEMS_PER_PAGE + 1, null))
                    ->map(fn (): LtcsBillingStatementItem => new LtcsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('111000'),
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
                    ))
                    ->toArray(),
            ]);

            $actual = UserBillingStatementPdf::from(
                $user,
                $this->examples->userBillings[0],
                $this->issuedOn,
                Option::some($dwsBillingStatement),
                Option::some($ltcsBillingStatement),
                $this->dwsServiceCodeMap,
                $this->ltcsServiceCodeMap
            );
            $this->assertSame(
                (int)ceil(count($dwsBillingStatement->items) / self::ITEMS_PER_PAGE)
                + (int)ceil(count($ltcsBillingStatement->items) / self::ITEMS_PER_PAGE),
                $actual->count()
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->userBillingStatementPdf->toJson());
        });
    }
}
