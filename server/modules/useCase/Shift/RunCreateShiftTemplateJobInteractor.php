<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Shift;

use Domain\Config\Config;
use Domain\Context\Context;
use Domain\Job\Job as DomainJob;
use Domain\Shift\ServiceOption;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use UseCase\Job\RunJobUseCase;

/**
 * 勤務シフト雛形生成ジョブ実行ユースケース実装.
 */
final class RunCreateShiftTemplateJobInteractor implements RunCreateShiftTemplateJobUseCase
{
    private RunJobUseCase $runJobUseCase;
    private GenerateShiftTemplateUseCase $generateShiftTemplateUseCase;
    private Config $config;

    /**
     * Constructor.
     *
     * @param \UseCase\Job\RunJobUseCase $runJobUseCase
     * @param \UseCase\Shift\GenerateShiftTemplateUseCase $generateShiftTemplateUseCase
     * @param \Domain\Config\Config $config
     */
    public function __construct(
        RunJobUseCase $runJobUseCase,
        GenerateShiftTemplateUseCase $generateShiftTemplateUseCase,
        Config $config
    ) {
        $this->runJobUseCase = $runJobUseCase;
        $this->generateShiftTemplateUseCase = $generateShiftTemplateUseCase;
        $this->config = $config;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, DomainJob $domainJob, array $parameters): void
    {
        $this->runJobUseCase->handle(
            $context,
            $domainJob,
            function () use ($context, $parameters): array {
                $range = $parameters['range'];
                $isCopy = $parameters['isCopy'];
                $filterParams = [
                    'officeId' => $parameters['officeId'],
                    'excludeOption' => Seq::fromArray([ServiceOption::oneOff()]),
                ];
                /** @var \Domain\Common\CarbonRange|\ScalikePHP\Option $sourceOption */
                $sourceOption = $parameters['source'];
                if ($sourceOption->nonEmpty()) {
                    $assoc = Map::from($sourceOption->get()->toAssoc())->flatMap(function ($v, $k): array {
                        return match ($k) {
                            'start' => ['scheduleDateAfter' => $v],
                            'end' => ['scheduleDateBefore' => $v],
                            default => [$k => $v],
                        };
                    })->toAssoc();
                    $filterParams = $filterParams + $assoc;
                }
                $path = $this->generateShiftTemplateUseCase->handle($context, $range, $isCopy, $filterParams);
                $filename = $this->config->filename('zinger.filename.shift_template');
                return [
                    'uri' => $context->uri("shift-templates/download/{$path}"),
                    'filename' => $filename,
                ];
            }
        );
    }
}
