<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsBillingOffice;
use Domain\Billing\DwsBillingStatementItem;
use Domain\Billing\DwsBillingStatementPdf;
use Domain\Billing\DwsBillingUser;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Prefecture;
use Domain\Common\StructuredName;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsBillingStatementPdf} のテスト.
 */
final class DwsBillingStatementPdfTest extends Test
{
    use ExamplesConsumer;
    use MatchesSnapshots;
    use UnitSupport;

    private const ITEM_ROWS = 13;
    private const DAYS_RECORD_ROWS = 3;
    private const AGGREGATE_COLUMNS = 4;

    /**
     * @test
     * @return void
     */
    public function describe_create(): void
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
        $this->should('return an instance from statement and bundle', function (): void {
            $x = $this->createInstance();
            $this->assertMatchesModelSnapshot($x);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_pages(): void
    {
        $this->should('return total page count', function (): void {
            $x = $this->createInstance();
            $this->assertSame(
                (int)ceil(count($x->items) / self::ITEM_ROWS),
                $x->pages()
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_formattedPage(): void
    {
        $this->should('return format page number', function (): void {
            $x = $this->createInstance();
            $this->assertSame(sprintf('%02d', $x->pages()), $x->formatedPage($x->pages()));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_isLastPage(): void
    {
        $this->should('decide it is last page or not', function (): void {
            $x = $this->createInstance();
            $this->assertFalse($x->isLastPage(1));
            $this->assertTrue($x->isLastPage(2));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_extraDaysRecordRows(): void
    {
        $this->should('return extra days record rows', function (): void {
            $x = $this->createInstance();
            $this->assertSame(self::DAYS_RECORD_ROWS - count($x->daysRecords), $x->extraDaysRecordRows());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_itemsInThePage(): void
    {
        $this->should('return items in the page', function (): void {
            $x = $this->createInstance();
            $this->assertSame(
                array_slice($x->items, 0, self::ITEM_ROWS),
                $x->itemsInThePage(1)
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_itemRows(): void
    {
        $this->should('return item rows', function (): void {
            $x = $this->createInstance();
            $this->assertSame(self::ITEM_ROWS, $x->itemRows());
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
            $page = 1;
            $expected = self::ITEM_ROWS - count(array_slice($x->items, 0, self::ITEM_ROWS * $page));
            $this->assertSame($expected, $x->extraItemRows($page));
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
            $page = 1;
            $this->assertSame(self::AGGREGATE_COLUMNS, $x->extraAggregateColumns($page));
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
            $this->assertMatchesJsonSnapshot($x);
        });
    }

    /**
     * テスト対象のインスタンスを生成する.
     *
     * @param array $attrs
     * @return \Domain\Billing\DwsBillingStatementPdf
     */
    private function createInstance(array $attrs = []): DwsBillingStatementPdf
    {
        $values = [
            'office' => DwsBillingOffice::create([
                'officeId' => 1,
                'code' => '0123456789',
                'name' => '事業所テスト',
                'abbr' => '事テス',
                'addr' => new Addr(
                    postcode: '164-0012',
                    prefecture: Prefecture::tokyo(),
                    city: '中野区',
                    street: '本町1丁目1801',
                    apartment: '',
                ),
                'tel' => '090-0000-0000',
            ]),
            'cityCode' => '123456',
            'providedIn' => self::localized(Carbon::parse('2020/4/1')),
            'subsidyCityCode' => 012345,
            'user' => DwsBillingUser::create([
                'userId' => 4,
                'dwsCertificationId' => 3,
                'dwsNumber' => '0123456789',
                'name' => new StructuredName(
                    familyName: '内藤',
                    givenName: '勇介',
                    phoneticFamilyName: 'ナイトウ',
                    phoneticGivenName: 'ユウスケ',
                ),
                'childName' => new StructuredName(
                    familyName: '',
                    givenName: '',
                    phoneticFamilyName: '',
                    phoneticGivenName: '',
                ),
                'copayLimit' => 0,
            ]),
            'dwsAreaGradeName' => '地域区分名テスト',
            'copayLimit' => '    0',
            'copayCoordination' => [
                'code' => '          ',
                'name' => '',
                'result' => ' ',
                'amount' => '     ',
            ],
            'daysRecords' => [
                [
                    'dwsServiceDivisionCode' => '11',
                    'startedOn' => self::localized(Carbon::parse('2021/4/1')),
                    'terminatedOn' => self::localized(null),
                    'serviceDays' => '10',
                ],
            ],
            'items' => $this->items(),
            'aggregates' => [
                [
                    'serviceDivisionCode' => '11',
                    'resolvedServiceDivisionCode' => '居宅介護',
                    'serviceDays' => '14',
                    'subtotalScore' => '  7028',
                    'unitCost' => '1120',
                    'subtotalFee' => ' 78713',
                    'unmanagedCopay' => '  7871',
                    'managedCopay' => '  7871',
                    'cappedCopay' => '     0',
                    'adjustedCopay' => '      ',
                    'coordinatedCopay' => '      ',
                    'subtotalCopay' => '     0',
                    'subtotalBenefit' => ' 78713',
                    'subtotalSubsidy' => '      ',
                ],
            ],
            'totalScore' => '  7028',
            'totalFee' => ' 78713',
            'totalCappedCopay' => '     0',
            'totalAdjustedCopay' => '      ',
            'totalCoordinatedCopay' => '      ',
            'totalCopay' => '     0',
            'totalBenefit' => ' 78713',
            'totalSubsidy' => '      ',
            'exemptionMeasure' => 1,
            'exemptionTarget' => 1,
        ];
        return DwsBillingStatementPdf::create($attrs + $values);
    }

    /**
     * items を生成.
     *
     * @return array
     */
    private function items(): array
    {
        $items = [];
        for ($i = 0; $i < 15; ++$i) {
            $items[] = new DwsBillingStatementItem(
                serviceCode: ServiceCode::fromString('123456'),
                serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                unitScore: 100,
                count: 1,
                totalScore: 100,
            );
        }
        return $items;
    }

    /**
     * 日付を和暦、年、月、日にする.
     *
     * @param null|\Domain\Common\Carbon $carbon
     * @return array
     */
    private static function localized(?Carbon $carbon): array
    {
        return [
            'japaneseCalender' => $carbon !== null ? mb_substr($carbon->formatLocalized('%EC%Ey'), 0, 2) : '  ',
            'year' => $carbon !== null ? sprintf('%02s', mb_substr($carbon->formatLocalized('%EC%Ey'), 2)) : '  ',
            'month' => $carbon !== null ? $carbon->format('m') : '  ',
            'day' => $carbon !== null ? $carbon->format('d') : '  ',
        ];
    }
}
