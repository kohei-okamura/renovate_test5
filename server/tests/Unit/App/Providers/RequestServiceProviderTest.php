<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Providers;

use App\Http\HttpContext;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Lib\Exceptions\NotFoundException;
use Lib\Exceptions\UnauthorizedException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeFinderMixin;
use Tests\Unit\Mixins\OfficeGroupFinderMixin;
use Tests\Unit\Mixins\OfficeGroupRepositoryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Providers\RequestServiceProvider} Test.
 */
class RequestServiceProviderTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use OfficeFinderMixin;
    use OfficeGroupFinderMixin;
    use OfficeGroupRepositoryMixin;
    use OfficeRepositoryMixin;
    use OrganizationResolverMixin;
    use RoleRepositoryMixin;
    use StaffResolverMixin;
    use UnitSupport;

    /** cf
     *
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachTest(function (RequestServiceProviderTest $self): void {
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_boot(): void
    {
        $this->should('resolve OrganizationRequest', function (): void {
            /** @var \App\Http\Requests\OrganizationRequest $request */
            $request = app(OrganizationRequest::class);

            $this->assertNotNull($request);
            $this->assertEquals(
                new HttpContext(
                    $this->examples->organizations[0],
                    Option::none(),
                    Seq::emptySeq(),
                    'https://eustylelab1.zinger.test/api/',
                    Seq::emptySeq(),
                    Seq::emptySeq(),
                ),
                $request->context()
            );
        });
        $this->should('throw NotFoundException if cannot resolve Organization', function (): void {
            $this->organizationResolver
                ->allows('resolve')
                ->andReturn(Option::none());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    app(OrganizationRequest::class);
                }
            );
        });
        $this->should('resolve StaffRequest', function (): void {
            $staff = $this->examples->staffs[1];
            $this->staffResolver
                ->allows('resolve')
                ->andReturn(Option::from($staff));

            $request = app(StaffRequest::class);

            $this->assertNotNull($request);
        });
        $this->should('throw UnauthorizedException if cannot resolve Staff', function (): void {
            $this->staffResolver
                ->expects('resolve')
                ->andReturn(Option::none());

            $this->assertThrows(
                UnauthorizedException::class,
                function (): void {
                    app(StaffRequest::class);
                }
            );
        });
    }
}
