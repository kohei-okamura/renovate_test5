<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\UserBilling;

use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementAggregate;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Model;
use Domain\Pdf\PdfSupport;
use Domain\User\User;
use ScalikePHP\Seq;

/**
 * 利用者請求：代理受領額通知書 PDF
 *
 * @property-read int $dwsNumber 受給者番号
 * @property-read \Domain\Common\Addr $userAddr 利用者住所
 * @property-read \Domain\Billing\DwsBillingUser $dwsBillingUser 利用者（支給決定障害者）
 * @property-read string $providedIn サービス提供年月
 * @property-read string $issuedOn 発行日
 * @property-read string $cityName 市町村名
 * @property-read string $dwsServiceDivision サービス区分名称
 * @property-read \Domain\UserBilling\UserBillingOffice $office 事業所
 * @property-read int $subtotalFee 総費用額
 * @property-read int $subtotalCopay 利用者負担額
 * @property-read int $receiptedAmount 受領金額
 */
final class UserBillingNoticePdf extends Model
{
    use PdfSupport;

    /**
     * インスタンスを生成する.
     *
     * @param \Domain\User\User $user
     * @param \Domain\Billing\DwsBillingStatement $dwsBillingStatement
     * @param \Domain\Billing\DwsBillingBundle $dwsBillingBundle
     * @param \Domain\UserBilling\UserBilling $userBilling
     * @param \Domain\Common\Carbon $issuedOn
     * @return \ScalikePHP\Seq
     */
    public static function from(
        User $user,
        DwsBillingStatement $dwsBillingStatement,
        DwsBillingBundle $dwsBillingBundle,
        UserBilling $userBilling,
        Carbon $issuedOn
    ): Seq {
        return Seq::fromArray($dwsBillingStatement->aggregates)->map(function (DwsBillingStatementAggregate $x) use (
            $user,
            $dwsBillingStatement,
            $dwsBillingBundle,
            $userBilling,
            $issuedOn
        ) {
            return self::create([
                'dwsNumber' => $dwsBillingStatement->user->dwsNumber,
                'userAddr' => $user->addr,
                'dwsBillingUser' => $dwsBillingStatement->user,
                'providedIn' => $userBilling->providedIn->toJapaneseYearMonth(),
                'issuedOn' => $issuedOn->toJapaneseDate(),
                'cityName' => $dwsBillingBundle->cityName,
                'dwsServiceDivision' => DwsServiceDivisionCode::resolve($x->serviceDivisionCode),
                'office' => $userBilling->office,
                'subtotalFee' => $x->subtotalFee,
                'subtotalCopay' => $x->subtotalCopay,
                'receiptedAmount' => $x->subtotalFee - $x->subtotalCopay,
            ]);
        });
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'dwsNumber',
            'userAddr',
            'dwsBillingUser',
            'providedIn',
            'issuedOn',
            'cityName',
            'dwsServiceDivision',
            'office',
            'subtotalFee',
            'subtotalCopay',
            'receiptedAmount',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'dwsNumber' => true,
            'userAddr' => true,
            'dwsBillingUser' => true,
            'providedIn' => true,
            'issuedOn' => true,
            'cityName' => true,
            'dwsServiceDivision' => true,
            'office' => true,
            'subtotalFee' => true,
            'subtotalCopay' => true,
            'receiptedAmount' => true,
        ];
    }
}
