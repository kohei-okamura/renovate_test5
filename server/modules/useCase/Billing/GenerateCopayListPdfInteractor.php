<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Context\Context;
use ScalikePHP\Seq;
use UseCase\File\StorePdfUseCase;

/**
 * 利用者負担額一覧表 PDF 生成ユースケース実装.
 */
final class GenerateCopayListPdfInteractor implements GenerateCopayListPdfUseCase
{
    private const STORE_TO = 'exported';
    private const TEMPLATE = 'pdfs.billings.copay-list.index';

    private BuildCopayListPdfParamUseCase $buildCopayListPdfUseCase;
    private StorePdfUseCase $storePdfUseCase;

    /**
     * {@link \UseCase\Billing\GenerateCopayListPdfInteractor} constructor.
     *
     * @param \UseCase\File\StorePdfUseCase $storePdfUseCase
     * @param \UseCase\Billing\BuildCopayListPdfParamUseCase $buildCopayListPdfUseCase
     */
    public function __construct(
        BuildCopayListPdfParamUseCase $buildCopayListPdfUseCase,
        StorePdfUseCase $storePdfUseCase
    ) {
        $this->buildCopayListPdfUseCase = $buildCopayListPdfUseCase;
        $this->storePdfUseCase = $storePdfUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, DwsBilling $billing, Seq $bundles, Seq $statements): string
    {
        return $this->store($context, $billing, $bundles, $statements);
    }

    /**
     * PDF を生成して格納する.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\DwsBillingBundle[]&\ScalikePHP\Seq $bundles
     * @param \Domain\Billing\DwsBillingStatement&\ScalikePHP\Seq $statements
     * @param DwsBilling $billing
     * @return string
     */
    private function store(Context $context, DwsBilling $billing, Seq $bundles, Seq $statements): string
    {
        $params = $this->buildCopayListPdfUseCase->handle($context, $billing, $bundles, $statements);
        return $this->storePdfUseCase->handle(
            $context,
            self::STORE_TO,
            self::TEMPLATE,
            $params,
        );
    }
}
