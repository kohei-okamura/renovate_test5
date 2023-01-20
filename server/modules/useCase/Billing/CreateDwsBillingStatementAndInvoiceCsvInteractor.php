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
use UseCase\File\StoreCsvUseCase;

/**
 * 障害福祉サービス：介護給付費・訓練等給付費等明細書 CSV 生成ユースケース実装.
 */
final class CreateDwsBillingStatementAndInvoiceCsvInteractor implements CreateDwsBillingStatementAndInvoiceCsvUseCase
{
    use UniqueTokenSupport;

    private const FILE_PLACEHOLDER_YEAR_MONTH_FORMAT = 'Ym';
    private const STORE_TO = 'artifacts';
    private const TEMPORARY_FILE_PREFIX = 'dws-billing-';
    private const TOKEN_MAX_RETRY_COUNT = 100;

    /**
     * {@link \UseCase\Billing\BuildDwsBillingStatementAndInvoiceRecordListInteractor} constructor.
     *
     * @param \Domain\Config\Config $config
     * @param \UseCase\Contracts\TokenMaker $tokenMaker
     * @param \UseCase\Billing\BuildDwsBillingStatementAndInvoiceRecordListUseCase $buildRecordListUseCase
     * @param \UseCase\File\StoreCsvUseCase $storeCsvUseCase
     * @param GenerateFileNameUseCase $generateFileNameUseCase
     */
    public function __construct(
        private Config $config,
        TokenMaker $tokenMaker,
        private BuildDwsBillingStatementAndInvoiceRecordListUseCase $buildRecordListUseCase,
        private GenerateFileNameUseCase $generateFileNameUseCase,
        private StoreCsvUseCase $storeCsvUseCase
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
            token: $this->createUniqueToken(TokenMaker::DEFAULT_TOKEN_LENGTH, self::TOKEN_MAX_RETRY_COUNT),
            mimeType: MimeType::csv(),
            createdAt: Carbon::now(),
            downloadedAt: null,
        );
    }

    /** {@inheritdoc} */
    protected function isUnique(string $token): bool
    {
        // TODO: DEV-4531 請求関連ファイルのトークン一意性担保について検討・対応
        return true;
    }

    /**
     * CSV ファイルに書き込むレコード情報を生成する.
     *
     * @param array|\Domain\Exchange\ExchangeRecord[] $records
     * @return array[]&iterable
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
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Billing\DwsBillingBundle[]&\ScalikePHP\Seq $bundles
     * @return string
     */
    private function store(Context $context, DwsBilling $billing, Seq $bundles): string
    {
        $records = $this->buildRecordListUseCase->handle($context, $billing, $bundles);
        $rows = $this->generateRows($records);
        return $this->storeCsvUseCase->handle($context, self::STORE_TO, self::TEMPORARY_FILE_PREFIX, $rows);
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
        return $this->generateFileNameUseCase->handle('dws_invoice_csv', $placeholders);
    }
}
