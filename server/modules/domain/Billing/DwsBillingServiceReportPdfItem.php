<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Common\DayOfWeek;
use Domain\Polite;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Seq;

/**
 * 障害：サービス提供実績記録票 PDF 明細
 */
final class DwsBillingServiceReportPdfItem extends Polite
{
    /**
     * {@link \Domain\Billing\DwsBillingServiceReportPdfItem} constructor.
     *
     * @param string $providedOn 日付
     * @param string $weekday 曜日
     * @param string $serviceCount サービスの提供回数
     * @param string $serviceType サービス内容
     * @param string $situation サービス提供の状況
     * @param \Domain\Billing\DwsBillingServiceReportPdfDuration $plan
     * @param \Domain\Billing\DwsBillingServiceReportPdfDuration $result
     * @param string $headcount 派遣人数
     * @param string $isFirstTime 初回加算
     * @param string $isEmergency 緊急時対応加算
     * @param string $isWelfareSpecialistCooperation 福祉専門職員等連携加算
     * @param string $isMovingCareSupport 移動介護緊急時支援加算
     * @param string $note 備考
     */
    public function __construct(
        public readonly string $providedOn,
        public readonly string $weekday,
        public readonly string $serviceCount,
        public readonly string $serviceType,
        public readonly string $situation,
        public readonly DwsBillingServiceReportPdfDuration $plan,
        public readonly DwsBillingServiceReportPdfDuration $result,
        public readonly string $headcount,
        public readonly string $isFirstTime,
        public readonly string $isEmergency,
        public readonly string $isWelfareSpecialistCooperation,
        public readonly string $isMovingCareSupport,
        public readonly string $note,
    ) {
    }

    /**
     * サービス提供実績記録票：明細 を PDF に描画する形式に変換する.
     *
     * @param \Domain\Billing\DwsBillingServiceReportItem[]&iterable $items
     * @return \Domain\Billing\DwsBillingServiceReportPdfItem[]&\ScalikePHP\Seq
     */
    public static function from(iterable $items): Seq
    {
        return Seq::from(...$items)->map(function (DwsBillingServiceReportItem $x): self {
            $plan = $x->plan === null
                ? DwsBillingServiceReportPdfDuration::empty()
                : new DwsBillingServiceReportPdfDuration(
                    start: $x->plan->period->start->format('H:i'),
                    end: $x->plan->period->end->format('H:i'),
                    serviceDurationHours: (string)($x->plan->serviceDurationHours?->toFloat() ?: ''),
                    movingDurationHours: (string)($x->plan->movingDurationHours?->toFloat() ?: ''),
                );
            $result = $x->result === null
                ? DwsBillingServiceReportPdfDuration::empty()
                : new DwsBillingServiceReportPdfDuration(
                    start: $x->result->period->start->format('H:i'),
                    end: $x->result->period->end->format('H:i'),
                    serviceDurationHours: (string)($x->result->serviceDurationHours?->toFloat() ?: ''),
                    movingDurationHours: (string)($x->result->movingDurationHours?->toFloat() ?: ''),
                );
            return new self(
                providedOn: $x->providedOn->format('j'),
                weekday: DayOfWeek::resolve(DayOfWeek::from($x->providedOn->dayOfWeekIso)),
                serviceCount: $x->serviceCount === 0 ? '' : ($x->serviceCount === 1 ? '①' : '②'),
                serviceType: self::toServiceTypeString($x),
                situation: $x->situation->equals(DwsBillingServiceReportSituation::none())
                    ? ''
                    : DwsBillingServiceReportSituation::resolve($x->situation),
                plan: $plan,
                result: $result,
                headcount: (string)$x->headcount,
                isFirstTime: $x->isFirstTime ? '1' : '',
                isEmergency: $x->isEmergency ? '1' : '',
                isWelfareSpecialistCooperation: $x->isWelfareSpecialistCooperation ? '1' : '',
                isMovingCareSupport: $x->isMovingCareSupport ? '1' : '',
                note: $x->note
            );
        });
    }

    /**
     * サービス内容に記載する形式に変換する.
     *
     * @param \Domain\Billing\DwsBillingServiceReportItem $item
     * @return string
     */
    private static function toServiceTypeString(DwsBillingServiceReportItem $item): string
    {
        $providerType = match ($item->providerType) {
            DwsBillingServiceReportProviderType::beginner(),
            DwsBillingServiceReportProviderType::careWorkerForPwsd() => '('
                . DwsBillingServiceReportProviderType::resolve($item->providerType)
                . ')',
            default => '',
        };
        return self::rewriteServiceType($item->serviceType) . $providerType;
    }

    /**
     * 決定サービスコードをみてサービス内容に記載する値を返す.
     *
     * @param \Domain\Billing\DwsGrantedServiceCode $code
     * @return string
     */
    private static function rewriteServiceType(DwsGrantedServiceCode $code): string
    {
        return match ($code) {
            DwsGrantedServiceCode::none(),
            DwsGrantedServiceCode::physicalCare() => '身体',
            DwsGrantedServiceCode::housework() => '家事',
            DwsGrantedServiceCode::accompanyWithPhysicalCare() => '通院(伴う)',
            DwsGrantedServiceCode::accompany() => '通院',
            DwsGrantedServiceCode::visitingCareForPwsd1(),
            DwsGrantedServiceCode::visitingCareForPwsd2(),
            DwsGrantedServiceCode::visitingCareForPwsd3(),
            DwsGrantedServiceCode::outingSupportForPwsd(),
            DwsGrantedServiceCode::comprehensiveSupport() => '',
            default => throw new InvalidArgumentException('Unexpected DwsGrantedServiceCode value'),
        };
    }
}
