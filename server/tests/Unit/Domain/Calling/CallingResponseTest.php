<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Calling;

use Domain\Calling\CallingResponse;
use Domain\Common\Carbon;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Calling\CallingResponse} Test.
 */
class CallingResponseTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use UnitSupport;

    protected CallingResponse $callingResponse;
    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CallingResponseTest $self): void {
            $self->values = [
                'callingId' => $self->examples->callings[0]->id,
                'createdAt' => Carbon::now(),
            ];
            $self->callingResponse = CallingResponse::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have specified attribute', function (string $key): void {
            $this->assertSame($this->callingResponse->get($key), Arr::get($this->values, $key));
        }, [
            'examples' => [
                'callingId' => ['callingId'],
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
            $this->assertMatchesJsonSnapshot($this->callingResponse);
        });
    }
}
