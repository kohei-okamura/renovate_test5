<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\User;

use Domain\Common\Carbon;
use Domain\User\DwsUserLocationAddition;
use Domain\User\UserDwsCalcSpec;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\User\UserDwsCalcSpec} のテスト.
 */
final class UserDwsCalcSpecTest extends Test
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
     * @return \Domain\User\UserDwsCalcSpec
     */
    private function createInstance(array $attrs = []): UserDwsCalcSpec
    {
        $x = new UserDwsCalcSpec(
            id: null,
            userId: 1,
            effectivatedOn: Carbon::create(2012, 11, 15),
            locationAddition: DwsUserLocationAddition::specifiedArea(),
            isEnabled: true,
            version: 1,
            createdAt: Carbon::create(2009, 10, 10, 19, 11, 19),
            updatedAt: Carbon::create(2016, 12, 13, 3, 55, 31),
        );
        return $x->copy($attrs);
    }
}
