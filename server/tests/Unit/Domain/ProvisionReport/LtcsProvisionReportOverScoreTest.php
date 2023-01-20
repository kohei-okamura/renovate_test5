<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\ProvisionReport;

use Domain\ProvisionReport\LtcsProvisionReportOverScore;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\ProvisionReport\LtcsProvisionReportOverScore} のテスト.
 */
final class LtcsProvisionReportOverScoreTest extends Test
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
     * @return \Domain\ProvisionReport\LtcsProvisionReportOverScore
     */
    private function createInstance(array $attrs = []): LtcsProvisionReportOverScore
    {
        $x = new LtcsProvisionReportOverScore(
            maxBenefitExcessScore: 100,
            maxBenefitQuotaExcessScore: 200
        );
        return $x->copy($attrs);
    }
}
