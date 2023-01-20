<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\DeleteOfficeGroupRequest;
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
use Tests\Unit\Mixins\FindOfficeGroupUseCaseMixin;
use Tests\Unit\Mixins\FindOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\DeleteOfficeGroupRequest} のテスト.
 */
class DeleteOfficeGroupRequestTest extends Test
{
    use ConfigMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use FindOfficeUseCaseMixin;
    use FindOfficeGroupUseCaseMixin;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use RoleRepositoryMixin;
    use StaffResolverMixin;
    use UnitSupport;

    protected DeleteOfficeGroupRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (DeleteOfficeGroupRequestTest $self): void {
            $self->findOfficeUseCase
                ->allows('handle')
                ->andReturn(FinderResult::create([
                    'list' => Seq::emptySeq(),
                    'pagination' => Pagination::create(),
                ]))
                ->byDefault();
            $self->findOfficeUseCase
                ->allows('handle')
                ->with(
                    anInstanceOf(Context::class),
                    [Permission::deleteOfficeGroups()],
                    equalTo(['officeGroupIds' => Seq::fromArray([$self->examples->officeGroups[1]->id])->toArray()]),
                    equalTo(['all' => true])
                )
                ->andReturn(FinderResult::create([
                    'list' => Seq::fromArray([$self->examples->offices[0]]),
                    'pagination' => Pagination::create(),
                ]))
                ->byDefault();
            $self->findOfficeGroupUseCase
                ->allows('handle')
                ->andReturn(FinderResult::create([
                    'list' => Seq::emptySeq(),
                    'pagination' => Pagination::create(),
                ]))
                ->byDefault();
            $self->findOfficeGroupUseCase
                ->allows('handle')
                ->with(
                    anInstanceOf(Context::class),
                    equalTo(['parentOfficeGroupIds' => Seq::fromArray([$self->examples->officeGroups[2]->id])->toArray()]),
                    equalTo(['all' => true])
                )
                ->andReturn(FinderResult::create([
                    'list' => Seq::fromArray([$self->examples->offices[0]]),
                    'pagination' => Pagination::create(),
                ]));

            $self->request = new DeleteOfficeGroupRequest(); // Parameter必須なのでnewする

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
            'when id is related to some offices' => [
                ['id' => ['指定した事業所グループに紐づく事業所が存在しています。']],
                ['id' => $this->examples->officeGroups[1]->id],
                ['id' => $this->examples->officeGroups[0]->id],
            ],
            'when id is parent of some office-groups' => [
                ['id' => ['指定した事業所グループを親とする事業所グループが存在しています。']],
                ['id' => $this->examples->officeGroups[2]->id],
                ['id' => $this->examples->officeGroups[0]->id],
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
            'id' => $this->examples->officeGroups[0]->id,
        ];
    }
}
