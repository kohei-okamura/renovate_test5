<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Office;

use Domain\Common\Carbon;
use Domain\Office\OfficeLtcsPreventionService;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Office\OfficeLtcsPreventionService} のテスト.
 */
final class OfficeLtcsPreventionServiceTest extends Test
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
     * @return \Domain\Office\OfficeLtcsPreventionService
     */
    private function createInstance(array $attrs = []): OfficeLtcsPreventionService
    {
        $x = new OfficeLtcsPreventionService(
            code: '01234567890123456789',
            openedOn: Carbon::parse('2010-03-30'),
            designationExpiredOn: Carbon::parse('2015-08-09')
        );
        return $x->copy($attrs);
    }
}
