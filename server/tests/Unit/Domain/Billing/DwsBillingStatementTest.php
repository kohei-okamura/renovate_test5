<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBillingOffice;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementAggregate;
use Domain\Billing\DwsBillingStatementContract;
use Domain\Billing\DwsBillingStatementCopayCoordination;
use Domain\Billing\DwsBillingStatementCopayCoordinationStatus;
use Domain\Billing\DwsBillingStatementItem;
use Domain\Billing\DwsBillingStatus;
use Domain\Billing\DwsBillingUser;
use Domain\Billing\DwsGrantedServiceCode;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\Common\Prefecture;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsBillingStatement} のテスト.
 */
final class DwsBillingStatementTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use UnitSupport;
    use MatchesSnapshots;

    private DwsBillingStatement $statement;

    private array $values = [];
    private array $contractValues = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->contractValues = [
                'dwsGrantedServiceCode' => DwsGrantedServiceCode::housework(),
                'grantedAmount' => 1000,
                'agreedOn' => Carbon::today()->subMonth(),
                'expiredOn' => Carbon::today(),
                'indexNumber' => 1,
            ];
            $self->values = [
                'dwsBillingId' => 3,
                'dwsBillingBundleId' => 1,
                'subsidyCityCode' => '123456',
                'user' => DwsBillingUser::create([
                    'userId' => $self->examples->users[0]->id,
                    'dwsCertificationId' => $self->examples->dwsCertifications[9]->id,
                    'dwsNumber' => '0123456789',
                    'name' => 'ナマエ',
                    'childName' => 'オナマエ',
                    'copayLimit' => 10000,
                ]),
                'dwsAreaGradeName' => '地域区分名',
                'dwsAreaGradeCode' => '28',
                'totalScore' => 1000,
                'totalFee' => 1000,
                'totalCappedCopay' => 1000,
                'totalAdjustedCopay' => 1000,
                'totalCoordinatedCopay' => 1000,
                'totalCopay' => 1000,
                'totalBenefit' => 1000,
                'totalSubsidy' => 1000,
                'isProvided' => false,
                'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::unapplicable(),
                'copayCoordination' => DwsBillingStatementCopayCoordination::create([
                    'office' => DwsBillingOffice::create([
                        'officeId' => $self->examples->offices[0]->id,
                        'code' => '123456',
                        'name' => '事業所1',
                        'abbr' => '事業1',
                        'addr' => new Addr(
                            postcode: '739-0604',
                            prefecture: Prefecture::hiroshima(),
                            city: '大竹市',
                            street: '北栄1-13-11',
                            apartment: '北栄荘411',
                        ),
                        'tel' => '090-3169-6661',
                    ]),
                    'result' => CopayCoordinationResult::appropriated(),
                    'amount' => 1000,
                ]),
                'aggregates' => [
                    new DwsBillingStatementAggregate(
                        serviceDivisionCode: DwsServiceDivisionCode::visitingCareForPwsd(),
                        startedOn: Carbon::today()->subDay(),
                        terminatedOn: Carbon::today(),
                        serviceDays: 3,
                        subtotalScore: 1000,
                        unitCost: Decimal::fromInt(10_0000),
                        subtotalFee: 1000,
                        unmanagedCopay: 1000,
                        managedCopay: 1000,
                        cappedCopay: 1000,
                        adjustedCopay: 1000,
                        coordinatedCopay: 1000,
                        subtotalCopay: 1000,
                        subtotalBenefit: 1000,
                        subtotalSubsidy: 1000,
                    ),
                ],
                'contracts' => [
                    DwsBillingStatement::contract($self->contractValues),
                ],
                'items' => [
                    new DwsBillingStatementItem(
                        serviceCode: ServiceCode::fromString('123456'),
                        serviceCodeCategory: DwsServiceCodeCategory::physicalCare(),
                        unitScore: 200,
                        count: 10,
                        totalScore: 2000,
                    ),
                ],
                'status' => DwsBillingStatus::checking(),
                'fixedAt' => Carbon::now(),
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $self->statement = DwsBillingStatement::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_contract(): void
    {
        $this->assertModelStrictEquals(
            DwsBillingStatement::contract($this->contractValues),
            DwsBillingStatementContract::create($this->contractValues)
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'id' => ['id'],
            'dwsBillingBundleId' => ['dwsBillingBundleId'],
            'subsidyCityCode' => ['subsidyCityCode'],
            'user' => ['user'],
            'dwsAreaGradeName' => ['dwsAreaGradeName'],
            'dwsAreaGradeCode' => ['dwsAreaGradeCode'],
            'copayLimit' => ['copayLimit'],
            'totalScore' => ['totalScore'],
            'totalFee' => ['totalFee'],
            'totalCappedCopay' => ['totalCappedCopay'],
            'totalAdjustedCopay' => ['totalAdjustedCopay'],
            'totalCoordinatedCopay' => ['totalCoordinatedCopay'],
            'totalCopay' => ['totalCopay'],
            'totalBenefit' => ['totalBenefit'],
            'totalSubsidy' => ['totalSubsidy'],
            'isProvided' => ['isProvided'],
            'copayCoordinationStatus' => ['copayCoordinationStatus'],
            'copayCoordination' => ['copayCoordination'],
            'aggregates' => ['aggregates'],
            'contracts' => ['contracts'],
            'items' => ['items'],
            'status' => ['status'],
            'fixedAt' => ['fixedAt'],
            'createdAt' => ['createdAt'],
            'updatedAt' => ['updatedAt'],
        ];

        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->statement->get($key), Arr::get($this->values, $key));
            },
            compact('examples')
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->statement);
        });
    }
}
