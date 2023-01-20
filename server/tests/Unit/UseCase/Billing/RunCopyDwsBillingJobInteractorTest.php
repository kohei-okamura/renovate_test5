<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Job\Job;
use Mockery;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\CopyDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunJobUseCaseMixin;
use Tests\Unit\Test;
use UseCase\Billing\RunCopyDwsBillingJobInteractor;

/**
 * {@link \UseCase\Billing\RunCopyDwsBillingJobInteractor} のテスト.
 */
final class RunCopyDwsBillingJobInteractorTest extends Test
{
    use ContextMixin;
    use CopyDwsBillingUseCaseMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use RunJobUseCaseMixin;
    use UnitSupport;

    private Job $domainJob;
    private int $id;
    private RunCopyDwsBillingJobInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->domainJob = $self->examples->jobs[0];
            $self->id = $self->examples->dwsBillings[0]->id;
            $self->runJobUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->copyDwsBillingUseCase
                ->allows('handle')
                ->andReturn($self->examples->dwsBillings[0])
                ->byDefault();

            $self->interactor = app(RunCopyDwsBillingJobInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use RunJobUseCase', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, Mockery::capture($f))
                ->andReturnNull();

            $this->interactor->handle(
                $this->context,
                $this->domainJob,
                $this->id,
            );
            $this->assertSame(['billing' => $this->examples->dwsBillings[0]->toAssoc()], $f());
        });
    }
}
