<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Project\DwsProject;
use Infrastructure\Project\DwsProjectAttr;
use Infrastructure\Project\DwsProjectContent;
use Infrastructure\Project\DwsProjectProgram;

/**
 * DwsProject fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait DwsProjectFixture
{
    /**
     * 障害福祉サービス：計画 登録.
     *
     * @return void
     */
    protected function createDwsProjects(): void
    {
        foreach ($this->examples->dwsProjects as $entity) {
            $dwsProject = DwsProject::fromDomain($entity);
            $dwsProjectAttr = DwsProjectAttr::fromDomain($entity);
            $dwsProject->saveIfNotExists();
            $dwsProject->attr()->save($dwsProjectAttr);
            foreach ($entity->programs as $programIndex => $domainProgram) {
                $program = DwsProjectProgram::fromDomain(
                    $domainProgram,
                    [
                        'dws_project_attr_id' => $dwsProjectAttr->id,
                        'sort_order' => $programIndex,
                    ]
                );
                $dwsProjectAttr->programs()->save($program);
                foreach ($domainProgram->contents as $contentIndex => $domainContent) {
                    $content = DwsProjectContent::fromDomain(
                        $domainContent,
                        [
                            'dws_project_program_id' => $program->id,
                            'sort_order' => $contentIndex,
                        ]
                    );
                    $program->contents()->save($content);
                }
                $program->syncServiceOptions($domainProgram->options);
            }
        }
    }
}
