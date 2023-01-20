<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\FindOwnExpenseProgramRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link FindOwnExpenseProgramRequest} のテスト.
 */
class FindOwnExpenseProgramRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupOfficeUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    public const FILTER_PARAMS = [
        'officeId' => 1,
    ];

    public const PAGINATION_PARAMS = [
        'sortBy' => 'id',
        'all' => true,
        'desc' => true,
        'itemsPerPage' => 10,
        'page' => 2,
    ];

    private FindOwnExpenseProgramRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindOwnExpenseProgramRequestTest $self): void {
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), [Permission::listOwnExpensePrograms()], self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty())
                ->byDefault();

            $self->request = (new FindOwnExpenseProgramRequest())->replace($self->input());
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::empty(),
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_filterParams(): void
    {
        $this->should('return an array of specified filter params', function (): void {
            $this->assertSame(self::FILTER_PARAMS, $this->request->filterParams());
        });
        $this->should('return an array of allowed filter params only', function (): void {
            $notAllowedFilterParams = ['name' => '太郎'];
            $request = (new FindOwnExpenseProgramRequest())->replace($notAllowedFilterParams + $this->input());

            $this->assertSame(self::FILTER_PARAMS, $request->filterParams());
        });
        $this->should('replace key name when specify key pairs', function (): void {
            $this->assertSame(['officeIdOrNull' => 1], $this->request->filterParams(['officeId' => 'officeIdOrNull']));
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
     * @test
     * @return void
     */
    public function describe_validation(): void
    {
        $examples = [
            'when officeId is not exist' => [
                ['officeId' => ['正しい値を入力してください。']],
                ['officeId' => self::NOT_EXISTING_ID],
                ['officeId' => $this->examples->offices[0]->id],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $validator = $this->request->createValidatorInstance($invalid + $this->input());
                $this->assertTrue($validator->fails());
                $this->assertSame($validator->errors()->toArray(), $expected);
                if ($valid !== null) {
                    $validator = $this->request->createValidatorInstance($valid + $this->input());
                    $this->assertTrue($validator->passes());
                }
            },
            compact('examples')
        );
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
