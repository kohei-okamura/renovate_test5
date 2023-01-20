<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Jobs;

use App\Jobs\ImportWithdrawalTransactionFileJob;
use Domain\Job\Job as DomainJob;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunImportWithdrawalTransactionFileJobUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Jobs\ImportWithdrawalTransactionFileJob} のテスト.
 */
final class ImportWithdrawalTransactionFileJobTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use RunImportWithdrawalTransactionFileJobUseCaseMixin;
    use UnitSupport;

    private const FILE_NAME = 'filename';

    private DomainJob $domainJob;
    private ImportWithdrawalTransactionFileJob $job;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->domainJob = $self->examples->jobs[0];
            $self->job = new ImportWithdrawalTransactionFileJob($self->context, self::FILE_NAME, $self->domainJob);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call RunImportWithdrawalTransactionFileJobUseCase', function (): void {
            $this->runImportWithdrawalTransactionFileJobUseCase
                ->expects('handle')
                ->with($this->context, self::FILE_NAME, $this->domainJob)
                ->andReturnNull();

            $this->job->handle($this->runImportWithdrawalTransactionFileJobUseCase);
        });
    }
}
