<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\UserBilling;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\User\User;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingInvoicePdf;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use UseCase\User\LookupUserUseCase;

/**
 * 利用者請求：請求書 PDFパラメータ組み立てユースケース実装.
 */
class BuildUserBillingInvoicePdfParamInteractor implements BuildUserBillingInvoicePdfParamUseCase
{
    private LookupUserUseCase $lookupUserUseCase;

    public function __construct(
        LookupUserUseCase $lookupUserUseCase
    ) {
        $this->lookupUserUseCase = $lookupUserUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, Seq $userBillings, Carbon $issuedOn): array
    {
        return [
            'billings' => $userBillings
                ->sortBy(fn (UserBilling $x) => $x->user->name->phoneticDisplayName)
                ->map(fn (UserBilling $x) => UserBillingInvoicePdf::from(
                    $this->lookupUser($context, $x->userId),
                    $x,
                    $issuedOn
                ))->computed(),
        ];
    }

    /**
     * 利用者を取得する.
     *
     * @param Context $context
     * @param int $id
     * @return \Domain\User\User
     */
    private function lookupUser(Context $context, int $id): User
    {
        $entities = $this->lookupUserUseCase->handle($context, Permission::viewUserBillings(), $id);
        if ($entities->isEmpty()) {
            throw new NotFoundException("User ({$id}) not found");
        }
        return $entities->head();
    }
}
