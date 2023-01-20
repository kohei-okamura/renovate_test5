<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\ProvisionReport;

use Domain\LtcsInsCard\LtcsLevel;
use Domain\ProvisionReport\LtcsProvisionReportType;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\ProvisionReport\LtcsProvisionReportType} のテスト.
 */
final class LtcsProvisionReportTypeTest extends Test
{
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_fromLtcsLevel(): void
    {
        $examples = [
            'target' => [LtcsLevel::target(), LtcsProvisionReportType::comprehensiveService()],
            'supportLevel1' => [LtcsLevel::supportLevel1(), LtcsProvisionReportType::comprehensiveService()],
            'supportLevel2' => [LtcsLevel::supportLevel2(), LtcsProvisionReportType::comprehensiveService()],
            'careLevel1' => [LtcsLevel::careLevel1(), LtcsProvisionReportType::homeVisitLongTermCare()],
            'careLevel2' => [LtcsLevel::careLevel2(), LtcsProvisionReportType::homeVisitLongTermCare()],
            'careLevel3' => [LtcsLevel::careLevel3(), LtcsProvisionReportType::homeVisitLongTermCare()],
            'careLevel4' => [LtcsLevel::careLevel4(), LtcsProvisionReportType::homeVisitLongTermCare()],
            'careLevel5' => [LtcsLevel::careLevel5(), LtcsProvisionReportType::homeVisitLongTermCare()],
        ];
        $this->should(
            'return maxBenefit',
            function (LtcsLevel $level, $provisionReportType): void {
                $this->assertEquals($provisionReportType, LtcsProvisionReportType::fromLtcsLevel($level));
            },
            compact('examples')
        );
    }
}
