<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Concretes;

use Domain\Config\Config;
use Domain\Context\Context;
use Domain\Url\UrlBuilder;

/**
 * URL生成実装.
 */
final class UrlBuilderImpl implements UrlBuilder
{
    private Config $config;

    /**
     * UrlBuilderImpl constructor.
     *
     * @param \Domain\Config\Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /** {@inheritdoc} */
    public function build(Context $context, string $path): string
    {
        return sprintf(
            "https://{$this->config->get('zinger.host')}{$path}",
            $context->organization->code
        );
    }
}
