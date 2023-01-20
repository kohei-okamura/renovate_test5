<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Job;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Job\JobStatus;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\JobRepositoryMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupJobUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Job\EditJobInteractor;

/**
 * EditJobInteractor のテスト.
 */
class EditJobInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupJobUseCaseMixin;
    use MockeryMixin;
    use JobRepositoryMixin;
    use OrganizationRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private EditJobInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (EditJobInteractorTest $self): void {
            $self->lookupJobUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->jobs[0]))
                ->byDefault();
            $self->jobRepository
                ->allows('store')
                ->andReturn($self->examples->jobs[0])
                ->byDefault();
            $self->jobRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();
            $self->interactor = app(EditJobInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('edit the job after transaction begun', function (): void {
            $this->lookupJobUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->jobs[0]->id)
                ->andReturn(Seq::from($this->examples->jobs[0]));
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に編集処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に編集処理が行われないことの検証は（恐らく）できない
                    $this->jobRepository
                        ->expects('store')
                        ->andReturn($this->examples->jobs[0]);
                    return $callback();
                });
            $this->interactor->handle($this->context, $this->examples->jobs[0]->id, $this->editJobValue());
        });
        $this->should('return the Job', function (): void {
            $this->assertModelStrictEquals(
                $this->examples->jobs[0],
                $this->interactor->handle($this->context, $this->examples->offices[0]->id, $this->editJobValue())
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('ジョブが更新されました', ['id' => $this->examples->jobs[0]->id, 'status' => $this->examples->jobs[0]->status->value()] + $context);

            $this->interactor->handle($this->context, $this->examples->jobs[0]->id, $this->editJobValue());
        });
        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupJobUseCase
                ->expects('handle')
                ->with($this->context, self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());
            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, self::NOT_EXISTING_ID, $this->editJobValue());
                }
            );
        });
    }

    /**
     * 編集情報を取得する.
     *
     * @return array
     */
    public function editJobValue(): array
    {
        return [
            'status' => JobStatus::success(),
            'data' => [
                'foo' => 'hoge',
                'bar' => 'fuga',
            ],
        ];
    }
}
