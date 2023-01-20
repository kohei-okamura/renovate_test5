<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Job;

use Domain\Common\Carbon;
use Domain\Job\JobStatus;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EditJobUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Test;
use UseCase\Job\StartJobInteractor;

/**
 * BeginJobInteractor のテスト.
 */
class StartJobInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use EditJobUseCaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use UnitSupport;

    private StartJobInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (StartJobInteractorTest $self): void {
            $self->editJobUseCase
                ->allows('handle')
                ->andReturn($self->examples->jobs[0])
                ->byDefault();
            $self->interactor = app(StartJobInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('Edit is called', function (): void {
            $this->editJobUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->jobs[2]->id, $this->editJobValue())
                ->andReturn($this->examples->jobs[0]);

            $this->interactor->handle(
                $this->context,
                $this->examples->jobs[2]->id
            );
        });
    }

    /**
     * ジョブのデータ情報を取得する.
     *
     * @return array
     */
    private function editJobValue(): array
    {
        return [
            'status' => JobStatus::inProgress(),
            'updatedAt' => Carbon::now(),
        ];
    }
}
