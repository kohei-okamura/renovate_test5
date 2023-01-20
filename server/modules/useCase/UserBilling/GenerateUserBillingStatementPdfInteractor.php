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
 * 利用者請求：介護サービス利用明細書 PDF 生成ユースケース実装.
 */
final class GenerateUserBillingStatementPdfInteractor implements GenerateUserBillingStatementPdfUseCase
{
    private const STORE_TO = 'exported';
    private const TEMPLATE = 'pdfs.user-billings.statement';

    private BuildUserBillingStatementPdfParamUseCase $buildPdfUseCase;
    private LookupUserBillingUseCase $lookupUserBillingUseCase;
    private StorePdfUseCase $storePdfUseCase;

    /**
     * {@link \UseCase\UserBilling\GenerateUserBillingStatementPdfInteractor} constructor.
     *
     * @param \UseCase\UserBilling\BuildUserBillingStatementPdfParamUseCase $buildPdfUseCase
     * @param \UseCase\UserBilling\LookupUserBillingUseCase $lookupUserBillingUseCase
     * @param \UseCase\File\StorePdfUseCase $storePdfUseCase
     */
    public function __construct(
        BuildUserBillingStatementPdfParamUseCase $buildPdfUseCase,
        LookupUserBillingUseCase $lookupUserBillingUseCase,
        StorePdfUseCase $storePdfUseCase
    ) {
        $this->buildPdfUseCase = $buildPdfUseCase;
        $this->lookupUserBillingUseCase = $lookupUserBillingUseCase;
        $this->storePdfUseCase = $storePdfUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, array $ids, Carbon $issuedOn): string
    {
        $userBillings = $this->lookupBillings($context, $ids);
        return $this->store($context, $userBillings, $issuedOn);
    }

    /**
     * 利用者請求を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param array $ids
     * @return \Domain\UserBilling\UserBilling[]&\ScalikePHP\Seq
     */
    private function lookupBillings(Context $context, array $ids): Seq
    {
        $x = $this->lookupUserBillingUseCase->handle($context, Permission::viewUserBillings(), ...$ids);
        if ($x->isEmpty()) {
            throw new NotFoundException('UserBillings not found');
        }
        return $x;
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
        $params = $this->buildPdfUseCase->handle($context, $userBillings, $issuedOn);
        return $this->storePdfUseCase->handle(
            $context,
            self::STORE_TO,
            self::TEMPLATE,
            $params
        );
    }
}
