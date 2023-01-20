<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\DeleteLtcsProvisionReportRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\ProvisionReport\LtcsProvisionReport;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\GetLtcsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\DeleteLtcsProvisionReportRequest} のテスト.
 */
class DeleteLtcsProvisionReportRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use GetLtcsProvisionReportUseCaseMixin;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use UnitSupport;

    protected DeleteLtcsProvisionReportRequest $request;
    private LtcsProvisionReport $ltcsProvisionReport;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DeleteLtcsProvisionReportRequestTest $self): void {
            $self->ltcsProvisionReport = $self->examples->ltcsProvisionReports[0];
            $self->getLtcsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->ltcsProvisionReport))
                ->byDefault();
            $self->request = new DeleteLtcsProvisionReportRequest();
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
                $this->getLtcsProvisionReportUseCase
                    ->expects('handle')
                    ->with(
                        anInstanceOf(Context::class),
                        Permission::updateLtcsProvisionReports(),
                        $input['officeId'],
                        $input['userId'],
                        equalTo(Carbon::parse($input['providedIn']))
                    )
                    ->andReturn(Option::from($this->examples->ltcsProvisionReports[3]));
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
        $ltcsProvisionReport = $this->examples->ltcsProvisionReports[0];
        return [
            // ルートパラメーター
            'officeId' => $ltcsProvisionReport->officeId,
            'userId' => $ltcsProvisionReport->userId,
            'providedIn' => $ltcsProvisionReport->providedIn->format('Y-m'),
        ];
    }
}
