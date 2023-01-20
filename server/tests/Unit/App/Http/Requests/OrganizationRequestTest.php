<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use Domain\Context\Context;
use Lib\Exceptions\LogicException;
use ScalikePHP\None;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * OrganizationRequest のテスト.
 */
final class OrganizationRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private OrganizationRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (OrganizationRequestTest $self): void {
            $self->request = new OrganizationRequest();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_createContext(): void
    {
        $this->should('return a Context without staff', function (): void {
            OrganizationRequest::prepareOrganizationRequest(
                $this->request,
                $this->examples->organizations[0]
            );
            $actual = $this->request->context();
            $this->assertInstanceOf(Context::class, $actual);
            $this->assertInstanceOf(None::class, $actual->staff);
        });
        $this->should('throw LogicException when organization is not prepared', function (): void {
            $this->assertThrows(LogicException::class, function (): void {
                $this->request->context();
            });
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_prepareOrganizationRequest(): void
    {
        $this->should('use given organization to create context', function (): void {
            OrganizationRequest::prepareOrganizationRequest(
                $this->request,
                $this->examples->organizations[0]
            );
            $this->assertSame($this->examples->organizations[0], $this->request->context()->organization);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_to_array(): void
    {
        $this->should('never use toArray()', function (): void {
            $this->assertThrows(LogicException::class, function (): void {
                $this->request->toArray();
            });
        });
    }
}
