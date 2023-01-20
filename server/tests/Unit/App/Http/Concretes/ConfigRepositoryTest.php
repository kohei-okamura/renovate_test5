<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Concretes;

use App\Concretes\ConfigRepository;
use Domain\Common\Carbon;
use Lib\Exceptions\LogicException;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Concretes\ConfigRepository} Test.
 */
class ConfigRepositoryTest extends Test
{
    use CarbonMixin;
    use UnitSupport;

    private ConfigRepository $repository;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ConfigRepositoryTest $self): void {
            $self->repository = app(ConfigRepository::class);
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_get(): void
    {
        $this->should('return config value', function (): void {
            /** @var \Illuminate\Config\Repository */
            $config = app('config');
            $config->set('test.config.value', 'FooBarBaz');

            $this->assertEquals('FooBarBaz', $this->repository->get('test.config.value'));
        });
        $this->should('throw LogicException when the config is not defined', function (): void {
            /** @var \Illuminate\Config\Repository */
            $config = app('config');

            $this->assertFalse($config->has('undefined.key'));
            $this->assertThrows(
                LogicException::class,
                function (): void {
                    $this->repository->get('undefined.key');
                }
            );
        });
    }

    /**
     * @test
     * @returns void
     */
    public function describe_filename(): void
    {
        $this->should('return formatted filename', function (): void {
            $this->assertEquals(
                Carbon::now()->formatLocalized('訪問介護計画書_%Y%m%d%H%M%S.xlsx'),
                $this->repository->filename('zinger.filename.ltcs_project_pdf')
            );
        });
    }
}
