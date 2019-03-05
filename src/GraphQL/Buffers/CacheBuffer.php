<?php

namespace Drupal\graphql\GraphQL\Buffers;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;

/**
 * Class CacheBuffer
 *
 * @package Drupal\graphql\GraphQL\Buffers
 */
class CacheBuffer extends BufferBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  protected $cacheBackend;

  /**
   * EntityBuffer constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, CacheBackendInterface $cacheBackend) {
    $this->entityTypeManager = $entityTypeManager;
    $this->cacheBackend = $cacheBackend;
  }

  /**
   * Add an item to the buffer.
   *
   * @param string $type
   *   The type of cache operation (read or write).
   * @param array|int $cid
   *   The cache ID.
   *
   * @return \Closure
   *   The callback to invoke to load the result for this buffer item.
   */
  public function add($type, $cid) {
    $item = new \ArrayObject([
      'type' => $type,
      'cid' => $cid,
    ]);

    return $this->createBufferResolver($item);
  }

  /**
   * {@inheritdoc}
   */
  protected function getBufferId($item) {
    return $item['cid'];
  }

  /**
   * {@inheritdoc}
   */
  public function resolveBufferArray(array $buffer) {

    // Get CIDS.
    $gets = array_filter($buffer, function ($item) {
      return $item['type'] === 'read';
    });
    $get_cids = array_map(function (\ArrayObject $item) {
      return (array) $item['cid'];
    }, $gets);
    $get_cids = call_user_func_array('array_merge', $get_cids);
    $get_cids = array_values(array_unique($get_cids));

    $cache = $this->cacheBackend->getMultiple($get_cids);

    return array_map(function ($item) use ($cache) {
      if (is_array($item['cid'])) {
        return array_reduce($item['cid'], function ($carry, $current) use ($cache) {
          if (!empty($cache[$current])) {
            return $carry + [$current => $cache[$current]];
          }

          return $carry;
        }, []);
      }

      return isset($cache[$item['cid']]) ? $cache[$item['cid']] : NULL;
    }, $gets);


//    return $this->cacheBackend->get($cid);
    // Set CIDS.

//    $type = reset($buffer)['type'];
//    $cids = array_map(function (\ArrayObject $item) {
//      return (array) $item['cid'];
//    }, $buffer);
//
//
//    $adapter = new SyncPromiseAdapter();
//    if (($cache = $this->cacheBackend->getMultiple($cids))) {
//      return $adapter->createFulfilled($cache->data);
//    }$adapter = new SyncPromiseAdapter();
    //    if (($cache = $this->cacheBackend->getMultiple($get_cids))) {
    //      return $adapter->createFulfilled($cache->data);
    //    }




    // end


//    $type = reset($buffer)['type'];
//    $ids = array_map(function (\ArrayObject $item) {
//      return (array) $item['id'];
//    }, $buffer);
//
//    $ids = call_user_func_array('array_merge', $ids);
//    $ids = array_values(array_unique($ids));
//
//    // Load the buffered entities.
//    $entities = $this->entityTypeManager
//      ->getStorage($type)
//      ->loadMultiple($ids);
//
//    return array_map(function ($item) use ($entities) {
//      if (is_array($item['id'])) {
//        return array_reduce($item['id'], function ($carry, $current) use ($entities) {
//          if (!empty($entities[$current])) {
//            return $carry + [$current => $entities[$current]];
//          }
//
//          return $carry;
//        }, []);
//      }
//
//      return isset($entities[$item['id']]) ? $entities[$item['id']] : NULL;
//    }, $buffer);
  }

}
