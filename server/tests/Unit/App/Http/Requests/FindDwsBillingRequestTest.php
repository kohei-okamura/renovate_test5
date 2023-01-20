<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\FindDwsBillingRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Billing\DwsBillingStatus;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\FindDwsBillingRequest} Test.
 */
class FindDwsBillingRequestTest extends Test
{
    use ConfigMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LookupOfficeUseCaseMixin;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use UnitSupport;

    public const PAGINATION_PARAMS = [
        'sortBy' => 'date',
        'all' => true,
        'desc' => true,
        'itemsPerPage' => 10,
        'page' => 2,
    ];

    private FindDwsBillingRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindDwsBillingRequestTest $self): void {
            $self->request = (new FindDwsBillingRequest())->replace($self->input());
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            $roles = $self->examples->roles[0]->copy([
                'isSystemAdmin' => false,
                'permissions' => [Permission::viewStaffs(), Permission::createStaffs()],
            ]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::from($roles),
                Seq::fromArray($self->examples->offices),
                Seq::empty(),
            );
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), [Permission::listBillings()], self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty());
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_filterParams(): void
    {
        $this->should('return an array of specified filter params', function (): void {
            $this->assertEquals($this->filterParams(), $this->request->filterParams());
        });
        $this->should('set carbon params to null when specify empty', function (): void {
            $request = (new FindDwsBillingRequest())->replace(['start' => '', 'end' => '']);
            $filterParams = $request->filterParams();
            $this->assertSame(['start' => null, 'end' => null], $filterParams);
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
            'when start is invalid format' => [
                ['start' => ['正しい日付を入力してください。']],
                ['start' => '2020/01'],
                ['start' => $this->examples->dwsBillings[0]->transactedIn->format('Y-m')],
            ],
            'when end is invalid format' => [
                ['end' => ['正しい日付を入力してください。']],
                ['end' => '2020/01'],
                ['end' => $this->examples->dwsBillings[0]->transactedIn->format('Y-m')],
            ],
            'when statuses is not array ' => [
                ['statuses' => ['配列にしてください。']],
                ['statuses' => 10],
                ['statuses' => [
                    DwsBillingStatus::checking()->value(),
                    DwsBillingStatus::fixed()->value(),
                ]],
            ],
            'when statuses contains invalid elements' => [
                ['statuses.0' => ['障害福祉サービス：請求：状態を指定してください。']],
                ['statuses' => [self::INVALID_ENUM_VALUE]],
                ['statuses' => [DwsBillingStatus::checking()->value()]],
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
        return [
            ...self::PAGINATION_PARAMS,
            ...$this->filterParams(),
            'start' => '2020-01',
            'end' => '2020-01',
            'statuses' => [
                (string)DwsBillingStatus::checking()->value(),
                (string)DwsBillingStatus::fixed()->value(),
            ],
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
            'start' => Carbon::parse('2020-01'),
            'end' => Carbon::parse('2020-01'),
            'statuses' => [DwsBillingStatus::checking(), DwsBillingStatus::fixed()],
            'officeId' => $this->examples->offices[0]->id,
        ];
    }
}
