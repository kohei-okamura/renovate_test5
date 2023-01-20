<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\File;

use Domain\Common\Carbon;
use Domain\Config\Config;
use ScalikePHP\Map;

/**
 * ファイル名生成ユースケース実装.
 */
final class GenerateFileNameInteractor implements GenerateFileNameUseCase
{
    private const FILE_PLACEHOLDER_YEAR_MONTH_FORMAT = 'Ym';

    private Config $config;

    /**
     * {@link \UseCase\File\GenerateFileNameUseCase} constructor.
     *
     * @param \Domain\Config\Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /** {@inheritdoc} */
    public function handle(string $filename, array $replaceKeyValues = []): string
    {
        $placeholders = Map::from($replaceKeyValues)
            ->flatMap(function ($v, $k) {
                $val = $v instanceof Carbon ? $v->format(self::FILE_PLACEHOLDER_YEAR_MONTH_FORMAT) : $v;
                return ["#{{$k}}" => $val];
            })
            ->toAssoc();
        return str_replace(
            array_keys($placeholders),
            array_values($placeholders),
            $this->config->get("zinger.filename.{$filename}")
        );
    }
}
