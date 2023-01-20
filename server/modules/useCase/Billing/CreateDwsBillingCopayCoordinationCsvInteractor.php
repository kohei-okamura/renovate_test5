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
use ScalikePHP\Option;
use ScalikePHP\Seq;
use UseCase\Concerns\UniqueTokenSupport;
use UseCase\Contracts\TokenMaker;
use UseCase\File\GenerateFileNameUseCase;
use UseCase\File\StoreCsvUseCase;

/**
 * 障害福祉サービス：利用者負担上限額管理結果票 CSV 生成ユースケース実装.
 */
final class CreateDwsBillingCopayCoordinationCsvInteractor implements CreateDwsBillingCopayCoordinationCsvUseCase
{
    use UniqueTokenSupport;

    private const DWS_CONTROL_RECORD_INDEX = 0;

    private const FILE_PLACEHOLDER_YEAR_MONTH_FORMAT = 'Ym';
    private const STORE_TO = 'artifacts';
    private const TEMPORARY_FILE_PREFIX = 'dws-billing-';
    private const TOKEN_MAX_RETRY_COUNT = 100;

    /**
     * {@link \UseCase\Billing\CreateDwsBillingCopayCoordinationCsvInteractor} constructor.
     *
     * @param \Domain\Config\Config $config
     * @param \UseCase\Contracts\TokenMaker $tokenMaker
     * @param \UseCase\Billing\BuildDwsBillingCopayCoordinationRecordListUseCase $buildRecordsUseCase
     * @param \UseCase\File\StoreCsvUseCase $storeCsvUseCase
     * @param GenerateFileNameUseCase $generateFileNameUseCase
     */
    public function __construct(
        private Config $config,
        TokenMaker $tokenMaker,
        private BuildDwsBillingCopayCoordinationRecordListUseCase $buildRecordsUseCase,
        private GenerateFileNameUseCase $generateFileNameUseCase,
        private StoreCsvUseCase $storeCsvUseCase
    ) {
        $this->tokenMaker = $tokenMaker;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, DwsBilling $billing, Seq $bundles): Option
    {
        $providedInString = $bundles
            ->map(fn (DwsBillingBundle $x): string => $x->providedIn->toDateString())
            ->distinct();
        if ($providedInString->count() !== 1) {
            // 請求単位の一覧に複数のサービス提供年月が含まれている場合は異常なケースなので例外とする
            throw new LogicException('DwsBillingBundle contains multiple providedIn.');
        }
        $providedIn = $bundles->head()->providedIn;

        return $this
            ->store($context, $billing, $bundles)
            ->map(fn (string $path): DwsBillingFile => new DwsBillingFile(
                name: $this->createName($billing, $providedIn),
                path: $path,
                token: $this->createUniqueToken(TokenMaker::DEFAULT_TOKEN_LENGTH, self::TOKEN_MAX_RETRY_COUNT),
                mimeType: MimeType::csv(),
                createdAt: Carbon::now(),
                downloadedAt: null,
            ));
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
     * @return \ScalikePHP\Option&string[] ファイルパス
     */
    private function store(Context $context, DwsBilling $billing, Seq $bundles): Option
    {
        $records = $this->buildRecordsUseCase->handle($context, $billing, $bundles);
        $rows = $this->generateRows($records);
        if ($records[self::DWS_CONTROL_RECORD_INDEX]->recordCount !== 0) {
            $path = $this->storeCsvUseCase->handle($context, self::STORE_TO, self::TEMPORARY_FILE_PREFIX, $rows);
            return Option::some($path);
        } else {
            return Option::none();
        }
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
        return $this->generateFileNameUseCase->handle('dws_copay_coordination_csv', $placeholders);
    }
}
