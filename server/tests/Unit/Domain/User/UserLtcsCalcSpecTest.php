<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\User;

use Domain\Common\Carbon;
use Domain\User\LtcsUserLocationAddition;
use Domain\User\UserLtcsCalcSpec;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\User\UserLtcsCalcSpec} のテスト.
 */
final class UserLtcsCalcSpecTest extends Test
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
     * @return \Domain\User\UserLtcsCalcSpec
     */
    private function createInstance(array $attrs = []): UserLtcsCalcSpec
    {
        $x = new UserLtcsCalcSpec(
            id: null,
            userId: 1,
            effectivatedOn: Carbon::create(2012, 11, 15),
            locationAddition: LtcsUserLocationAddition::mountainousArea(),
            isEnabled: true,
            version: 1,
            createdAt: Carbon::create(2009, 10, 10, 19, 11, 19),
            updatedAt: Carbon::create(2016, 12, 13, 3, 55, 31),
        );
        return $x->copy($attrs);
    }
}
