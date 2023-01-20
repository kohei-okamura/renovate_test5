<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\RefreshLtcsBillingStatementRequest;
use App\Http\Requests\StaffRequest;
use Domain\Billing\LtcsBillingStatus;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\Context\Context;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use Faker\Generator;
use Illuminate\Support\Arr;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\FindLtcsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\LookupLtcsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\LookupLtcsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\SimpleLookupLtcsBillingStatementUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\RefreshLtcsBillingStatementRequest} のテスト.
 */
final class RefreshLtcsBillingStatementRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use SimpleLookupLtcsBillingStatementUseCaseMixin;
    use LookupLtcsBillingUseCaseMixin;
    use LookupLtcsBillingBundleUseCaseMixin;
    use FindLtcsProvisionReportUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    protected RefreshLtcsBillingStatementRequest $request;

    private Generator $faker;
    /** @var \Domain\Billing\LtcsBillingStatement[] */
    private array $billingStatements;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->billingStatements = $self->examples->ltcsBillingStatements;
            $self->faker = app(Generator::class);
            $self->request = new RefreshLtcsBillingStatementRequest();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::empty(),
            );
            $billing = $self->examples->ltcsBillings[0];
            $provisionReports = [
                $self->examples->ltcsProvisionReports[0]->copy([
                    'status' => LtcsProvisionReportStatus::fixed(),
                    'entries' => [
                        $self->examples->ltcsProvisionReports[0]->entries[0]->copy([
                            'category' => LtcsProjectServiceCategory::physicalCare(),
                            'results' => [Carbon::parse('2020-10-13')],
                        ]),
                    ],
                ]),
            ];
            $pagination = Pagination::create(['all' => true]);

            $self->lookupLtcsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($billing))
                ->byDefault();
            $self->simpleLookupLtcsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->billingStatements[0]->copy(['status' => LtcsBillingStatus::ready()])))
                ->byDefault();
            $self->simpleLookupLtcsBillingStatementUseCase
                ->allows('handle')
                ->with(
                    anInstanceOf(Context::class),
                    Permission::updateBillings(),
                    $self->billingStatements[1]->id
                )
                ->andReturn(Seq::from($self->billingStatements[1]->copy(['status' => LtcsBillingStatus::fixed()])))
                ->byDefault();
            $self->lookupLtcsBillingBundleUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ltcsBillingBundles[0]))
                ->byDefault();
            $self->findLtcsProvisionReportUseCase
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
            'billingId' => $this->billingStatements[0]->billingId,
            'ids' => [$this->billingStatements[0]->id],
        ];
    }
}
