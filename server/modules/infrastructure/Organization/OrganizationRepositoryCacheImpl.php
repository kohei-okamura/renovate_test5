<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Organization;

use DateTimeInterface;
use Domain\Common\Carbon;
use Domain\Contracts\LookupOptionByCode;
use Domain\Organization\OrganizationRepository;
use Infrastructure\Repository\CacheRepository;
use ScalikePHP\Option;

/**
 * OrganizationRepository cache implementation.
 */
final class OrganizationRepositoryCacheImpl extends CacheRepository implements OrganizationRepository
{
    /**
     * OrganizationRepositoryCacheImpl constructor.
     *
     * @param \Infrastructure\Organization\OrganizationRepositoryFallback $fallback
     */
    public function __construct(OrganizationRepositoryFallback $fallback)
    {
        parent::__construct($fallback);
    }

    /** {@inheritdoc} */
    public function lookupOptionByCode(string $code): Option
    {
        return $this->cache->remember(
            "{$this->namespace()}:code:{$code}",
            $this->expiredAt(),
            function () use ($code) {
                assert($this->fallback instanceof LookupOptionByCode);
                return $this->fallback->lookupOptionByCode($code);
            }
        );
    }

    /** {@inheritdoc} */
    protected function expiredAt(): DateTimeInterface
    {
        return Carbon::tomorrow();
    }

    /** {@inheritdoc} */
    protected function namespace(): string
    {
        return 'organization';
    }
}
