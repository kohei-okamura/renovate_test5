<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\FindDwsAreaGradeRequest;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * FindDwsAreaGradeRequest のテスト.
 */
class FindDwsAreaGradeRequestTest extends Test
{
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    public const FILTER_PARAMS = [];
    public const PAGINATION_PARAMS = [
        'sortBy' => 'id',
        'all' => true,
        'desc' => true,
        'itemsPerPage' => 10,
        'page' => 2,
    ];

    private FindDwsAreaGradeRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindDwsAreaGradeRequestTest $self): void {
            $self->request = (new FindDwsAreaGradeRequest())->replace($self->input());
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
