<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\CreateOfficeGroupRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Context\Context;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupOfficeGroupUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * CreateOfficeGroupRequest のテスト
 */
class CreateOfficeGroupRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupOfficeGroupUseCaseMixin;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use StaffResolverMixin;
    use UnitSupport;

    protected CreateOfficeGroupRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateOfficeGroupRequestTest $self): void {
            $self->request = new CreateOfficeGroupRequest();
            $self->lookupOfficeGroupUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), $self->examples->officeGroups[0]->id)
                ->andReturn(Seq::from($self->examples->officeGroups[0]));
            $self->lookupOfficeGroupUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());
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
            'when unknown parentOfficeGroupId given' => [
                ['parentOfficeGroupId' => ['正しい値を入力してください。']],
                ['parentOfficeGroupId' => self::NOT_EXISTING_ID],
                ['parentOfficeGroupId' => $this->examples->officeGroups[0]->id],
            ],
            'when name is empty' => [
                ['name' => ['入力してください。']],
                ['name' => ''],
            ],
            'when name is longer than 100' => [
                ['name' => ['100文字以内で入力してください。']],
                ['name' => str_repeat('山', 101)],
                ['name' => str_repeat('山', 100)],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $validator = $this->request->createValidatorInstance($invalid + $this->defaultInput());
                $this->assertTrue($validator->fails());
                $this->assertSame($validator->errors()->toArray(), $expected);
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
            'parentOfficeGroupId' => $this->examples->officeGroups[0]->id,
            'name' => '北海道ブロック',
        ];
    }
}
