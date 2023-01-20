<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Project;

use Domain\Project\DwsProjectServiceCategory;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Project\DwsProjectServiceCategory} のテスト.
 */
final class DwsProjectServiceCategoryTest extends Test
{
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
    }

    /**
     * @test
     * @return void
     */
    public function describe_isHomeHelpService(): void
    {
        $this->should(
            'return true',
            function (DwsProjectServiceCategory $category): void {
                $this->assertTrue($category->isHomeHelpService());
            },
            [
                'examples' => [
                    'physicalCare' => [DwsProjectServiceCategory::physicalCare()],
                    'accompanyWithPhysicalCare' => [DwsProjectServiceCategory::accompanyWithPhysicalCare()],
                    'accompany' => [DwsProjectServiceCategory::accompany()],
                    'housework' => [DwsProjectServiceCategory::housework()],
                ],
            ]
        );
        $this->should(
            'return false',
            function (DwsProjectServiceCategory $category): void {
                $this->assertFalse($category->isHomeHelpService());
            },
            [
                'examples' => [
                    'visitingCareForPwsd' => [DwsProjectServiceCategory::visitingCareForPwsd()],
                    'ownExpense' => [DwsProjectServiceCategory::ownExpense()],
                ],
            ]
        );
    }
}
