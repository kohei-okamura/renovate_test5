<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\UserBilling;

use Closure;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use Mockery;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GenerateUserBillingNoticePdfUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunJobUseCaseMixin;
use Tests\Unit\Test;
use UseCase\UserBilling\RunCreateUserBillingNoticeJobInteractor;

/**
 * {@link \UseCase\UserBilling\RunCreateUserBillingNoticeJobInteractor} Test.
 */
class RunCreateUserBillingNoticeJobInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use LoggerMixin;
    use RunJobUseCaseMixin;
    use UnitSupport;
    use GenerateUserBillingNoticePdfUseCaseMixin;

    private const PATH = 'path';
    private const FILENAME = 'example.pdf';

    private DomainJob $domainJob;
    private array $ids;
    private Carbon $issuedOn;
    private RunCreateUserBillingNoticeJobInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (RunCreateUserBillingNoticeJobInteractorTest $self): void {
            $self->ids = [
                $self->examples->userBillings[0]->id,
                $self->examples->userBillings[1]->id,
            ];
            $self->issuedOn = Carbon::parse('2021-11-10');
            $self->domainJob = $self->examples->jobs[0];

            $self->runJobUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->generateUserBillingNoticePdfUseCase
                ->allows('handle')
                ->andReturn(self::PATH)
                ->byDefault();
            $self->logger
                ->allows('info')
                ->byDefault();
            $self->config
                ->allows('filename')
                ->andReturn(self::FILENAME)
                ->byDefault();

            $self->interactor = app(RunCreateUserBillingNoticeJobInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call RunJobUseCase', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, Mockery::any())
                ->andReturnNull();

            $this->interactor->handle($this->context, $this->domainJob, $this->ids, $this->issuedOn);
        });
        $this->should('call GenerateUserBillingNoticePdfUseCase', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, Mockery::any())
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f): void {
                        $this->generateUserBillingNoticePdfUseCase
                            ->expects('handle')
                            ->with($this->context, $this->ids, $this->issuedOn)
                            ->andReturn(self::PATH);
                        $this->assertSame(
                            [
                                'uri' => $context->uri('user-billings/download/' . self::PATH),
                                'filename' => self::FILENAME,
                            ],
                            $f()
                        );
                    }
                );

            $this->interactor->handle($this->context, $this->domainJob, $this->ids, $this->issuedOn);
        });
        $this->should('log using info', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, Mockery::any())
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f): void {
                        $context = ['organizationId' => $this->examples->organizations[0]->id];
                        $this->context
                            ->expects('logContext')
                            ->andReturn($context);
                        $this->logger
                            ->expects('info')
                            ->with('利用者請求：代理受領額通知書生成ジョブ終了', ['filename' => self::FILENAME] + $context);
                        $f();
                    }
                );

            $this->interactor->handle($this->context, $this->domainJob, $this->ids, $this->issuedOn);
        });
    }
}
