<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\FindLtcsProvisionReportRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\FindLtcsProvisionReportRequest} のテスト.
 */
class FindLtcsProvisionReportRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupOfficeUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    public const PAGINATION_PARAMS = [
        'all' => true,
        'itemsPerPage' => 10,
        'page' => 1,
    ];

    private FindLtcsProvisionReportRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindLtcsProvisionReportRequestTest $self): void {
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), [Permission::listLtcsProvisionReports()], self::NOT_EXISTING_ID)
                ->andReturn(Seq::empty())
                ->byDefault();

            $self->request = (new FindLtcsProvisionReportRequest())->replace($self->input());
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
            $this->assertEquals($this->filterParams(), $this->request->filterParams());
        });
        $this->should('set carbon params to null when specify empty', function (): void {
            $request = (new FindLtcsProvisionReportRequest())->replace(['providedIn' => '']);
            $filterParams = $request->filterParams();
            $this->assertSame(['providedIn' => null], $filterParams);
        });
        $this->should('return an array of allowed filter params only', function (): void {
            $notAllowedFilterParams = ['name' => '太郎'];
            $request = (new FindLtcsProvisionReportRequest())->replace($notAllowedFilterParams + $this->input());

            $this->assertEquals($this->filterParams(), $request->filterParams());
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
            'when officeId is empty' => [
                ['officeId' => ['入力してください。']],
                ['officeId' => ''],
                ['officeId' => $this->examples->ltcsProvisionReports[0]->officeId],
            ],
            'when unknown officeId given' => [
                ['officeId' => ['正しい値を入力してください。']],
                ['officeId' => self::NOT_EXISTING_ID],
                ['officeId' => $this->examples->ltcsProvisionReports[0]->officeId],
            ],
            'when providedIn is empty' => [
                ['providedIn' => ['入力してください。']],
                ['providedIn' => ''],
                ['providedIn' => $this->examples->ltcsProvisionReports[0]->providedIn->format('Y-m')],
            ],
            'when providedIn is invalid date format' => [
                ['providedIn' => ['正しい日付を入力してください。']],
                ['providedIn' => '2020-10-10'],
                ['providedIn' => $this->examples->ltcsProvisionReports[0]->providedIn->format('Y-m')],
            ],
            'when an invalid status is given' => [
                ['status' => ['介護保険サービス：予実：状態を指定してください。']],
                ['status' => self::INVALID_ENUM_VALUE],
                ['status' => LtcsProvisionReportStatus::inProgress()->value()],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $input = $this->input();
                foreach ($invalid as $key => $value) {
                    Arr::set($input, $key, $value);
                }
                $validator = $this->request->createValidatorInstance($invalid + $input);
                $this->assertTrue($validator->fails());
                $this->assertEquals($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    foreach ($valid as $key => $value) {
                        Arr::set($input, $key, $value);
                    }
                    $validator = $this->request->createValidatorInstance($valid + $input);
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
            'providedIn' => '2020-10',
            'status' => (string)LtcsProvisionReportStatus::inProgress()->value(),
        ] + $this->filterParams() + self::PAGINATION_PARAMS;
    }

    /**
     * filterParams() が返す期待値.
     *
     * @return array
     */
    private function filterParams(): array
    {
        return [
            'officeId' => 1,
            'providedIn' => Carbon::parse('2020-10'),
            'status' => LtcsProvisionReportStatus::inProgress(),
            'q' => ',',
        ];
    }
}
