<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Office;

use Closure;
use Domain\Common\Addr;
use Domain\Common\Location;
use Domain\Common\Prefecture;
use Domain\Office\Office;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Mockery;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Test;
use UseCase\Office\EditOfficeInteractor;

/**
 * EditOfficeInteractor のテスト.
 */
final class EditOfficeInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LoggerMixin;
    use LookupOfficeUseCaseMixin;
    use MockeryMixin;
    use OfficeRepositoryMixin;
    use OrganizationRepositoryMixin;
    use TransactionManagerMixin;
    use UnitSupport;

    /**
     * @var callable|\Closure|\Mockery\MockInterface
     */
    private $callable;
    private EditOfficeInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->officeRepository
                ->allows('store')
                ->andReturn($self->examples->offices[0])
                ->byDefault();
            $self->logger
                ->allows('info')
                ->byDefault();

            $self->callable = Mockery::spy(fn (Office $office) => 'RUN CALLBACK');

            $self->interactor = app(EditOfficeInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('Lookup is called and return target Office', function () {
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    [Permission::updateInternalOffices(), Permission::updateExternalOffices()],
                    $this->examples->offices[0]->id
                )
                ->andReturn(Seq::from($this->examples->offices[0]));

            $this->interactor->handle(
                $this->context,
                $this->examples->offices[0]->id,
                $this->getEditValue(),
                $this->callable
            );
        });
        $this->should('edit the Office after transaction begun', function (): void {
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    [Permission::updateInternalOffices(), Permission::updateExternalOffices()],
                    $this->examples->offices[0]->id
                )
                ->andReturn(Seq::from($this->examples->offices[0]));
            $this->transactionManager
                ->expects('run')
                ->andReturnUsing(function (Closure $callback) {
                    // トランザクションを開始する前に編集処理が行われないことを検証するため
                    // `run` に渡されたコールバック関数を呼び出す直前にモックの定義を行う
                    // なお、トランザクションの終了後に編集処理が行われないことの検証は（恐らく）できない
                    $this->officeRepository
                        ->expects('store')
                        ->andReturn($this->examples->offices[0]);
                    return $callback();
                });

            $this->interactor->handle(
                $this->context,
                $this->examples->offices[0]->id,
                $this->getEditValue(),
                $this->callable
            );
        });
        $this->should('return the Office', function (): void {
            $this->assertModelStrictEquals(
                $this->examples->offices[0],
                $this->interactor->handle(
                    $this->context,
                    $this->examples->offices[0]->id,
                    $this->getEditValue(),
                    $this->callable
                )
            );
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('事業所が更新されました', ['id' => $this->examples->offices[0]->id] + $context);

            $this->interactor->handle(
                $this->context,
                $this->examples->offices[0]->id,
                $this->getEditValue(),
                $this->callable
            );
        });
        $this->should('throw a NotFoundException when the id not exists in db', function (): void {
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    [Permission::updateInternalOffices(), Permission::updateExternalOffices()],
                    self::NOT_EXISTING_ID
                )
                ->andReturn(Seq::empty());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        self::NOT_EXISTING_ID,
                        $this->getEditValue(),
                        $this->callable
                    );
                }
            );
        });
        $this->should('call callable function', function (): void {
            $this->officeRepository
                ->allows('store')
                ->andReturn($this->examples->offices[0]->copy($this->getEditValue()));

            $this->interactor->handle(
                $this->context,
                $this->examples->offices[0]->id,
                $this->getEditValue(),
                $this->callable
            );

            $this->callable->shouldHaveBeenCalled();
        });
        $this->should('not call callable function when no edit address', function (): void {
            $this->interactor->handle(
                $this->context,
                $this->examples->offices[0]->id,
                $this->examples->offices[0]->toAssoc(),
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
    public function getEditValue(): array
    {
        return [
            'organizationId' => $this->examples->offices[0]->organizationId,
            'officeGroupId' => $this->examples->offices[0]->officeGroupId,
            'name' => '土屋訪問介護事業所 札幌',
            'abbr' => '札幌事業所',
            'phoneticName' => 'ツチヤホウモンカイゴジギョウショサッポロ',
            'purpose' => $this->examples->offices[0]->purpose,
            'addr' => new Addr(
                postcode: '164-0011',
                prefecture: Prefecture::tokyo(),
                city: '中野区',
                street: '中央 1-2-3',
                apartment: 'レッチフィールド中野坂上ビル1F',
            ),
            'location' => Location::create([
                'lat' => 12.345678,
                'lng' => 123.456789,
            ]),
            'tel' => '012-345-6789',
            'fax' => '03-3333-3333',
            'email' => 'example@mail.com',
            'updatedAt' => 5555555555,
        ];
    }
}
