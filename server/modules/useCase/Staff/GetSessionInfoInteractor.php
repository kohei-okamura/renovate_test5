<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Staff;

use Domain\Context\Context;
use Domain\Staff\Staff;
use ScalikePHP\Option;

/**
 * Get session info interactor.
 */
class GetSessionInfoInteractor implements GetSessionInfoUseCase
{
    private BuildAuthResponseUseCase $buildAuthResponseUseCase;

    /**
     * Constructor.
     * @param \UseCase\Staff\BuildAuthResponseUseCase $buildAuthResponseUseCase
     */
    public function __construct(BuildAuthResponseUseCase $buildAuthResponseUseCase)
    {
        $this->buildAuthResponseUseCase = $buildAuthResponseUseCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context): Option
    {
        return $context->staff->map(fn (Staff $staff) => $this->buildAuthResponseUseCase->handle($context, $staff));
    }
}
