<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Shift;

use Domain\Shift\Assignee;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * Assignee のテスト
 */
class AssigneeTest extends Test
{
    use ExamplesConsumer;
    use OrganizationExample;
    use UnitSupport;
    use MatchesSnapshots;

    protected Assignee $assignee;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (AssigneeTest $self): void {
            $self->values = [
                'staffId' => $self->examples->staffs[0]->id,
                'isUndecided' => true,
                'isTraining' => true,
            ];
            $self->assignee = Assignee::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have staffId attribute', function (): void {
            $this->assertSame($this->assignee->get('staffId'), Arr::get($this->values, 'staffId'));
        });
        $this->should('have isUndecided attribute', function (): void {
            $this->assertSame($this->assignee->get('isUndecided'), Arr::get($this->values, 'isUndecided'));
        });
        $this->should('have isTraining attribute', function (): void {
            $this->assertSame($this->assignee->get('isTraining'), Arr::get($this->values, 'isTraining'));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->assignee);
        });
    }
}
