<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Shift;

use Domain\Context\Context;
use Domain\Shift\Shift;
use Domain\Shift\ShiftRepository;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use Lib\Exceptions\PhpSpreadsheetException;
use Lib\Logging;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use ScalikePHP\Seq;
use UseCase\File\DownloadStorageUseCase;

/**
 * 勤務シフト一括登録実装.
 */
final class ImportShiftInteractor implements ImportShiftUseCase
{
    use Logging;

    private const SHEET_INDEX_SHIFT = 0;

    private DownloadStorageUseCase $downloadStorageUseCase;
    private LoadShiftUseCase $loadShiftUseCase;
    private ShiftRepository $repository;
    private TransactionManager $transaction;
    private XlsxReader $xlsxReader;

    /**
     * Constructor.
     *
     * @param \UseCase\File\DownloadStorageUseCase $downloadStorageUseCase
     * @param \UseCase\Shift\LoadShiftUseCase $loadShiftUseCase
     * @param \Domain\Shift\ShiftRepository $repository
     * @param \Domain\TransactionManagerFactory $transaction
     */
    public function __construct(
        DownloadStorageUseCase $downloadStorageUseCase,
        LoadShiftUseCase $loadShiftUseCase,
        ShiftRepository $repository,
        TransactionManagerFactory $transaction
    ) {
        $this->downloadStorageUseCase = $downloadStorageUseCase;
        $this->loadShiftUseCase = $loadShiftUseCase;
        $this->repository = $repository;
        $this->transaction = $transaction->factory($repository);
        $this->xlsxReader = app(XlsxReader::class);
    }

    /** {@inheritdoc} */
    public function handle(Context $context, string $path): void
    {
        $localFileInfo = $this->downloadStorageUseCase->handle($context, $path);
        try {
            $spreadsheet = $this->xlsxReader->load($localFileInfo->getRealPath());
            $worksheet = $spreadsheet->getSheet(self::SHEET_INDEX_SHIFT);
            $shifts = $this->loadShiftUseCase->handle($context, $worksheet);
        } catch (Exception $exception) {
            throw new PhpSpreadsheetException(
                "Failed to load the spreadsheet: {$exception->getMessage()}",
                0,
                $exception
            );
        }

        $this->transaction->run(function () use ($context, $shifts): void {
            $entityIds = Seq::fromArray($shifts)->map(
                fn (Shift $shift): int => $this->repository
                    ->store($shift->copy([
                        'organizationId' => $context->organization->id,
                        'isCanceled' => false,
                        'reason' => '',
                    ]))
                    ->id
            )->toArray();
            $this->logger()->info(
                '勤務シフトが一括登録されました',
                ['ids' => $entityIds] + $context->logContext() // TODO DEV-1577 複数IDのログ出力
            );
        });
    }
}
