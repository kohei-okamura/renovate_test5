<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsVisitingCareForPwsdChunk as Chunk;
use Domain\Billing\DwsVisitingCareForPwsdChunkFinder;
use Domain\Billing\DwsVisitingCareForPwsdChunkImpl;
use Domain\Billing\DwsVisitingCareForPwsdChunkRepository;
use Domain\Billing\DwsVisitingCareForPwsdFragment;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Context\Context;
use Domain\DwsCertification\DwsCertification;
use Domain\ProvisionReport\DwsProvisionReport;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Domain\Shift\ServiceOption;
use Domain\TransactionManager;
use Domain\TransactionManagerFactory;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：サービス単位（重度訪問介護）一覧生成ユースケース実装.
 */
final class CreateDwsVisitingCareForPwsdChunkListInteractor implements CreateDwsVisitingCareForPwsdChunkListUseCase
{
    private DwsVisitingCareForPwsdChunkFinder $chunkFinder;
    private DwsVisitingCareForPwsdChunkRepository $chunkRepository;
    private TransactionManager $transaction;

    /**
     * {@link \UseCase\Billing\CreateDwsVisitingCareForPwsdChunkListInteractor} constructor.
     *
     * @param \Domain\Billing\DwsVisitingCareForPwsdChunkFinder $chunkFinder
     * @param \Domain\Billing\DwsVisitingCareForPwsdChunkRepository $chunkRepository
     * @param \Domain\TransactionManagerFactory $transactionManagerFactory
     */
    public function __construct(
        DwsVisitingCareForPwsdChunkFinder $chunkFinder,
        DwsVisitingCareForPwsdChunkRepository $chunkRepository,
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
        DwsProvisionReport $provisionReport,
        bool $isPlan = false
    ): Seq {
        return $this->transaction->rollback(function () use ($provisionReport, $isPlan, $certification): Seq {
            $chunks = $isPlan
                ? $this->buildChunk($certification, $provisionReport, $provisionReport->plans)
                : $this->buildChunk($certification, $provisionReport, $provisionReport->results);
            foreach ($chunks as $chunk) {
                // 合成可能な既存の要素がある場合：合成して既存の要素を更新
                // 合成可能な既存の要素がない場合：新しい要素を作成

                /** @var \Domain\Billing\DwsVisitingCareForPwsdChunk $composed */
                $composed = $this
                    ->findComposable($chunk)
                    ->map(fn (Chunk $x): Chunk => $x->compose($chunk))
                    ->getOrElseValue($chunk);

                // 日跨ぎの場合は日ごとに分割してから保管する
                foreach ($composed->split() as $x) {
                    $this->chunkRepository->store($x);
                }
            }
            $xs = $this->fetchAllChunksFromRepository()->filter(fn (Chunk $x): bool => $x->isEffective());
            return Seq::from(...$xs);
        });
    }

    /**
     * 障害福祉サービス：予実から障害福祉サービス：サービス単位（重度訪問介護）（実績）の一覧を生成する.
     *
     * @param \Domain\DwsCertification\DwsCertification $certification
     * @param \Domain\ProvisionReport\DwsProvisionReport $report
     * @param array|\Domain\ProvisionReport\DwsProvisionReportItem[] $items
     * @return \Domain\Billing\DwsVisitingCareForPwsdChunk[]|iterable
     */
    private function buildChunk(DwsCertification $certification, DwsProvisionReport $report, array $items): iterable
    {
        return Seq::fromArray($items)
            ->filter(fn (DwsProvisionReportItem $item): bool => $item->isVisitingCareForPwsd())
            ->sortBy(fn (DwsProvisionReportItem $item): Carbon => $item->schedule->start)
            ->map(function (DwsProvisionReportItem $item) use ($certification, $report): Chunk {
                $range = $item->schedule->toRange();
                $fragments = $this->buildFragments($item, $range);
                return DwsVisitingCareForPwsdChunkImpl::create([
                    'userId' => $report->userId,
                    'category' => DwsServiceCodeCategory::fromDwsCertification($certification, $report->providedIn),
                    'isEmergency' => $item->hasOption(ServiceOption::emergency()),
                    'isFirst' => $item->hasOption(ServiceOption::firstTime()),
                    'isBehavioralDisorderSupportCooperation' => $item->hasOption(
                        ServiceOption::behavioralDisorderSupportCooperation()
                    ),
                    'providedOn' => $range->start->startOfDay(),
                    'range' => $range,
                    'fragments' => Seq::from(...$fragments),
                ]);
            });
    }

    /**
     * 要素を組み立てる.
     *
     * @param \Domain\ProvisionReport\DwsProvisionReportItem $item
     * @param \Domain\Common\CarbonRange $range
     * @return \Domain\Billing\DwsVisitingCareForPwsdFragment[]|iterable
     */
    private function buildFragments(DwsProvisionReportItem $item, CarbonRange $range): iterable
    {
        $movingDurationMinutes = $item->movingDurationMinutes;
        $isLongHospitalized = $item->hasOption(ServiceOption::longHospitalized());
        $isHospitalized = $isLongHospitalized || $item->hasOption(ServiceOption::hospitalized());

        yield $fragment = DwsVisitingCareForPwsdFragment::create([
            'isHospitalized' => $isHospitalized,
            'isLongHospitalized' => $isLongHospitalized,
            'isCoaching' => $item->hasOption(ServiceOption::coaching()),
            'isMoving' => false,
            'isSecondary' => false,
            'movingDurationMinutes' => 0,
            'range' => $range,
            'headcount' => $item->headcount,
        ]);

        if ($movingDurationMinutes > 0) {
            yield $fragment->copy([
                'isMoving' => true,
                'movingDurationMinutes' => $movingDurationMinutes,
            ]);
        }
    }

    /**
     * 合成可能なサービス単位を見つける.
     *
     * @param \Domain\Billing\DwsVisitingCareForPwsdChunk $chunk
     * @return \Domain\Billing\DwsVisitingCareForPwsdChunk[]|\ScalikePHP\Option
     */
    private function findComposable(Chunk $chunk): Option
    {
        $filterParams = [
            'userId' => $chunk->userId,
            'category' => $chunk->category,
            'providedOn' => $chunk->providedOn,
        ];
        $paginationParams = [
            'itemsPerPage' => 1,
            'sortBy' => 'id',
        ];
        return $this->chunkFinder->find($filterParams, $paginationParams)->list->headOption();
    }

    /**
     * 生成されたすべてのサービス単位を返す.
     *
     * @return \Domain\Billing\DwsVisitingCareForPwsdChunk[]|\ScalikePHP\Seq
     */
    private function fetchAllChunksFromRepository(): Seq
    {
        return $this->chunkFinder->find([], ['all' => true, 'sortBy' => 'id'])->list;
    }
}
