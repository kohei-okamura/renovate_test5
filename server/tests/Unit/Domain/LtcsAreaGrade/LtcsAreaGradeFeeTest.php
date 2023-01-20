<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\LtcsAreaGrade;

use Domain\Common\Carbon;
use Domain\Common\Decimal;
use Domain\LtcsAreaGrade\LtcsAreaGradeFee;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\LtcsAreaGrade\LtcsAreaGradeFee} のテスト.
 */
final class LtcsAreaGradeFeeTest extends Test
{
    use CarbonMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    protected LtcsAreaGradeFee $domain;

    protected array $values = [];

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->values = [
                'id' => 1,
                'ltcsAreaGradeId' => 2,
                'effectivatedOn' => Carbon::now(),
                'fee' => Decimal::fromInt(10_0000),
            ];

            $self->domain = LtcsAreaGradeFee::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have specified attribute', function (string $key): void {
            $this->assertSame($this->domain->get($key), Arr::get($this->values, $key));
        }, [
            'examples' => [
                'ltcsAreaGradeId' => ['ltcsAreaGradeId'],
                'effectivatedOn' => ['effectivatedOn'],
                'fee' => ['fee'],
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
