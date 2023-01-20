<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\UserBilling;

use Domain\Common\Addr;
use Domain\Polite;
use Domain\User\BillingDestination;
use Domain\User\User;

/**
 * 利用者請求：領収書 PDF 請求先情報
 */
final class UserBillingReceiptPdfBillingDestination extends Polite
{
    /**
     * {@link \Domain\Billing\UserBillingStatementPdfAmount} constructor
     *
     * @param \Domain\Common\Addr $addr 住所
     * @param string $corporationName 担当者名
     * @param string $agentName 団体名（法人・団体 の場合のみ）
     */
    public function __construct(
        public readonly Addr $addr,
        public readonly string $corporationName,
        public readonly string $agentName
    ) {
    }

    /**
     * 利用者請求：領収書 PDF 請求先情報ドメインモデルを生成する.
     *
     * @param \Domain\User\User $user
     * @return self
     */
    public static function from(User $user): self
    {
        $destination = $user->billingDestination->destination;
        return match ($destination) {
            BillingDestination::agent() => new self(
                addr: $user->billingDestination->addr,
                corporationName: '',
                agentName: $user->billingDestination->agentName
            ),
            BillingDestination::corporation() => new self(
                addr: $user->billingDestination->addr,
                corporationName: $user->billingDestination->corporationName,
                agentName: $user->billingDestination->agentName
            ),
            default => new self(
                addr: $user->addr,
                corporationName: '',
                agentName: $user->name->displayName
            ),
        };
    }
}
