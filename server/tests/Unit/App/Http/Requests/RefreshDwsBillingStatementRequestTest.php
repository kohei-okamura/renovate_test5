<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\RefreshDwsBillingStatementRequest;
use App\Http\Requests\StaffRequest;
use Domain\Billing\DwsBillingStatus;
use Domain\Common\Pagination;
use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\Project\DwsProjectServiceCategory;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use Faker\Generator;
use Illuminate\Support\Arr;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\FindDwsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\IdentifyDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\SimpleLookupDwsBillingStatementUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\RefreshDwsBillingStatementRequest} のテスト.
 */
final class RefreshDwsBillingStatementRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use SimpleLookupDwsBillingStatementUseCaseMixin;
    use LookupDwsBillingUseCaseMixin;
    use LookupDwsBillingBundleUseCaseMixin;
    use FindDwsProvisionReportUseCaseMixin;
    use IdentifyDwsCertificationUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    protected RefreshDwsBillingStatementRequest $request;

    private Generator $faker;
    /** @var \Domain\Billing\DwsBillingStatement[] */
    private array $billingStatements;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->billingStatements = $self->examples->dwsBillingStatements;
            $self->faker = app(Generator::class);
            $self->request = new RefreshDwsBillingStatementRequest();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::empty(),
            );
            $billing = $self->examples->dwsBillings[0];
            $provisionReports = [
                $self->examples->dwsProvisionReports[0]->copy([
                    'status' => DwsProvisionReportStatus::fixed(),
                    'results' => [
                        $self->examples->dwsProvisionReports[0]->results[0]->copy([
                            'category' => DwsProjectServiceCategory::physicalCare(),
                        ]),
                    ],
                ]),
            ];
            $pagination = Pagination::create(['all' => true]);

            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($billing))
                ->byDefault();
            $self->identifyDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Option::some($self->examples->dwsCertifications[0]))
                ->byDefault();
            $self->simpleLookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->billingStatements[0]->copy(['status' => DwsBillingStatus::ready()])))
                ->byDefault();
            $self->simpleLookupDwsBillingStatementUseCase
                ->allows('handle')
                ->with(
                    anInstanceOf(Context::class),
                    Permission::updateBillings(),
                    $self->billingStatements[1]->id
                )
                ->andReturn(Seq::from($self->billingStatements[1]->copy(['dwsBillingId' => $self->examples->dwsBillings[6]->id])))
                ->byDefault();
            $self->lookupDwsBillingBundleUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillingBundles[0]))
                ->byDefault();
            $self->findDwsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($provisionReports, $pagination))
                ->byDefault();
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
            'when ids is empty ' => [
                ['ids' => ['入力してください。']],
                ['ids' => []],
                ['ids' => [$this->billingStatements[0]->id]],
            ],
            'when ids is not array ' => [
                ['ids' => ['配列にしてください。']],
                ['ids' => 'error'],
                ['ids' => [$this->billingStatements[0]->id]],
            ],
            'when statements with the ids can not be refreshed' => [
                ['ids' => ['再作成ができない明細書が含まれています。']],
                ['ids' => [$this->billingStatements[1]->id]],
                ['ids' => [$this->billingStatements[0]->id]],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $input = $this->defaultInput();
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
     * リクエストクラスが受け取る入力のデフォルト値.
     *
     * @return array
     */
    private function defaultInput(): array
    {
        return [
            'billingId' => $this->billingStatements[0]->dwsBillingId,
            'ids' => [$this->billingStatements[0]->id],
        ];
    }
}
