<?php
/**
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Decimal;
use Domain\ProvisionReport\LtcsProvisionReport;
use Domain\ProvisionReport\LtcsProvisionReportSheetAppendix;
use Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry;
use Faker\Generator;

/**
 * LtcsProvisionReport Example.
 *
 * @property-read LtcsProvisionReportSheetAppendixEntry[] $ltcsProvisionReportSheetAppendixEntry
 */
trait LtcsProvisionReportSheetAppendixEntryExample
{
    /**
     *  介護保険サービス：サービス提供票別表一覧を生成する.
     *
     * @return \Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry[]
     */
    protected function ltcsProvisionReportSheetAppendixEntry(): array
    {
        $faker = app(Generator::class);
        return [
            $this->generateLtcsProvisionReportSheetAppendixEntry($faker, []),
            $this->generateLtcsProvisionReportSheetAppendixEntry($faker, [
                'officeName' => '事業所名',
                'officeCode' => '0123456789',
                'serviceName' => '訪問介護処遇改善加算Ⅰ',
                'serviceCode' => '116275',
                'unitScore' => 60,
                'count' => 1,
                'wholeScore' => 69,
                'maxBenefitQuotaExcessScore' => 3,
                'maxBenefitExcessScore' => 40,
                'unitCost' => Decimal::fromInt(11_400),
                'benefitRate' => 80,
            ]),
            $this->generateLtcsProvisionReportSheetAppendixEntry($faker, [
                'officeName' => '事業所名',
                'officeCode' => '0123456789',
                'serviceName' => '訪問介護合計',
                'serviceCode' => '',
                'unitScore' => 0,
                'count' => 0,
                'wholeScore' => 1000,
            ]),
            $this->generateLtcsProvisionReportSheetAppendixEntry($faker, [
                'officeName' => '事業所名',
                'officeCode' => '0123456789',
                'serviceName' => '身体介護2',
                'serviceCode' => '111211',
                'unitScore' => 396,
                'count' => 2,
                'wholeScore' => 792,
                'maxBenefitQuotaExcessScore' => 20,
                'maxBenefitExcessScore' => 40,
            ]),
            $this->generateLtcsProvisionReportSheetAppendixEntry($faker, [
                'maxBenefitQuotaExcessScore' => 0,
                'maxBenefitExcessScore' => 0,
            ]),
            $this->generateLtcsProvisionReportSheetAppendixEntry($faker, [
                'officeName' => '事業所名',
                'officeCode' => '0123456789',
                'serviceName' => '訪問介護処遇改善加算Ⅰ',
                'serviceCode' => '116275',
                'unitScore' => 60,
                'count' => 1,
                'wholeScore' => 69,
                'maxBenefitQuotaExcessScore' => 0,
                'maxBenefitExcessScore' => 0,
                'unitCost' => Decimal::fromInt(11_400),
                'benefitRate' => 80,
            ]),
            $this->generateLtcsProvisionReportSheetAppendixEntry($faker, [
                'officeName' => '事業所名',
                'officeCode' => '0123456789',
                'serviceName' => '訪問介護合計',
                'serviceCode' => '',
                'unitScore' => 0,
                'count' => 0,
                'wholeScore' => 1000,
            ]),
            $this->generateLtcsProvisionReportSheetAppendixEntry($faker, [
                'officeName' => '事業所名',
                'officeCode' => '0123456789',
                'serviceName' => '身体介護2',
                'serviceCode' => '111211',
                'unitScore' => 396,
                'count' => 2,
                'wholeScore' => 792,
                'maxBenefitQuotaExcessScore' => 0,
                'maxBenefitExcessScore' => 0,
            ]),
        ];
    }

    /**
     * Generate an example of LtcsProvisionReportSheetAppendix.
     *
     * @param \Faker\Generator $faker
     * @param array $overwrites
     * @return \Domain\ProvisionReport\LtcsProvisionReportSheetAppendixEntry
     */
    protected function generateLtcsProvisionReportSheetAppendixEntry(Generator $faker, array $overwrites): LtcsProvisionReportSheetAppendixEntry
    {
        $attrs = $overwrites + [
            'officeName' => '事業所名',
            'officeCode' => '0123456789',
            'serviceName' => '身体介護1',
            'serviceCode' => '111111',
            'unitScore' => 250,
            'count' => 2,
            'wholeScore' => 500,
            'maxBenefitQuotaExcessScore' => 20,
            'maxBenefitExcessScore' => 40,
            'unitCost' => Decimal::fromInt(11_400),
            'benefitRate' => 80,
        ];
        return new LtcsProvisionReportSheetAppendixEntry(
            officeName: $attrs['officeName'],
            officeCode: $attrs['officeCode'],
            serviceName: $attrs['serviceName'],
            serviceCode: $attrs['serviceCode'],
            unitScore: $attrs['unitScore'],
            count: $attrs['count'],
            wholeScore: $attrs['wholeScore'],
            maxBenefitQuotaExcessScore: $attrs['maxBenefitQuotaExcessScore'],
            maxBenefitExcessScore: $attrs['maxBenefitExcessScore'],
            unitCost: $attrs['unitCost'],
            benefitRate: $attrs['benefitRate'],
        );
    }
}
