<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingFile;
use Domain\Common\Carbon;
use Domain\Common\MimeType;
use Domain\Context\Context;
use UseCase\Concerns\UniqueTokenSupport;
use UseCase\Contracts\TokenMaker;
use UseCase\File\GenerateFileNameUseCase;
use UseCase\File\StoreCsvUseCase;

/**
 * 介護保険サービス：介護給付費請求書・明細書 CSV 生成ユースケース実装.
 */
final class CreateLtcsBillingInvoiceCsvInteractor implements CreateLtcsBillingInvoiceCsvUseCase
{
    use UniqueTokenSupport;

    private const STORE_TO = 'artifacts';
    private const TEMPORARY_FILE_PREFIX = 'ltcs-billing-';
    private const TOKEN_LENGTH = 60;
    private const TOKEN_MAX_RETRY_COUNT = 100;

    private BuildLtcsBillingInvoiceRecordListUseCase $buildRecordListUseCase;
    private GenerateFileNameUseCase $generateFileNameUseCase;
    private StoreCsvUseCase $storeCsvUseCase;

    /**
     * {@link \UseCase\Billing\CreateLtcsBillingInvoiceCsvInteractor} constructor.
     *
     * @param \UseCase\Contracts\TokenMaker $tokenMaker
     * @param \UseCase\Billing\BuildLtcsBillingInvoiceRecordListUseCase $buildRecordListUseCase
     * @param \UseCase\File\GenerateFileNameUseCase $generateFileNameUseCase
     * @param \UseCase\File\StoreCsvUseCase $storeCsvUseCase
     */
    public function __construct(
        TokenMaker $tokenMaker,
        BuildLtcsBillingInvoiceRecordListUseCase $buildRecordListUseCase,
        GenerateFileNameUseCase $generateFileNameUseCase,
        StoreCsvUseCase $storeCsvUseCase
    ) {
        $this->tokenMaker = $tokenMaker;
        $this->buildRecordListUseCase = $buildRecordListUseCase;
        $this->generateFileNameUseCase = $generateFileNameUseCase;
        $this->storeCsvUseCase = $storeCsvUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, LtcsBilling $billing, LtcsBillingBundle $bundle): LtcsBillingFile
    {
        $path = $this->store($context, $billing, $bundle);
        return new LtcsBillingFile(
            name: $this->createName($billing, $bundle),
            path: $path,
            token: $this->createUniqueToken(self::TOKEN_LENGTH, self::TOKEN_MAX_RETRY_COUNT),
            mimeType: MimeType::csv(),
            createdAt: Carbon::now(),
            downloadedAt: null,
        );
    }

    /** {@inheritdoc} */
    protected function isUnique(string $token): bool
    {
        // TODO: DEV-4531 請求関連ファイルのトークン一意性担保について検討・対応
        // 単一の `LtcsBilling` においてユニークであれば十分なはずなので無条件で通してもそれほど問題にならない気もする.
        return true;
    }

    /**
     * CSV ファイルに書き込むレコード情報を生成する.
     *
     * @param array|\Domain\Exchange\ExchangeRecord[] $records
     * @return iterable|mixed[][]
     */
    private function generateRows(array $records): iterable
    {
        $recordNumber = 1;
        foreach ($records as $record) {
            yield $record->toArray($recordNumber++);
        }
    }

    /**
     * CSV を生成して格納する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\LtcsBilling $billing
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @return string
     */
    private function store(Context $context, LtcsBilling $billing, LtcsBillingBundle $bundle): string
    {
        $records = $this->buildRecordListUseCase->handle($context, $billing, $bundle);
        $rows = $this->generateRows($records);
        return $this->storeCsvUseCase->handle($context, self::STORE_TO, self::TEMPORARY_FILE_PREFIX, $rows);
    }

    /**
     * ダウンロード用のファイル名を生成する.
     *
     * @param \Domain\Billing\LtcsBilling $billing
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @return string
     */
    private function createName(LtcsBilling $billing, LtcsBillingBundle $bundle): string
    {
        $placeholders = [
            'office' => $billing->office->abbr,
            'transactedIn' => $billing->transactedIn,
            'providedIn' => $bundle->providedIn,
        ];
        return $this->generateFileNameUseCase->handle('ltcs_invoice_csv', $placeholders);
    }
}
