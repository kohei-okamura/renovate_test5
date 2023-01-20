<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Resolvers;

use App\Resolvers\OrganizationResolver;
use App\Resolvers\OrganizationResolverImpl;
use Domain\Common\Pagination;
use Domain\FinderResult;
use ScalikePHP\None;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\LumenRequestMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationFinderMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Test;

/**
 * OrganizationResolver のテスト.
 */
class OrganizationResolverImplTest extends Test
{
    use ExamplesConsumer;
    use MockeryMixin;
    use OrganizationFinderMixin;
    use OrganizationRepositoryMixin;
    use OrganizationResolverMixin;
    use LumenRequestMixin;
    use UnitSupport;

    protected OrganizationResolver $resolver;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (OrganizationResolverImplTest $self): void {
            $self->lumenRequest
                ->allows('getHttpHost')
                ->andReturn('example.zinger.test')
                ->byDefault();
            $self->organizationRepository
                ->allows('lookupOptionByCode')
                ->andReturn(Option::from($self->examples->organizations[0]))
                ->byDefault();
            $self->organizationFinder
                ->allows('find')
                ->andReturn(FinderResult::from([$self->examples->organizations[0]], Pagination::create()))
                ->byDefault();

            $self->resolver = app(OrganizationResolverImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_resolve(): void
    {
        $this->should('return an option of organization', function (): void {
            $actual = $this->resolver->resolve($this->lumenRequest);

            $this->assertInstanceOf(Option::class, $actual);
            $this->assertSame($this->examples->organizations[0], $actual->get());
        });
        $this->should('return an none when organization not exists', function (): void {
            $this->organizationRepository
                ->allows('lookupOptionByCode')
                ->andReturn(Option::none());

            $this->assertInstanceOf(None::class, $this->resolver->resolve($this->lumenRequest));
        });
        $this->should('get an organization using a code taken from request', function (): void {
            $this->lumenRequest
                ->expects('getHttpHost')
                ->andReturn('eustylelab.zinger.test');
            $this->organizationRepository
                ->expects('lookupOptionByCode')
                ->with('eustylelab')
                ->andReturn(Option::from($this->examples->organizations[0]));

            $this->resolver->resolve($this->lumenRequest);
        });
    }
}
