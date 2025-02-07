<?php

namespace Drupal\ai_provider_anythingllm\OperationType\AiSearchApi;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\ai\Attribute\OperationType;
use Drupal\ai\OperationType\OperationTypeInterface;

/**
 * Interface for text to speech models.
 */
#[OperationType(
  id: 'ai_search_api',
  label: new TranslatableMarkup('AI Search API'),
)]
interface AiSearchApiInterface extends OperationTypeInterface {

  /**
   * Generate audio from text.
   *
   * Do not use this method directly. Use the invokeModelResponse instead with
   * TextToSpeechOperation as the operation type to make sure logging and
   * event triggering is working correctly.
   *
   * @param string|\Drupal\ai\Operation\TextToSpeech\TextToSpeechInput $input
   *   The text to generate audio from or a Output.
   * @param string $model_id
   *   The model id to use.
   * @param array $tags
   *   Extra tags to set.
   *
   * @return \Drupal\ai\OperationType\TextToSpeech\TextToSpeechOutput
   *   The output Output.
   */
  public function aiSearchApi(string|AiSearchApiInput $input, string $model_id, array $tags = []): AiSearchApiOutput;

}
