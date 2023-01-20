<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\ServiceCodeDictionary;

use Domain\Common\Carbon;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionary;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * DwsVisitingCareForPwsdDictionary のテスト.
 */
class DwsVisitingCareForPwsdDictionaryTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use OrganizationExample;
    use UnitSupport;
    use MatchesSnapshots;

    protected DwsVisitingCareForPwsdDictionary $dwsVisitingCareForPwsdDictionary;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsVisitingCareForPwsdDictionaryTest $self): void {
            $self->values = [
                'id' => 1,
                'effectivatedOn' => Carbon::now(),
                'name' => '名前',
                'version' => 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $self->dwsVisitingCareForPwsdDictionary = DwsVisitingCareForPwsdDictionary::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have id attribute', function (string $key): void {
            $this->assertSame($this->dwsVisitingCareForPwsdDictionary->get($key), Arr::get($this->values, $key));
        }, [
            'examples' => [
                'id' => ['id'],
                'effectivatedOn' => ['effectivatedOn'],
                'name' => ['name'],
                'version' => ['version'],
                'createdAt' => ['createdAt'],
                'updatedAt' => ['updatedAt'],
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
            $this->assertMatchesJsonSnapshot($this->dwsVisitingCareForPwsdDictionary);
        });
    }
}
