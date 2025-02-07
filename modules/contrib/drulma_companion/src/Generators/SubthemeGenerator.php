<?php

namespace Drupal\drulma_companion\Generators;

use Drupal\Core\Extension\ThemeExtensionList;
use DrupalCodeGenerator\Command\ThemeGenerator;

/**
 * Implements a drulma subtheme generator.
 */
final class SubthemeGenerator extends ThemeGenerator {

  /**
   * Command name.
   *
   * @var string
   */
  protected string $name = 'theme:drulma';

  /**
   * Description.
   *
   * @var string
   */
  protected string $description = 'Generates a drulma subtheme.';

  /**
   * Alias.
   *
   * @var string
   */
  protected string $alias = 'drulma';

  /**
   * Template path.
   *
   * @var string
   */
  protected string $templatePath = __DIR__;

  /**
   * Theme extension list.
   *
   * @var \Drupal\Core\Extension\ThemeExtensionList
   */
  protected $themeExtensionList;

  /**
   * Generator constructor.
   */
  public function __construct(ThemeExtensionList $themeExtensionList = NULL) {
    parent::__construct($this->name);
    $this->themeExtensionList = $themeExtensionList;
  }

  /**
   * {@inheritdoc}
   */
  protected function generate(array &$vars): void {
    $this->collectDefault($vars);

    $vars['name'] = $this->ask('Theme name', 'Drulma subtheme', '::validateRequired');
    $vars['description'] = $this->ask(
        'Theme description',
        'A theme using Bulma: a free, open source CSS framework based on Flexbox.',
        '::validateRequired'
    );

    $this->addFile('{machine_name}.info.yml', 'subdrulma.info.yml');
    $this->addFile('{machine_name}.theme', 'subdrulma.theme');
    $this->addFile('{machine_name}.libraries.yml', 'subdrulma.libraries.yml');
    $this->addFile('css/overrides.css', 'css.overrides.css');

    $drulmaPath = DRUPAL_ROOT . '/' . $this->themeExtensionList->getPath('drulma');

    $this->addFile('config/install/{machine_name}.settings.yml')
      ->content(file_get_contents(
          $drulmaPath . '/config/install/drulma.settings.yml'
      ));

    $this->addFile('config/schema/{machine_name}.schema.yml')
      ->content(str_replace('drulma.settings', $vars['machine_name'] . '.settings', file_get_contents(
          $drulmaPath . '/config/schema/drulma.schema.yml'
      )));

    $this->addFile('favicon.ico')
      ->content(file_get_contents($drulmaPath . '/favicon.ico'));

    $this->addFile('logo.svg')
      ->content(file_get_contents($drulmaPath . '/logo.svg'));

    // Copy all the optional configuration.
    $files = glob($drulmaPath . '/config/optional/*.yml');
    foreach ($files as $file) {
      $destinationFile = str_replace('block.block.drulma_', 'block.block.' . $vars['machine_name'] . '_', $file);
      $content = str_replace("drulma\n", $vars['machine_name'] . "\n", file_get_contents($file));
      $content = str_replace('id: drulma', 'id: ' . $vars['machine_name'], $content);
      $this->addFile('config/optional/' . basename($destinationFile))
        ->content($content);
    }

    // Templates directory structure.
    $this->addDirectory('templates/page');
    $this->addDirectory('templates/node');
    $this->addDirectory('templates/field');
    $this->addDirectory('templates/views');
    $this->addDirectory('templates/block');
    $this->addDirectory('templates/menu');
    $this->addDirectory('images');
  }

}
