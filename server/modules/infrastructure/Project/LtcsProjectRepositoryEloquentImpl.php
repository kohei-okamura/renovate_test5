<?php
/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Project;

use Domain\Project\LtcsProject as DomainLtcsProject;
use Domain\Project\LtcsProjectRepository;
use Infrastructure\Repository\EloquentRepository;
use ScalikePHP\Seq;

/**
 * LtcsProjectRepository eloquent implementation.
 */
class LtcsProjectRepositoryEloquentImpl extends EloquentRepository implements LtcsProjectRepository
{
    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        $xs = LtcsProject::findMany($ids);
        return Seq::fromArray($xs)->map(fn (LtcsProject $x): DomainLtcsProject => $x->toDomain());
    }

    /** {@inheritdoc} */
    protected function storeInTransaction(mixed $entity): DomainLtcsProject
    {
        assert($entity instanceof DomainLtcsProject);
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
        return $ltcsProject->refresh()->toDomain();
    }

    /** {@inheritdoc} */
    protected function removeByIdInTransaction(int ...$ids): void
    {
        LtcsProjectAttr::whereIn('ltcs_project_id', $ids)->delete();
        LtcsProject::destroy($ids);
    }
}
