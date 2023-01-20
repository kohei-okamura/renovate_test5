<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\LtcsBillingStatementAggregate;
use Domain\Billing\LtcsBillingStatementAggregateInsurance;
use Domain\Billing\LtcsBillingStatementAggregateSubsidy;
use Domain\Billing\LtcsServiceDivisionCode;
use Domain\Common\Decimal;
use Domain\Common\DefrayerCategory;
use Lib\Exceptions\LogicException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;
use Tests\Unit\UseCase\Billing\LtcsBillingTestSupport;

/**
 * {@link \Domain\Billing\LtcsBillingStatementAggregate} のテスト.
 */
final class LtcsBillingStatementAggregateTest extends Test
{
    use LtcsBillingTestSupport;
    use MatchesSnapshots;
    use UnitSupport;

    private Seq $userSubsidies;
    private Seq $emptyUserSubsidies;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->setupTestData();
            $self->userSubsidies = Seq::from(
                Option::some($self->subsidies[0]->copy([
                    'defrayerCategory' => DefrayerCategory::livelihoodProtection(),
                    'benefitRate' => 100,
                    'copay' => 0,
                ])),
                Option::none(),
                Option::none(),
            );
            $self->emptyUserSubsidies = Seq::from(
                Option::none(),
                Option::none(),
                Option::none(),
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_instance(): void
    {
        $this->should('return an instance', function (): void {
            $x = $this->createInstance();
            $this->assertMatchesModelSnapshot($x);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $this->should(
            'return an instance of LtcsBillingStatementAggregate',
            function (
                int $serviceDays,
                int $plannedScore,
                int $managedScore,
                int $unmanagedScore,
                Decimal $unitCost,
                int $benefitRate,
                Seq $userSubsidies
            ): void {
                $actual = LtcsBillingStatementAggregate::from(
                    userSubsidies: $userSubsidies,
                    benefitRate: $benefitRate,
                    serviceDivisionCode: LtcsServiceDivisionCode::homeVisitLongTermCare(),
                    serviceDays: $serviceDays,
                    plannedScore: $plannedScore,
                    managedScore: $managedScore,
                    unmanagedScore: $unmanagedScore,
                    unitCost: $unitCost
                );
                $this->assertInstanceOf(LtcsBillingStatementAggregate::class, $actual);
                $this->assertMatchesModelSnapshot($actual);
            },
            [
                'examples' => [
                    '1' => [30, 6789, 6789, 1234, Decimal::fromInt(11_4000), 90, $this->emptyUserSubsidies],
                    '2' => [28, 6789, 6789, 1234, Decimal::fromInt(11_4000), 90, $this->emptyUserSubsidies],
                    '3' => [30, 5678, 6789, 1234, Decimal::fromInt(11_4000), 90, $this->emptyUserSubsidies],
                    '4' => [30, 6789, 5678, 1234, Decimal::fromInt(11_4000), 90, $this->emptyUserSubsidies],
                    '5' => [30, 6789, 6789, 2345, Decimal::fromInt(11_4000), 90, $this->emptyUserSubsidies],
                    '6' => [30, 6789, 6789, 1234, Decimal::fromInt(10_2100), 90, $this->emptyUserSubsidies],
                    '7' => [30, 6789, 6789, 1234, Decimal::fromInt(11_4000), 80, $this->emptyUserSubsidies],
                    '8' => [30, 6789, 6789, 1234, Decimal::fromInt(11_4000), 90, $this->userSubsidies],
                ],
            ]
        );
        $this->should(
            'throw a LogicException when the size of userSubsidies !== 3',
            function (Seq $userSubsidies): void {
                $this->assertThrows(LogicException::class, function () use ($userSubsidies): void {
                    LtcsBillingStatementAggregate::from(
                        userSubsidies: $userSubsidies,
                        benefitRate: 90,
                        serviceDivisionCode: LtcsServiceDivisionCode::homeVisitLongTermCare(), // ①サービス種類コード
                        serviceDays: 30,
                        plannedScore: 12345,
                        managedScore: 12345,
                        unmanagedScore: 4567,
                        unitCost: Decimal::fromInt(11_4000)
                    );
                });
            },
            [
                'examples' => [
                    [Seq::empty()],
                    [Seq::from(Option::none())],
                    [Seq::from(Option::none(), Option::none())],
                    [Seq::from(Option::none(), Option::none(), Option::none(), Option::none())],
                ],
            ]
        );
        $this->describe('totalScore', function (): void {
            $this->should(
                'be a value either of plannedScore, or managedScore whichever is lower',
                function (int $plannedScore, int $managedScore, int $unmanagedScore, int $expected): void {
                    $aggregate = LtcsBillingStatementAggregate::from(
                        userSubsidies: $this->emptyUserSubsidies,
                        benefitRate: 90,
                        serviceDivisionCode: LtcsServiceDivisionCode::homeVisitLongTermCare(),
                        serviceDays: 30,
                        plannedScore: $plannedScore,
                        managedScore: $managedScore,
                        unmanagedScore: $unmanagedScore,
                        unitCost: Decimal::fromInt(11_4000)
                    );
                    $actual = $aggregate->insurance->totalScore;

                    $this->assertSame($expected, $actual);
                },
                [
                    'examples' => [
                        'when plannedScore === managedScore' => [1000, 1000, 2000, 3000],
                        'when plannedScore < managedScore' => [2000, 2001, 1210, 3210],
                        'when plannedScore > managedScore' => [3001, 3000, 1567, 4567],
                    ],
                ]
            );
        });
        $this->describe('claimAmount', function (): void {
            $this->should(
                'be a value that equals to (totalScore * unitCost) * (benefitRate / 100)',
                function (
                    int $managedScore,
                    int $unmanagedScore,
                    Decimal $unitCost,
                    int $benefitRate,
                    int $expected
                ): void {
                    $aggregate = LtcsBillingStatementAggregate::from(
                        userSubsidies: $this->emptyUserSubsidies,
                        benefitRate: $benefitRate,
                        serviceDivisionCode: LtcsServiceDivisionCode::homeVisitLongTermCare(),
                        serviceDays: 30,
                        plannedScore: $managedScore,
                        managedScore: $managedScore,
                        unmanagedScore: $unmanagedScore,
                        unitCost: $unitCost
                    );
                    $actual = $aggregate->insurance->claimAmount;

                    $this->assertSame($expected, $actual);
                },
                [
                    'examples' => [
                        [6789, 1234, Decimal::fromInt(11_4000), 90, 82315],
                        [4567, 1234, Decimal::fromInt(11_4000), 90, 59517],
                        [6789, 2345, Decimal::fromInt(11_4000), 90, 93714],
                        [6789, 1234, Decimal::fromInt(10_8400), 90, 78272],
                        [6789, 1234, Decimal::fromInt(11_4000), 70, 64023],
                    ],
                ]
            );
        });
        $this->describe('copayAmount', function (): void {
            $this->should(
                'be a value that equals totalAmount - claimAmount - (sum of subsidy\'s claimAmount)',
                function (
                    int $managedScore,
                    int $unmanagedScore,
                    Decimal $unitCost,
                    int $benefitRate,
                    Seq $userSubsidies,
                    int $expected
                ): void {
                    $aggregate = LtcsBillingStatementAggregate::from(
                        userSubsidies: $userSubsidies,
                        benefitRate: $benefitRate,
                        serviceDivisionCode: LtcsServiceDivisionCode::homeVisitLongTermCare(),
                        serviceDays: 30,
                        plannedScore: $managedScore,
                        managedScore: $managedScore,
                        unmanagedScore: $unmanagedScore,
                        unitCost: $unitCost
                    );
                    $actual = $aggregate->insurance->copayAmount;

                    $this->assertSame($expected, $actual);
                },
                [
                    'examples' => [
                        [6789, 1234, Decimal::fromInt(11_4000), 90, $this->emptyUserSubsidies, 9147],
                        [4567, 1234, Decimal::fromInt(11_4000), 90, $this->emptyUserSubsidies, 6614],
                        [6789, 2345, Decimal::fromInt(11_4000), 90, $this->emptyUserSubsidies, 10413],
                        [6789, 1234, Decimal::fromInt(10_8400), 90, $this->emptyUserSubsidies, 8697],
                        [6789, 1234, Decimal::fromInt(11_4000), 70, $this->emptyUserSubsidies, 27439],
                        [6789, 1234, Decimal::fromInt(11_4000), 90, $this->userSubsidies, 0],
                    ],
                ]
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('be able to encode to json', function (): void {
            $x = $this->createInstance();
            $this->assertMatchesJsonSnapshot($x->toJson());
        });
    }

    /**
     * テスト対象のインスタンスを生成する.
     *
     * @param array $attrs
     * @return \Domain\Billing\LtcsBillingStatementAggregate
     */
    private function createInstance(array $attrs = []): LtcsBillingStatementAggregate
    {
        $x = new LtcsBillingStatementAggregate(
            serviceDivisionCode: LtcsServiceDivisionCode::homeVisitLongTermCare(),
            serviceDays: 20,
            plannedScore: 57878,
            managedScore: 444238,
            unmanagedScore: 459666,
            insurance: new LtcsBillingStatementAggregateInsurance(
                totalScore: 296956,
                unitCost: Decimal::fromInt(11_1200),
                claimAmount: 539297,
                copayAmount: 194157,
            ),
            subsidies: [
                new LtcsBillingStatementAggregateSubsidy(
                    totalScore: 18087,
                    claimAmount: 229439,
                    copayAmount: 351933,
                ),
                LtcsBillingStatementAggregateSubsidy::empty(),
                LtcsBillingStatementAggregateSubsidy::empty(),
            ],
        );
        return $x->copy($attrs);
    }
}
