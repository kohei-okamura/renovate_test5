<?php
/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Project;

use Domain\Context\Context;
use Domain\Office\OfficeRepository;
use Domain\Permission\Permission;
use Domain\Project\DwsProject;
use Domain\Project\DwsProjectProgram;
use Domain\Project\DwsProjectServiceMenuFinder;
use Domain\Staff\StaffRepository;
use Domain\User\UserRepository;
use Lib\Exceptions\NotFoundException;
use Lib\Math;
use ScalikePHP\Seq;

/**
 * 障害福祉サービス：計画ダウンロード実装.
 */
final class DownloadDwsProjectInteractor implements DownloadDwsProjectUseCase
{
    private LookupDwsProjectUseCase $lookupUseCase;
    private OfficeRepository $officeRepository;
    private DwsProjectServiceMenuFinder $serviceMenuFinder;
    private StaffRepository $staffRepository;
    private UserRepository $userRepository;

    /**
     * Constructor.
     *
     * @param \UseCase\Project\LookupDwsProjectUseCase $lookupUseCase
     * @param \Domain\Office\OfficeRepository $officeRepository
     * @param \Domain\Project\DwsProjectServiceMenuFinder $serviceMenuFinder
     * @param \Domain\Staff\StaffRepository $staffRepository
     * @param \Domain\User\UserRepository $userRepository
     */
    public function __construct(
        LookupDwsProjectUseCase $lookupUseCase,
        OfficeRepository $officeRepository,
        DwsProjectServiceMenuFinder $serviceMenuFinder,
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
        /** @var \Domain\Project\DwsProject $project */
        $project = $this->lookupUseCase->handle($context, Permission::viewDwsProjects(), $userId, $id)
            ->headOption()
            ->getOrElse(function () use ($id): void {
                throw new NotFoundException("DwsProject[{$id}] not found");
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
     * @param \Domain\Project\DwsProject $project
     * @return array
     */
    private function programsPerPage(DwsProject $project): array
    {
        $programsCount = count($project->programs);
        $page = DwsProject::FIRST_PAGE;
        $programsPerPage = [];
        $remain = 0;

        // 高さ
        $title = 10;
        $basic = 20 + DwsProject::MARGIN_BOTTOM; // マージン分足す
        $goal = 30 + DwsProject::MARGIN_BOTTOM;
        $serviceCategory = Math::ceil($programsCount / 2) * DwsProject::CELL_HEIGHT + DwsProject::MARGIN_BOTTOM;
        $weeklyProgram = $programsCount * DwsProject::CELL_HEIGHT
            + DwsProject::CELL_HEIGHT * 2 // ヘッダー2行分
            + DwsProject::MARGIN_BOTTOM;
        $allContents = Seq::fromArray($project->programs)
            ->map(fn (DwsProjectProgram $x): int => count($x->contents))
            ->sum();
        $serviceDetails = $allContents * 15 // 行 15mm 固定
            + $programsCount * (DwsProject::CELL_HEIGHT * 2) // ヘッダ
            + $programsCount * DwsProject::MARGIN_BOTTOM; // マージン
        $sign = 45;

        // border の高さ
        $basicBorders = 3;
        $goalBorders = 4;
        $serviceCategoryBorders = Math::ceil($programsCount / 2) + 1;
        $projectBorders = $programsCount + 2 + 1;
        $detailBorders = $allContents + $programsCount * 2 + $programsCount * 1;
        $bordersHeight = ($basicBorders + $goalBorders + $serviceCategoryBorders + $projectBorders + $detailBorders) * DwsProject::BORDER_HEIGHT;

        $contentHeight = DwsProject::PADDING * 2 + $title + $basic + $goal + $serviceCategory + $weeklyProgram + $serviceDetails + $sign + $bordersHeight;

        // 高さが292mm(1枚分)以上
        if ($contentHeight > DwsProject::A4_HEIGHT) {
            // ※「週間サービス計画」までは、1枚めに収まる想定で実装.
            // 残りの余白
            $remain = DwsProject::A4_HEIGHT
                - (DwsProject::PADDING * 2 + $title + $basic + $goal + $serviceCategory + $weeklyProgram)
                - ($basicBorders + $goalBorders + $serviceCategoryBorders + $projectBorders) * DwsProject::BORDER_HEIGHT;

            foreach ($project->programs as $key => $program) {
                // サービス詳細の高さ
                $rows = count($program->contents);
                $height = $rows * 15 + DwsProject::CELL_HEIGHT * 2 + DwsProject::MARGIN_BOTTOM + ($rows + 2 + 1) * DwsProject::BORDER_HEIGHT;
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
                    $remain = DwsProject::A4_HEIGHT - ($title + DwsProject::PADDING * 2) - $height;
                }
            }
        }
        return [$programsPerPage, $remain, $sign];
    }
}
