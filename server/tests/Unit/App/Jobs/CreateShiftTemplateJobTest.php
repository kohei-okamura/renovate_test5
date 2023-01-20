<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\CreateShiftTemplateJob;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Job\Job;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunCreateShiftTemplateJobUseCaseMixin;
use Tests\Unit\Test;

/**
 * CreateShiftTemplateJob のテスト.
 */
final class CreateShiftTemplateJobTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use RunCreateShiftTemplateJobUseCaseMixin;
    use UnitSupport;

    private Job $domainJob;
    private CreateShiftTemplateJob $job;
    private array $parameters;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateShiftTemplateJobTest $self): void {
            $self->parameters = [
                'officeId' => $self->examples->offices[0]->id,
                'isCopy' => true,
                'source' => CarbonRange::create([
                    'start' => Carbon::now()->subWeeks(1),
                    'end' => Carbon::now()->subWeeks(1)->addDays(6),
                ]),
                'range' => CarbonRange::create([
                    'start' => Carbon::now(),
                    'end' => Carbon::now()->addDays(6),
                ]),
            ];
            $self->domainJob = $self->examples->jobs[0];
            $self->job = new CreateShiftTemplateJob($self->context, $self->domainJob, $self->parameters);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call RunCreateShiftTemplateJobUseCase', function (): void {
            $this->runCreateShiftTemplateJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, $this->parameters)
                ->andReturnNull();

            $this->job->handle($this->runCreateShiftTemplateJobUseCase);
        });
    }
}
