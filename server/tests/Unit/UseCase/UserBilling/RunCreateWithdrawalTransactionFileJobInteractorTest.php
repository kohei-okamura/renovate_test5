<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\UserBilling;

use Closure;
use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use Mockery;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GenerateWithdrawalTransactionFileUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunCreateWithdrawalTransactionFileJobUseCaseMixin;
use Tests\Unit\Mixins\RunJobUseCaseMixin;
use Tests\Unit\Test;
use UseCase\UserBilling\RunCreateWithdrawalTransactionFileJobInteractor;

/**
 * {@link \UseCase\UserBilling\RunCreateWithdrawalTransactionFileJobInteractor} のテスト.
 */
final class RunCreateWithdrawalTransactionFileJobInteractorTest extends Test
{
    use ConfigMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use GenerateWithdrawalTransactionFileUseCaseMixin;
    use RunCreateWithdrawalTransactionFileJobUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use RunJobUseCaseMixin;
    use UnitSupport;

    private const PATH = 'path';
    private const FILENAME = 'zengin-file';

    private DomainJob $domainJob;
    private RunCreateWithdrawalTransactionFileJobInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->runJobUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->config
                ->allows('filename')
                ->andReturn(self::FILENAME)
                ->byDefault();

            $self->domainJob = $self->examples->jobs[0];
            $self->interactor = app(RunCreateWithdrawalTransactionFileJobInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('cal RunJobUseCase', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, Mockery::any())
                ->andReturn(null);

            $this->interactor->handle($this->context, $this->domainJob, $this->examples->withdrawalTransactions[0]->id);
        });
        $this->should('call GenerateShiftTemplateUseCase', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, Mockery::any())
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f): void {
                        $this->generateWithdrawalTransactionFileUseCase
                            ->expects('handle')
                            ->with(
                                $this->context,
                                $this->examples->withdrawalTransactions[0]->id
                            )
                            ->andReturn(self::PATH);

                        // 正しい値を返すことも検証
                        $this->assertSame(
                            [
                                'uri' => $this->context->uri('user-billings/download/' . self::PATH),
                                'filename' => self::FILENAME,
                            ],
                            $f()
                        );
                    }
                );

            $this->interactor->handle($this->context, $this->domainJob, $this->examples->withdrawalTransactions[0]->id);
        });
    }
}
