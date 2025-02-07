<?php

namespace Drupal\ui_suite_uswds;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

// cspell:ignore stylelintrc, gulpfile

/**
 * UI Suite USWDS subtheme manager.
 */
class SubthemeManager implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected FileSystemInterface $fileSystem;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected MessengerInterface $messenger;

  /**
   * The theme extension list.
   *
   * @var \Drupal\Core\Extension\ThemeExtensionList
   */
  protected ThemeExtensionList $themeExtensionList;

  /**
   * SubthemeManager constructor.
   *
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\Extension\ThemeExtensionList $extension_list_theme
   *   The theme extension list.
   */
  public function __construct(FileSystemInterface $file_system, MessengerInterface $messenger, ThemeExtensionList $extension_list_theme) {
    $this->fileSystem = $file_system;
    $this->messenger = $messenger;
    $this->themeExtensionList = $extension_list_theme;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    // @phpstan-ignore-next-line
    return new static(
      $container->get('file_system'),
      $container->get('messenger'),
      $container->get('extension.list.theme')
    );
  }

  /**
   * Validate callback.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @see hook_form_alter()
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $subthemePathValue = $form_state->getValue('subtheme_folder');
    // Check for empty values.
    if (!$subthemePathValue) {
      $form_state->setErrorByName('subtheme_folder', $this->t('Subtheme folder is empty.'));
    }
    if (!$form_state->getValue('subtheme_machine_name')) {
      $form_state->setErrorByName('subtheme_machine_name', $this->t('Subtheme machine name is empty.'));
    }
    if (count($form_state->getErrors())) {
      return;
    }

    // Check for path trailing slash.
    if (strrev(trim($subthemePathValue))[0] === '/') {
      $form_state->setErrorByName('subtheme_folder', $this->t('Subtheme folder should be without trailing slash.'));
    }
    // Check for name validity.
    if (!$form_state->getValue('subtheme_machine_name')) {
      $form_state->setErrorByName('subtheme_machine_name', $this->t('Subtheme name format is incorrect.'));
    }
    if (count($form_state->getErrors())) {
      return;
    }

    // Check for writable path.
    $directory = DRUPAL_ROOT . '/' . $subthemePathValue;
    if ($this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS) === FALSE) {
      $form_state->setErrorByName('subtheme_folder', $this->t('Subtheme cannot be created. Check permissions.'));
    }
    // Check for common theme names.
    if (in_array($form_state->getValue('subtheme_machine_name'), ['claro', 'bartik', 'seven'])) {
      $form_state->setErrorByName('subtheme_machine_name', $this->t('Subtheme name should not match existing themes.'));
    }

    if (count($form_state->getErrors())) {
      return;
    }

    // Check for reserved terms.
    if (in_array($form_state->getValue('subtheme_machine_name'), [
      'src', 'lib', 'vendor', 'assets', 'css', 'files', 'images', 'js', 'misc', 'templates', 'includes', 'fixtures', 'Drupal',
    ])) {
      $form_state->setErrorByName('subtheme_machine_name', t('Subtheme name should not match reserved terms.'));
    }

    // Validate machine name to ensure correct format.
    if (!preg_match("/^[a-z]+[0-9a-z_]+$/", $form_state->getValue('subtheme_machine_name'))) {
      $form_state->setErrorByName('subtheme_machine_name', t('Subtheme machine name format is incorrect.'));
    }
    // Check machine name is not longer than 50 characters.
    if (strlen($form_state->getValue('subtheme_machine_name')) > 50) {
      $form_state->setErrorByName('subtheme_folder', t('Subtheme machine name must not be longer than 50 characters.'));
    }

    // Check for writable path.
    $themePath = $directory . '/' . $form_state->getValue('subtheme_machine_name');
    if (file_exists($themePath)) {
      $form_state->setErrorByName('subtheme_machine_name', $this->t('Folder already exists.'));
    }
  }

  /**
   * Submit callback.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @see hook_form_alter()
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $fs = $this->fileSystem;

    // Create subtheme.
    $themeMName = $form_state->getValue('subtheme_machine_name');
    $themeName = $form_state->getValue('subtheme_name');
    if (empty($themeName)) {
      $themeName = $themeMName;
    }

    $subthemePathValue = $form_state->getValue('subtheme_folder');
    $themePath = DRUPAL_ROOT . DIRECTORY_SEPARATOR . $subthemePathValue . DIRECTORY_SEPARATOR . $themeMName;
    if (!is_dir($themePath)) {
      // Copy CSS + JS file replace empty one.
      $subfolders = [
        'starterkit_subtheme/sass',
        'starterkit_subtheme/sass/base',
        'starterkit_subtheme/sass/base/fonts',
        'starterkit_subtheme/sass/base/mixins',
        'starterkit_subtheme/sass/base/uswds',
        'starterkit_subtheme/sass/base/uswds/settings-list',
        'starterkit_subtheme/sass/layouts',
        'starterkit_subtheme/sass/theme',
        'starterkit_subtheme/js',
        'starterkit_subtheme/templates',
      ];
      foreach ($subfolders as $subfolder) {
        $directory = $themePath . DIRECTORY_SEPARATOR . str_replace('starterkit_subtheme/', '', $subfolder) . DIRECTORY_SEPARATOR;
        $fs->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

        $files = $fs->scanDirectory(
          $this->themeExtensionList->getPath('ui_suite_uswds') . DIRECTORY_SEPARATOR . $subfolder . DIRECTORY_SEPARATOR, '/.*scss|.*txt/', ['recurse' => FALSE],
        );

        foreach ($files as $file) {
          $fileName = $file->filename;
          $fs->copy(
            $this->themeExtensionList->getPath('ui_suite_uswds') . DIRECTORY_SEPARATOR . $subfolder . DIRECTORY_SEPARATOR . $fileName,
            $themePath . DIRECTORY_SEPARATOR . str_replace('starterkit_subtheme/', '', $subfolder) . DIRECTORY_SEPARATOR . $fileName, TRUE);
        }
      }

      // Copy image + linting files.
      $files = [
        'starterkit_subtheme/README.md',
        'favicon.ico',
        'logo.png',
        'screenshot.png',
        'starterkit_subtheme/gulpfile.js',
        'starterkit_subtheme/package.json',
        'starterkit_subtheme/.eslintrc.json-rename.txt',
        'starterkit_subtheme/.prettierrc.json',
        'starterkit_subtheme/.stylelintrc.json',
      ];
      foreach ($files as $fileName) {
        $destination = $fileName;
        if (str_contains($fileName, '/')) {
          $parts = explode('/', $fileName);
          array_shift($parts);
          $destination = implode('/', $parts);
        }

        if (str_contains($destination, '-rename.txt')) {
          $destination = str_replace('-rename.txt', '', $destination);
        }

        $fs->copy($this->themeExtensionList->getPath('ui_suite_uswds') . DIRECTORY_SEPARATOR . $fileName,
          $themePath . DIRECTORY_SEPARATOR . $destination, TRUE);
      }

      // Copy files and rename content (array of lines of copy existing).
      $files = [
        'ui_suite_uswds.ui_styles.yml' => [
          '/* eslint yml/no-empty-document: 0 */',
          '# Define custom UI Styles.',
          '',
        ],
        'ui_suite_uswds.breakpoints.yml' => -1,
        'ui_suite_uswds.libraries.yml' => [
          'global:',
          '  css:',
          '    theme:',
          '      assets/css/styles.css: { }',
          '  js:',
          '    assets/uswds/js/uswds.min.js: {}',
          '    assets/uswds/js/uswds-init.min.js: {}',
          '  dependencies:',
          '    - core/drupal',
          '    - core/jquery',
          '',
        ],
        'ui_suite_uswds.theme' => [
          '<?php',
          '',
          '/**',
          ' * @file',
          ' * ' . $themeName . ' theme file.',
          ' */',
          '',
        ],
      ];

      foreach ($files as $fileName => $lines) {
        // Get file content.
        $content = str_replace('ui_suite_uswds', $themeMName, file_get_contents($this->themeExtensionList->getPath('ui_suite_uswds') . DIRECTORY_SEPARATOR . $fileName));
        if (is_array($lines)) {
          $content = implode(PHP_EOL, $lines);
        }
        file_put_contents($themePath . DIRECTORY_SEPARATOR . str_replace('ui_suite_uswds', $themeMName, $fileName),
          $content);
      }

      // Info yml file generation.
      $infoYml = Yaml::decode(file_get_contents($this->themeExtensionList->getPath('ui_suite_uswds') . DIRECTORY_SEPARATOR . 'ui_suite_uswds.info.yml'));
      $infoYml['name'] = $themeName;
      $infoYml['description'] = $themeName . ' subtheme based on UI Suite USWDS theme.';
      $infoYml['base theme'] = 'ui_suite_uswds';

      $infoYml['libraries'] = [];
      $infoYml['libraries'][] = $themeMName . '/framework';

      foreach ([
        'version',
        'project',
        'datestamp',
        'starterkit',
        'generator',
        'libraries-extend',
      ] as $value) {
        if (isset($infoYml[$value])) {
          unset($infoYml[$value]);
        }
      }

      file_put_contents($themePath . DIRECTORY_SEPARATOR . $themeMName . '.info.yml',
        Yaml::encode($infoYml));

      // Add block config to subtheme.
      $orig_config_path = $this->themeExtensionList->getPath('ui_suite_uswds') . DIRECTORY_SEPARATOR . 'config/optional';
      $config_path = $themePath . DIRECTORY_SEPARATOR . 'config/optional';
      $files = scandir($orig_config_path);
      $fs->prepareDirectory($config_path, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
      foreach ($files as $filename) {
        if (str_starts_with($filename, 'block')) {
          $confYml = Yaml::decode(file_get_contents($orig_config_path . DIRECTORY_SEPARATOR . $filename));
          $confYml['dependencies']['theme'] = [];
          $confYml['dependencies']['theme'][] = $themeMName;
          $confYml['id'] = str_replace('ui_suite_uswds', $themeMName, $confYml['id']);
          $confYml['theme'] = $themeMName;
          $file_name = str_replace('ui_suite_uswds', $themeMName, $filename);
          file_put_contents($config_path . DIRECTORY_SEPARATOR . $file_name,
            Yaml::encode($confYml));
        }
      }

      // Add install config to subtheme.
      $orig_config_path = $this->themeExtensionList->getPath('ui_suite_uswds') . DIRECTORY_SEPARATOR . 'config/install';
      $config_path = $themePath . DIRECTORY_SEPARATOR . 'config/install';
      $files = scandir($orig_config_path);
      $fs->prepareDirectory($config_path, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
      foreach ($files as $filename) {
        if (str_starts_with($filename, 'ui_suite_uswds')) {
          $confYml = Yaml::decode(file_get_contents($orig_config_path . DIRECTORY_SEPARATOR . $filename));
          $file_name = str_replace('ui_suite_uswds', $themeMName, $filename);
          file_put_contents($config_path . DIRECTORY_SEPARATOR . $file_name,
            Yaml::encode($confYml));
        }
      }

      $this->messenger->addStatus(t('Subtheme created at %subtheme', [
        '%subtheme' => $themePath,
      ]));
    }
    else {
      $this->messenger->addError(t('Folder already exists at %subtheme', [
        '%subtheme' => $themePath,
      ]));
    }
  }

}
