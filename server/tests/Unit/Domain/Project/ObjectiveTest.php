<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Project;

use Domain\Common\CarbonRange;
use Domain\Project\Objective;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Office\DwsProject} Test.
 */
class ObjectiveTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use UnitSupport;

    protected Objective $domain;
    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ObjectiveTest $self): void {
            $self->values = [
                'term' => CarbonRange::create(),
                'text' => 'Text',
            ];
            $self->domain = Objective::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have specified attribute', function (string $key, $expected = null): void {
            $expected ?:
            $this->assertEquals($this->domain->get($key), $expected ?? Arr::get($this->values, $key));
        }, [
            'examples' => [
                'term' => ['term'],
                'text' => ['text'],
            ],
        ]);
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->domain);
        });
    }
}
