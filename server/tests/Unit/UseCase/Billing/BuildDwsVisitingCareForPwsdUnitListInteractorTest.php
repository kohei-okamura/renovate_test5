<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \UseCase\Billing\BuildDwsVisitingCareForPwsdUnitListInteractor} のテスト.
 */
final class BuildDwsVisitingCareForPwsdUnitListInteractorTest extends Test
{
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            // TODO: 各テストケース（メソッド）の実行前に行う処理（準備）を記述する
        });
        self::beforeEachSpec(function (self $self): void {
            // TODO: 各テストケース（メソッド）の実行前に行う処理（準備）を記述する
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle()
    {
        self::markTestIncomplete('TODO: テストを記述する');
    }
}
