<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Shift;

use Closure;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use Domain\Shift\ServiceOption;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GenerateShiftTemplateUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\RunJobUseCaseMixin;
use Tests\Unit\Test;
use UseCase\Shift\RunCreateShiftTemplateJobInteractor;

/**
 * RunCreateShiftTemplateJobInteractor のテスト.
 */
final class RunCreateShiftTemplateJobInteractorTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use GenerateShiftTemplateUseCaseMixin;
    use MockeryMixin;
    use RunJobUseCaseMixin;
    use UnitSupport;

    private const PATH = 'exported/example.xlsx';
    private const FILENAME = 'example.xlsx';

    private CarbonRange $range;
    private DomainJob $domainJob;
    private array $parameters;
    private RunCreateShiftTemplateJobInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (RunCreateShiftTemplateJobInteractorTest $self): void {
            $self->range = CarbonRange::create(['start' => Carbon::now(), 'end' => Carbon::now()->addDays(6)]);
            $self->parameters = [
                'officeId' => $self->examples->offices[0]->id,
                'isCopy' => false,
                'range' => $self->range,
                'source' => Option::none(),
            ];
            $self->domainJob = $self->examples->jobs[0];

            $self->runJobUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();
            $self->generateShiftTemplateUseCase
                ->allows('handle')
                ->andReturn(self::PATH)
                ->byDefault();
            $self->config
                ->allows('filename')
                ->andReturn(self::FILENAME)
                ->byDefault();

            $self->interactor = app(RunCreateShiftTemplateJobInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('call RunJobUseCase', function (): void {
            $this->runJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, Mockery::any())
                ->andReturn(null);

            $this->interactor->handle($this->context, $this->domainJob, $this->parameters);
        });
        $this->should('call GenerateShiftTemplateUseCase', function (): void {
            $uri = $this->context->uri('shift-templates/download/' . self::PATH);
            $this->runJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, Mockery::any())
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f) use ($uri): void {
                        $this->generateShiftTemplateUseCase
                            ->expects('handle')
                            ->with(
                                $this->context,
                                $this->parameters['range'],
                                $this->parameters['isCopy'],
                                equalTo([
                                    'officeId' => $this->parameters['officeId'],
                                    'excludeOption' => Seq::fromArray([ServiceOption::oneOff()]),
                                ]),
                            )
                            ->andReturn(self::PATH);

                        // 正しい値を返すことも検証
                        $this->assertSame(
                            ['uri' => $uri, 'filename' => self::FILENAME],
                            $f()
                        );
                    }
                );

            $this->interactor->handle($this->context, $this->domainJob, $this->parameters);
        });
        $this->should('call UseCase with `isCopy` is true', function (): void {
            $source = CarbonRange::create([
                'start' => Carbon::now(),
                'end' => Carbon::now()->addDays(6),
            ]);
            $parameters = [
                'isCopy' => true,
                'source' => Option::from($source),
            ] + $this->parameters;
            $this->runJobUseCase
                ->expects('handle')
                ->with($this->context, $this->domainJob, Mockery::any())
                ->andReturnUsing(
                    function (Context $context, DomainJob $job, Closure $f) use ($parameters, $source): void {
                        $this->generateShiftTemplateUseCase
                            ->expects('handle')
                            ->with($this->context, $parameters['range'], $parameters['isCopy'], equalTo([
                                'officeId' => $parameters['officeId'],
                                'excludeOption' => Seq::fromArray([ServiceOption::oneOff()]),
                                'scheduleDateAfter' => $source->start,
                                'scheduleDateBefore' => $source->end,
                            ]))
                            ->andReturn(self::PATH);

                        $f();
                    }
                );

            $this->interactor->handle($this->context, $this->domainJob, $parameters);
        });
    }
}
