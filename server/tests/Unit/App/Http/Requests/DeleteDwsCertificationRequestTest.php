<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\DeleteDwsCertificationRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Common\Pagination;
use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingStatementFinderMixin;
use Tests\Unit\Mixins\LookupDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\DeleteDwsCertificationRequest} のテスト.
 */
class DeleteDwsCertificationRequestTest extends Test
{
    use ConfigMixin;
    use ContextMixin;
    use DwsBillingStatementFinderMixin;
    use LookupDwsCertificationUseCaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use StaffResolverMixin;
    use UnitSupport;

    private const DWS_CERTIFICATION_ID_BELONG_TO_BILLING = 3;

    protected DeleteDwsCertificationRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DeleteDwsCertificationRequestTest $self): void {
            $self->dwsBillingStatementFinder
                ->allows('find')
                ->andReturn(FinderResult::from([], Pagination::create()))
                ->byDefault();
            $self->dwsBillingStatementFinder
                ->allows('find')
                ->with(['dwsCertificationId' => self::DWS_CERTIFICATION_ID_BELONG_TO_BILLING], \Mockery::any())
                ->andReturn(FinderResult::from($self->examples->dwsBillingStatements, Pagination::create()))
                ->byDefault();
            $self->lookupDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsCertifications[0]))
                ->byDefault();
            $self->lookupDwsCertificationUseCase
                ->allows('handle')
                ->with(
                    anInstanceOf(Context::class),
                    Permission::deleteDwsCertifications(),
                    $self->examples->dwsCertifications[2]->userId,
                    self::DWS_CERTIFICATION_ID_BELONG_TO_BILLING
                )
                ->andReturn(Seq::from($self->examples->dwsCertifications[2]))
                ->byDefault();

            $self->request = new DeleteDwsCertificationRequest();

            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::emptySeq(),
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

        $examples = [
            'when id belongs to billing' => [
                ['id' => ['指定した障害福祉サービス受給者証に紐づく請求情報が存在しています。']],
                ['id' => self::DWS_CERTIFICATION_ID_BELONG_TO_BILLING, 'userId' => $this->examples->dwsCertifications[2]->userId],
                ['id' => $this->examples->dwsCertifications[0]->id, 'userId' => $this->examples->dwsCertifications[0]->userId],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $validator = $this->request->createValidatorInstance($invalid + $this->defaultInput());
                $this->assertTrue($validator->fails());
                $this->assertSame($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    $validator = $this->request->createValidatorInstance($valid + $this->defaultInput());
                    $this->assertTrue($validator->passes());
                }
            },
            compact('examples')
        );
    }

    /**
     * リクエストクラスが受け取る入力のデフォルト値.
     *
     * @return array
     */
    private function defaultInput(): array
    {
        return [
            'id' => $this->examples->dwsCertifications[0]->id,
            'userId' => $this->examples->dwsCertifications[0]->userId,
        ];
    }
}
