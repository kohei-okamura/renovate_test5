<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\CreateLtcsContractRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Closure;
use Domain\Common\ServiceSegment;
use Domain\Context\Context;
use Domain\Contract\Contract;
use Domain\Permission\Permission;
use Generator;
use Lib\Json;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\FindContractUseCaseMixin;
use Tests\Unit\Mixins\GetOverlapContractUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\CreateLtcsContractRequest} のテスト.
 */
final class CreateLtcsContractRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use FindContractUseCaseMixin;
    use GetOverlapContractUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private Contract $contract;
    private CreateLtcsContractRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->contract = $self->examples->contracts[0]->copy([
                'organizationId' => $self->examples->organizations[0]->id,
            ]);
        });
        self::beforeEachSpec(function (self $self): void {
            $self->getOverlapContractUseCase
                ->allows('handle')
                ->andReturn(Seq::emptySeq())
                ->byDefault();
            $self->getOverlapContractUseCase
                ->allows('handle')
                ->with(
                    anInstanceOf(Context::class),
                    Permission::createLtcsContracts(),
                    $self->examples->users[1]->id,
                    $self->contract->officeId,
                    ServiceSegment::longTermCare(),
                )
                ->andReturn(Seq::from([$self->contract]))
                ->byDefault();

            $self->lookupOfficeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), [Permission::createLtcsContracts()], $self->examples->offices[0]->id)
                ->andReturn(Seq::from($self->examples->offices[0]));
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), [Permission::createLtcsContracts()], self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $self->request = new CreateLtcsContractRequest();
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
    public function describe_payload(): void
    {
        $examples = [
            'all elements given' => [
                $this->createInput(),
            ],
            'without note' => [
                $this->createInput(function (array &$input): void {
                    unset($input['note']);
                }),
            ],
        ];
        $this->should(
            'return array',
            function (array $input): void {
                $this->request->initialize(
                    [],
                    [],
                    [],
                    [],
                    [],
                    ['CONTENT_TYPE' => 'application/json'],
                    Json::encode($input)
                );

                $actual = $this->request->payload();

                $this->assertMatchesModelSnapshot($actual);
            },
            compact('examples')
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_validation(): void
    {
        $this->should(
            'pass',
            function (?Closure $f = null): void {
                $validator = $this->request->createValidatorInstance($this->createInput($f));
                $this->assertTrue($validator->passes());
            },
            ['examples' => iterator_to_array($this->passes())]
        );
        $this->should(
            'fail',
            function (?Closure $f, array $errors): void {
                $validator = $this->request->createValidatorInstance($this->createInput($f));
                $this->assertTrue($validator->fails());
                $this->assertSame($errors, $validator->errors()->toArray());
            },
            ['examples' => iterator_to_array($this->fails())]
        );
    }

    /**
     * 入力値を生成する.
     *
     * @param null|\Closure $f
     * @return array
     */
    private function createInput(?Closure $f = null): array
    {
        $contract = $this->contract;
        $input = [
            'officeId' => $contract->officeId,
            'note' => 'だるまさんがころんだ',

            // URLパラメータ
            'userId' => $this->examples->users[0]->id,
        ];
        if ($f !== null) {
            $f($input);
        }
        return $input;
    }

    /**
     * バリデーションに成功するパターン.
     *
     * @returns \Generator
     */
    private function passes(): Generator
    {
        // 備考
        yield 'when note is not given' => [
            function (array &$input): void {
                unset($input['note']);
            },
        ];
        yield 'when note is empty' => [
            function (array &$input): void {
                $input['note'] = '';
            },
        ];
        yield 'when the note is not longer than 255 characters' => [
            function (array &$input): void {
                $input['note'] = str_repeat('あ', 255);
            },
        ];
    }

    /**
     * バリデーションに失敗するパターン.
     *
     * @returns \Generator
     */
    private function fails(): Generator
    {
        // 事業所 ID
        yield 'when the officeId is not given' => [
            function (array &$input): void {
                unset($input['officeId']);
            },
            ['officeId' => ['入力してください。']],
        ];
        yield 'when the officeId is empty' => [
            function (array &$input): void {
                $input['officeId'] = '';
            },
            ['officeId' => ['入力してください。']],
        ];
        yield 'when the office not exists' => [
            function (array &$input): void {
                $input['officeId'] = self::NOT_EXISTING_ID;
            },
            ['officeId' => ['正しい値を入力してください。']],
        ];

        // 契約の重複バリデーション
        yield 'when a overlapped contract exists' => [
            function (array &$input): void {
                // `userId` が `$this->examples->users[1]->id` である場合のみ重複する契約を返すようモックを設定している
                $input['userId'] = $this->examples->users[1]->id;
            },
            ['contractedOn' => ['重複する契約が既に登録されています。ご確認ください。']],
        ];

        // 備考
        yield 'when the note is longer than 255 characters' => [
            function (array &$input): void {
                $input['note'] = str_repeat('あ', 256);
            },
            ['note' => ['255文字以内で入力してください。']],
        ];
    }
}
