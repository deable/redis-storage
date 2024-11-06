<?php

declare(strict_types=1);

namespace Deable\RedisStorage;

use Deable\Redis\RedisClient;
use Deable\RedisCache\RedisCache;
use Nette\Caching\Storage;
use RuntimeException;

/**
 * Class RedisStorage
 *
 * @package Deable\RedisStorage
 */
final class RedisStorage implements Storage
{
	private RedisClient $client;

	private RedisCache $cache;

	public function __construct(RedisClient $client)
	{
		$this->client = $client;
		$this->cache = new RedisCache($client);
	}

	public function read(string $key)
	{
		return $this->cache->get($key);
	}

	public function lock(string $key): void
	{
		if (!$this->client->getLock()->acquire($key)) {
			throw new RuntimeException("Cannot lock cache key '$key'.");
		}
	}

	public function write(string $key, $data, array $dependencies): void
	{
		$this->cache->set($key, $data);
		$this->client->getLock()->release($key);
	}

	public function remove(string $key): void
	{
		$this->cache->delete($key);
	}

	public function clean(array $conditions): void
	{
		$this->cache->clear();
	}

}
