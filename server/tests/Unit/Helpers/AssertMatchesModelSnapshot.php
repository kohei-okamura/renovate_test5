<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Helpers;

/**
 * ドメインモデルのスナップショット比較.
 *
 * @mixin \PHPUnit\Framework\Assert
 * @mixin \Spatie\Snapshots\MatchesSnapshots
 */
trait AssertMatchesModelSnapshot
{
    /**
     * ドメインモデルをスナップショットと比較する.
     *
     * @param \Domain\ModelCompat|iterable $actual
     * @return void
     */
    protected function assertMatchesModelSnapshot($actual): void
    {
        $this->assertMatchesSnapshot($actual, new ZingerModelDriver());
    }
}
