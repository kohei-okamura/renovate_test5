<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\FindDwsProvisionReportRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Mixins\SessionMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\FindDwsProvisionReportRequest} のテスト.
 */
class FindDwsProvisionReportRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LookupOfficeUseCaseMixin;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use SessionMixin;
    use UnitSupport;

    public const PAGINATION_PARAMS = [
        'all' => true,
        'itemsPerPage' => 10,
        'page' => 1,
    ];

    private FindDwsProvisionReportRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindDwsProvisionReportRequestTest $self): void {
            $self->request = (new FindDwsProvisionReportRequest())->replace($self->input());
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
            $self->session
                ->allows('get')
                ->with('staffId')
                ->andReturn($self->examples->roles[0]->id);
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), [Permission::listDwsProvisionReports()], self::NOT_EXISTING_ID)
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
            $request = (new FindDwsProvisionReportRequest())->replace(['providedIn' => '']);
            $filterParams = $request->filterParams();
            $this->assertSame(['providedIn' => null], $filterParams);
        });
        $this->should('return an array of allowed filter params only', function (): void {
            $notAllowedFilterParams = ['name' => '太郎'];
            $request = (new FindDwsProvisionReportRequest())->replace($notAllowedFilterParams + $this->input());

            $this->assertEquals($this->filterParams(), $request->filterParams());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validation(): void
    {
        $examples = [
            'when officeId is empty' => [
                ['officeId' => ['入力してください。']],
                ['officeId' => ''],
                ['officeId' => $this->examples->offices[0]->id],
            ],
            'when officeId is not exist' => [
                ['officeId' => ['正しい値を入力してください。']],
                ['officeId' => self::NOT_EXISTING_ID],
                ['officeId' => $this->examples->offices[0]->id],
            ],
            'when providedIn is empty' => [
                ['providedIn' => ['入力してください。']],
                ['providedIn' => ''],
                ['providedIn' => $this->examples->dwsProvisionReports[0]->providedIn->format('Y-m')],
            ],
            'when providedIn is invalid format' => [
                ['providedIn' => ['正しい日付を入力してください。']],
                ['providedIn' => '2020/01'],
                ['providedIn' => $this->examples->dwsProvisionReports[0]->providedIn->format('Y-m')],
            ],
            'when an invalid status is given' => [
                ['status' => ['障害福祉サービス：予実：状態を指定してください。']],
                ['status' => self::INVALID_ENUM_VALUE],
                ['status' => DwsProvisionReportStatus::inProgress()->value()],
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
            'providedIn' => '2020-10',
            'status' => (string)DwsProvisionReportStatus::inProgress()->value(),
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
            'status' => DwsProvisionReportStatus::inProgress(),
            'q' => ',',
        ];
    }
}
