<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\DeleteLtcsInsCardRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Common\Pagination;
use Domain\Context\Context;
use Domain\FinderResult;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupLtcsInsCardUseCaseMixin;
use Tests\Unit\Mixins\LtcsBillingStatementFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\DeleteLtcsInsCardRequest} のテスト.
 */
class DeleteLtcsInsCardRequestTest extends Test
{
    use ConfigMixin;
    use ContextMixin;
    use LtcsBillingStatementFinderMixin;
    use LookupLtcsInsCardUseCaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use StaffResolverMixin;
    use UnitSupport;

    private const USER_ID_NOT_BELONG_TO_BILLING = 2;
    protected DeleteLtcsInsCardRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DeleteLtcsInsCardRequestTest $self): void {
            $self->ltcsBillingStatementFinder
                ->allows('find')
                ->andReturn(FinderResult::from($self->examples->ltcsBillingStatements, Pagination::create()))
                ->byDefault();
            $self->ltcsBillingStatementFinder
                ->allows('find')
                ->with(['userId' => self::USER_ID_NOT_BELONG_TO_BILLING], \Mockery::any())
                ->andReturn(FinderResult::from([], Pagination::create()))
                ->byDefault();
            $self->lookupLtcsInsCardUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->ltcsInsCards[0]))
                ->byDefault();
            $self->lookupLtcsInsCardUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), $self->examples->ltcsInsCards[0]->userId, self::NOT_EXISTING_ID)
                ->andReturn(Seq::from($self->examples->ltcsInsCards[0]))
                ->byDefault();

            $self->request = new DeleteLtcsInsCardRequest();

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
                ['id' => ['指定した介護保険被保険者証に紐づく請求情報が存在しています。']],
                ['id' => $this->examples->ltcsInsCards[0]->id, 'userId' => $this->examples->ltcsInsCards[0]->userId],
                ['id' => $this->examples->ltcsInsCards[0]->id, 'userId' => self::USER_ID_NOT_BELONG_TO_BILLING],
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
            'id' => $this->examples->ltcsInsCards[0]->id,
            'userId' => self::USER_ID_NOT_BELONG_TO_BILLING,
        ];
    }
}
