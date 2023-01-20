<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Project;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Common\Carbon;
use Domain\Common\ServiceSegment;
use Domain\Permission\Permission;
use Domain\Project\LtcsProject;
use Lib\Exceptions\NotFoundException;
use Lib\Json;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\IdentifyContractUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupLtcsProjectUseCaseMixin;
use Tests\Unit\Mixins\LtcsProjectRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Project\EditLtcsProjectInteractor;

/**
 * {@link \UseCase\Project\EditLtcsProjectInteractor} のテスト.
 */
class EditLtcsProjectInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use LtcsProjectRepositoryMixin;
    use ExamplesConsumer;
    use IdentifyContractUseCaseMixin;
    use LoggerMixin;
    use LookupLtcsProjectUseCaseMixin;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private EditLtcsProjectInteractor $interactor;
    private LtcsProject $ltcsProject;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (EditLtcsProjectInteractorTest $self): void {
            $self->ltcsProject = $self->examples->ltcsProjects[0];
            $self->identifyContractUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->contracts[0]))
                ->byDefault();
            $self->lookupLtcsProjectUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ltcsProjects[0]))
                ->byDefault();
            $self->ltcsProjectRepository
                ->allows('store')
                ->andReturn($self->examples->ltcsProjects[0])
                ->byDefault();
            $self->ltcsProjectRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->organizationRepository
                ->allows('lookupOptionByCode')
                ->andReturn(Seq::from($self->examples->organizations[0]))
                ->byDefault();
            $self->logger
                ->allows('info')
                ->byDefault();
            $self->interactor = app(EditLtcsProjectInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('edit the LtcsProject after transaction begun', function (): void {
            $this->lookupLtcsProjectUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateLtcsProjects(), $this->ltcsProject->userId, $this->ltcsProject->id)
                ->andReturn(Seq::from($this->ltcsProject));
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に編集処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に編集処理が行われないことの検証は（恐らく）できない
                    $this->ltcsProjectRepository
                        ->expects('store')
                        ->with(equalTo($this->ltcsProject->copy($this->payload() + [
                            'contractId' => $this->examples->contracts[0]->id,
                            'userId' => $this->ltcsProject->userId,
                            'version' => $this->ltcsProject->version + 1,
                            'updatedAt' => Carbon::now(),
                        ])))
                        ->andReturn($this->ltcsProject);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->ltcsProject->userId, $this->ltcsProject->id, $this->payload());
        });
        $this->should('use LookupLtcsProjectUseCase after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->lookupLtcsProjectUseCase
                        ->expects('handle')
                        ->with($this->context, Permission::updateLtcsProjects(), $this->ltcsProject->userId, $this->ltcsProject->id)
                        ->andReturn(Seq::from($this->ltcsProject));
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->ltcsProject->userId, $this->ltcsProject->id, $this->payload());
        });
        $this->should('return the LtcsProject', function (): void {
            $this->assertModelStrictEquals(
                $this->ltcsProject,
                $this->interactor->handle($this->context, $this->ltcsProject->userId, $this->ltcsProject->id, $this->payload())
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('介護保険サービス：計画が更新されました', ['id' => $this->ltcsProject->id] + $context);

            $this->interactor->handle($this->context, $this->ltcsProject->userId, $this->ltcsProject->id, $this->payload());
        });
        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupLtcsProjectUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateLtcsProjects(), self::NOT_EXISTING_ID, self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    self::NOT_EXISTING_ID,
                    self::NOT_EXISTING_ID,
                    $this->payload()
                );
            });
        });
        $this->should('use IdentifyContractUseCase after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    $this->identifyContractUseCase
                        ->expects('handle')
                        ->with(
                            $this->context,
                            Permission::updateLtcsProjects(),
                            $this->ltcsProject->officeId,
                            $this->ltcsProject->userId,
                            ServiceSegment::longTermCare(),
                            equalTo(Carbon::now())
                        )
                        ->andReturn(Option::from($this->examples->contracts[0]));
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->ltcsProject->userId,
                $this->ltcsProject->id,
                $this->payload()
            );
        });
        $this->should('throw NotFoundException when IdentifyContractUseCase return none', function (): void {
            $this->identifyContractUseCase
                ->expects('handle')
                ->andReturn(Option::none());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->ltcsProject->userId,
                    $this->ltcsProject->id,
                    $this->payload()
                );
            });
        });
    }

    /**
     * 編集情報を取得する.
     *
     * @return array
     */
    public function payload(): array
    {
        return Json::decode(Json::encode($this->ltcsProject), true);
    }
}
