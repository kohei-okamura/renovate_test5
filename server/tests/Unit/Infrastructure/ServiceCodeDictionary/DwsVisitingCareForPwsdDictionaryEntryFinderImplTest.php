<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Infrastructure\ServiceCodeDictionary;

use Domain\Common\Carbon;
use Domain\ServiceCode\ServiceCode;
use Infrastructure\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryFinderImpl;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Infrastructure\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntryFinderImpl} のテスト.
 */
final class DwsVisitingCareForPwsdDictionaryEntryFinderImplTest extends Test
{
    use ConfigMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private DwsVisitingCareForPwsdDictionaryEntryFinderImpl $finder;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            // TODO: 各テストケース（メソッド）の実行前に行う処理（準備）を記述する
        });
        self::beforeEachSpec(function (self $self): void {
            $self->finder = app(DwsVisitingCareForPwsdDictionaryEntryFinderImpl::class);
        });
    }

    /**
     * 一旦テスト呼ばないようにしておく（最終的には外部API叩かないように単体テストで動かないようにする）
     * @return void
     */
    public function describe_find(): void
    {
        $this->should('call api', function () {
            $actual = $this->finder->find(
                [
                    'providedIn' => Carbon::now(),
                    'q' => ServiceCode::fromString('128040')->toString(),
                ],
                ['all' => true]
            );
            $list = $actual->list->computed();
            $hoge = $actual;
        });
    }
}
