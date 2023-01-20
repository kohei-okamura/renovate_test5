<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\LtcsAreaGrade;

use Domain\LtcsAreaGrade\LtcsAreaGrade;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * LtcsAreaGrade のテスト
 */
class LtcsAreaGradeTest extends Test
{
    use ExamplesConsumer;
    use OrganizationExample;
    use UnitSupport;
    use MatchesSnapshots;

    protected LtcsAreaGrade $ltcsAreaGrade;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LtcsAreaGradeTest $self): void {
            $self->values = [
                'id' => 1,
                'code' => '01',
                'name' => 'テスト介保地域区分',
            ];
            $self->ltcsAreaGrade = LtcsAreaGrade::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have id attribute', function (): void {
            $this->assertSame($this->ltcsAreaGrade->get('id'), Arr::get($this->values, 'id'));
        });
        $this->should('have code attribute', function (): void {
            $this->assertSame($this->ltcsAreaGrade->get('code'), Arr::get($this->values, 'code'));
        });
        $this->should('have name attribute', function (): void {
            $this->assertSame($this->ltcsAreaGrade->get('name'), Arr::get($this->values, 'name'));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->ltcsAreaGrade);
        });
    }
}
