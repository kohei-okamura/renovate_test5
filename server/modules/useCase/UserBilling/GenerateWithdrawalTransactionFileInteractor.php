<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\File\FileInputStream;
use Domain\File\FileStorage;
use Domain\File\TemporaryFiles;
use Domain\Permission\Permission;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\WithdrawalTransaction;
use Domain\UserBilling\WithdrawalTransactionRepository;
use Domain\UserBilling\ZenginRecord;
use Lib\Exceptions\FileIOException;
use Lib\Exceptions\NotFoundException;
use Lib\Logging;
use Lib\StreamFilter\StreamFilter;
use SplFileObject;

/**
 * {@link \UseCase\UserBilling\GenerateWithdrawalTransactionFileUseCase} の実装.
 */
class GenerateWithdrawalTransactionFileInteractor implements GenerateWithdrawalTransactionFileUseCase
{
    use Logging;

    private const STORE_TO = 'exported';

    private FileStorage $fileStorage;
    private TemporaryFiles $temporaryFiles;
    private LookupUserBillingUseCase $lookupUserBillingUseCase;
    private LookupWithdrawalTransactionUseCase $lookupWithdrawalTransactionUseCase;
    private WithdrawalTransactionRepository $repository;
    private TransactionManager $transaction;

    /**
     * constructor.
     *
     * @param \Domain\File\FileStorage $fileStorage
     * @param \Domain\File\TemporaryFiles $temporaryFiles
     * @param \UseCase\UserBilling\LookupUserBillingUseCase $lookupUserBillingUseCase
     * @param \UseCase\UserBilling\LookupWithdrawalTransactionUseCase $lookupWithdrawalTransactionUseCase
     * @param \Domain\UserBilling\WithdrawalTransactionRepository $repository
     * @param \Domain\TransactionManagerFactory $factory
     */
    public function __construct(
        FileStorage $fileStorage,
        TemporaryFiles $temporaryFiles,
        LookupUserBillingUseCase $lookupUserBillingUseCase,
        LookupWithdrawalTransactionUseCase $lookupWithdrawalTransactionUseCase,
        WithdrawalTransactionRepository $repository,
        TransactionManagerFactory $factory
    ) {
        $this->fileStorage = $fileStorage;
        $this->temporaryFiles = $temporaryFiles;
        $this->lookupUserBillingUseCase = $lookupUserBillingUseCase;
        $this->lookupWithdrawalTransactionUseCase = $lookupWithdrawalTransactionUseCase;
        $this->repository = $repository;
        $this->transaction = $factory->factory($repository);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $id): string
    {
        $withdrawalTransaction = $this->lookupWithdrawalTransaction($context, $id);
        $userBilling = $this->lookupUserBilling($context, $withdrawalTransaction->items[0]->userBillingIds[0]);

        $zenginRecord = ZenginRecord::from(
            $withdrawalTransaction,
            $context->organization->name,
            $userBilling->deductedOn ?? $this->getDeductedOn()
        );

        $path = $this->store(self::STORE_TO, $this->createFile('zengin-', $zenginRecord->toZenginRecordString()));

        $this->transaction->run(fn () => $this->repository->store($withdrawalTransaction->copy([
            'downloadedAt' => Carbon::now(),
            'updatedAt' => Carbon::now(),
        ])));
        $this->logger()->info(
            '口座振替データが更新されました',
            ['id' => $withdrawalTransaction->id] + $context->logContext()
        );

        return $path;
    }

    /**
     * 口座振替データを取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @return \Domain\UserBilling\WithdrawalTransaction
     */
    private function lookupWithdrawalTransaction(Context $context, int $id): WithdrawalTransaction
    {
        return $this->lookupWithdrawalTransactionUseCase
            ->handle($context, Permission::downloadWithdrawalTransactions(), $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("WithdrawalTransaction({$id}) not found");
            });
    }

    /**
     * 利用者請求を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param int $id
     * @return \Domain\UserBilling\UserBilling
     */
    private function lookupUserBilling(Context $context, int $id): UserBilling
    {
        return $this->lookupUserBillingUseCase
            ->handle($context, Permission::downloadWithdrawalTransactions(), $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("UserBilling({$id}) not found");
            });
    }

    /**
     * 全銀ファイルを生成してそのパスを返す.
     *
     * @param string $prefix
     * @param string $data
     * @return string
     */
    private function createFile(string $prefix, string $data): string
    {
        $path = $this->temporaryFiles->create($prefix, '')->getPathname();
        $stream = StreamFilter::pathBuilder()
            ->withResource($path)
            ->withWriteFilter(StreamFilter::crlf())
            ->withWriteFilter(StreamFilter::iconv('utf-8', 'cp932'))
            ->build();
        $file = new SplFileObject($stream, 'w');
        $file->fwrite($data);
        return $path;
    }

    /**
     * 全銀ファイルをファイルストレージに格納してそのパスを返す.
     *
     * @param string $dir
     * @param string $source
     * @return string
     */
    private function store(string $dir, string $source): string
    {
        $inputStream = FileInputStream::from(basename($source), $source);
        return $this->fileStorage->store($dir, $inputStream)->getOrElse(function () use ($source): void {
            throw new FileIOException("Failed to store file: {$source}");
        });
    }

    /**
     * 引落日を取得する.
     *
     * @return \Domain\Common\Carbon
     */
    private function getDeductedOn(): Carbon
    {
        return Carbon::now()->firstOfMonth()->addDays(25)->getNextBusinessDay();
    }
}
