<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Project;

use Domain\Context\Context;
use Domain\Office\OfficeRepository;
use Domain\Permission\Permission;
use Domain\Project\LtcsProject;
use Domain\Project\LtcsProjectProgram;
use Domain\Project\LtcsProjectServiceMenuFinder;
use Domain\Staff\StaffRepository;
use Domain\User\UserRepository;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;

/**
 * 介護保険サービス計画：ダウンロード実装.
 */
final class DownloadLtcsProjectInteractor implements DownloadLtcsProjectUseCase
{
    private LookupLtcsProjectUseCase $lookupUseCase;
    private OfficeRepository $officeRepository;
    private LtcsProjectServiceMenuFinder $serviceMenuFinder;
    private StaffRepository $staffRepository;
    private UserRepository $userRepository;

    /**
     * Constructor.
     *
     * @param \UseCase\Project\LookupLtcsProjectUseCase $lookupUseCase
     * @param \Domain\Office\OfficeRepository $officeRepository
     * @param \Domain\Project\LtcsProjectServiceMenuFinder $serviceMenuFinder
     * @param \Domain\Staff\StaffRepository $staffRepository
     * @param \Domain\User\UserRepository $userRepository
     */
    public function __construct(
        LookupLtcsProjectUseCase $lookupUseCase,
        OfficeRepository $officeRepository,
        LtcsProjectServiceMenuFinder $serviceMenuFinder,
        StaffRepository $staffRepository,
        UserRepository $userRepository
    ) {
        $this->lookupUseCase = $lookupUseCase;
        $this->officeRepository = $officeRepository;
        $this->serviceMenuFinder = $serviceMenuFinder;
        $this->staffRepository = $staffRepository;
        $this->userRepository = $userRepository;
    }

    /** {@inheritdoc} */
    public function handle(Context $context, int $userId, int $id): array
    {
        /** @var \Domain\Project\LtcsProject $project */
        $project = $this->lookupUseCase->handle($context, Permission::viewLtcsProjects(), $userId, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("LtcsProject[{$id}] not found");
            });
        $user = $this->userRepository->lookup($project->userId)
            ->headOption()
            ->getOrElse(function () use ($project) {
                throw new NotFoundException("User({$project->userId}) not found");
            });
        $staff = $this->staffRepository->lookup($project->staffId)
            ->headOption()
            ->getOrElse(function () use ($project) {
                throw new NotFoundException("Staff({$project->staffId}) not found");
            });
        $office = $this->officeRepository->lookup($project->officeId)
            ->headOption()
            ->getOrElse(function () use ($project) {
                throw new NotFoundException("Office({$project->officeId}) not found");
            });
        $serviceMenus = $this->serviceMenuFinder
            ->find([], ['sortBy' => 'id'])
            ->list
            ->groupBy('id')
            ->toAssoc();

        [$programsPerPage, $remain, $sign] = $this->programsPerPage($project);

        return compact(
            'project',
            'user',
            'staff',
            'office',
            'serviceMenus',
            'programsPerPage',
            'remain',
            'sign',
        );
    }

    /**
     * マルチプリント用の配列を取得する.
     *
     * @param \Domain\Project\LtcsProject $project
     * @return array
     */
    private function programsPerPage(LtcsProject $project): array
    {
        $programsCount = count($project->programs);
        $page = LtcsProject::FIRST_PAGE;
        $programsPerPage = [];
        $remain = 0;

        // 高さ
        $title = 10;
        $basic = 20 + LtcsProject::MARGIN_BOTTOM; // マージン分足す
        $goal = 55 + LtcsProject::MARGIN_BOTTOM;
        $weeklyProgram = $programsCount * LtcsProject::CELL_HEIGHT
            + LtcsProject::CELL_HEIGHT * 2 // ヘッダー2行分
            + LtcsProject::MARGIN_BOTTOM;
        $allContents = Seq::fromArray($project->programs)
            ->map(fn (LtcsProjectProgram $x): int => count($x->contents))
            ->sum();
        $serviceDetails = $allContents * 15 // 行 15mm 固定
            + $programsCount * (LtcsProject::CELL_HEIGHT * 2) // ヘッダ
            + $programsCount * LtcsProject::MARGIN_BOTTOM; // マージン
        $sign = 45;

        // border の高さ
        $basicBorders = 3;
        $goalBorders = 7;
        $projectBorders = $programsCount + 2 + 1;
        $detailBorders = $allContents + $programsCount * 2 + $programsCount * 1;
        $bordersHeight = ($basicBorders + $goalBorders + $projectBorders + $detailBorders) * LtcsProject::BORDER_HEIGHT;

        $contentHeight = LtcsProject::PADDING * 2 + $title + $basic + $goal + $weeklyProgram + $serviceDetails + $sign + $bordersHeight;

        // 高さが292mm(1枚分)以上
        if ($contentHeight > LtcsProject::A4_HEIGHT) {
            // ※「週間サービス計画」までは、1枚めに収まる想定で実装.
            // 残りの余白
            $remain = LtcsProject::A4_HEIGHT
                - (LtcsProject::PADDING * 2 + $title + $basic + $goal + $weeklyProgram)
                - ($basicBorders + $goalBorders + $projectBorders) * LtcsProject::BORDER_HEIGHT;

            foreach ($project->programs as $key => $program) {
                // サービス詳細の高さ
                $rows = count($program->contents);
                $height = $rows * 15 + LtcsProject::CELL_HEIGHT * 2 + LtcsProject::MARGIN_BOTTOM + ($rows + 2 + 1) * LtcsProject::BORDER_HEIGHT;
                // 余白に収まる場合
                if ($height <= $remain) {
                    $programsPerPage[$page][] = $program;
                    $remain -= $height;
                    continue;
                }
                // 余白に収まらない場合
                if ($height > $remain) {
                    ++$page;
                    $programsPerPage[$page][] = $program;
                    $remain = LtcsProject::A4_HEIGHT - ($title + LtcsProject::PADDING * 2) - $height;
                }
            }
        }
        return [$programsPerPage, $remain, $sign];
    }
}
