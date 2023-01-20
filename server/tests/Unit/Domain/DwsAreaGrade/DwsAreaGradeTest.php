<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\DwsAreaGrade;

use Domain\DwsAreaGrade\DwsAreaGrade;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * DwsAreaGrade のテスト
 */
class DwsAreaGradeTest extends Test
{
    use ExamplesConsumer;
    use OrganizationExample;
    use UnitSupport;
    use MatchesSnapshots;

    protected DwsAreaGrade $dwsAreaGrade;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DwsAreaGradeTest $self): void {
            $self->values = [
                'id' => 1,
                'code' => 2,
                'name' => '地域区分テスト',
            ];
            $self->dwsAreaGrade = DwsAreaGrade::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have id attribute', function (): void {
            $this->assertSame($this->dwsAreaGrade->get('id'), Arr::get($this->values, 'id'));
        });
        $this->should('have code attribute', function (): void {
            $this->assertSame($this->dwsAreaGrade->get('code'), Arr::get($this->values, 'code'));
        });
        $this->should('have name attribute', function (): void {
            $this->assertSame($this->dwsAreaGrade->get('name'), Arr::get($this->values, 'name'));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->dwsAreaGrade);
        });
    }
}
