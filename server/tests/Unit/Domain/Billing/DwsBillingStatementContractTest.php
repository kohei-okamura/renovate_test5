<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\DwsBillingStatementContract;
use Domain\Billing\DwsGrantedServiceCode;
use Domain\Common\Carbon;
use Domain\DwsCertification\DwsCertificationAgreementType;
use Domain\DwsCertification\DwsLevel;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\DwsBillingStatementContract} のテスト.
 */
final class DwsBillingStatementContractTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use UnitSupport;
    use MatchesSnapshots;

    protected DwsBillingStatementContract $dwsBillingStatementContract;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (DwsBillingStatementContractTest $self): void {
            $self->values = [
                'dwsGrantedServiceCode' => DwsGrantedServiceCode::housework(),
                'grantedAmount' => 1000,
                'agreedOn' => Carbon::today()->subMonth(),
                'expiredOn' => Carbon::today(),
                'indexNumber' => 1,
            ];
            $self->dwsBillingStatementContract = DwsBillingStatementContract::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $examples = [
            'physicalCare' => [
                DwsCertificationAgreementType::physicalCare(),
                DwsGrantedServiceCode::physicalCare(),
            ],
            'housework' => [
                DwsCertificationAgreementType::housework(),
                DwsGrantedServiceCode::housework(),
            ],
            'accompanyWithPhysicalCare' => [
                DwsCertificationAgreementType::accompanyWithPhysicalCare(),
                DwsGrantedServiceCode::accompanyWithPhysicalCare(),
            ],
            'accompany' => [
                DwsCertificationAgreementType::accompany(),
                DwsGrantedServiceCode::accompany(),
            ],
            'visitingCareForPwsd1' => [
                DwsCertificationAgreementType::visitingCareForPwsd1(),
                DwsGrantedServiceCode::visitingCareForPwsd1(),
                ['dwsLevel' => DwsLevel::level6(), 'isSubjectOfComprehensiveSupport' => true],
            ],
            'visitingCareForPwsd2' => [
                DwsCertificationAgreementType::visitingCareForPwsd2(),
                DwsGrantedServiceCode::visitingCareForPwsd2(),
                ['dwsLevel' => DwsLevel::level6(), 'isSubjectOfComprehensiveSupport' => false],
            ],
            'visitingCareForPwsd3' => [
                DwsCertificationAgreementType::visitingCareForPwsd3(),
                DwsGrantedServiceCode::visitingCareForPwsd3(),
                ['dwsLevel' => DwsLevel::level1()],
            ],
            'outingSupportForPwsd' => [
                DwsCertificationAgreementType::outingSupportForPwsd(),
                DwsGrantedServiceCode::outingSupportForPwsd(),
            ],
        ];
        $this->should(
            'create DwsBillingStatementContract',
            function (DwsCertificationAgreementType $agreementType, DwsGrantedServiceCode $serviceCode): void {
                $agreement = $this->examples->dwsCertifications[0]->agreements[0]->copy([
                    'dwsCertificationAgreementType' => $agreementType,
                ]);
                $expected = DwsBillingStatementContract::create([
                    'dwsGrantedServiceCode' => $serviceCode,
                    'grantedAmount' => $agreement->paymentAmount,
                    'agreedOn' => $agreement->agreedOn,
                    'expiredOn' => $agreement->expiredOn,
                    'indexNumber' => $agreement->indexNumber,
                ]);

                $this->assertModelStrictEquals(
                    $expected,
                    DwsBillingStatementContract::from($agreement)
                );
            },
            compact('examples')
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'dwsGrantedServiceCode' => ['dwsGrantedServiceCode'],
            'grantedAmount' => ['grantedAmount'],
            'agreedOn' => ['agreedOn'],
            'expiredOn' => ['expiredOn'],
            'indexNumber' => ['indexNumber'],
        ];

        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->dwsBillingStatementContract->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->dwsBillingStatementContract);
        });
    }
}
