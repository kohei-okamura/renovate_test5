<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\User;

use Domain\Model;

/**
 * 利用者：請求先情報.
 *
 * @property-read \Domain\User\BillingDestination $destination 請求先
 * @property-read \Domain\User\PaymentMethod $paymentMethod 支払方法
 * @property-read string $contractNumber 契約者番号
 * @property-read string $corporationName 請求先法人名・団体名
 * @property-read string $agentName 請求先氏名・担当者名
 * @property-read null|\Domain\Common\Addr $addr 請求先住所
 * @property-read string $tel 請求先電話番号
 */
class UserBillingDestination extends Model
{
    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'destination',
            'paymentMethod',
            'contractNumber',
            'corporationName',
            'agentName',
            'addr',
            'tel',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'destination' => true,
            'paymentMethod' => true,
            'contractNumber' => true,
            'corporationName' => true,
            'agentName' => true,
            'addr' => true,
            'tel' => true,
        ];
    }
}
