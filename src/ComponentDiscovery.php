<?php

/**
 * @file
 * Contains \Drupal\pdb\ComponentDiscovery.
 */

namespace Drupal\pdb;

use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\Core\Extension\InfoParserInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

class ComponentDiscovery extends ExtensionDiscovery implements ComponentDiscoveryInterface {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The info parser.
   *
   * @var \Drupal\Core\Extension\InfoParserInterface
   */
  protected $infoParser;

  /**
   * ComponentDiscovery constructor.
   *
   * @param string $root
   *   The root directory of the Drupal installation.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Extension\InfoParserInterface $info_parser
   *   The info parser.
   */
  public function __construct($root, ModuleHandlerInterface $module_handler, InfoParserInterface $info_parser) {
    parent::__construct($root);
    $this->moduleHandler = $module_handler;
    $this->infoParser = $info_parser;
  }

  /**
   * {@inheritdoc}
   */
  public function getComponents() {
    // Find components.
    $components = $this->scan('pdb');

    // Set defaults for module info.
    $defaults = array(
      'dependencies' => array(),
      'description' => '',
      'package' => 'Other',
      'version' => NULL,
    );

    // Read info files for each module.
    foreach ($components as $key => $component) {
      // Look for the info file.
      $component->info = $this->infoParser->parse($component->getPathname());
      $component->info['path'] = $component->origin . '/' . $component->subpath;

      // Merge in defaults and save.
      $components[$key]->info = $component->info + $defaults;
    }
    $this->moduleHandler->alter('component_info', $components);

    return $components;
  }

}