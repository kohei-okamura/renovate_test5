<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Concretes;

use Domain\Common\Carbon;
use Domain\Config\Config;
use Illuminate\Config\Repository;
use Lib\Exceptions\LogicException;

/**
 * 設定リポジトリ実装.
 */
final class ConfigRepository implements Config
{
    private Repository $config;

    /**
     * ConfigProvider constructor.
     */
    public function __construct()
    {
        $this->config = app('config');
    }

    /** {@inheritdoc} */
    public function get(string $key)
    {
        if ($this->config->has($key)) {
            return $this->config->get($key);
        } else {
            throw new LogicException("config not found: {$key}");
        }
    }

    /** {@inheritdoc} */
    public function filename(string $key): string
    {
        return Carbon::now()->formatLocalized($this->get($key));
    }
}
