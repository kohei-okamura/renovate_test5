<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Project;

use Domain\Project\LtcsProjectContent;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Project\LtcsProjectContent} のテスト
 */
class LtcsProjectContentTest extends Test
{
    use CarbonMixin;
    use UnitSupport;
    use MatchesSnapshots;

    protected LtcsProjectContent $ltcsProjectContent;
    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LtcsProjectContentTest $self): void {
            $self->values = [
                'menuId' => 1,
                'duration' => 60,
                'content' => '掃除',
                'memo' => '特になし',
            ];
            $self->ltcsProjectContent = LtcsProjectContent::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'menuId' => ['menuId'],
            'duration' => ['duration'],
            'content' => ['content'],
            'memo' => ['memo'],
        ];

        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->ltcsProjectContent->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->ltcsProjectContent);
        });
    }
}
