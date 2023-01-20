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
use Domain\Project\DwsProject;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsProjectRepositoryMixin;
use Tests\Unit\Mixins\EditDwsProjectUseCaseMixin;
use Tests\Unit\Mixins\EnsureUserUseCaseMixin;
use Tests\Unit\Mixins\IdentifyContractUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Project\CreateDwsProjectInteractor;

/**
 * CreateDwsProjectInteractor のテスト.
 */
class CreateDwsProjectInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use EnsureUserUseCaseMixin;
    use EditDwsProjectUseCaseMixin;
    use ExamplesConsumer;
    use IdentifyContractUseCaseMixin;
    use LoggerMixin;
    use MockeryMixin;
    use DwsProjectRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private CreateDwsProjectInteractor $interactor;
    private DwsProject $dwsProject;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateDwsProjectInteractorTest $self): void {
            $self->dwsProject = $self->examples->dwsProjects[0];
            $self->dwsProjectRepository
                ->allows('store')
                ->andReturn($self->dwsProject)
                ->byDefault();
            $self->ensureUserUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->identifyContractUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->contracts[0]))
                ->byDefault();
            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(CreateDwsProjectInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use EnsureUserUseCase after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->ensureUserUseCase
                        ->expects('handle')
                        ->with($this->context, Permission::createDwsProjects(), $this->examples->users[0]->id)
                        ->andReturnNull();
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->examples->users[0]->id, $this->dwsProject);
        });
        $this->should('store the DwsProject after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->dwsProjectRepository
                        ->expects('store')
                        ->with(equalTo($this->dwsProject->copy([
                            'organizationId' => $this->context->organization->id,
                            'contractId' => $this->examples->contracts[0]->id,
                            'userId' => $this->examples->users[0]->id,
                            'version' => 1,
                            'createdAt' => Carbon::now(),
                            'updatedAt' => Carbon::now(),
                        ])))
                        ->andReturn($this->dwsProject);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->examples->users[0]->id, $this->dwsProject);
        });
        $this->should('use IdentifyContractUseCase after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->identifyContractUseCase
                        ->expects('handle')
                        ->with(
                            $this->context,
                            Permission::createDwsProjects(),
                            $this->dwsProject->officeId,
                            $this->dwsProject->userId,
                            ServiceSegment::disabilitiesWelfare(),
                            equalTo(Carbon::now())
                        )
                        ->andReturn(Option::from($this->examples->contracts[0]));
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->dwsProject->userId, $this->dwsProject);
        });
        $this->should('throw NotFoundException when IdentifyContractUseCase return none', function (): void {
            $this->identifyContractUseCase
                ->expects('handle')
                ->andReturn(Option::none());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle($this->context, $this->dwsProject->userId, $this->dwsProject);
            });
        });
        $this->should('return the DwsProject', function (): void {
            $this->assertModelStrictEquals(
                $this->dwsProject,
                $this->interactor->handle($this->context, $this->examples->users[0]->id, $this->dwsProject)
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('障害福祉サービス：計画が登録されました', ['id' => $this->dwsProject->id] + $context);

            $this->interactor->handle($this->context, $this->examples->users[0]->id, $this->dwsProject);
        });
    }
}
