<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Job;

use Domain\Job\Job;
use Domain\Staff\Staff;
use Lib\Exceptions\UnauthorizedException;
use ScalikePHP\Option;
use Tests\Unit\App\Http\Concretes\TestingContext;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\JobRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Job\LookupJobByTokenInteractor;

/**
 * LookupJobByTokenInteractor のテスト.
 */
class LookupJobByTokenInteractorTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use JobRepositoryMixin;
    use MockeryMixin;
    use UnitSupport;

    private LookupJobByTokenInteractor $interactor;
    private Job $job;
    private Staff $staff;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupJobByTokenInteractorTest $self): void {
            $self->job = $self->examples->jobs[0];
            $self->jobRepository->allows('lookupOptionByToken')->andReturn(Option::from($self->job))->byDefault();
            $self->interactor = app(LookupJobByTokenInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use JobRepository', function (): void {
            $this->jobRepository
                ->expects('lookupOptionByToken')
                ->with($this->job->token)
                ->andReturn(Option::from($this->job));

            $actual = $this->interactor->handle($this->context, $this->job->token);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($this->job, $actual->head());
        });
        $this->should('throw UnAuthorizeException when Staff is not exists', function (): void {
            $context = new TestingContext();
            TestingContext::prepare($context, $this->examples->organizations[0], Option::none());

            $this->assertThrows(
                UnauthorizedException::class,
                function () use ($context): void {
                    $this->interactor->handle($context, $this->job->token);
                }
            );
        });
        $this->should('return empty when staff is different', function (): void {
            $this->jobRepository
                ->expects('lookupOptionByToken')
                ->with($this->examples->jobs[1]->token)
                ->andReturn(Option::from($this->examples->jobs[1]));

            $actual = $this->interactor->handle($this->context, $this->examples->jobs[1]->token);
            $this->assertCount(0, $actual);
        });
    }
}
