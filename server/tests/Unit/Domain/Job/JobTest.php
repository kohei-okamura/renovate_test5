<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Job;

use Domain\Common\Carbon;
use Domain\Job\Job;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * Job のテスト
 */
class JobTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use OrganizationExample;
    use UnitSupport;

    protected Job $job;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (JobTest $self): void {
            $self->values = [
                'id' => 1,
                'organizationId' => $self->examples->organizations[0]->id,
                'staffId' => $self->examples->staffs[0]->id,
                'data' => ['test1' => 'data1', 'test2' => 'data2', 'test3' => 'data3', 'test4' => 'data4'],
                'status' => 2,
                'token' => $self->examples->jobs[0]->token,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $self->job = Job::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have id attribute', function (): void {
            $this->assertSame($this->job->get('id'), Arr::get($this->values, 'id'));
        });
        $this->should('have organizationId attribute', function (): void {
            $this->assertSame($this->job->get('organizationId'), Arr::get($this->values, 'organizationId'));
        });
        $this->should('have staffId attribute', function (): void {
            $this->assertSame($this->job->get('staffId'), Arr::get($this->values, 'staffId'));
        });
        $this->should('have data attribute', function (): void {
            $this->assertSame($this->job->get('data'), Arr::get($this->values, 'data'));
        });
        $this->should('have status attribute', function (): void {
            $this->assertSame($this->job->get('status'), Arr::get($this->values, 'status'));
        });
        $this->should('have token attribute', function (): void {
            $this->assertSame($this->job->get('token'), Arr::get($this->values, 'token'));
        });
        $this->should('have createdAt attribute', function (): void {
            $this->assertSame($this->job->get('createdAt'), Arr::get($this->values, 'createdAt'));
        });
        $this->should('have updatedAt attribute', function (): void {
            $this->assertSame($this->job->get('updatedAt'), Arr::get($this->values, 'updatedAt'));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->job);
        });
    }
}
