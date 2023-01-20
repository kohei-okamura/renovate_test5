<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Common\Carbon;
use Domain\Office\VisitingCareForPwsdCalcSpec;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupVisitingCareForPwsdCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Mixins\VisitingCareForPwsdCalcSpecRepositoryMixin;
use Tests\Unit\Test;
use UseCase\Office\EditVisitingCareForPwsdCalcSpecInteractor;

/**
 * EditVisitingCareForPwsdCalcSpecInteractor のテスト.
 */
class EditVisitingCareForPwsdCalcSpecInteractorTest extends Test
{
    use ContextMixin;
    use CarbonMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupVisitingCareForPwsdCalcSpecUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;
    use VisitingCareForPwsdCalcSpecRepositoryMixin;

    private EditVisitingCareForPwsdCalcSpecInteractor $interactor;
    private VisitingCareForPwsdCalcSpec $visitingCareForPwsdCalcSpec;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (EditVisitingCareForPwsdCalcSpecInteractorTest $self): void {
            $self->visitingCareForPwsdCalcSpec = $self->examples->visitingCareForPwsdCalcSpecs[0];

            $self->lookupVisitingCareForPwsdCalcSpecUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->visitingCareForPwsdCalcSpec))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->visitingCareForPwsdCalcSpecRepository
                ->allows('store')
                ->andReturn($self->visitingCareForPwsdCalcSpec)
                ->byDefault();
            $self->visitingCareForPwsdCalcSpecRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(EditVisitingCareForPwsdCalcSpecInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('throw a NotFoundException when the VisitingCareForPwsdCalcSpec not exists in db', function (): void {
            $this->lookupVisitingCareForPwsdCalcSpecUseCase
                ->expects('handle')
                ->with($this->context, [Permission::updateInternalOffices()], $this->visitingCareForPwsdCalcSpec->officeId, self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->visitingCareForPwsdCalcSpec->officeId,
                        self::NOT_EXISTING_ID,
                        $this->payload()
                    );
                }
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('事業所算定情報（障害・重度訪問介護）が更新されました', ['id' => $this->visitingCareForPwsdCalcSpec->id] + $context);

            $this->interactor->handle(
                $this->context,
                $this->visitingCareForPwsdCalcSpec->officeId,
                $this->visitingCareForPwsdCalcSpec->id,
                $this->payload()
            );
        });
        $this->should('edit the VisitingCareForPwsdCalcSpec after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に編集処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に編集処理が行われないことの検証は（恐らく）できない
                    $entity = Seq::from($this->visitingCareForPwsdCalcSpec)
                        ->headOption()->getOrElse(fn () => 'error');
                    $this->visitingCareForPwsdCalcSpecRepository
                        ->expects('store')
                        ->with(
                            equalTo($entity->copy(
                                $this->payload() +
                                [
                                    'version' => $entity->version + 1,
                                    'updatedAt' => Carbon::now(),
                                ]
                            ))
                        )
                        ->andReturn($this->visitingCareForPwsdCalcSpec);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->visitingCareForPwsdCalcSpec->officeId,
                $this->visitingCareForPwsdCalcSpec->id,
                $this->payload()
            );
        });
        $this->should('return the VisitingCareForPwsdCalcSpec', function (): void {
            $this->assertModelStrictEquals(
                $this->visitingCareForPwsdCalcSpec,
                $this->interactor->handle(
                    $this->context,
                    $this->visitingCareForPwsdCalcSpec->officeId,
                    $this->visitingCareForPwsdCalcSpec->id,
                    $this->payload()
                )
            );
        });
    }

    /**
     * payload が返す配列.
     *
     * @return array
     */
    private function payload(): array
    {
        return Json::decode(Json::encode($this->visitingCareForPwsdCalcSpec), true);
    }
}
