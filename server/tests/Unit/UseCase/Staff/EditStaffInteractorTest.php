<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Staff;

use App\Concretes\PermanentDatabaseTransactionManager;
use Closure;
use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Location;
use Domain\Common\Password;
use Domain\Common\Prefecture;
use Domain\Common\Sex;
use Domain\Common\StructuredName;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GetStaffInfoUseCaseMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupStaffUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\StaffRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Staff\EditStaffInteractor;

/**
 * EditStaffInteractor のテスト.
 */
final class EditStaffInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use GetStaffInfoUseCaseMixin;
    use LoggerMixin;
    use LookupStaffUseCaseMixin;
    use MockeryMixin;
    use StaffRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    private EditStaffInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->lookupStaffUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->staffs[0]))
                ->byDefault();
            $self->staffRepository
                ->allows('store')
                ->andReturn($self->examples->staffs[0])
                ->byDefault();
            $self->logger
                ->allows('info')
                ->byDefault();
            $self->staffRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->getStaffInfoUseCase
                ->allows('handle')
                ->andReturn([])
                ->byDefault();
            $self->interactor = app(EditStaffInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('throw a NotFoundException when the staffId not exists in db', function (): void {
            $this->lookupStaffUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateStaffs(), self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle($this->context, self::NOT_EXISTING_ID, $this->getEditValue());
                }
            );
        });
        $this->should('edit the Staff after transaction begun', function (): void {
            $this->lookupStaffUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateStaffs(), $this->examples->staffs[0]->id)
                ->andReturn(Seq::from($this->examples->staffs[0]));
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に編集処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に編集処理が行われないことの検証は（恐らく）できない
                    $this->staffRepository
                        ->expects('store')
                        ->andReturn($this->examples->staffs[0]);
                    return $callback();
                });

            $this->interactor->handle($this->context, $this->examples->staffs[0]->id, $this->getEditValue());
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('スタッフ情報が更新されました', ['id' => $this->examples->staffs[0]->id] + $context);

            $this->interactor->handle($this->context, $this->examples->staffs[0]->id, $this->getEditValue());
        });
        $this->should('return array', function (): void {
            $this->assertArrayStrictEquals(
                [],
                $this->interactor->handle($this->context, $this->examples->staffs[0]->id, $this->getEditValue())
            );
        });
    }

    /**
     * 編集情報を取得する.
     *
     * @return array
     */
    public function getEditValue(): array
    {
        return [
            'name' => new StructuredName(
                familyName: '新垣',
                givenName: '栄作',
                phoneticFamilyName: 'シンガキ',
                phoneticGivenName: 'エイサク',
            ),
            'sex' => Sex::male()->value(),
            'birthday' => Carbon::parse('1982-05-09'),
            'addr' => new Addr(
                postcode: '351-0106',
                prefecture: Prefecture::saitama(),
                city: '和光市',
                street: '広沢2-10',
                apartment: 'クラブコート和光',
            ),
            'location' => Location::create([
                'lat' => 12.345678,
                'lng' => 123.456789,
            ]),
            'tel' => '048-111-2222',
            'email' => 'sample@mail.com',
            'password' => Password::fromString('passworddddddd'),
            'createdAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ];
    }
}
