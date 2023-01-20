<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\UserBilling;

use App\Concretes\PermanentDatabaseTransactionManager;
use Domain\Common\Carbon;
use Domain\File\FileInputStream;
use Domain\Permission\Permission;
use Lib\Exceptions\FileIOException;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use SplFileInfo;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FileStorageMixin;
use Tests\Unit\Mixins\LoggerMixin;
use Tests\Unit\Mixins\LookupUserBillingUseCaseMixin;
use Tests\Unit\Mixins\LookupWithdrawalTransactionUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\TemporaryFilesMixin;
use Tests\Unit\Mixins\TransactionManagerMixin;
use Tests\Unit\Mixins\WithdrawalTransactionRepositoryMixin;
use Tests\Unit\Test;
use UseCase\UserBilling\GenerateWithdrawalTransactionFileInteractor;

/**
 * {@link \UseCase\UserBilling\GenerateWithdrawalTransactionFileInteractor} のテスト.
 */
final class GenerateWithdrawalTransactionFileInteractorTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use FileStorageMixin;
    use LookupUserBillingUseCaseMixin;
    use LookupWithdrawalTransactionUseCaseMixin;
    use LoggerMixin;
    use MockeryMixin;
    use TemporaryFilesMixin;
    use TransactionManagerMixin;
    use UnitSupport;
    use WithdrawalTransactionRepositoryMixin;

    private const STORE_TO = 'exported';

    private GenerateWithdrawalTransactionFileInteractor $interactor;
    private SplFileInfo $file;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->file = $self->createTemporaryFileInfoStub();

            $self->fileStorage
                ->allows('store')
                ->andReturn(Option::some('path/to/zengin-file'))
                ->byDefault();
            $self->temporaryFiles
                ->allows('create')
                ->andReturn($self->file)
                ->byDefault();
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->userBillings[0]))
                ->byDefault();
            $self->lookupWithdrawalTransactionUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->withdrawalTransactions[0]))
                ->byDefault();
            $self->withdrawalTransactionRepository
                ->allows('store')
                ->andReturn($self->examples->withdrawalTransactions[0])
                ->byDefault();
            $self->withdrawalTransactionRepository
                ->allows('transactionManager')
                ->andReturn(PermanentDatabaseTransactionManager::class)
                ->passthru();
            $self->logger
                ->allows('info')
                ->byDefault();

            $self->interactor = app(GenerateWithdrawalTransactionFileInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use LookupUserBillingUseCase', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::downloadWithdrawalTransactions(),
                    $this->examples->withdrawalTransactions[0]->items[0]->userBillingIds[0]
                )
                ->andReturn(Seq::from($this->examples->userBillings[0]));

            $this->interactor->handle($this->context, $this->examples->withdrawalTransactions[0]->id);
        });
        $this->should('throw NotFoundException when LookupUserBillingUseCase return empty', function (): void {
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle($this->context, $this->examples->withdrawalTransactions[0]->id);
            });
        });
        $this->should('use LookupWithdrawalTransactionUseCase', function (): void {
            $this->lookupWithdrawalTransactionUseCase
                ->expects('handle')
                ->with($this->context, Permission::downloadWithdrawalTransactions(), $this->examples->withdrawalTransactions[0]->id)
                ->andReturn(Seq::from($this->examples->withdrawalTransactions[0]));

            $this->interactor->handle($this->context, $this->examples->withdrawalTransactions[0]->id);
        });
        $this->should('throw NotFoundException when LookupWithdrawalTransactionUseCase return empty', function (): void {
            $this->lookupWithdrawalTransactionUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle($this->context, $this->examples->withdrawalTransactions[0]->id);
            });
        });
        $this->should('use store on WithdrawalTransactionRepository', function (): void {
            $this->withdrawalTransactionRepository
                ->expects('store')
                ->with(equalTo($this->examples->withdrawalTransactions[0]->copy([
                    'downloadedAt' => Carbon::now(),
                    'updatedAt' => Carbon::now(),
                ])))
                ->andReturn(Seq::from($this->examples->withdrawalTransactions[0]));

            $this->interactor->handle($this->context, $this->examples->withdrawalTransactions[0]->id);
        });
        $this->should('return stored file path', function (): void {
            $this->assertSame(
                'path/to/zengin-file',
                $this->interactor->handle($this->context, $this->examples->withdrawalTransactions[0]->id)
            );
        });
        $this->should('use store on FileStorage', function (): void {
            $this->fileStorage
                ->expects('store')
                ->withArgs(function (string $dir, FileInputStream $inputStream): bool {
                    $filepath = $this->file->getPathname();
                    return $dir === self::STORE_TO
                        && $inputStream->name() === basename($filepath)
                        && $inputStream->source() === $filepath;
                })
                ->andReturn(Option::some('path/to/zengin-file'));

            $this->interactor->handle($this->context, $this->examples->withdrawalTransactions[0]->id);
        });
        $this->should('throw FileIOException store on FileStorage return none', function (): void {
            $this->fileStorage
                ->expects('store')
                ->andReturn(Option::none());

            $this->assertThrows(FileIOException::class, function (): void {
                $this->interactor->handle($this->context, $this->examples->withdrawalTransactions[0]->id);
            });
        });
        $this->should('log using info', function (): void {
            $context = ['organizationId' => $this->examples->organizations[0]->id];
            $this->context
                ->expects('logContext')
                ->andReturn($context);
            $this->logger
                ->expects('info')
                ->with('口座振替データが更新されました', ['id' => $this->examples->withdrawalTransactions[0]->id] + $context);

            $this->interactor->handle($this->context, $this->examples->withdrawalTransactions[0]->id);
        });
    }

    /**
     * テスト用の {@link \SplFileInfo} を生成する.
     *
     * @return \SplFileInfo
     */
    private function createTemporaryFileInfoStub(): SplFileInfo
    {
        $file = tempnam(sys_get_temp_dir(), 'test-');
        return new SplFileInfo($file);
    }
}
