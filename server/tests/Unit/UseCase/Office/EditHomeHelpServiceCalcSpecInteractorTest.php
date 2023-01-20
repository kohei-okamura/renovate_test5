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
use Domain\Office\HomeHelpServiceCalcSpec;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\HomeHelpServiceCalcSpecRepositoryMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupHomeHelpServiceCalcSpecUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Office\EditHomeHelpServiceCalcSpecInteractor;

/**
 * EditHomeHelpServiceCalcSpecInteractor のテスト.
 */
class EditHomeHelpServiceCalcSpecInteractorTest extends Test
{
    use ContextMixin;
    use CarbonMixin;
    use ExamplesConsumer;
    use HomeHelpServiceCalcSpecRepositoryMixin;
    use LoggerMixin;
    use LookupHomeHelpServiceCalcSpecUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private EditHomeHelpServiceCalcSpecInteractor $interactor;
    private HomeHelpServiceCalcSpec $homeHelpServiceCalcSpec;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (EditHomeHelpServiceCalcSpecInteractorTest $self): void {
            $self->homeHelpServiceCalcSpec = $self->examples->homeHelpServiceCalcSpecs[0];

            $self->lookupHomeHelpServiceCalcSpecUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->homeHelpServiceCalcSpec))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->homeHelpServiceCalcSpecRepository
                ->allows('store')
                ->andReturn($self->homeHelpServiceCalcSpec)
                ->byDefault();
            $self->homeHelpServiceCalcSpecRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(EditHomeHelpServiceCalcSpecInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('throw a NotFoundException when the HomeHelpServiceCalcSpec not exists in db', function (): void {
            $this->lookupHomeHelpServiceCalcSpecUseCase
                ->expects('handle')
                ->with($this->context, [Permission::updateInternalOffices()], $this->homeHelpServiceCalcSpec->officeId, self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->examples->offices[0]->id,
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
                ->with('事業所算定情報（障害・居宅介護）が更新されました', ['id' => $this->homeHelpServiceCalcSpec->id] + $context);

            $this->interactor->handle(
                $this->context,
                $this->homeHelpServiceCalcSpec->officeId,
                $this->homeHelpServiceCalcSpec->id,
                $this->payload()
            );
        });
        $this->should('edit the HomeHelpServiceCalcSpec after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に編集処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に編集処理が行われないことの検証は（恐らく）できない
                    $entity = Seq::from($this->homeHelpServiceCalcSpec)
                        ->headOption()->getOrElse(fn () => 'error');
                    $this->homeHelpServiceCalcSpecRepository
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
                        ->andReturn($this->homeHelpServiceCalcSpec);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->homeHelpServiceCalcSpec->officeId,
                $this->homeHelpServiceCalcSpec->id,
                $this->payload()
            );
        });
        $this->should('return the HomeHelpServiceCalcSpec', function (): void {
            $this->assertModelStrictEquals(
                $this->homeHelpServiceCalcSpec,
                $this->interactor->handle(
                    $this->context,
                    $this->homeHelpServiceCalcSpec->officeId,
                    $this->homeHelpServiceCalcSpec->id,
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
        return Json::decode(Json::encode($this->homeHelpServiceCalcSpec), true);
    }
}
