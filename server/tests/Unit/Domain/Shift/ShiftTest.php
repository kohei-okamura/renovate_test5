<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Shift;

use Domain\Common\Carbon;
use Domain\ServiceCode\ServiceCode;
use Domain\Shift\Shift;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * Shift のテスト
 */
class ShiftTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use OrganizationExample;
    use UnitSupport;
    use MatchesSnapshots;

    protected Shift $shift;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ShiftTest $self): void {
            $self->values = [
                'id' => 1,
                'organization_id' => $self->examples->organizations[0]->id,
                'contractId' => $self->examples->contracts[0]->id,
                'officeId' => $self->examples->offices[0]->id,
                'userId' => $self->examples->users[0]->id,
                'assignerId' => 1,
                'task' => 1001,
                'serviceCode' => ServiceCode::fromString('123456'),
                'headcount' => '01234',
                'assignees' => ['123', 'true', 'true'],
                'schedule' => ['2018-02-28 23:59:59', '2018-02-28 23:59:59', '2018-02-28 23:59:59'],
                'durations' => [1001, 1],
                'options' => 1000,
                'note' => '備考',
                'isConfirmed' => true,
                'isCanceled' => true,
                'reason' => 'キャンセル理由',
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $self->shift = Shift::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have id attribute', function (): void {
            $this->assertSame($this->shift->get('id'), Arr::get($this->values, 'id'));
        });
        $this->should('have organizationId attribute', function (): void {
            $this->assertSame($this->shift->get('organizationId'), Arr::get($this->values, 'organizationId'));
        });
        $this->should('have contractId attribute', function (): void {
            $this->assertSame($this->shift->get('contractId'), Arr::get($this->values, 'contractId'));
        });
        $this->should('have officeId attribute', function (): void {
            $this->assertSame($this->shift->get('officeId'), Arr::get($this->values, 'officeId'));
        });
        $this->should('have userId attribute', function (): void {
            $this->assertSame($this->shift->get('userId'), Arr::get($this->values, 'userId'));
        });
        $this->should('have assignerId attribute', function (): void {
            $this->assertSame($this->shift->get('assignerId'), Arr::get($this->values, 'assignerId'));
        });
        $this->should('have task attribute', function (): void {
            $this->assertSame($this->shift->get('task'), Arr::get($this->values, 'task'));
        });
        $this->should('have serviceCode attribute', function (): void {
            $this->assertSame($this->shift->get('serviceCode'), Arr::get($this->values, 'serviceCode'));
        });
        $this->should('have headcount attribute', function (): void {
            $this->assertSame($this->shift->get('headcount'), Arr::get($this->values, 'headcount'));
        });
        $this->should('have assignees attribute', function (): void {
            $this->assertSame($this->shift->get('assignees'), Arr::get($this->values, 'assignees'));
        });
        $this->should('have schedule attribute', function (): void {
            $this->assertSame($this->shift->get('schedule'), Arr::get($this->values, 'schedule'));
        });
        $this->should('have durations attribute', function (): void {
            $this->assertSame($this->shift->get('durations'), Arr::get($this->values, 'durations'));
        });
        $this->should('have options attribute', function (): void {
            $this->assertSame($this->shift->get('options'), Arr::get($this->values, 'options'));
        });
        $this->should('have note attribute', function (): void {
            $this->assertSame($this->shift->get('note'), Arr::get($this->values, 'note'));
        });
        $this->should('have isConfirmed attribute', function (): void {
            $this->assertSame($this->shift->get('isConfirmed'), Arr::get($this->values, 'isConfirmed'));
        });
        $this->should('have isCanceled attribute', function (): void {
            $this->assertSame($this->shift->get('isCanceled'), Arr::get($this->values, 'isCanceled'));
        });
        $this->should('have reason attribute', function (): void {
            $this->assertSame($this->shift->get('reason'), Arr::get($this->values, 'reason'));
        });
        $this->should('have createdAt attribute', function (): void {
            $this->assertSame($this->shift->get('createdAt'), Arr::get($this->values, 'createdAt'));
        });
        $this->should('have updatedAt attribute', function (): void {
            $this->assertSame($this->shift->get('updatedAt'), Arr::get($this->values, 'updatedAt'));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->shift);
        });
    }
}
