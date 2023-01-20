<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\UserBilling;

use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementItem;
use Domain\Billing\LtcsBillingStatement;
use Domain\Billing\LtcsBillingStatementItem;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Pdf\PdfSupport;
use Domain\Polite;
use Domain\User\User;
use Lib\Exceptions\InvalidArgumentException;
use Lib\Math;
use ScalikePHP\Map;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 利用者請求：介護サービス利用明細書 PDF
 */
final class UserBillingStatementPdf extends Polite
{
    use PdfSupport;
    use UserBillingPdfSupport;

    private const ITEMS_PER_PAGE = 25;

    /**
     * {@link \Domain\UserBilling\UserBillingStatementPdf} constructor
     *
     * @param string $issuedOn 発行日
     * @param \Domain\UserBilling\UserBillingOffice $office 事業所
     * @param \Domain\Common\CarbonRange $period サービス提供期間
     * @param \Domain\User\User $user 利用者
     * @param array&\Domain\UserBilling\UserBillingStatementPdfItem[] $billingItems 明細
     * @param \Domain\UserBilling\UserBillingStatementPdfAmount $itemsAmounts 各合計
     * @param int $page ページ
     * @param int $maxPage 最大ページ数
     */
    public function __construct(
        public readonly string $issuedOn,
        public readonly UserBillingOffice $office,
        public readonly CarbonRange $period,
        public readonly User $user,
        public readonly array $billingItems,
        public readonly UserBillingStatementPdfAmount $itemsAmounts,
        public readonly int $page,
        public readonly int $maxPage
    ) {
    }

    /**
     * 利用者請求：介護サービス利用明細書 PDF ドメインモデルを生成する.
     *
     * @param \Domain\User\User $user
     * @param \Domain\UserBilling\UserBilling $userBilling
     * @param \Domain\Common\Carbon $issuedOn
     * @param \Domain\Billing\DwsBillingStatement[]&\ScalikePHP\Option $dwsBillingStatement
     * @param \Domain\Billing\LtcsBillingStatement[]&\ScalikePHP\Option $ltcsBillingStatement
     * @param \ScalikePHP\Map $dwsServiceCodeMap サービス名称Map [サービスコード => 名称, ...]
     * @param \ScalikePHP\Map $ltcsServiceCodeMap サービス名称Map [サービスコード => 名称, ...]
     * @return \ScalikePHP\Seq
     */
    public static function from(
        User $user,
        UserBilling $userBilling,
        Carbon $issuedOn,
        Option $dwsBillingStatement,
        Option $ltcsBillingStatement,
        Map $dwsServiceCodeMap,
        Map $ltcsServiceCodeMap
    ): Seq {
        if ($dwsBillingStatement->isEmpty() && $ltcsBillingStatement->isEmpty()) {
            throw new InvalidArgumentException('UserBillingStatement must have dwsBillingStatement or ltcsBillingStatement');
        }
        $dwsUserBillingStatementPdfs = Seq::fromArray(call_user_func(
            function () use ($user, $userBilling, $issuedOn, $dwsBillingStatement, $dwsServiceCodeMap) {
                $items = $dwsBillingStatement
                    ->map(fn (DwsBillingStatement $x): Seq => Seq::from(...$x->items))
                    ->map(
                        fn (Seq $items) => $items->map(
                            fn (DwsBillingStatementItem $item): UserBillingStatementPdfItem => UserBillingStatementPdfItem::fromDws($item, $dwsServiceCodeMap)
                        )
                    )
                    ->toSeq()
                    ->flatten();

                $xs = array_chunk($items->toArray(), self::ITEMS_PER_PAGE);
                return Seq::from(...$xs)->map(fn (array $billingItems, int $index): self => new self(
                    issuedOn: $issuedOn->toJapaneseDate(),
                    office: $userBilling->office,
                    period: CarbonRange::ofMonth($userBilling->providedIn),
                    user: $user,
                    billingItems: $billingItems,
                    itemsAmounts: self::calcDwsTotal(Option::from($userBilling->dwsItem)),
                    page: $index + 1,
                    maxPage: self::dwsMaxPage($dwsBillingStatement),
                ));
            }
        ));
        $ltcsUserBillingStatementPdfs = Seq::fromArray(call_user_func(
            function () use ($user, $userBilling, $issuedOn, $ltcsBillingStatement, $ltcsServiceCodeMap) {
                $items = $ltcsBillingStatement
                    ->map(fn (LtcsBillingStatement $x): Seq => Seq::from(...$x->items))
                    ->map(
                        fn (Seq $items) => $items->map(
                            fn (LtcsBillingStatementItem $item): UserBillingStatementPdfItem => UserBillingStatementPdfItem::fromLtcs($item, $ltcsServiceCodeMap)
                        )
                    )
                    ->toSeq()
                    ->flatten();

                $xs = array_chunk($items->toArray(), self::ITEMS_PER_PAGE);
                return Seq::from(...$xs)->map(fn (array $billingItems, int $index): self => new self(
                    issuedOn: $issuedOn->toJapaneseDate(),
                    office: $userBilling->office,
                    period: CarbonRange::ofMonth($userBilling->providedIn),
                    user: $user,
                    billingItems: $billingItems,
                    itemsAmounts: self::calcLtcsTotal(Option::from($userBilling->ltcsItem)),
                    page: $index + 1,
                    maxPage: self::ltcsMaxPage($ltcsBillingStatement),
                ));
            }
        ));
        return Seq::from(...$dwsUserBillingStatementPdfs, ...$ltcsUserBillingStatementPdfs);
    }

    /**
     * 障害の最大ページ数を取得する.
     *
     * @param \Domain\Billing\DwsBillingStatement[]&\ScalikePHP\Option $dwsBillingStatement
     * @return int
     */
    private static function dwsMaxPage(Option $dwsBillingStatement): int
    {
        return $dwsBillingStatement
            ->map(fn (DwsBillingStatement $x): int => Math::ceil(count($x->items) / self::ITEMS_PER_PAGE))
            ->getOrElseValue(0);
    }

    /**
     * 介保の最大ページ数を取得する.
     *
     * @param \Domain\Billing\LtcsBillingStatement[]&\ScalikePHP\Option $ltcsBillingStatement
     * @return int
     */
    private static function ltcsMaxPage(Option $ltcsBillingStatement): int
    {
        return $ltcsBillingStatement
            ->map(fn (LtcsBillingStatement $x): int => Math::ceil(count($x->items) / self::ITEMS_PER_PAGE))
            ->getOrElseValue(0);
    }

    /**
     * 障害福祉サービス明細の各合計を計算する.
     *
     * @param \Domain\UserBilling\UserBillingDwsItem[]&\ScalikePHP\Option $dwsItem
     * @return \Domain\UserBilling\UserBillingStatementPdfAmount
     */
    private static function calcDwsTotal(Option $dwsItem): UserBillingStatementPdfAmount
    {
        return $dwsItem->isEmpty()
            ? UserBillingStatementPdfAmount::empty()
            : UserBillingStatementPdfAmount::fromDws($dwsItem->get());
    }

    /**
     * 介護保険サービス明細の各合計を計算する.
     *
     * @param \Domain\UserBilling\UserBillingLtcsItem[]&\ScalikePHP\Option $ltcsItem
     * @return array
     */
    private static function calcLtcsTotal(Option $ltcsItem): UserBillingStatementPdfAmount
    {
        return $ltcsItem->isEmpty()
            ? UserBillingStatementPdfAmount::empty()
            : UserBillingStatementPdfAmount::fromLTcs($ltcsItem->get());
    }
}
