<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Common;

use Domain\Common\Addr;
use Domain\Common\Prefecture;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * Addr のテスト
 */
final class AddrTest extends Test
{
    use UnitSupport;
    use MatchesSnapshots;

    /**
     * @test
     * @return void
     */
    public function describe_constructor(): void
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
     * @return \Domain\Common\Addr
     */
    private function createInstance(array $attrs = []): Addr
    {
        $x = new Addr(
            postcode: '984-0056',
            prefecture: Prefecture::miyagi(),
            city: '仙台市',
            street: '若林区成田町16番地の2',
            apartment: 'ロイヤルヒルズ成田町403号',
        );
        return $x->copy($attrs);
    }
}
