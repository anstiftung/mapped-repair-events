<?php
declare(strict_types=1);
namespace App\Model\Table;

use Cake\ORM\Table;

/**
 * @template TEntity of \Cake\Datasource\EntityInterface
 * @method TEntity newEmptyEntity()
 * @method TEntity newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<TEntity> newEntities(array<array<string, mixed>> $data, array<string, mixed> $options = [])
 * @method TEntity get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method TEntity findOrCreate(mixed $search, ?callable $callback = null, array<string, mixed> $options = [])
 * @method TEntity patchEntity(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method array<TEntity> patchEntities(iterable<TEntity> $entities, array<string, mixed> $data, array<string, mixed> $options = [])
 * @method TEntity|false save(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method TEntity saveOrFail(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method iterable<TEntity>|false saveMany(iterable<TEntity> $entities, array<string, mixed> $options = [])
 * @method iterable<TEntity> saveManyOrFail(iterable<TEntity> $entities, array<string, mixed> $options = [])
 * @method bool delete(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method bool deleteOrFail(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method bool deleteMany(iterable<TEntity> $entities, array<string, mixed> $options = [])
 * @method bool deleteManyOrFail(iterable<TEntity> $entities, array<string, mixed> $options = [])
 */
abstract class AppTable extends Table
{
}
