<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Project\DwsProjectAttr;
use Infrastructure\Project\LtcsProjectAttr;
use Infrastructure\Project\Project;
use Infrastructure\Project\ProjectAttr;
use Infrastructure\Project\ProjectProgram;
use Infrastructure\Project\ProjectProgramAmount;
use Infrastructure\Project\ProjectProgramContent;

/**
 * Project fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait ProjectFixture
{
    /**
     * 計画 登録.
     *
     * @return void
     */
    protected function createProjects(): void
    {
        $this->createDwsProjects();
        $this->createLtcsProjects();
    }

    /**
     * Create DwsProject.
     */
    private function createDwsProjects(): void
    {
        foreach ($this->examples->dwsProjects as $entity) {
            $project = $this->createProject($entity);
            $project->attr->dwsProjectAttr()->save(DwsProjectAttr::fromDomain($entity));
            $this->createProjectPrograms($project, $entity->programs);
        }
    }

    /**
     * Create LtcsProject.
     */
    private function createLtcsProjects(): void
    {
        foreach ($this->examples->ltcsProjects as $entity) {
            $project = Project::fromDomain($entity)->saveIfNotExists();
            $project->attr()->save(ProjectAttr::fromDomain($entity));
            $project->attr->ltcsProjectAttr()->save(LtcsProjectAttr::fromDomain($entity));
            $this->createProjectPrograms($project, $entity->programs);
        }
    }

    /**
     * Create Project.
     *
     * @param \Domain\Project\Project $entity
     * @return \Infrastructure\Project\Project
     */
    private function createProject($entity): Project
    {
        $project = Project::fromDomain($entity)->saveIfNotExists();
        $project->attr()->save(ProjectAttr::fromDomain($entity));
        return $project;
    }

    /**
     * Create ProjectProgram.
     *
     * @param \Infrastructure\Project\Project $project
     * @param \Domain\Project\ProjectProgram[] $projectPrograms
     */
    private function createProjectPrograms($project, $projectPrograms): void
    {
        foreach ($projectPrograms as $domainProjectProgram) {
            $projectProgram = ProjectProgram::fromDomain($domainProjectProgram);
            $project->attr->projectPrograms()->save($projectProgram);
            $this->createProjectProgramAmounts($projectProgram, $domainProjectProgram->amounts);
            $this->createProjectProgramContents($projectProgram, $domainProjectProgram->contents);
        }
    }

    /**
     * Create ProjectProgramAmount.
     *
     * @param \Infrastructure\Project\ProjectProgram $projectProgram
     * @param \Domain\Project\ProjectProgramAmount[] $amounts
     */
    private function createProjectProgramAmounts($projectProgram, $amounts): void
    {
        foreach ($amounts as $index => $domainAmount) {
            $amount = ProjectProgramAmount::fromDomain($domainAmount, ['sort_order' => $index]);
            $projectProgram->amounts()->save($amount);
        }
    }

    /**
     * Create ProjectProgramContent.
     *
     * @param \Infrastructure\Project\ProjectProgram $projectProgram
     * @param \Domain\Project\ProjectProgramContent[] $contents
     */
    private function createProjectProgramContents($projectProgram, $contents): void
    {
        foreach ($contents as $index => $domainContent) {
            $content = ProjectProgramContent::fromDomain($domainContent, ['sort_order' => $index]);
            /** @var \Infrastructure\Project\ProjectProgramContent $storedContent */
            $storedContent = $projectProgram->contents()->save($content);
            $storedContent->serviceMenuOptions()->sync($domainContent->serviceMenuOptionIds);
        }
    }
}
