<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace UseCase\Billing;

use Domain\Billing\CopayListPdf;
use Domain\Billing\CopayListSource;
use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingStatement;
use Domain\Common\Carbon;
use Domain\Context\Context;
use Domain\DwsCertification\DwsCertification;
use Domain\Office\Office;
use Domain\Permission\Permission;
use Lib\Exceptions\LogicException;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use UseCase\DwsCertification\IdentifyDwsCertificationUseCase;
use UseCase\Office\LookupOfficeUseCase;

/**
 * 利用者負担額一覧表 PDFパラメータ組み立てユースケース実装.
 */
final class BuildCopayListPdfParamInteractor implements BuildCopayListPdfParamUseCase
{
    /**
     * {@link \UseCase\Billing\BuildCopayListPdfParamInteractor} constructor.
     *
     * @param \UseCase\Office\LookupOfficeUseCase $lookupOfficeUseCase
     * @param \UseCase\DwsCertification\IdentifyDwsCertificationUseCase $identifyDwsCertificationUseCase
     */
    public function __construct(
        private LookupOfficeUseCase $lookupOfficeUseCase,
        private IdentifyDwsCertificationUseCase $identifyDwsCertificationUseCase,
    ) {
    }

    /** {@inheritdoc} */
    public function handle(Context $context, DwsBilling $billing, Seq $bundles, Seq $statements): array
    {
        $providedIn = $bundles->head()->providedIn;

        $certificationMap = $this->identifyCertifications($context, $statements, $providedIn);
        $officeMap = $this->lookupOffices($context, [...$certificationMap->keys()]);

        $sources = $certificationMap
            ->mapValues(fn (Seq $certifications, int $officeId): CopayListSource => new CopayListSource(
                copayCoordinationOfficeName: self::getOfficeName($officeMap, $officeId),
                statements: self::findStatementsForCertifications($statements, $certifications)->toArray(),
            ))
            ->values();

        return [
            'copayLists' => CopayListPdf::from($billing, $bundles, $sources, Carbon::now()),
        ];
    }

    /**
     * 各明細書に対応する障害福祉サービス受給者証を取得（特定）し, 事業所 ID をキーにしたマップを返す.
     *
     * @param \Domain\Context\Context $context
     * @param \Domain\Billing\DwsBillingStatement&\ScalikePHP\Seq $statements
     * @param \Domain\Common\Carbon $providedIn
     * @return \Domain\DwsCertification\DwsCertification&\ScalikePHP\Map
     */
    private function identifyCertifications(Context $context, Seq $statements, Carbon $providedIn): Map
    {
        $certifications = $statements->map(
            fn (DwsBillingStatement $x): DwsCertification => $this->identifyDwsCertificationUseCase
                ->handle($context, $x->user->userId, $providedIn)
                ->getOrElse(function () use ($providedIn, $x): never {
                    throw new NotFoundException(
                        "DwsCertification(userId={$x->user->userId}, providedIn={$providedIn->format('Y-m')}) not found"
                    );
                })
        );
        return $certifications->groupBy(fn (DwsCertification $x): int => $x->copayCoordination->officeId);
    }

    /**
     * 事業所のマップを返す.
     *
     * @param \Domain\Context\Context $context
     * @param int[] $officeIds
     * @return \Domain\Office\Office[]&\ScalikePHP\Map
     */
    private function lookupOffices(Context $context, array $officeIds): Map
    {
        return $this->lookupOfficeUseCase
            ->handle($context, [Permission::viewBillings()], ...$officeIds)
            ->toMap(fn (Office $x): int => $x->id);
    }

    /**
     * 事業所のマップから事業所名を取得する.
     *
     * 前段の処理で必ず ID に対応する事業所がマップに含まれているはずなので, 存在しない場合は例外を投げる.
     *
     * @param \Domain\Office\Office[]&\ScalikePHP\Map $officeMap
     * @param int $officeId
     * @return string
     */
    private static function getOfficeName(Map $officeMap, int $officeId): string
    {
        return $officeMap
            ->get($officeId)
            ->map(fn (Office $office): string => $office->name)
            ->getOrElse(function (): never {
                // ここが実行される = バグがあるのでカバレッジの対象外とする
                // @codeCoverageIgnoreStart
                throw new LogicException('Office not found.');
                // @codeCoverageIgnoreEnd
            });
    }

    /**
     * 障害福祉サービス受給者証に対応する明細書の一覧を取得する.
     *
     * @param \Domain\Billing\DwsBillingStatement[]&\ScalikePHP\Seq $statements
     * @param \Domain\DwsCertification\DwsCertification[]&\ScalikePHP\Seq $certifications
     * @return \Domain\Billing\DwsBillingStatement[]&\ScalikePHP\Seq
     */
    private static function findStatementsForCertifications(Seq $statements, Seq $certifications): Seq
    {
        $userIds = $certifications->map(fn (DwsCertification $x): int => $x->userId)->computed();
        return $statements->filter(fn (DwsBillingStatement $x): bool => $userIds->contains($x->user->userId));
    }
}
