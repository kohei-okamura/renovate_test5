<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Project;

use Closure;
use Domain\Common\Carbon;
use Domain\Common\ServiceSegment;
use Domain\Permission\Permission;
use Domain\Project\LtcsProject;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EnsureUserUseCaseMixin;
use Tests\Unit\Mixins\IdentifyContractUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LtcsProjectRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Project\CreateLtcsProjectInteractor;

/**
 * CreateLtcsProjectInteractor のテスト.
 */
class CreateLtcsProjectInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use EnsureUserUseCaseMixin;
    use IdentifyContractUseCaseMixin;
    use LoggerMixin;
    use LtcsProjectRepositoryMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private CreateLtcsProjectInteractor $interactor;
    private LtcsProject $ltcsProject;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateLtcsProjectInteractorTest $self): void {
            $self->ltcsProject = $self->examples->ltcsProjects[0];
            $self->ensureUserUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->identifyContractUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->contracts[0]))
                ->byDefault();
            $self->ltcsProjectRepository
                ->allows('store')
                ->andReturn($self->ltcsProject)
                ->byDefault();
            $self->logger
                ->allows('info')
                ->andReturnNull()
                ->byDefault();

            $self->interactor = app(CreateLtcsProjectInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return the LtcsProject', function (): void {
            $this->assertModelStrictEquals(
                $this->ltcsProject,
                $this->interactor->handle($this->context, $this->examples->users[0]->id, $this->ltcsProject)
            );
        });
        $this->should('use EnsureUserUseCase after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->ensureUserUseCase
                        ->expects('handle')
                        ->with($this->context, Permission::createLtcsProjects(), $this->examples->users[0]->id)
                        ->andReturnNull();
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->ltcsProject->userId, $this->ltcsProject);
        });
        $this->should('use IdentifyContractUseCase after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->identifyContractUseCase
                        ->expects('handle')
                        ->with(
                            $this->context,
                            Permission::createLtcsProjects(),
                            $this->ltcsProject->officeId,
                            $this->ltcsProject->userId,
                            ServiceSegment::longTermCare(),
                            equalTo(Carbon::now())
                        )
                        ->andReturn(Option::from($this->examples->contracts[0]));
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->ltcsProject->userId, $this->ltcsProject);
        });
        $this->should('throw NotFoundException when IdentifyContractUseCase return none', function (): void {
            $this->identifyContractUseCase
                ->expects('handle')
                ->andReturn(Option::none());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle($this->context, $this->ltcsProject->userId, $this->ltcsProject);
            });
        });
        $this->should('throw NotFoundException when IdentifyContractUseCase return none', function (): void {
            $this->identifyContractUseCase
                ->expects('handle')
                ->andReturn(Option::none());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle($this->context, $this->ltcsProject->userId, $this->ltcsProject);
            });
        });
        $this->should('store the LtcsProject after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->ltcsProjectRepository
                        ->expects('store')
                        ->with(equalTo($this->ltcsProject->copy([
                            'organizationId' => $this->context->organization->id,
                            'contractId' => $this->examples->contracts[0]->id,
                            'userId' => $this->ltcsProject->userId,
                            'version' => 1,
                            'createdAt' => Carbon::now(),
                            'updatedAt' => Carbon::now(),
                        ])))
                        ->andReturn($this->ltcsProject);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->ltcsProject->userId, $this->ltcsProject);
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('介護保険サービス：計画が登録されました', ['id' => $this->ltcsProject->id] + $context);

            $this->interactor->handle($this->context, $this->ltcsProject->userId, $this->ltcsProject);
        });
    }
}
