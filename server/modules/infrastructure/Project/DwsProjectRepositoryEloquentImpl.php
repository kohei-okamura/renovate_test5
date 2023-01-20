<?php
/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Project;

use Domain\Project\DwsProject as DomainDwsProject;
use Domain\Project\DwsProjectRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * DwsProjectRepository eloquent implementation.
 */
class DwsProjectRepositoryEloquentImpl extends EloquentRepository implements DwsProjectRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = DwsProject::findMany($ids);
        return Seq::fromArray($xs)->map(fn (DwsProject $x): DomainDwsProject => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainDwsProject
    {
        assert($entity instanceof DomainDwsProject);
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
        return $dwsProject->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        DwsProjectAttr::whereIn('dws_project_id', $ids)->delete();
        DwsProject::destroy($ids);
    }
}
