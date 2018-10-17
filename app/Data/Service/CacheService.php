<?php
/**
 * Created by PhpStorm.
 * User: dobrik
 * Date: 1/22/18
 * Time: 4:27 PM
 */

namespace Woxapp\Scaffold\Data\Service;

use Memcached;

class CacheService
{

    const TAGS_KEY = 'TAGS';

    private $cache_keys = [];

    private $methodName;

    private $tags = [];

    /**
     * @var Memcached
     */
    protected $memcached;

    public function __construct(Memcached $memcached)
    {
        $this->memcached = $memcached;
    }

    /**
     *
     * set composite key
     *
     * @param array ...$cache_keys
     * @return CacheService
     */
    public function setCacheKeys(...$cache_keys): self
    {
        $this->cache_keys = $cache_keys;
        return $this;
    }

    /**
     *
     * get key for nested storing
     *
     * @return array
     */
    public function getCacheKeys(): array
    {
        return $this->cache_keys;
    }


    /**
     *
     * get tags for current cache store
     *
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     *
     * set method name
     *
     * @param string $methodName
     * @return CacheService
     */
    public function setMethod(string $methodName): self
    {
        $this->methodName = $methodName;
        return $this;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getMethod()
    {
        if (empty($this->methodName)) {
            throw new \Exception('The name of the method must be specified');
        }
        return $this->methodName;
    }

    /**
     *
     * get cached data
     *
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function get()
    {
        if (empty($this->getCacheKeys())) {
            return $this->getSingle();
        } else {
            return $this->getNested();
        }
    }

    /**
     *
     * save cached data
     *
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    public function save(array $data)
    {
        if (empty($this->getCacheKeys())) {
            return $this->setSingle($data);
        } else {
            return $this->setNested($data);
        }

    }

    /**
     *
     * delete cache data
     *
     * @return bool|void
     * @throws \Exception
     */
    public function delete()
    {
        if (empty($this->getCacheKeys())) {
            return $this->deleteEverything();
        } else {
            return $this->deleteNested();
        }
    }

    /**
     * flush all cache data
     */
    public function flushCache()
    {
        $this->memcached->flush();
    }

    /**
     *
     * delete cache data by method name
     *
     * @return bool
     * @throws \Exception
     */
    private function deleteEverything(): bool
    {
        return $this->memcached->delete($this->getMethod());
    }

    /**
     *
     * get data by method name
     *
     * @throws \Exception
     */
    private function getSingle()
    {
        $cachedData = $this->memcached->get($this->getMethod());

        return ($cachedData !== null) ? $cachedData : false;
    }

    /**
     *
     * Store data by method name
     *
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    private function setSingle(array $data): bool
    {
        $this->memcached->set($this->getMethod(), $data);

        if (!empty($this->getTags())) {
            $this->writeTagData();
        }

        return true;
    }

    /**
     *
     * Create nested array with data in end
     * ex: [1 => [2 => [3 => ['your' => 'data']]]]
     *
     * @param $data
     * @return array
     */
    private function createNestedCacheData($data)
    {
        foreach (array_reverse($this->getCacheKeys()) as $cache_key) {
            $tmpArray = [];
            $tmpArray[$cache_key] = $data;
            $data = $tmpArray;
        }
        return $data;
    }


    /**
     *
     * get data by composite key
     *
     * @return array|bool|mixed
     * @throws \Exception
     */
    private function getNested()
    {
        $cachedData = $this->getSingle();

        if ($cachedData !== false) {
            if (!empty($this->getCacheKeys()) && is_array($cachedData)) {
                foreach ($this->getCacheKeys() as $cache_key) {
                    if (is_array($cachedData) && array_key_exists($cache_key, $cachedData)) {
                        $cachedData = $cachedData[$cache_key];
                    } else {
                        return false;
                    }
                }
                return $cachedData;
            }
            return $cachedData;
        }

        return false;
    }

    /**
     *
     * store data by composite key
     *
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    private function setNested(array $data): bool
    {
        $cachedData = $this->getSingle();

        if (!empty($this->getCacheKeys())) {
            $data = $this->createNestedCacheData($data);
        }

        if ($cachedData !== false) {
            $data = array_replace_recursive($cachedData, $data);
        }

        $this->setSingle($data);

        return true;
    }

    /**
     *
     * Delete cache data by composite key
     *
     * @return bool
     * @throws \Exception
     */
    private function deleteNested(): bool
    {
        $cachedData = $this->getNested();

        if ($cachedData === false) return false;

        $cachedData = $this->getSingle();

        $tmpArray = &$cachedData;
        $i = 0;
        foreach ($this->getCacheKeys() as $cache_key) {
            if (count($this->getCacheKeys()) == ($i + 1)) {
                unset($tmpArray[$cache_key]);
            } else {
                $tmpArray = &$tmpArray[$cache_key];
            }
            $i++;
        }

        $this->setSingle($cachedData);

        return true;
    }

    /**
     *
     * get key for current tag
     *
     * @param string $tag
     * @param array $params
     * @return string
     */
    private function formatTagKey(string $tag, array $params)
    {
        if (empty($params)) {
            return $tag;
        }

        return $tag . '_' . implode('_', $params);
    }

    /**
     *
     * add tag to current cache data
     *
     * @param string $tag
     * @param array ...$params
     * @return CacheService
     */
    public function addTag(string $tag, ...$params): self
    {
        $this->tags[] = $this->formatTagKey($tag, $params);
        return $this;
    }

    /**
     *
     * get currently cached tags
     *
     * @return array|mixed
     */
    public function getTagsCached()
    {
        $tagsCached = $this->memcached->get(self::TAGS_KEY);
        return $tagsCached === false ? [] : $tagsCached;
    }

    /**
     *
     * write tags to cache
     *
     * @throws \Exception
     */
    private function writeTagData()
    {
        $tagsCached = $this->getTagsCached();

        foreach ($this->getTags() as $tag) {
            if (!array_key_exists($tag, $tagsCached)) {
                $tagsCached[$tag] = [];
            }
            //serialize for store unique pairs only
            $tagsCached[$tag][md5(serialize([$this->getMethod() => $this->getCacheKeys()]))] = ['method' => $this->getMethod(), 'keys' => $this->getCacheKeys()];
        }

        $this->memcached->set(self::TAGS_KEY, $tagsCached);
    }

    /**
     *
     * delete cached data by tags
     *
     * @param string $tag
     * @param array ...$params
     * @throws \Exception
     */
    public function deleteByTag(string $tag, ...$params)
    {
        $tagsCached = $this->getTagsCached();

        if (empty($tagsCached)) return;

        $tag = $this->formatTagKey($tag, $params);
        $cacheCloned = clone $this;
        if (array_key_exists($tag, $tagsCached)) {
            foreach ($tagsCached[$tag] as $item) {
                $cacheCloned->setMethod($item['method'])->setCacheKeys(...$item['keys'])->delete();
            }
            unset($tagsCached[$tag]);
            $this->memcached->set(self::TAGS_KEY, $tagsCached);
        }
    }
}