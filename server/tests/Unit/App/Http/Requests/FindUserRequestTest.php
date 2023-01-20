<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\FindUserRequest;
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
 * FindUserRequest のテスト.
 */
final class FindUserRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupOfficeUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    public const FILTER_PARAMS = [
        'q' => '検索キーワード',
        'officeId' => 1,
        'isEnabled' => true,
    ];

    public const PAGINATION_PARAMS = [
        'sortBy' => 'date',
        'all' => true,
        'desc' => true,
        'itemsPerPage' => 10,
        'page' => 2,
    ];

    private FindUserRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindUserRequestTest $self): void {
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), [Permission::listUsers()], self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty())
                ->byDefault();

            $self->request = (new FindUserRequest())->replace($self->input());
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
        $this->should('return an array of filter params', function (): void {
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
     * @test
     * @return void
     */
    public function describe_validation(): void
    {
        $this->should('succeed when the data passes the validation rules', function (): void {
            $validator = $this->request->createValidatorInstance($this->input());
            $this->assertTrue($validator->passes());
        });

        $examples = [
            'when officeId is not exist' => [
                ['officeId' => ['正しい値を入力してください。']],
                ['officeId' => self::NOT_EXISTING_ID],
                ['officeId' => $this->examples->offices[0]->id],
            ],
            'when isEnabled is not boolean_ext' => [
                ['isEnabled' => ['trueかfalseにしてください。']],
                ['isEnabled' => 'string'],
                ['isEnabled' => 'true'],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $validator = $this->request->createValidatorInstance($invalid + $this->input());
                $this->assertTrue($validator->fails());
                $this->assertSame($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    $validator = $this->request->createValidatorInstance($valid + $this->input());
                    $this->assertTrue($validator->passes(), $validator->errors()->toJson(\JSON_UNESCAPED_UNICODE));
                    $this->assertSame([], $validator->errors()->toArray());
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
