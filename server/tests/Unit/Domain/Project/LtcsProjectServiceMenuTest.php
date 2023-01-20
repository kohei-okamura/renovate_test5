<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Project;

use Domain\Common\Carbon;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\Project\LtcsProjectServiceMenu;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Project\LtcsProjectServiceMenu} のテスト
 */
class LtcsProjectServiceMenuTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use UnitSupport;
    use MatchesSnapshots;

    protected LtcsProjectServiceMenu $ltcsProjectServiceMenu;
    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LtcsProjectServiceMenuTest $self): void {
            $self->values = [
                'id' => 1,
                'category' => LtcsProjectServiceCategory::housework(),
                'name' => '洗濯',
                'displayName' => '表示名',
                'sortOrder' => 1,
                'createdAt' => Carbon::now(),
            ];
            $self->ltcsProjectServiceMenu = LtcsProjectServiceMenu::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'id' => ['id'],
            'category' => ['category'],
            'name' => ['name'],
            'displayName' => ['displayName'],
            'sortOrder' => ['sortOrder'],
            'createdAt' => ['createdAt'],
        ];

        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->ltcsProjectServiceMenu->get($key), Arr::get($this->values, $key));
            },
            compact('examples')
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->ltcsProjectServiceMenu);
        });
    }
}
