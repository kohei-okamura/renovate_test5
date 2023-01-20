<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\ServiceCode;

use Domain\ServiceCode\ServiceCode;
use Illuminate\Support\Arr;
use Lib\Exceptions\RuntimeException;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * ServiceCode のテスト
 */
class ServiceCodeTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use OrganizationExample;
    use UnitSupport;
    use MatchesSnapshots;

    protected ServiceCode $serviceCode;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ServiceCodeTest $self): void {
            $self->values = [
                'serviceDivisionCode' => '123',
                'serviceCategoryCode' => '123',
            ];
            $self->serviceCode = ServiceCode::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have serviceDivisionCode attribute', function (): void {
            $this->assertSame($this->serviceCode->get('serviceDivisionCode'), Arr::get($this->values, 'serviceDivisionCode'));
        });
        $this->should('have serviceCategoryCode attribute', function (): void {
            $this->assertSame($this->serviceCode->get('serviceCategoryCode'), Arr::get($this->values, 'serviceCategoryCode'));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->serviceCode);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_fromString(): void
    {
        $this->should('return object', function (): void {
            $actual = ServiceCode::fromString('123456');

            $this->assertSame('12', $actual->serviceDivisionCode);
            $this->assertSame('3456', $actual->serviceCategoryCode);
        });
        $this->should('throw Exception when not length is 6', function (): void {
            $this->assertThrows(
                RuntimeException::class,
                function (): void {
                    ServiceCode::fromString('12345');
                }
            );
        });
    }
}
