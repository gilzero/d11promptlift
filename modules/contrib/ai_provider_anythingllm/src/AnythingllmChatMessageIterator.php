<?php

namespace Drupal\ai_provider_anythingllm;

use Drupal\ai\OperationType\Chat\StreamedChatMessage;
use Drupal\ai\OperationType\Chat\StreamedChatMessageIterator;

/**
 * AnythingLLM Chat message iterator.
 */
class AnythingllmChatMessageIterator extends StreamedChatMessageIterator {

  /**
   * {@inheritdoc}
   */
  public function getIterator(): \Generator {
    foreach ($this->iterator->getIterator() as $data) {
      yield new StreamedChatMessage(
        $data['choices'][0]['message']['role'] ?? '',
        $data['choices'][0]['message']['content'] ?? '',
        $data['choices'][0]['usage'] ?? []
      );
    }
  }

}
