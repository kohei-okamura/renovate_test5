<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Repository;

use DateTimeInterface;
use Domain\Repository;
use Illuminate\Cache\CacheManager;
use ScalikePHP\Seq;

/**
 * Repository cache implementation.
 */
abstract class CacheRepository extends AbstractRepository
{
    protected CacheManager $cache;

    /**
     * Fallback repository.
     */
    protected Repository $fallback;

    /**
     * CacheRepository constructor.
     *
     * @param \Domain\Repository $fallback
     */
    public function __construct(Repository $fallback)
    {
        $this->cache = app('cache');
        $this->fallback = $fallback;
    }

    /** {@inheritdoc} */
    public function transactionManager(): string
    {
        return $this->fallback->transactionManager();
    }

    /** {@inheritdoc} */
    public function store(mixed $entity): mixed
    {
        $this->cache->forget($this->id($entity->id));
        return $this->fallback->store($entity);
    }

    /** {@inheritdoc} */
    public function remove(mixed $entity): void
    {
        $this->cache->forget($this->id($entity->id));
        $this->fallback->remove($entity);
    }

    /** {@inheritdoc} */
    public function removeById(int ...$ids): void
    {
        foreach ($ids as $id) {
            $this->cache->forget($this->id($id));
        }
        $this->fallback->removeById(...$ids);
    }

    /** {@inheritdoc} */
    protected function lookupHandler(int ...$ids): Seq
    {
        return Seq::fromArray($ids)->flatMap(fn (int $id) => $this->cache->remember(
            $this->id($id),
            $this->expiredAt(),
            // Seq のままだと遅延されているためキャッシュできない → 配列に変換してキャッシュする
            fn (): array => $this->fallback->lookup($id)->toArray()
        ));
    }

    /**
     * キャッシュの有効期限を返す.
     *
     * @return \DateTimeInterface
     */
    abstract protected function expiredAt(): DateTimeInterface;

    /**
     * キャッシュ名前空間を取得する.
     *
     * @return string
     */
    abstract protected function namespace(): string;

    /**
     * id を用いてキーを生成する.
     *
     * @param int $id
     * @return string
     */
    protected function id(int $id): string
    {
        return $this->namespace() . ':id:' . $id;
    }
}
