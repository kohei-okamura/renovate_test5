<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Calling;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Calling\CallingResponse;
use Domain\Common\Carbon;
use Lib\Exceptions\NotFoundException;
use Lib\Exceptions\TokenExpiredException;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CallingResponseRepositoryMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupCallingByTokenUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Calling\AcknowledgeStaffAttendanceInteractor;

/**
 * AcknowledgeStaffAttendanceInteractor のテスト.
 */
final class AcknowledgeStaffAttendanceInteractorTest extends Test
{
    use CallingResponseRepositoryMixin;
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupCallingByTokenUseCaseMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    public const TOKEN = '1234567890abcdefghij1234567890ABCDEFGHIJ12345678901234567890';

    private AcknowledgeStaffAttendanceInteractor $interactor;
    private array $shifts;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (AcknowledgeStaffAttendanceInteractorTest $self): void {
            $self->lookupCallingByTokenUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->callings[0]))
                ->byDefault();
            $self->callingResponseRepository
                ->allows('store')
                ->andReturn($self->examples->callingResponses[0])
                ->byDefault();
            $self->callingResponseRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(AcknowledgeStaffAttendanceInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('run handle succeed normally', function (): void {
            $this->interactor->handle($this->context, self::TOKEN);
        });
        $this->should('store the Calling after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に登録処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に登録処理が行われないことの検証は（恐らく）できない
                    $this->callingResponseRepository
                        ->expects('store')
                        ->withArgs(function (CallingResponse $x) {
                            $this->assertEquals($this->examples->callings[0]->id, $x->callingId);
                            $this->assertEquals(Carbon::now(), $x->createdAt);
                            return true;
                        })->andReturn($this->examples->callingResponses[0]);
                    return $callback();
                });

            $this->interactor->handle($this->context, self::TOKEN);
        });
        $this->should('use UseCase lookup entity', function (): void {
            $this->lookupCallingByTokenUseCase
                ->expects('handle')
                ->with($this->context, self::TOKEN)
                ->andReturn(Option::from($this->examples->callings[0]));

            $this->interactor->handle($this->context, self::TOKEN);
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('出勤確認応答を登録しました', ['id' => $this->examples->callingResponses[0]->id] + $context);

            $this->interactor->handle($this->context, self::TOKEN);
        });
        $this->should('throw a NotFoundException when lookupCallingByTokenUseCase return empty', function (): void {
            $this->lookupCallingByTokenUseCase
                ->expects('handle')
                ->with($this->context, self::TOKEN)
                ->andReturn(Option::none());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, self::TOKEN);
                }
            );
        });
        $this->should('throw ForbiddenException when Token expired', function (): void {
            $this->lookupCallingByTokenUseCase
                ->expects('handle')
                ->with($this->context, self::TOKEN)
                ->andReturn(Option::from($this->examples->callings[2]->copy(['expiredAt' => Carbon::now()->subMinute()])));

            $this->assertThrows(
                TokenExpiredException::class,
                function (): void {
                    $this->interactor->handle($this->context, self::TOKEN);
                    $this->assertTrue(false, 'Non Throw');
                }
            );
        });
    }
}
