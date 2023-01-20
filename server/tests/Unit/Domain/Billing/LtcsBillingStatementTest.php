<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\LtcsBillingStatement;
use Domain\Billing\LtcsBillingStatementAggregate;
use Domain\Billing\LtcsBillingStatementAggregateInsurance;
use Domain\Billing\LtcsBillingStatementAggregateSubsidy;
use Domain\Billing\LtcsBillingStatementInsurance;
use Domain\Billing\LtcsBillingStatementItem;
use Domain\Billing\LtcsBillingStatementItemSubsidy;
use Domain\Billing\LtcsBillingStatementSubsidy;
use Domain\Billing\LtcsBillingStatus;
use Domain\Billing\LtcsBillingUser;
use Domain\Billing\LtcsCarePlanAuthor;
use Domain\Billing\LtcsExpiredReason;
use Domain\Billing\LtcsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\Common\DefrayerCategory;
use Domain\Common\Sex;
use Domain\Common\StructuredName;
use Domain\LtcsInsCard\LtcsCarePlanAuthorType;
use Domain\LtcsInsCard\LtcsLevel;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\LtcsBillingStatement} のテスト.
 */
final class LtcsBillingStatementTest extends Test
{
    use MatchesSnapshots;
    use UnitSupport;

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
     * @return \Domain\Billing\LtcsBillingStatement
     */
    private function createInstance(array $attrs = []): LtcsBillingStatement
    {
        $x = new LtcsBillingStatement(
            id: 22519017,
            billingId: 93152532,
            bundleId: 78289017,
            insurerNumber: '519388',
            insurerName: '盛岡市',
            user: new LtcsBillingUser(
                userId: 46457795,
                ltcsInsCardId: 18011460,
                insNumber: '1568891108',
                name: new StructuredName(
                    familyName: '富樫',
                    givenName: '友香',
                    phoneticFamilyName: 'トガシ',
                    phoneticGivenName: 'トモカ',
                ),
                sex: Sex::female(),
                birthday: Carbon::create(1970, 8, 28),
                ltcsLevel: LtcsLevel::careLevel4(),
                activatedOn: Carbon::create(2017, 4, 16),
                deactivatedOn: Carbon::create(1988, 10, 19),
            ),
            carePlanAuthor: new LtcsCarePlanAuthor(
                authorType: LtcsCarePlanAuthorType::careManagerOffice(),
                officeId: 97811357,
                code: '1321928942',
                name: '株式会社 桐山',
            ),
            agreedOn: Carbon::create(1999, 8, 27),
            expiredOn: Carbon::create(1976, 9, 4),
            expiredReason: LtcsExpiredReason::admittedToWelfareFacility(),
            insurance: new LtcsBillingStatementInsurance(
                benefitRate: 90,
                totalScore: 76658,
                claimAmount: 70326,
                copayAmount: 31923,
            ),
            subsidies: [
                new LtcsBillingStatementSubsidy(
                    defrayerCategory: DefrayerCategory::atomicBombVictim(),
                    defrayerNumber: '90982440',
                    recipientNumber: '30772136',
                    benefitRate: 100,
                    totalScore: 76703,
                    claimAmount: 23928,
                    copayAmount: 93923,
                ),
                LtcsBillingStatementSubsidy::empty(),
                LtcsBillingStatementSubsidy::empty(),
            ],
            items: [
                new LtcsBillingStatementItem(
                    serviceCode: ServiceCode::fromString('118592'),
                    serviceCodeCategory: LtcsServiceCodeCategory::housework(),
                    unitScore: 1382,
                    count: 16,
                    totalScore: 67284,
                    subsidies: [
                        new LtcsBillingStatementItemSubsidy(
                            count: 21,
                            totalScore: 557601,
                        ),
                        LtcsBillingStatementItemSubsidy::empty(),
                        LtcsBillingStatementItemSubsidy::empty(),
                    ],
                    note: '300',
                ),
            ],
            aggregates: [
                new LtcsBillingStatementAggregate(
                    serviceDivisionCode: LtcsServiceDivisionCode::homeVisitLongTermCare(),
                    serviceDays: 3,
                    plannedScore: 382759,
                    managedScore: 124281,
                    unmanagedScore: 50551,
                    insurance: new LtcsBillingStatementAggregateInsurance(
                        totalScore: 503019,
                        unitCost: Decimal::fromInt(11_4000),
                        claimAmount: 590722,
                        copayAmount: 209863,
                    ),
                    subsidies: [
                        new LtcsBillingStatementAggregateSubsidy(
                            totalScore: 561173,
                            claimAmount: 493609,
                            copayAmount: 484278,
                        ),
                        LtcsBillingStatementAggregateSubsidy::empty(),
                        LtcsBillingStatementAggregateSubsidy::empty(),
                    ],
                ),
            ],
            appendix: null,
            status: LtcsBillingStatus::checking(),
            fixedAt: Carbon::create(1975, 7, 15, 9, 32, 0),
            createdAt: Carbon::create(1993, 2, 18, 11, 57, 24),
            updatedAt: Carbon::create(2020, 7, 18, 3, 50, 48),
        );
        return $x->copy($attrs);
    }
}
