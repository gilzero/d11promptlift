<?php

namespace Drupal\autoservices;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Finder\Finder;

/**
 * Defines a service provider for the Autoservices module.
 */
class AutoservicesServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    $this->aliasInterfaces($container);

    foreach ($container->getParameter('container.modules') as $moduleName => $moduleInfo) {
      $autoservices = [
        'Autoservice' => [
          'autowire' => TRUE,
        ],
        'AutoPluginManager' => [
          'autowire' => FALSE,
          'parent' => 'default_plugin_manager',
        ],
        'AutoEventSubscriber' => [
          'autowire' => TRUE,
          'tags' => [
            ['name' => 'event_subscriber'],
          ],
        ],
      ];

      foreach ($autoservices as $namespace => $configuration) {
        $path = dirname($moduleInfo['pathname']) . '/src/' . $namespace;
        if (file_exists($path)) {
          $finder = new Finder();
          $finder->in($path)->files()->name('*.php');
          foreach ($finder as $fileInfo) {
            $class = 'Drupal\\' . $moduleName . '\\' . $namespace . '\\' . substr($fileInfo->getFilename(), 0, -4);
            if ($container->hasDefinition($class)) {
              continue;
            }
            $definition = isset($configuration['parent']) ? new ChildDefinition($configuration['parent']) : new Definition($class);
            $definition->setAutowired($configuration['autowire']);
            if (isset($configuration['tags'])) {
              foreach ($configuration['tags'] as $tag) {
                $definition->addTag($tag['name']);
              }
            }
            $definition->setPublic(TRUE);
            $container->setDefinition($class, $definition);
          }
        }
      }
    }
  }

  /**
   * Add aliases for all services that implement an obvious interface.
   */
  protected function aliasInterfaces(ContainerBuilder $container) {
    foreach ($container->getDefinitions() as $definitionName => $definition) {
      $definitionInitial = substr($definitionName, 0, 1);
      $interfaceCandidate = $definition->getClass() . 'Interface';
      if (
        $definitionInitial === strtolower($definitionInitial) &&
        interface_exists($interfaceCandidate) &&
        !$container->hasAlias($interfaceCandidate)
      ) {
        $container->setAlias($interfaceCandidate, $definitionName);
      }
    }
  }

}
