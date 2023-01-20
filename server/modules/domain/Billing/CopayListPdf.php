<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Billing;

use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Pdf\PdfSupport;
use Domain\Polite;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Seq;

/**
 * 利用者負担額一覧表 PDF.
 */
final class CopayListPdf extends Polite
{
    use PdfSupport;

    private const ITEMS_PER_PAGE = 10;

    /**
     * {@link \Domain\Billing\CopayListPdf} constructor.
     *
     * @param string $issuedOn 作成年月日（発行年月日）
     * @param string $copayCoordinationOfficeName 提供先
     * @param string $officeCode 指定事業所番号
     * @param \Domain\Common\Addr $officeAddr 住所
     * @param string $officeTel 電話番号
     * @param string $officeName 事業所名
     * @param string[] $providedIn サービス提供年月
     * @param array&\Domain\Billing\CopayListPdfItem[] $items 明細
     */
    public function __construct(
        public readonly string $issuedOn,
        public readonly string $copayCoordinationOfficeName,
        public readonly string $officeCode,
        public readonly Addr $officeAddr,
        public readonly string $officeTel,
        public readonly string $officeName,
        public readonly array $providedIn,
        public readonly array $items,
    ) {
    }

    /**
     * 利用者負担額一覧表 PDF ドメインモデルを生成する.
     *
     * @param \Domain\Billing\DwsBilling $billing
     * @param \Domain\Billing\DwsBillingBundle[]&\ScalikePHP\Seq $bundles
     * @param \Domain\Billing\CopayListSource[]&\ScalikePHP\Seq $sources
     * @param \Domain\Common\Carbon $issuedOn
     * @return \ScalikePHP\Seq&self[]
     */
    public static function from(DwsBilling $billing, Seq $bundles, Seq $sources, Carbon $issuedOn): Seq
    {
        return $sources->flatMap(function (CopayListSource $source) use ($billing, $bundles, $issuedOn) {
            $copayCoordinationOfficeName = $source->copayCoordinationOfficeName;
            $xs = array_chunk($source->statements, self::ITEMS_PER_PAGE);
            return Seq::from(...$xs)->map(fn (array $statements): self => new self(
                issuedOn: $issuedOn->toJapaneseDate(),
                copayCoordinationOfficeName: $copayCoordinationOfficeName,
                officeCode: $billing->office->code,
                officeAddr: $billing->office->addr,
                officeTel: $billing->office->tel,
                officeName: $billing->office->name,
                providedIn: self::localized($bundles->head()->providedIn),
                items: Seq::from(...$statements)
                    ->map(fn (DwsBillingStatement $statement, int $index): CopayListPdfItem => CopayListPdfItem::from(
                        $bundles
                            ->find(fn (DwsBillingBundle $x): bool => $x->id === $statement->dwsBillingBundleId)
                            ->getOrElse(function () use ($statement): never {
                                throw new InvalidArgumentException("sources must contain a bundle for the statement({$statement->id})");
                            }),
                        $statement,
                        $index + 1
                    ))
                    ->toArray()
            ));
        });
    }
}
