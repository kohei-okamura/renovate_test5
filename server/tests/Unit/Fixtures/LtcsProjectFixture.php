<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Project\LtcsProject;
use Infrastructure\Project\LtcsProjectAttr;
use Infrastructure\Project\LtcsProjectContent;
use Infrastructure\Project\LtcsProjectProgram;
use Infrastructure\Project\LtcsProjectProgramAmount;

/**
 * LtcsProject fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait LtcsProjectFixture
{
    /**
     * 介護保険サービス：計画 登録.
     *
     * @return void
     */
    protected function createLtcsProjects(): void
    {
        foreach ($this->examples->ltcsProjects as $entity) {
            $ltcsProject = LtcsProject::fromDomain($entity);
            $ltcsProjectAttr = LtcsProjectAttr::fromDomain($entity);
            $ltcsProject->saveIfNotExists();
            $ltcsProject->attr()->save($ltcsProjectAttr);
            foreach ($entity->programs as $programIndex => $domainProgram) {
                $program = LtcsProjectProgram::fromDomain(
                    $domainProgram,
                    [
                        'ltcs_project_attr_id' => $ltcsProjectAttr->id,
                        'sort_order' => $programIndex,
                    ]
                );
                $ltcsProjectAttr->programs()->save($program);
                foreach ($domainProgram->amounts as $amountIndex => $domainAmount) {
                    $amount = LtcsProjectProgramAmount::fromDomain(
                        $domainAmount,
                        [
                            'ltcs_project_program_id' => $program->id,
                            'sort_order' => $amountIndex,
                        ]
                    );
                    $program->amounts()->save($amount);
                }
                foreach ($domainProgram->contents as $contentIndex => $domainContent) {
                    $content = LtcsProjectContent::fromDomain(
                        $domainContent,
                        [
                            'ltcs_project_program_id' => $program->id,
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
