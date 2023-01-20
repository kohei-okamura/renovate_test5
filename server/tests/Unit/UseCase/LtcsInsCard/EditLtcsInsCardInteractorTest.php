<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\LtcsInsCard;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\LtcsInsCard\LtcsInsCardServiceType;
use Domain\LtcsInsCard\LtcsInsCardStatus;
use Domain\LtcsInsCard\LtcsLevel;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupLtcsInsCardUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\LtcsInsCardRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\LtcsInsCard\EditLtcsInsCardInteractor;

/**
 * EditLtcsInsCardInteractor のテスト.
 */
final class EditLtcsInsCardInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupLtcsInsCardUseCaseMixin;
    use LookupUserUseCaseMixin;
    use LtcsInsCardRepositoryMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private EditLtcsInsCardInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (EditLtcsInsCardInteractorTest $self): void {
            $self->lookupLtcsInsCardUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ltcsInsCards[0]))
                ->byDefault();
            $self->ltcsInsCardRepository
                ->allows('store')
                ->andReturn($self->examples->ltcsInsCards[0])
                ->byDefault();
            $self->ltcsInsCardRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();
            $self->interactor = app(EditLtcsInsCardInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('介護保険被保険者証が更新されました', ['id' => $this->examples->ltcsInsCards[0]->id] + $context);

            $this->interactor->handle(
                $this->context,
                $this->examples->users[3]->id,
                $this->examples->ltcsInsCards[0]->id,
                $this->payload()
            );
        });
        $this->should('throw a NotFoundException when the LtcsInsCardId not exists in db', function (): void {
            $this->lookupLtcsInsCardUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateLtcsInsCards(), $this->examples->users[0]->id, $this->examples->ltcsInsCards[0]->id)
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->examples->users[0]->id,
                        $this->examples->ltcsInsCards[0]->id,
                        $this->payload()
                    );
                }
            );
        });
        $this->should('edit the LtcsInsCard after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に編集処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に編集処理が行われないことの検証は（恐らく）できない
                    $this->ltcsInsCardRepository
                        ->expects('store')
                        ->andReturn($this->examples->ltcsInsCards[0]);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->examples->users[3]->id,
                $this->examples->ltcsInsCards[0]->id,
                $this->payload()
            );
        });
        $this->should('return the LtcsInsCard', function (): void {
            $this->assertModelStrictEquals(
                $this->examples->ltcsInsCards[0],
                $this->interactor->handle(
                    $this->context,
                    $this->examples->users[3]->id,
                    $this->examples->ltcsInsCards[0]->id,
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
        return [
            'userId' => $this->examples->users[3]->id,
            'ltcsInsCardStatus' => LtcsInsCardStatus::approved()->value(),
            'insNumber' => '0123456789',
            'insurerNumber' => '012345',
            'insurerName' => '新垣栄作',
            'ltcsLevel' => LtcsLevel::careLevel1()->value(),
            'ltcsInsCardServiceType' => LtcsInsCardServiceType::serviceType3()->value(),
            'maxBenefitQuota' => 98765432100,
            'copayRate' => 12345006789,
            'issuedOn' => '2015-01-01',
            'effectivatedOn' => '2016-01-01',
            'certificatedOn' => '2016-12-31',
            'activatedOn' => '2017-06-30',
            'deactivatedOn' => '2018-10-15',
            'copayActivatedOn' => '2018-12-31',
            'copayDeactivatedOn' => '2019-01-01',
        ];
    }
}
