<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Controllers;

use App\Http\Controllers\LtcsHomeVisitLongTermCareDictionaryEntryController;
use App\Http\Requests\FindLtcsHomeVisitLongTermCareDictionaryEntryRequest;
use App\Http\Requests\GetLtcsHomeVisitLongTermCareDictionaryEntryRequest;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Illuminate\Http\Request as LumenRequest;
use Illuminate\Http\Response;
use Lib\Exceptions\NotFoundException;
use Lib\Json;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GetIndexLtcsHomeVisitLongTermCareDictionaryEntryUseCaseMixin;
use Tests\Unit\Mixins\GetLtcsHomeVisitLongTermCareDictionaryEntryUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RequestMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * LtcsHomeVisitLongTermCareDictionaryEntryController のテスト.
 */
class LtcsHomeVisitLongTermCareDictionaryEntryControllerTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use GetIndexLtcsHomeVisitLongTermCareDictionaryEntryUseCaseMixin;
    use GetLtcsHomeVisitLongTermCareDictionaryEntryUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use RequestMixin;
    use StaffResolverMixin;
    use UnitSupport;

    public const PAGINATION_PARAMS = [];
    private FinderResult $finderResult;
    private LtcsHomeVisitLongTermCareDictionaryEntryController $controller;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LtcsHomeVisitLongTermCareDictionaryEntryControllerTest $self): void {
            $self->finderResult = FinderResult::from(
                $self->examples->ltcsHomeVisitLongTermCareDictionaryEntries,
                Pagination::create()
            );
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->getIndexLtcsHomeVisitLongTermCareDictionaryEntryUseCase
                ->allows('handle')
                ->andReturn($self->finderResult)
                ->byDefault();
            $self->getLtcsHomeVisitLongTermCareDictionaryEntryUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0]))
                ->byDefault();
            $self->controller = app(LtcsHomeVisitLongTermCareDictionaryEntryController::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_getIndex(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/ltcs-home-visit-long-term-care-dictionary',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode($this->filterParams() + self::PAGINATION_PARAMS)
        ));
        app()->bind(FindLtcsHomeVisitLongTermCareDictionaryEntryRequest::class, function () {
            $request = Mockery::mock(FindLtcsHomeVisitLongTermCareDictionaryEntryRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            $request->allows('filterParams')->andReturn($this->filterParams())->byDefault();
            $request->allows('paginationParams')->andReturn(self::PAGINATION_PARAMS)->byDefault();
            return $request;
        });
        $this->should('return a 200 response', function (): void {
            $this->assertSame(
                Response::HTTP_OK,
                app()->call([$this->controller, 'getIndex'])->getStatusCode()
            );
        });
        $this->should('return a JSON of FinderResult', function (): void {
            $this->assertSame(
                $this->finderResult->toJson(),
                app()->call([$this->controller, 'getIndex'])->getContent()
            );
        });
        $this->should('find LtcsHomeVisitLongTermCareDictionaryEntries using use case', function (): void {
            $this->getIndexLtcsHomeVisitLongTermCareDictionaryEntryUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->filterParams()
                )
                ->andReturn($this->finderResult);
            app()->call([$this->controller, 'getIndex']);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_get(): void
    {
        app()->bind('request', fn () => LumenRequest::create(
            '/api/ltcs-home-visit-long-term-care-dictionary-entries/{serviceCode}',
            'GET',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            Json::encode(['providedIn' => '2021-10'])
        ));
        app()->bind(GetLtcsHomeVisitLongTermCareDictionaryEntryRequest::class, function () {
            $request = Mockery::mock(GetLtcsHomeVisitLongTermCareDictionaryEntryRequest::class)->makePartial();
            $request->allows('context')->andReturn($this->context)->byDefault();
            return $request;
        });
        $this->should('get LtcsHomeVisitLongTermCareDictionaryEntry using use case', function (): void {
            $this->getLtcsHomeVisitLongTermCareDictionaryEntryUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    '111111',
                    equalTo(Carbon::parse('2021-10'))
                )
                ->andReturn(Option::from($this->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0]));
            app()->call(
                [$this->controller, 'get'],
                ['serviceCode' => '111111']
            );
        });
        $this->should('return a 200 response', function (): void {
            $actual = app()->call(
                [$this->controller, 'get'],
                ['serviceCode' => '111111']
            );

            $this->assertSame(Response::HTTP_OK, $actual->getStatusCode());
        });
        $this->should('return a JSON of dictionaryEntry', function (): void {
            $expected = Json::encode(['dictionaryEntry' => $this->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0]]);

            $actual = app()->call(
                [$this->controller, 'get'],
                ['serviceCode' => '111111']
            );

            $this->assertSame($expected, $actual->getContent());
        });
        $this->should(
            'throw NotFoundException when getLtcsHomeVisitLongTermCareDictionaryEntryUseCase return None',
            function (): void {
                $this->getLtcsHomeVisitLongTermCareDictionaryEntryUseCase
                    ->expects('handle')
                    ->andReturn(Option::none());
                $this->assertThrows(
                    NotFoundException::class,
                    function (): void {
                        app()->call(
                            [$this->controller, 'get'],
                            ['serviceCode' => '111111']
                        );
                    }
                );
            }
        );
    }

    /**
     * フィルターパラメーターを返す.
     *
     * @return array
     */
    private function filterParams(): array
    {
        return [
            'officeId' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->officeId,
            'isEffectiveOn' => $this->examples->ltcsHomeVisitLongTermCareDictionaries[0]->effectivatedOn->format('Y-m-d'),
        ];
    }
}
