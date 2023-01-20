<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\FindPermissionGroupRequest;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Test;

/**
 * FindPermissionGroupRequest のテスト.
 */
class FindPermissionGroupRequestTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use UnitSupport;

    public const FILTER_PARAMS = [];

    public const PAGINATION_PARAMS = [
        'all' => true,
    ];

    private FindPermissionGroupRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindPermissionGroupRequestTest $self): void {
            $self->organizationRepository
                ->allows('lookupOptionByCode')
                ->andReturn(Option::from($self->context->organization))
                ->byDefault();
            $self->request = (new FindPermissionGroupRequest())->replace($self->input());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_filterParams(): void
    {
        $this->should('return an array of empty filter params', function (): void {
            $this->assertSame(self::FILTER_PARAMS, $this->request->filterParams());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_paginationParams(): void
    {
        $this->should('return an array of pagination params', function (): void {
            $this->assertSame(self::PAGINATION_PARAMS, $this->request->paginationParams());
        });
    }

    /**
     * リクエストクラスが受け取る入力値.
     *
     * @return array
     */
    private function input(): array
    {
        return self::FILTER_PARAMS + self::PAGINATION_PARAMS;
    }
}
