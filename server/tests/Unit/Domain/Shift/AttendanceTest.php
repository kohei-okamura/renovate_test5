<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Shift;

use Domain\Common\Carbon;
use Domain\Shift\Assignee;
use Domain\Shift\Attendance;
use Domain\Shift\Duration;
use Domain\Shift\ServiceOption;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Shift\Attendance} Test.
 */
class AttendanceTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use UnitSupport;

    protected Attendance $domain;
    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (AttendanceTest $self): void {
            $self->values = [
                'id' => $self->examples->attendances[0]->id,
                'contractId' => $self->examples->attendances[0]->contractId,
                'officeId' => $self->examples->attendances[0]->officeId,
                'userId' => $self->examples->attendances[0]->userId,
                'assignerId' => $self->examples->attendances[0]->assignerId,
                'task' => $self->examples->attendances[0]->task,
                'serviceCode' => $self->examples->attendances[0]->serviceCode,
                'headcount' => $self->examples->attendances[0]->headcount,
                'assignees' => [Assignee::create()],
                'schedule' => $self->examples->attendances[0]->schedule,
                'durations' => [Duration::create()],
                'options' => [ServiceOption::firstTime()],
                'note' => 'Note',
                'isConfirmed' => $self->examples->attendances[0]->isConfirmed,
                'reason' => 'キャンセル理由',
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $self->domain = Attendance::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have specified attribute', function (string $key, $expected = null): void {
            $this->assertEquals($this->domain->copy([
                'organizationId' => $this->examples->attendances[0]->organizationId,
            ])->get($key), $expected ?? Arr::get($this->values, $key));
        }, [
            'examples' => [
                'organizationId' => ['organizationId', $this->examples->attendances[0]->organizationId],
                'id' => ['id'],
                'contractId' => ['contractId'],
                'officeId' => ['officeId'],
                'userId' => ['userId'],
                'assignerId' => ['assignerId'],
                'task' => ['task'],
                'serviceCode' => ['serviceCode'],
                'headcount' => ['headcount'],
                'assignees' => ['assignees'],
                'schedule' => ['schedule'],
                'durations' => ['durations'],
                'options' => ['options'],
                'note' => ['note'],
                'isConfirmed' => ['isConfirmed'],
                'reason' => ['reason'],
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
        $this->should('changes to JSON encode attendances', function (): void {
            $this->assertMatchesJsonSnapshot($this->domain);
        });
    }
}
