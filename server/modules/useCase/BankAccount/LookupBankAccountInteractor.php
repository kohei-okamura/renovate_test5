<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\BankAccount;

use Domain\BankAccount\BankAccountRepository;
use Domain\Context\Context;
use ScalikePHP\Seq;

/**
 * 銀行口座情報取得ユースケース実装.
 */
final class LookupBankAccountInteractor implements LookupBankAccountUseCase
{
    private BankAccountRepository $repository;

    /**
     * Constructor.
     *
     * @param \Domain\BankAccount\BankAccountRepository $repository
     */
    public function __construct(BankAccountRepository $repository)
    {
        $this->repository = $repository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int ...$id): Seq
    {
        return $this->repository->lookup(...$id);
    }
}
