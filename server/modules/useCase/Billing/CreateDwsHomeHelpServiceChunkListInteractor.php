<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsHomeHelpServiceChunk;
use Domain\Billing\DwsHomeHelpServiceChunk as Chunk;
use Domain\Billing\DwsHomeHelpServiceChunkFinder;
use Domain\Billing\DwsHomeHelpServiceChunkImpl;
use Domain\Billing\DwsHomeHelpServiceChunkRepository;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\DwsCertification\DwsCertification;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス請求：サービス単位（居宅介護）生成ユースケース実装.
 */
final class CreateDwsHomeHelpServiceChunkListInteractor implements CreateDwsHomeHelpServiceChunkListUseCase
{
    private const SERVICE_COMBINE_TWO_HOURS_RULE = 2;

    private DwsHomeHelpServiceChunkFinder $chunkFinder;
    private DwsHomeHelpServiceChunkRepository $chunkRepository;
    private TransactionManager $transaction;

    /**
     * {@link \UseCase\Billing\CreateDwsHomeHelpServiceChunkListInteractor} constructor.
     *
     * @param DwsHomeHelpServiceChunkFinder $chunkFinder
     * @param DwsHomeHelpServiceChunkRepository $chunkRepository
     * @param TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        DwsHomeHelpServiceChunkFinder $chunkFinder,
        DwsHomeHelpServiceChunkRepository $chunkRepository,
        TransactionManagerFactory $transactionManagerFactory
    ) {
        $this->chunkFinder = $chunkFinder;
        $this->chunkRepository = $chunkRepository;
        $this->transaction = $transactionManagerFactory->factory($chunkRepository);
    }

    /** {@inheritdoc} */
    public function handle(
        Context $context,
        DwsCertification $certification,
        DwsProvisionReport $report,
        Option $previousReport,
        bool $isPlan = false
    ): Seq {
        return $this->transaction->rollback(function () use ($report, $previousReport, $isPlan): Seq {
            $chunks = $isPlan
                ? $this->buildChunk($report, $report->plans)
                : $this->buildChunk(
                    $report,
                    [
                        // 前月の最終日の実績を含めて Chunk を組み立てる
                        ...$previousReport->flatMap(
                            fn (DwsProvisionReport $x): Option => Seq::from(...$x->results)->lastOption()
                        ),
                        ...$report->results,
                    ]
                );
            foreach ($chunks as $chunk) {
                // 合成可能な既存の要素がある場合：合成して既存の要素を更新
                // 合成可能な既存の要素がない場合：新しい要素を作成

                /** @var \Domain\Billing\DwsHomeHelpServiceChunk $composed */
                $composed = $this
                    ->findComposable($chunk)
                    ->map(fn (Chunk $x): Chunk => $x->compose($chunk))
                    ->getOrElseValue($chunk);

                $this->chunkRepository->store($composed);
            }
            return $this->fetchAllChunksFromRepository();
        });
    }

    /**
     * 障害福祉サービス：予実の実績から障害福祉サービス：サービス単位（居宅介護）（実績）の一覧を生成する.
     *
     * @param \Domain\ProvisionReport\DwsProvisionReport $report
     * @param array|\Domain\ProvisionReport\DwsProvisionReportItem[] $items
     * @return \Infrastructure\Billing\DwsHomeHelpServiceChunk[]|iterable
     */
    private function buildChunk(DwsProvisionReport $report, array $items): iterable
    {
        return Seq::fromArray($items)
            ->filter(fn (DwsProvisionReportItem $item): bool => $item->isHomeHelpService())
            ->sortBy(fn (DwsProvisionReportItem $item): Carbon => $item->schedule->start)
            ->map(function (DwsProvisionReportItem $item) use ($report): DwsHomeHelpServiceChunk {
                return DwsHomeHelpServiceChunkImpl::from($report, $item);
            });
    }

    /**
     * 合成可能なサービス単位を見つける.
     *
     * @param \Domain\Billing\DwsHomeHelpServiceChunk $chunk
     * @return \Domain\Billing\DwsHomeHelpServiceChunk[]|\ScalikePHP\Option
     */
    private function findComposable(DwsHomeHelpServiceChunk $chunk): Option
    {
        if ($chunk->isEmergency) {
            return Option::none();
        } else {
            $filterParams = [
                'category' => $chunk->category,
                'buildingType' => $chunk->buildingType,
                'isEmergency' => false,
                'isPlannedByNovice' => $chunk->isPlannedByNovice,
                'rangeStartBefore' => $chunk->range->end->addHours(self::SERVICE_COMBINE_TWO_HOURS_RULE),
                'rangeEndAfter' => $chunk->range->start->subHours(self::SERVICE_COMBINE_TWO_HOURS_RULE),
            ];
            $paginationParams = [
                'page' => 1,
                'itemPerPage' => 1,
                'sortBy' => 'id',
            ];
            return $this->chunkFinder->find($filterParams, $paginationParams)->list->headOption();
        }
    }

    /**
     * 生成されたすべてのサービス単位を返す.
     *
     * @return \Domain\Billing\DwsHomeHelpServiceChunk[]|\ScalikePHP\Seq
     */
    private function fetchAllChunksFromRepository(): Seq
    {
        return $this->chunkFinder->find([], ['all' => true, 'sortBy' => 'id'])->list;
    }
}
