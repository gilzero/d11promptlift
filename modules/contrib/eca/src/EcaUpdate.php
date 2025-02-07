<?php

declare(strict_types=1);

namespace Drupal\eca;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\eca\Service\Modellers;

/**
 * Provides methods to update all existing ECA models and to output messages.
 */
final class EcaUpdate {

  /**
   * ECA config entity storage manager.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected EntityStorageInterface $configStorage;

  /**
   * List of errors.
   *
   * @var array
   */
  protected array $errors;

  /**
   * List of info messages.
   *
   * @var array
   */
  protected array $infos;

  /**
   * Constructs an EcaUpdate object.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly Modellers $ecaServiceModeller,
    private readonly MessengerInterface $messenger,
  ) {
    $this->configStorage = $this->entityTypeManager->getStorage('eca');
  }

  /**
   * Updates all existing ECA entities calling ::updateModel in their modeller.
   */
  public function updateAllModels(): void {
    $this->errors = [];
    $this->infos = [];
    /** @var \Drupal\eca\Entity\Eca $eca */
    foreach ($this->configStorage->loadMultiple() as $eca) {
      $modeller = $this->ecaServiceModeller->getModeller($eca->get('modeller'));
      if ($modeller === NULL) {
        $this->errors[] = '[' . $eca->label() . '] This modeller plugin ' . $eca->get('modeller') . ' for this ECA model does not exist.';
        continue;
      }
      $model = $eca->getModel();
      $modeller->setConfigEntity($eca);
      if ($modeller->updateModel($model)) {
        $filename = $model->getFilename();
        if ($filename && file_exists($filename)) {
          file_put_contents($filename, $model->getModeldata());
        }
        try {
          $modeller->save($model->getModeldata(), $filename);
          $this->infos[] = '[' . $eca->label() . '] Model has been updated.';
        }
        catch (\LogicException | EntityStorageException $e) {
          $this->errors[] = '[' . $eca->label() . '] Error while updating this model: ' . $e->getMessage();
        }
      }
      else {
        $this->infos[] = '[' . $eca->label() . '] Model does not require any updates.';
      }
    }
  }

  /**
   * Gets the list of all collected error messages.
   *
   * @return array
   *   The list of all collected error messages.
   */
  public function getErrors(): array {
    return $this->errors;
  }

  /**
   * Gets the list of all collected info messages.
   *
   * @return array
   *   The list of all collected info messages.
   */
  public function getInfos(): array {
    return $this->infos;
  }

  /**
   * Outputs all messages (info and error) to the user.
   */
  public function displayMessages(): void {
    foreach ($this->infos ?? [] as $info) {
      $this->messenger->addMessage($info);
    }
    foreach ($this->errors ?? [] as $error) {
      $this->messenger->addError($error);
    }
  }

}
