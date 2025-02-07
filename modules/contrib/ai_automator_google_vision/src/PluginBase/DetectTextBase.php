<?php

namespace Drupal\ai_automator_google_vision\PluginBase;

use Drupal\ai_automators\Exceptions\AiautomatorRequestErrorException;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Google\Cloud\Vision\V1\Feature\Type;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;

/**
 * One class for all the Drupal string types.
 */
class DetectTextBase extends GoogleVisionBase {

  /**
   * Line divider.
   *
   * @var string
   *   The line divider.
   */
  protected $lineDivider = "\n";

  /**
   * {@inheritDoc}
   */
  public function extraAdvancedFormFields(ContentEntityInterface $entity, FieldDefinitionInterface $fieldDefinition, FormStateInterface $formState, array $defaultValues = []) {
    // Load parent.
    $form = parent::extraAdvancedFormFields($entity, $fieldDefinition, $formState, $defaultValues);

    // Add a possibility to extract each text block as a separate field.
    $form['automator_vision_extract_text_blocks'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Extract text blocks'),
      '#description' => $this->t('Extract each text block as a separate field. NOTE - you have to have setup that the field has unlimited amount of values.'),
      '#default_value' => $defaultValues['automator_vision_extract_text_blocks'] ?? FALSE,
    ];

    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function generate(ContentEntityInterface $entity, FieldDefinitionInterface $fieldDefinition, array $automatorConfig) {
    // Let the parent generate to setup the base.
    parent::generate($entity, $fieldDefinition, $automatorConfig);
    // Check if to divide into text.
    $total = [];
    // Load each file.
    foreach ($entity->{$automatorConfig['base_field']} as $entityWrapper) {
      // An entity is found.
      if ($entityWrapper->entity) {
        $fileEntity = $entityWrapper->entity;
        $parts = $this->googleVisionDetectText($fileEntity, $automatorConfig['vision_extract_text_blocks']);
        $total = array_merge($total, $parts);
      }
    }
    return $total;
  }

  /**
   * Google Vision API to detect text.
   *
   * @param \Drupal\file\Entity\File $file
   *   The file to use.
   * @param bool $divide
   *   If to divide the text.
   *
   * @return string
   *   The text detected.
   */
  protected function googleVisionDetectText(File $file, $divide = FALSE) {
    // Make the call.
    $annotation = $this->makeGoogleVisionRequest($file);
    $values = [];
    // Return the values.
    if (isset($annotation->getTextAnnotations()[0])) {
      if ($divide) {
        foreach ($annotation->getTextAnnotations() as $text) {
          $values[] = $text->getDescription();
        }
      }
      else {
        $values[] = $annotation->getTextAnnotations()[0]->getDescription();
      }
    }

    return $values;
  }

  /**
   * Make request to Google Vision API.
   *
   * @param \Drupal\file\Entity\File $file
   *   The file to use.
   *
   * @return \Google\Cloud\Vision\V1\AnnotateImageResponse
   *   The response.
   */
  protected function makeGoogleVisionRequest(File $file) {
    $bytes = NULL;
    // Check the content type.
    switch ($file->getMimeType()) {
      case 'image/jpeg':
      case 'image/png':
        $bytes = fopen($file->getFileUri(), 'r');
        break;

      case 'application/pdf':
        $bytes = $this->convertImageToPdf($file);
        break;

      default:
        // Wrong mime type for what is allowed.
        throw new AiautomatorRequestErrorException('Unsupported mime type ' . $file->getMimeType() . '  for file ' . $file->getFileUri());
    }
    // Do the call.
    $annotator = new ImageAnnotatorClient();
    $annotation = $annotator->annotateImage(
      $bytes,
      [Type::TEXT_DETECTION]
    );
    return $annotation;
  }

  /**
   * Convert image to PDF using Imagick.
   *
   * @param \Drupal\file\Entity\File $file
   *   The file to convert.
   *
   * @return string
   *   The read new file.
   */
  protected function convertImageToPdf(File $file): string {
    // First check so Imagick is available.
    if (!extension_loaded('imagick')) {
      throw new AiautomatorRequestErrorException('Imagick is not available and is a requirement to read PDF files.');
    }

    // Try converting.
    try {
      // Create a temp file.
      $bytes = tmpfile();
      $imagick = new \Imagick();
      $imagick->readImage($file->getFileUri());
      $imagick->setBackgroundColor('white');
      $imagick->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
      $imagick->resetIterator();
      $imagick = $imagick->appendImages(TRUE);
      $imagick->writeImageFile($bytes, 'png');
    }
    catch (\Exception $e) {
      throw new AiautomatorRequestErrorException('Imagick error for file ' . $file->getFileUri() . '. ' . $e->getMessage());
    }
    rewind($bytes);
    return stream_get_contents($bytes);

  }

}
