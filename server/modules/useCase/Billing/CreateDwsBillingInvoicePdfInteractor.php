<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingFile;
use Domain\Common\Carbon;
use Domain\Common\MimeType;
use Domain\Config\Config;
use Domain\Context\Context;
use Lib\Exceptions\LogicException;
use ScalikePHP\Seq;
use UseCase\Concerns\UniqueTokenSupport;
use UseCase\Contracts\TokenMaker;
use UseCase\File\GenerateFileNameUseCase;
use UseCase\File\StorePdfUseCase;

/**
 * 障害福祉サービス：介護給付費請求書・明細書 PDF 生成ユースケース実装.
 */
final class CreateDwsBillingInvoicePdfInteractor implements CreateDwsBillingInvoicePdfUseCase
{
    use UniqueTokenSupport;

    private const FILE_PLACEHOLDER_YEAR_MONTH_FORMAT = 'Ym';
    private const STORE_TO = 'artifacts';
    private const TEMPLATE = 'pdfs.billings.dws-billing-invoice.index';
    private const TOKEN_LENGTH = 60;
    private const TOKEN_MAX_RETRY_COUNT = 100;

    /**
     * {@link \UseCase\Billing\CreateDwsBillingInvoiceCsvInteractor} constructor.
     *
     * @param \Domain\Config\Config $config
     * @param \UseCase\Contracts\TokenMaker $tokenMaker
     * @param \UseCase\Billing\BuildDwsBillingInvoicePdfParamUseCase $buildPdfUseCase
     * @param \UseCase\File\StorePdfUseCase $storePdfUseCase
     * @param GenerateFileNameUseCase $generateFileNameUseCase
     */
    public function __construct(
        private Config $config,
        TokenMaker $tokenMaker,
        private BuildDwsBillingInvoicePdfParamUseCase $buildPdfUseCase,
        private GenerateFileNameUseCase $generateFileNameUseCase,
        private StorePdfUseCase $storePdfUseCase
    ) {
        $this->tokenMaker = $tokenMaker;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, DwsBilling $billing, Seq $bundles): DwsBillingFile
    {
        $path = $this->store($context, $billing, $bundles);
        $providedInString = $bundles->map(function (DwsBillingBundle $x): string {
            return $x->providedIn->toDateString();
        })->distinct();
        // 請求単位の一覧に複数のサービス提供年月が含まれている場合は異常なケースなので例外とする
        if ($providedInString->count() !== 1) {
            throw new LogicException('DwsBillingBundle contains multiple providedIn.');
        }
        $providedIn = $bundles->head()->providedIn;
        return new DwsBillingFile(
            name: $this->createName($billing, $providedIn),
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
        // 単一の `DwsBilling` においてユニークであれば十分なはずなので無条件で通してもそれほど問題にならない気もする.
        return true;
    }

    /**
     * PDF を生成して格納する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Billing\DwsBillingBundle[]&\ScalikePHP\Seq $bundles
     * @return string
     */
    private function store(Context $context, DwsBilling $billing, Seq $bundles): string
    {
        $params = $this->buildPdfUseCase->handle($context, $billing, $bundles);
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
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Common\Carbon $providedIn
     * @return string
     */
    private function createName(DwsBilling $billing, Carbon $providedIn): string
    {
        $placeholders = [
            'office' => $billing->office->abbr,
            'transactedIn' => $billing->transactedIn,
            'providedIn' => $providedIn,
        ];
        return $this->generateFileNameUseCase->handle('dws_invoice_pdf', $placeholders);
    }
}
