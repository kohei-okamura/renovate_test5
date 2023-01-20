<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\LtcsInsCard;

use Domain\LtcsInsCard\LtcsLevel;
use Lib\Exceptions\LogicException;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\LtcsInsCard\LtcsLevel} のテスト.
 */
final class LtcsLevelTest extends Test
{
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    /**
     * @test
     * @return void
     */
    public function describe_maxBenefit(): void
    {
        $examples = [
            'supportLevel1' => [LtcsLevel::supportLevel1(), 5032],
            'supportLevel2' => [LtcsLevel::supportLevel2(), 10531],
            'careLevel1' => [LtcsLevel::careLevel1(), 16765],
            'careLevel2' => [LtcsLevel::careLevel2(), 19705],
            'careLevel3' => [LtcsLevel::careLevel3(), 27048],
            'careLevel4' => [LtcsLevel::careLevel4(), 30938],
            'careLevel5' => [LtcsLevel::careLevel5(), 36217],
        ];
        $this->should(
            'return maxBenefit',
            function (LtcsLevel $level, $maxBenefit): void {
                $this->assertEquals($maxBenefit, $level->maxBenefit());
            },
            compact('examples')
        );
        $this->should('throw LogicException when LtcsLevel is target', function (): void {
            $this->assertThrows(LogicException::class, function (): void {
                LtcsLevel::target()->maxBenefit();
            });
        });
    }
}
