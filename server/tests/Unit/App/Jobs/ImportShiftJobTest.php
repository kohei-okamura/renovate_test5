<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\ImportShiftJob;
use Domain\Job\Job as DomainJob;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunImportShiftJobUseCaseMixin;
use Tests\Unit\Test;

/**
 * ImportShiftJob のテスト.
 */
final class ImportShiftJobTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use RunImportShiftJobUseCaseMixin;
    use UnitSupport;

    private const FILE_NAME = 'filename';

    private DomainJob $domainJob;
    private ImportShiftJob $job;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ImportShiftJobTest $self): void {
            $self->domainJob = $self->examples->jobs[0];

            $self->job = new ImportShiftJob($self->context, self::FILE_NAME, $self->domainJob);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call RunImportShiftJobUseCase', function (): void {
            $this->runImportShiftJobUseCase
                ->expects('handle')
                ->with($this->context, self::FILE_NAME, $this->domainJob)
                ->andReturnNull();

            $this->job->handle($this->runImportShiftJobUseCase);
        });
    }
}
