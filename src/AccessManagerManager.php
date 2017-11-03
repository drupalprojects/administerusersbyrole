<?php

namespace Drupal\administerusersbyrole;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\administerusersbyrole\AccessManagerManagerInterface;

/**
 * Plugin manager class for AccessManagerInterface.
 *
 * @ingroup entity_api
 */
class AccessManagerManager extends DefaultPluginManager implements AccessManagerManagerInterface {

  /* @var \Drupal\administerusersbyrole\Plugin\administerusersbyrole\AccessManagerInterface $manager */
  private $manager;

  /**
   * Constructs an AccessManagerManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/administerusersbyrole/AccessManager',
      $namespaces,
      $module_handler,
      'Drupal\administerusersbyrole\AccessManagerInterface',
      'Drupal\administerusersbyrole\Annotation\AccessManager'
    );
    $this->alterInfo('administerusersbyrole_access_manager');
    $this->setCacheBackend($cache_backend, 'administerusersbyrole_access_manager');
    $this->factory = new DefaultFactory($this->getDiscovery());
  }

  public function get() {
    if (!isset($this->manager)) {
      $config = \Drupal::config('administerusersbyrole.settings'); //@@ use injection
      $mode = $config->get('mode');
      $instance_config = $config->get($mode) ?: [];
      //@@ Config defaults
      $this->manager = $this->createInstance($mode, $instance_config);
    }
    return $this->manager;
  }

  public function getAll() {
    $config = \Drupal::config('administerusersbyrole.settings'); //@@ use injection
    $plugins = [];

    foreach ($this->getDefinitions() as $id => $plugin) {
      $instance_config = $config->get($id) ?: [];
      $plugins[$id] = $this->createInstance($id, $instance_config);
    }
    return $plugins;
  }

}
