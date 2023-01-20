<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Office\Office;
use ScalikePHP\Option;

/**
 * 事業所 ID とサービス提供年月で介護保険サービス：訪問介護：算定情報取得ユースケース実装.
 */
final class GetHomeVisitLongTermCareCalcSpecInteractor implements GetHomeVisitLongTermCareCalcSpecUseCase
{
    private IdentifyHomeVisitLongTermCareCalcSpecUseCase $identifyCalcSpecUseCase;
    private LookupOfficeUseCase $lookupOfficeUseCase;

    /**
     * {@link \UseCase\Office\GetHomeVisitLongTermCareCalcSpecInteractor} constructor.
     *
     * @param \UseCase\Office\IdentifyHomeVisitLongTermCareCalcSpecUseCase $identifyCalcSpecUseCase
     * @param \UseCase\Office\LookupOfficeUseCase $lookupOfficeUseCase
     */
    public function __construct(
        IdentifyHomeVisitLongTermCareCalcSpecUseCase $identifyCalcSpecUseCase,
        LookupOfficeUseCase $lookupOfficeUseCase
    ) {
        $this->identifyCalcSpecUseCase = $identifyCalcSpecUseCase;
        $this->lookupOfficeUseCase = $lookupOfficeUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, array $permissions, int $officeId, Carbon $providedIn): Option
    {
        $office = $this->lookupOfficeOption($context, $permissions, $officeId);
        return $office->flatMap(
            fn (Office $x): Option => $this->identifyCalcSpecUseCase->handle($context, $x, $providedIn)
        );
    }

    /**
     * 事業所を取得する.
     *
     * @param \Domain\Context\Context $context
     * @param array|\Domain\Permission\Permission[] $permissions
     * @param int $officeId
     * @return \Domain\Office\Office[]|\ScalikePHP\Option
     */
    private function lookupOfficeOption(Context $context, array $permissions, int $officeId): Option
    {
        return $this->lookupOfficeUseCase
            ->handle($context, $permissions, $officeId)
            ->headOption();
    }
}
