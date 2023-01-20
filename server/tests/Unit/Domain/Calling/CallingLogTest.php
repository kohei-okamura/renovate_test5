<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Calling;

use Domain\Calling\CallingLog;
use Domain\Calling\CallingType;
use Domain\Common\Carbon;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Calling\CallingLog} Test.
 */
class CallingLogTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use UnitSupport;

    protected CallingLog $callingLog;
    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CallingLogTest $self): void {
            $self->values = [
                'callingId' => $self->examples->callings[0]->id,
                'callingType' => CallingType::mail(),
                'isSucceeded' => true,
                'createdAt' => Carbon::now(),
            ];
            $self->callingLog = CallingLog::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have specified attribute', function (string $key): void {
            $this->assertSame($this->callingLog->get($key), Arr::get($this->values, $key));
        }, [
            'examples' => [
                'callingId' => ['callingId'],
                'callingType' => ['callingType'],
                'isSucceeded' => ['isSucceeded'],
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
            $this->assertMatchesJsonSnapshot($this->callingLog);
        });
    }
}
