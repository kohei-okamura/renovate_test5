<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\DeleteDwsProvisionReportRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\ProvisionReport\DwsProvisionReport;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\GetDwsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\DeleteDwsProvisionReportRequest} のテスト.
 */
class DeleteDwsProvisionReportRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use GetDwsProvisionReportUseCaseMixin;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use UnitSupport;

    protected DeleteDwsProvisionReportRequest $request;
    private DwsProvisionReport $dwsProvisionReport;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DeleteDwsProvisionReportRequestTest $self): void {
            $self->dwsProvisionReport = $self->examples->dwsProvisionReports[0];
            $self->getDwsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->dwsProvisionReport))
                ->byDefault();
            $self->request = new DeleteDwsProvisionReportRequest();
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
    public function describe_validation(): void
    {
        $this->should('succeed when the data passes the validation rules', function (): void {
            $validator = $this->request->createValidatorInstance($this->defaultInput());
            $this->assertTrue($validator->passes());
        });
        $this->should(
            'fails when the status is fixed',
            function (): void {
                $input = $this->defaultInput();
                $this->getDwsProvisionReportUseCase
                    ->expects('handle')
                    ->with(
                        anInstanceOf(Context::class),
                        Permission::updateDwsProvisionReports(),
                        $input['officeId'],
                        $input['userId'],
                        equalTo(Carbon::parse($input['providedIn']))
                    )
                    ->andReturn(Option::from($this->examples->dwsProvisionReports[3]));
                $validator = $this->request->createValidatorInstance($input);
                $this->assertTrue($validator->fails());
                $this->assertSame(['plans' => ['確定済みの予実は編集できません。']], $validator->errors()->toArray());
            },
        );
    }

    /**
     * リクエストクラスが受け取る入力のデフォルト値.
     *
     * @return array
     */
    private function defaultInput(): array
    {
        $dwsProvisionReport = $this->examples->dwsProvisionReports[0];
        return [
            // ルートパラメーター
            'officeId' => $dwsProvisionReport->officeId,
            'userId' => $dwsProvisionReport->userId,
            'providedIn' => $dwsProvisionReport->providedIn->format('Y-m'),
        ];
    }
}
