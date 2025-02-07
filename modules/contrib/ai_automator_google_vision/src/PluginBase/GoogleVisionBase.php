<?php

namespace Drupal\ai_automator_google_vision\PluginBase;

use Drupal\ai_automators\Exceptions\AiAutomatorRequestErrorException;
use Drupal\ai_automators\PluginBaseClasses\ExternalBase;
use Drupal\ai_automator_google_vision\Form\GoogleVisionConfigForm;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for Google Vision AI Automators.
 */
class GoogleVisionBase extends ExternalBase implements ContainerFactoryPluginInterface {

  /**
   * The key repository.
   *
   * @var \Drupal\key\KeyRepository|null
   */
  protected $keyRepository;

  /**
   * The config for authentication.
   *
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Construct a Google Vision base.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\key\KeyRepository|null $keyRepository
   *   The key repository from key module (optional).
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    $keyRepository = NULL
  ) {
    $this->config = $configFactory->get(GoogleVisionConfigForm::CONFIG_NAME);
    $this->keyRepository = $keyRepository;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    // @phpstan-ignore-next-line
    return new static(
      $container->get('config.factory'),
      $container->get('key.repository')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function needsPrompt() {
    // Doesn't need a prompt.
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function advancedMode() {
    // Doesn't take any advanced inputters.
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function placeholderText() {
    // No placeholder text.
    return "";
  }

  /**
   * {@inheritDoc}
   */
  public function ruleIsAllowed(ContentEntityInterface $entity, FieldDefinitionInterface $fieldDefinition) {
    return TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function allowedInputs() {
    // The inputs are a file or an image.
    return [
      'file',
      'image',
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function generate(ContentEntityInterface $entity, FieldDefinitionInterface $fieldDefinition, array $interpolatorConfig) {
    // From the configuration try to load the file.
    $file = $this->keyRepository->getKey($this->config->get('file_location_key'))->getKeyValue();
    try {
      $file = $this->keyRepository->getKey($this->config->get('file_location_key'))->getKeyValue();
      // Check if the file exists and it will be readable.
      if (!file_exists($file) || !is_file($file)) {
        throw new \Exception('File does not exist referenced in /admin/config/google_vision/settings.');
      }
    }
    catch (\Exception $e) {
      throw new AiAutomatorRequestErrorException('Error loading authentication file for Google Vision AI: ' . $e->getMessage());
    }

    // Set the credentials into environments variable needed by Google.
    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $file);
    // Doesn't actually return any values.
    return [];
  }

}
