<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\ProvisionReport;

use Domain\ProvisionReport\LtcsProvisionReportSheetAppendixPdfEntry;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\ProvisionReport\LtcsProvisionReportSheetAppendixPdfEntry} のテスト.
 */
final class LtcsProvisionReportSheetAppendixPdfEntryTest extends Test
{
    use ExamplesConsumer;
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
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $this->should('return an instance', function (): void {
            $actual = LtcsProvisionReportSheetAppendixPdfEntry::from(
                Seq::from($this->examples->ltcsProvisionReportSheetAppendixEntry[0]),
                Seq::from($this->examples->ltcsProvisionReportSheetAppendixEntry[1]),
                $this->examples->ltcsProvisionReportSheetAppendixEntry[2]
            );

            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should('return an instance when some managedEntries', function (): void {
            $actual = LtcsProvisionReportSheetAppendixPdfEntry::from(
                Seq::from(
                    $this->examples->ltcsProvisionReportSheetAppendixEntry[0],
                    $this->examples->ltcsProvisionReportSheetAppendixEntry[3],
                ),
                Seq::from($this->examples->ltcsProvisionReportSheetAppendixEntry[1]),
                $this->examples->ltcsProvisionReportSheetAppendixEntry[2]
            );

            $this->assertMatchesModelSnapshot($actual);
        });
        $this->should('return an instance maxBenefitQuotaExcessScore and maxBenefitExcessScore zero', function (): void {
            $actual = LtcsProvisionReportSheetAppendixPdfEntry::from(
                Seq::from($this->examples->ltcsProvisionReportSheetAppendixEntry[4]),
                Seq::from($this->examples->ltcsProvisionReportSheetAppendixEntry[5]),
                $this->examples->ltcsProvisionReportSheetAppendixEntry[6]
            );

            $this->assertMatchesModelSnapshot($actual);
        });

        $this->should('return an instance when some managedEntries maxBenefitQuotaExcessScore and maxBenefitExcessScore zero', function (): void {
            $actual = LtcsProvisionReportSheetAppendixPdfEntry::from(
                Seq::from(
                    $this->examples->ltcsProvisionReportSheetAppendixEntry[4],
                    $this->examples->ltcsProvisionReportSheetAppendixEntry[7],
                ),
                Seq::from($this->examples->ltcsProvisionReportSheetAppendixEntry[4]),
                $this->examples->ltcsProvisionReportSheetAppendixEntry[5]
            );

            $this->assertMatchesModelSnapshot($actual);
        });
    }

    /**
     * テスト対象のインスタンスを生成する.
     *
     * @param array $attrs
     * @return \Domain\ProvisionReport\LtcsProvisionReportSheetAppendixPdfEntry
     */
    private function createInstance(array $attrs = []): LtcsProvisionReportSheetAppendixPdfEntry
    {
        $x = new LtcsProvisionReportSheetAppendixPdfEntry(
            officeName: '事業所名',
            officeCode: '0123456',
            serviceName: '訪問介護 合計',
            serviceCode: '123456',
            unitScore: '0',
            count: '0',
            wholeScore: '(' . number_format(0) . ')',
            maxBenefitQuotaExcessScore: '(' . number_format(0) . ')',
            maxBenefitExcessScore: '(' . number_format(0) . ')',
            scoreWithinMaxBenefitQuota: '(' . number_format(0) . ')',
            scoreWithinMaxBenefit: '(' . number_format(0) . ')',
            unitCost: sprintf('%.2f', 0.12),
            totalFeeForInsuranceOrBusiness: '(' . number_format(0) . ')',
            benefitRate: '(' . number_format(0) . ')',
            claimAmountForInsuranceOrBusiness: '(' . number_format(0) . ')',
            copayForInsuranceOrBusiness: '(' . number_format(0) . ')',
            copayWholeExpense: '(' . number_format(0) . ')',
        );
        return $x->copy($attrs);
    }
}
