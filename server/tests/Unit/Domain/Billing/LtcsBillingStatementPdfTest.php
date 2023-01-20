<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\LtcsBillingOffice;
use Domain\Billing\LtcsBillingStatementAggregate;
use Domain\Billing\LtcsBillingStatementItem;
use Domain\Billing\LtcsBillingStatementPdf;
use Domain\Billing\LtcsBillingStatementPdfAggregate;
use Domain\Billing\LtcsBillingStatementPdfItem;
use Domain\Billing\LtcsBillingUser;
use Domain\Billing\LtcsCarePlanAuthor;
use Domain\Billing\LtcsExpiredReason;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Prefecture;
use Domain\Common\Sex;
use Domain\Common\StructuredName;
use Domain\LtcsInsCard\LtcsCarePlanAuthorType;
use Domain\LtcsInsCard\LtcsLevel;
use Domain\Pdf\PdfSupport;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\LtcsBillingStatementPdf} のテスト.
 */
final class LtcsBillingStatementPdfTest extends Test
{
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use PdfSupport;
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $this->should('return self', function (): void {
            $entity = LtcsBillingStatementPdf::from(
                LtcsBillingOffice::from($this->examples->offices[0]),
                $this->examples->ltcsBillingBundles[0],
                $this->examples->ltcsBillingStatements[0],
                Seq::fromArray($this->examples->ltcsHomeVisitLongTermCareDictionaryEntries)
                    ->toMap(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->serviceCode->toString()),
            );

            $this->assertInstanceOf(LtcsBillingStatementPdf::class, $entity);
        });
        $this->should('return expected params', function (): void {
            $office = LtcsBillingOffice::from($this->examples->offices[0]);
            $bundle = $this->examples->ltcsBillingBundles[0];
            $statement = $this->examples->ltcsBillingStatements[0];
            $serviceCodeMap = Seq::fromArray($this->examples->ltcsHomeVisitLongTermCareDictionaryEntries)
                ->toMap(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->serviceCode->toString());
            $actual = LtcsBillingStatementPdf::from(
                $office,
                $bundle,
                $statement,
                $serviceCodeMap,
            );
            $expected = new LtcsBillingStatementPdf(
                office: $office,
                defrayerNumber: $statement->subsidies[0]->defrayerNumber,
                recipientNumber: $statement->subsidies[0]->recipientNumber,
                providedIn: $this->localized($bundle->providedIn),
                insurerNumber: $statement->insurerNumber,
                user: $statement->user,
                userBirthday: $this->localized($statement->user->birthday),
                userActivatedOn: $this->localized($statement->user->activatedOn),
                userDeactivatedOn: $this->localized($statement->user->deactivatedOn),
                carePlanAuthor: $statement->carePlanAuthor,
                agreedOn: $this->localized($statement->agreedOn),
                expiredOn: $this->localized($statement->expiredOn),
                expiredReason: $statement->expiredReason->value(),
                items: Seq::fromArray($statement->items)
                    ->map(
                        fn (LtcsBillingStatementItem $item): LtcsBillingStatementPdfItem => LtcsBillingStatementPdfItem::from($item, $serviceCodeMap)
                    )->toArray(),
                aggregates: Seq::fromArray($statement->aggregates)
                    ->map(
                        fn (LtcsBillingStatementAggregate $aggregate): LtcsBillingStatementPdfAggregate => LtcsBillingStatementPdfAggregate::from($aggregate)
                    )
                    ->toArray(),
                insuranceBenefitRate: sprintf('% 3d', $statement->insurance->benefitRate),
                subsidyBenefitRate: preg_split('//u', sprintf('% 3d', $statement->subsidies[0]->benefitRate)),
                totalInsuranceClaimAmount: sprintf(
                    '% 6d',
                    Seq::fromArray($statement->aggregates)
                        ->map(fn (LtcsBillingStatementAggregate $x): int => $x->insurance->claimAmount)
                        ->sum()
                ),
                totalInsuranceCopayAmount: sprintf(
                    '% 6d',
                    Seq::fromArray($statement->aggregates)
                        ->map(fn (LtcsBillingStatementAggregate $x): int => $x->insurance->copayAmount)
                        ->sum()
                ),
                totalSubsidyClaimAmount: sprintf(
                    '% 6d',
                    Seq::fromArray($statement->aggregates)
                        ->map(fn (LtcsBillingStatementAggregate $x): int => $x->subsidies[0]?->claimAmount ?? 0)
                        ->sum()
                ),
                totalSubsidyCopayAmount: sprintf(
                    '% 6d',
                    Seq::fromArray($statement->aggregates)
                        ->map(fn (LtcsBillingStatementAggregate $x): int => $x->subsidies[0]?->copayAmount ?? 0)
                        ->sum()
                )
            );

            $this->assertModelStrictEquals($expected, $actual);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_extraItemRows(): void
    {
        $this->should('return extra rows', function (): void {
            $x = $this->createInstance();
            $this->assertSame(
                10 - count($this->examples->ltcsBillingStatements[0]->items),
                $x->extraItemRows()
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_extraAggregateColumns(): void
    {
        $this->should('return extra columns', function (): void {
            $x = $this->createInstance();
            $this->assertSame(
                4 - count($this->examples->ltcsBillingStatements[0]->aggregates),
                $x->extraAggregateColumns()
            );
        });
    }

    /**
     * テスト対象のインスタンスを生成する.
     *
     * @param array $attrs
     * @return \Domain\Billing\LtcsBillingStatementPdf
     */
    private function createInstance(array $attrs = []): LtcsBillingStatementPdf
    {
        $values = $attrs + [
            'office' => new LtcsBillingOffice(
                officeId: 1,
                code: '0123456789',
                name: '事業所テスト',
                abbr: '事テス',
                addr: new Addr(
                    postcode: '164-0012',
                    prefecture: Prefecture::tokyo(),
                    city: '中野区',
                    street: '本町1丁目1801',
                    apartment: '',
                ),
                tel: '090-0000-0000',
            ),
            'defrayerNumber' => '01234567',
            'recipientNumber' => '0123456',
            'providedIn' => self::localized(Carbon::parse('2020/4/1')),
            'insurerNumber' => '012345',
            'user' => new LtcsBillingUser(
                userId: 4,
                ltcsInsCardId: 3,
                insNumber: '0123456789',
                name: new StructuredName(
                    familyName: '内藤',
                    givenName: '勇介',
                    phoneticFamilyName: 'ナイトウ',
                    phoneticGivenName: 'ユウスケ',
                ),
                sex: Sex::male(),
                birthday: Carbon::parse('1950/1/1'),
                ltcsLevel: LtcsLevel::careLevel4(),
                activatedOn: Carbon::parse('1994/1/1'),
                deactivatedOn: Carbon::parse('1995/1/1'),
            ),
            'userBirthday' => self::localized(Carbon::parse('1950/1/1')),
            'userActivatedOn' => self::localized(Carbon::parse('1994/1/1')),
            'userDeactivatedOn' => self::localized(Carbon::parse('1995/1/1')),
            'carePlanAuthor' => new LtcsCarePlanAuthor(
                authorType: LtcsCarePlanAuthorType::careManagerOffice(),
                officeId: 1,
                code: '0123456789',
                name: '事業所テスト',
            ),
            'agreedOn' => self::localized(Carbon::parse('2020/4/1')),
            'expiredOn' => self::localized(Carbon::parse('2020/5/1')),
            'expiredReason' => LtcsExpiredReason::admittedToCareAidMedicalCenter()->value(),
            'items' => [
                [
                    'serviceCode' => '112345',
                    'unitScore' => '1123',
                    'count' => '21',
                    'totalScore' => '12',
                    'subsidyCount' => '11',
                    'subsidyScore' => '12',
                    'note' => '適当なメモ',
                ],
            ],
            'aggregates' => [
                [
                    'serviceDivisionCode' => '11',
                    'resolvedServiceDivisionCode' => '訪問介護',
                    'serviceDays' => ' 1',
                    'plannedScore' => '123456',
                    'managedScore' => '123456',
                    'unmanagedScore' => '123456',
                    'totalScore' => '246012',
                    'subsidyTotalScore' => '123456',
                    'insuranceUnitCost' => '1234',
                    'insuranceClaimAmount' => '123456',
                    'insuranceCopayAmount' => '123456',
                    'subsidyClaimAmount' => '123456',
                    'subsidyCopayAmount' => '123456',
                ],
            ],
            'insuranceBenefitRate' => ' 90',
            'subsidyBenefitRate' => ['1', '0', '0'],
            'totalInsuranceClaimAmount' => '100000',
            'totalInsuranceCopayAmount' => '100000',
            'totalSubsidyClaimAmount' => '100000',
            'totalSubsidyCopayAmount' => '100000',
        ];
        return new LtcsBillingStatementPdf(
            office: $values['office'],
            defrayerNumber: $values['defrayerNumber'],
            recipientNumber: $values['recipientNumber'],
            providedIn: $values['providedIn'],
            insurerNumber: $values['insurerNumber'],
            user: $values['user'],
            userBirthday: $values['userBirthday'],
            userActivatedOn: $values['userActivatedOn'],
            userDeactivatedOn: $values['userDeactivatedOn'],
            carePlanAuthor: $values['carePlanAuthor'],
            agreedOn: $values['agreedOn'],
            expiredOn: $values['expiredOn'],
            expiredReason: $values['expiredReason'],
            items: $values['items'],
            aggregates: $values['aggregates'],
            insuranceBenefitRate: $values['insuranceBenefitRate'],
            subsidyBenefitRate: $values['subsidyBenefitRate'],
            totalInsuranceClaimAmount: $values['totalInsuranceClaimAmount'],
            totalInsuranceCopayAmount: $values['totalInsuranceCopayAmount'],
            totalSubsidyClaimAmount: $values['totalSubsidyClaimAmount'],
            totalSubsidyCopayAmount: $values['totalSubsidyCopayAmount'],
        );
    }
}
