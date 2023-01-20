<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\User;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Location;
use Domain\Common\Prefecture;
use Domain\Common\Sex;
use Domain\Common\StructuredName;
use Domain\Permission\Permission;
use Domain\User\User;
use Lib\Exceptions\NotFoundException;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\JobsDispatcherMixin;
use Tests\Unit\Mixins\LocationResolverMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Mixins\UserRepositoryMixin;
use Tests\Unit\Test;
use UseCase\User\EditUserInteractor;

/**
 * EditUserInteractor のテスト.
 */
final class EditUserInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use JobsDispatcherMixin;
    use LocationResolverMixin;
    use LookupUserUseCaseMixin;
    use MockeryMixin;
    use TransactionManagerMixin;
    use UnitSupport;
    use UserRepositoryMixin;

    /**
     * @var callable|\Closure|\Mockery\MockInterface
     */
    private $callable;
    private EditUserInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[0]))
                ->byDefault();
            $self->userRepository
                ->allows('store')
                ->andReturn($self->examples->users[0])
                ->byDefault();
            $self->userRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->resolver
                ->allows('resolve')
                ->andReturn(Option::from($self->examples->users[0]->location))
                ->byDefault();
            $self->logger
                ->allows('info')
                ->byDefault();

            $self->callable = Mockery::spy(fn (User $use) => 'RUN CALLBACK');

            $self->interactor = app(EditUserInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('Lookup is called and return target User', function (): void {
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateUsers(), $this->examples->users[0]->id)
                ->andReturn(Seq::from($this->examples->users[0]));

            $actual = $this->interactor->handle(
                $this->context,
                $this->examples->users[0]->id,
                $this->editUserValue(),
                $this->callable
            );

            $this->assertModelStrictEquals($this->examples->users[0], $actual);
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('利用者情報が更新されました', ['id' => $this->examples->users[0]->id] + $context);

            $this->interactor->handle(
                $this->context,
                $this->examples->users[0]->id,
                $this->editUserValue(),
                $this->callable
            );
        });
        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateUsers(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        self::NOT_EXISTING_ID,
                        $this->editUserValue(),
                        $this->callable
                    );
                }
            );
        });
        $this->should('edit the User after transaction begun', function (): void {
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に編集処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に編集処理が行われないことの検証は（恐らく）できない
                    $this->userRepository->expects('store')->andReturn($this->examples->users[0]);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->examples->users[0]->id,
                $this->editUserValue(),
                $this->callable
            );
        });
        $this->should('call callable function', function (): void {
            $this->userRepository
                ->allows('store')
                ->andReturn($this->examples->users[0]->copy($this->editUserValue()));

            $this->interactor->handle(
                $this->context,
                $this->examples->users[0]->id,
                $this->editUserValue(),
                $this->callable
            );

            $this->callable->shouldHaveBeenCalled();
        });
        $this->should('not call callable function when no edit address', function (): void {
            $this->interactor->handle(
                $this->context,
                $this->examples->users[0]->id,
                $this->examples->users[0]->toAssoc(),
                $this->callable
            );
            $this->callable->shouldNotHaveBeenCalled();
        });
    }

    /**
     * 編集情報を取得する.
     *
     * @return array
     */
    public function editUserValue(): array
    {
        return [
            'name' => new StructuredName(
                familyName: '新垣',
                givenName: '栄作',
                phoneticFamilyName: 'シンガキ',
                phoneticGivenName: 'エイサク',
            ),
            'sex' => Sex::male(),
            'birthday' => Carbon::create(1982, 5, 9),
            'addr' => new Addr(
                postcode: '123-4567',
                prefecture: Prefecture::saitama(),
                city: '和光市',
                street: '広沢 XX-XX-XX',
                apartment: 'XXX XXX号室',
            ),
            'location' => Location::create([
                'lat' => 12.345678,
                'lng' => 123.456789,
            ]),
            'tel' => '03-1234-5678',
            'fax' => '03-3333-3333',
            'email' => 'sample1@example.com',
            'isEnabled' => true,
            'version' => 1,
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ];
    }
}
