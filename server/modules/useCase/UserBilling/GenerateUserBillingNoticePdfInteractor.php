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
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use UseCase\File\StorePdfUseCase;

/**
 * 代理受領額通知書PDF生成ユースケース実装.
 */
class GenerateUserBillingNoticePdfInteractor implements GenerateUserBillingNoticePdfUseCase
{
    private const STORE_TO = 'exported';
    private const TEMPLATE = 'pdfs.user-billings.notice';

    private StorePdfUseCase $storePdfUseCase;
    private LookupUserBillingUseCase $lookupUserBillingUseCase;
    private BuildUserBillingNoticePdfParamUseCase $buildUseCase;

    public function __construct(
        StorePdfUseCase $storePdfUseCase,
        LookupUserBillingUseCase $lookupUserBillingUseCase,
        BuildUserBillingNoticePdfParamUseCase $buildUseCase
    ) {
        $this->storePdfUseCase = $storePdfUseCase;
        $this->lookupUserBillingUseCase = $lookupUserBillingUseCase;
        $this->buildUseCase = $buildUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, array $ids, Carbon $issuedOn): string
    {
        $userBillings = $this->lookupUserBillings($context, $ids);
        return $this->store($context, $userBillings, $issuedOn);
    }

    /**
     * 利用者請求を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param array $ids
     * @return \ScalikePHP\Seq
     */
    private function lookupUserBillings(Context $context, array $ids): Seq
    {
        $userBillings = $this->lookupUserBillingUseCase->handle($context, Permission::viewUserBillings(), ...$ids);
        if ($userBillings->isEmpty()) {
            $x = implode(',', $ids);
            throw new NotFoundException("UserBillings({$x}) not found");
        }
        return $userBillings;
    }

    /**
     * PDF を生成して格納する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\UserBilling\UserBilling[]&\ScalikePHP\Seq $userBillings
     * @param \Domain\Common\Carbon $issuedOn
     * @return string
     */
    private function store(Context $context, Seq $userBillings, Carbon $issuedOn): string
    {
        $params = $this->buildUseCase->handle($context, $userBillings, $issuedOn);
        return $this->storePdfUseCase->handle(
            $context,
            self::STORE_TO,
            self::TEMPLATE,
            $params
        );
    }
}
