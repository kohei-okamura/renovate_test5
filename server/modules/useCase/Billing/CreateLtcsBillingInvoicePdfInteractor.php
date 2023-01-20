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
use UseCase\File\StorePdfUseCase;

/**
 * 介護保険サービス：介護給付費請求書・明細書 PDF 生成ユースケース実装.
 */
final class CreateLtcsBillingInvoicePdfInteractor implements CreateLtcsBillingInvoicePdfUseCase
{
    use UniqueTokenSupport;

    private const STORE_TO = 'artifacts';
    private const TEMPLATE = 'pdfs.billings.ltcs-billing-invoice.index';
    private const TOKEN_LENGTH = 60;
    private const TOKEN_MAX_RETRY_COUNT = 100;

    private BuildLtcsBillingInvoicePdfParamUseCase $buildPdfUseCase;
    private GenerateFileNameUseCase $generateFileNameUseCase;
    private StorePdfUseCase $storePdfUseCase;

    /**
     * {@link \UseCase\Billing\CreateLtcsBillingInvoiceCsvInteractor} constructor.
     *
     * @param \UseCase\Contracts\TokenMaker $tokenMaker
     * @param \UseCase\Billing\BuildLtcsBillingInvoicePdfParamUseCase $buildPdfUseCase
     * @param \UseCase\File\GenerateFileNameUseCase $generateFileNameUseCase
     * @param \UseCase\File\StorePdfUseCase $storePdfUseCase
     */
    public function __construct(
        TokenMaker $tokenMaker,
        BuildLtcsBillingInvoicePdfParamUseCase $buildPdfUseCase,
        GenerateFileNameUseCase $generateFileNameUseCase,
        StorePdfUseCase $storePdfUseCase
    ) {
        $this->tokenMaker = $tokenMaker;
        $this->buildPdfUseCase = $buildPdfUseCase;
        $this->generateFileNameUseCase = $generateFileNameUseCase;
        $this->storePdfUseCase = $storePdfUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, LtcsBilling $billing, LtcsBillingBundle $bundle): LtcsBillingFile
    {
        $path = $this->store($context, $billing, $bundle);
        return new LtcsBillingFile(
            name: $this->createName($billing, $bundle),
            path: $path,
            token: $this->createUniqueToken(self::TOKEN_LENGTH, self::TOKEN_MAX_RETRY_COUNT),
            mimeType: MimeType::pdf(),
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
     * PDF を生成して格納する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\LtcsBilling $billing
     * @param \Domain\Billing\LtcsBillingBundle $bundle
     * @return string
     */
    private function store(Context $context, LtcsBilling $billing, LtcsBillingBundle $bundle): string
    {
        $params = $this->buildPdfUseCase->handle($context, $billing, $bundle);
        return $this->storePdfUseCase->handle(
            $context,
            self::STORE_TO,
            self::TEMPLATE,
            $params
        );
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
        return $this->generateFileNameUseCase->handle('ltcs_invoice_pdf', $placeholders);
    }
}
