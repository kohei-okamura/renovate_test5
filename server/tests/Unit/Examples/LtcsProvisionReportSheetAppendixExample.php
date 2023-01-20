<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportSheetAppendix;
use Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry;
use Faker\Generator;
use ScalikePHP\Seq;

/**
 * LtcsProvisionReport Example.
 *
 * @property-read LtcsProvisionReportSheetAppendix[] $ltcsProvisionReportSheetAppendix
 */
trait LtcsProvisionReportSheetAppendixExample
{
    /**
     *  介護保険サービス：サービス提供票別表一覧を生成する.
     *
     * @return \Domain\ProvisionReport\LtcsProvisionReportSheetAppendix[]
     */
    protected function ltcsProvisionReportSheetAppendix(): array
    {
        $faker = app(Generator::class);
        return [
            $this->generateLtcsProvisionReportSheetAppendix($faker, []),
        ];
    }

    /**
     * Generate an example of LtcsProvisionReportSheetAppendix.
     *
     * @param \Faker\Generator $faker
     * @param array $overwrites
     * @return \Domain\ProvisionReport\LtcsProvisionReportSheetAppendix
     */
    protected function generateLtcsProvisionReportSheetAppendix(Generator $faker, array $overwrites): LtcsProvisionReportSheetAppendix
    {
        $attrs = $overwrites + [
            'providedIn' => Carbon::now(),
            'insNumber' => '123456789',
            'userName' => '名前',
            'unmanagedEntries' => Seq::from(
                new LtcsProvisionReportSheetAppendixEntry(
                    officeName: '事業所名',
                    officeCode: '0123456789',
                    serviceName: '訪問介護処遇改善加算Ⅰ',
                    serviceCode: '116275',
                    unitScore: 60,
                    count: 1,
                    wholeScore: 69,
                    maxBenefitQuotaExcessScore: 3,
                    maxBenefitExcessScore: 40,
                    unitCost: Decimal::fromInt(11_400),
                    benefitRate: 80,
                )
            ),
            'managedEntries' => Seq::from(
                new LtcsProvisionReportSheetAppendixEntry(
                    officeName: '事業所名',
                    officeCode: '0123456789',
                    serviceName: '身体介護1',
                    serviceCode: '111111',
                    unitScore: 250,
                    count: 2,
                    wholeScore: 500,
                    maxBenefitQuotaExcessScore: 20,
                    maxBenefitExcessScore: 40,
                    unitCost: Decimal::fromInt(11_400),
                    benefitRate: 80,
                )
            ),
            'maxBenefit' => 36217,
            'insuranceClaimAmount' => 29373,
            'subsidyClaimAmount' => 0,
            'copayAmount' => 10000,
            'unitCost' => Decimal::fromInt(11_400),
        ];
        return new LtcsProvisionReportSheetAppendix(
            providedIn: $attrs['providedIn'],
            insNumber: $attrs['insNumber'],
            userName: $attrs['userName'],
            unmanagedEntries: $attrs['unmanagedEntries'],
            managedEntries: $attrs['managedEntries'],
            maxBenefit: $attrs['maxBenefit'],
            insuranceClaimAmount: $attrs['insuranceClaimAmount'],
            subsidyClaimAmount: $attrs['subsidyClaimAmount'],
            copayAmount: $attrs['copayAmount'],
            unitCost: $attrs['unitCost'],
        );
    }
}
