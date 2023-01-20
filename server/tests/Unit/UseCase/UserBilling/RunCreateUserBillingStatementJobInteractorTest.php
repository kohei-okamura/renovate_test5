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
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GenerateUserBillingStatementPdfUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunJobUseCaseMixin;
use Tests\Unit\Test;
use UseCase\UserBilling\RunCreateUserBillingStatementJobInteractor;

/**
 * RunCreateUserBillingStatementJobInteractor のテスト.
 */
final class RunCreateUserBillingStatementJobInteractorTest extends Test
{
    use ConfigMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use GenerateUserBillingStatementPdfUseCaseMixin;
    use MockeryMixin;
    use RunJobUseCaseMixin;
    use UnitSupport;

    private const PATH = 'dummies/download/dummy';
    private const FILENAME = 'dummy.pdf';

    private DomainJob $domainJob;
    private array $ids;
    private Carbon $issuedOn;
    private RunCreateUserBillingStatementJobInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (RunCreateUserBillingStatementJobInteractorTest $self): void {
            $self->ids = [
                $self->examples->userBillings[0]->id,
                $self->examples->userBillings[1]->id,
            ];
            $self->domainJob = $self->examples->jobs[0];
            $self->issuedOn = Carbon::parse('2021-11-10');

            $self->runJobUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->generateUserBillingStatementPdfUseCase
                ->allows('handle')
                ->andReturn(self::PATH)
                ->byDefault();
            $self->config
                ->allows('filename')
                ->andReturn(self::FILENAME)
                ->byDefault();

            $self->interactor = app(RunCreateUserBillingStatementJobInteractor::class);
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
                ->andReturn(null);

            $this->interactor->handle($this->context, $this->domainJob, $this->ids, $this->issuedOn);
        });
        $this->should('call GenerateUserBillingStatementPdfUseCase', function (): void {
            $uri = $this->context->uri('user-billings/download/' . self::PATH);
            $this->runJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, Mockery::any())
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f) use ($uri): void {
                        $this->generateUserBillingStatementPdfUseCase
                            ->expects('handle')
                            ->with($this->context, $this->ids, $this->issuedOn)
                            ->andReturn(self::PATH);

                        // 正しい値を返すことも検証
                        $res = $f();
                        $this->assertSame(
                            ['uri' => $uri, 'filename' => self::FILENAME],
                            $res
                        );
                    }
                );

            $this->interactor->handle($this->context, $this->domainJob, $this->ids, $this->issuedOn);
        });
    }
}
