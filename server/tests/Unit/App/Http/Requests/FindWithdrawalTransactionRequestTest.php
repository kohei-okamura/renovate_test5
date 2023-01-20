<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\FindWithdrawalTransactionRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Common\Carbon;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\FindWithdrawalTransactionRequest} のテスト.
 */
final class FindWithdrawalTransactionRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    public const PAGINATION_PARAMS = [
        'sortBy' => 'id',
        'all' => true,
        'desc' => true,
        'itemsPerPage' => 10,
        'page' => 2,
    ];

    private FindWithdrawalTransactionRequest $request;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->request = (new FindWithdrawalTransactionRequest())->replace($self->input());
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
            $this->assertEquals(
                [
                    'start' => Carbon::parse('2020-04-01'),
                    'end' => Carbon::parse('2020-05-01'),
                ],
                $this->request->filterParams()
            );
        });
        $this->should('return an array of allowed filter params only', function (): void {
            $notAllowedFilterParams = ['name' => '太郎'];
            $request = (new FindWithdrawalTransactionRequest())->replace($notAllowedFilterParams + $this->input());

            $this->assertEquals(
                [
                    'start' => Carbon::parse('2020-04-01'),
                    'end' => Carbon::parse('2020-05-01'),
                ],
                $request->filterParams()
            );
        });
        $this->should('replace key name when specify key pairs', function (): void {
            $this->assertEquals(
                [
                    'begin' => Carbon::parse('2020-04-01'),
                    'end' => Carbon::parse('2020-05-01'),
                ],
                $this->request->filterParams(['start' => 'begin'])
            );
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
            'when start is not date' => [
                ['start' => ['正しい日付を入力してください。']],
                ['start' => 'date'],
                ['start' => '2020-04-01'],
            ],
            'when end is not date' => [
                ['end' => ['正しい日付を入力してください。']],
                ['end' => 'date'],
                ['end' => '2020-05-01'],
            ],
            'when end is before start' => [
                ['end' => ['作成日（開始）以降の日付を入力してください。']],
                ['start' => '2020-04-02', 'end' => '2020-04-01'],
                ['start' => '2020-04-01', 'end' => '2020-04-01'],
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
        return [
            ...self::PAGINATION_PARAMS,
            ...$this->filterParams(),
            'start' => '2020-04-01',
            'end' => '2020-05-01',
        ];
    }

    /**
     * filterParams() が返す期待値.
     *
     * @return array
     */
    private function filterParams(): array
    {
        return [
            'start' => Carbon::parse('2020-04-01'),
            'end' => Carbon::parse('2020-05-01'),
        ];
    }
}
