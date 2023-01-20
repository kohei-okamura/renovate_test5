<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Calling;

use Domain\Calling\Calling;
use Domain\Common\Carbon;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Calling\Calling} Test.
 */
class CallingTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use UnitSupport;

    protected Calling $calling;
    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CallingTest $self): void {
            $self->values = [
                'staffId' => $self->examples->staffs[0]->id,
                'shiftIds' => [$self->examples->shifts[0]->id],
                'token' => 'eustylelab',
                'expiredAt' => Carbon::now(),
                'createdAt' => Carbon::now(),
            ];
            $self->calling = Calling::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have specified attribute', function (string $key): void {
            $this->assertSame($this->calling->get($key), Arr::get($this->values, $key));
        }, [
            'examples' => [
                'staffId' => ['staffId'],
                'shiftIds' => ['shiftIds'],
                'token' => ['token'],
                'expiredAt' => ['expiredAt'],
                'createdAt' => ['createdAt'],
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
            $this->assertMatchesJsonSnapshot($this->calling);
        });
    }
}
