<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Context\Context;
use Lib\Exceptions\LogicException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * StaffRequest のテスト.
 */
final class StaffRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private StaffRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (StaffRequestTest $self): void {
            $self->request = new StaffRequest();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_createContext(): void
    {
        $this->should('return a Context', function (): void {
            OrganizationRequest::prepareOrganizationRequest(
                $this->request,
                $this->examples->organizations[0]
            );
            StaffRequest::prepareStaffRequest(
                $this->request,
                $this->examples->staffs[0],
                Seq::fromArray($this->examples->roles),
                Seq::fromArray($this->examples->offices),
                Seq::emptySeq(),
            );

            $this->assertInstanceOf(Context::class, $this->request->context());
        });
        $this->should('throw LogicException when organization is not prepared', function (): void {
            StaffRequest::prepareStaffRequest(
                $this->request,
                $this->examples->staffs[0],
                Seq::fromArray($this->examples->roles),
                Seq::fromArray($this->examples->offices),
                Seq::emptySeq(),
            );
            $this->assertThrows(LogicException::class, function (): void {
                $this->request->context();
            });
        });
        $this->should('throw LogicException when staff is not prepared', function (): void {
            OrganizationRequest::prepareOrganizationRequest(
                $this->request,
                $this->examples->organizations[0]
            );
            $this->assertThrows(LogicException::class, function (): void {
                $this->request->context();
            });
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_prepareStaffRequest(): void
    {
        $this->should('use given staff to create context', function (): void {
            OrganizationRequest::prepareOrganizationRequest(
                $this->request,
                $this->examples->organizations[0]
            );
            StaffRequest::prepareStaffRequest(
                $this->request,
                $this->examples->staffs[0],
                Seq::fromArray($this->examples->roles),
                Seq::fromArray($this->examples->offices),
                Seq::emptySeq(),
            );

            $this->assertSame($this->examples->staffs[0], $this->request->context()->staff->get());
        });
    }
}
