<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Office;

use Domain\Context\Context;
use Lib\Exceptions\NotFoundException;

/**
 * 事業所保証実装.
 */
class EnsureOfficeInteractor implements EnsureOfficeUseCase
{
    private LookupOfficeUseCase $useCase;

    /**
     * Constructor.
     * @param \UseCase\Office\LookupOfficeUseCase $useCase
     */
    public function __construct(LookupOfficeUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, array $permissions, int $officeId): void
    {
        $this->useCase
            ->handle($context, $permissions, $officeId)
            ->headOption()
            ->getOrElse(function () use ($officeId) {
                throw new NotFoundException("Office[{$officeId}] is not found");
            });
    }
}
