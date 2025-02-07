<?php

declare(strict_types=1);

namespace Drupal\ai_deepchat\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\ai_assistant_api\AiAssistantApiRunner;
use Drupal\ai_assistant_api\Data\UserMessage;
use Drupal\ai_assistant_api\Entity\AiAssistant;
use Drupal\ai\OperationType\Chat\ChatMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Returns responses for AI Deepchat routes.
 */
final class DeepChatApi extends ControllerBase
{
  /**
   * The AI Assistant API client.
   *
   * @var \Drupal\ai_assistant_api\AiAssistantApiRunner
   */
  private AiAssistantApiRunner $aiAssistantClient;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected LoggerInterface $logger;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new DeepChatApi object.
   *
   * @param \Drupal\ai_assistant_api\AiAssistantApiRunner $aiAssistantClient
   *   The AI Assistant API client.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(
    AiAssistantApiRunner $aiAssistantClient,
    EntityTypeManagerInterface $entityTypeManager,
    LoggerInterface $logger
  ) {
    $this->aiAssistantClient = $aiAssistantClient;
    $this->entityTypeManager = $entityTypeManager;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self
  {
    return new static(
      $container->get("ai_assistant_api.runner"),
      $container->get("entity_type.manager"),
      $container->get("logger.factory")->get("ai_deepchat")
    );
  }

  /**
   * Handles the DeepChat API request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The HTTP request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\StreamedResponse
   *   The JSON or Streamed response.
   */
  public function api(Request $request): JsonResponse|StreamedResponse
  {
    /**
     * TODO: Add API-Logging for prompt and response
     */

    // Get the request content and decode it.
    $content = $request->getContent();
    $data = json_decode($content, true);
    $this->logger->info("Request data: @data", [
      "@data" => print_r($data, true),
    ]);

    if (!empty($data["stream"])) {
      $response_type = "stream";
    } else {
      $response_type = "json";
    }

    // Retrieve the assistant_id from request payload.
    if (isset($data["assistant_id"])) {
      $assistant_id = $data["assistant_id"];

      // Load the AiAssistant entity.
      $assistant = $this->entityTypeManager
        ->getStorage("ai_assistant")
        ->load($assistant_id);

      if (!$assistant instanceof AiAssistant) {
        return new JsonResponse(
          ["error" => $this->t("Invalid assistant ID.")],
          400
        );
      }

      // Set the assistant in the AiAssistantApiRunner.
      $this->aiAssistantClient->setAssistant($assistant);
    } else {
      // Assistant ID is required.
      return new JsonResponse(
        ["error" => $this->t("assistant_id is required.")],
        400
      );
    }

    // Optionally, set the thread_id if provided.
    if (isset($data["thread_id"])) {
      $this->aiAssistantClient->setThreadsKey($data["thread_id"]);
    }

    // Set the context if provided.
    if (isset($data["context"]) && is_array($data["context"])) {
      $this->aiAssistantClient->setContext($data["context"]);
    }

    // Check if 'messages' array is provided.
    if (isset($data["messages"]) && is_array($data["messages"])) {
      $messages = $data["messages"];

      // Extract user messages.
      $conversation = [];
      foreach ($messages as $message) {
        if (isset($message["role"], $message["text"])) {
          $role = $message["role"];
          $text = $message["text"];
          if ($role === "user") {
            $conversation[] = new UserMessage($text);
          }
        }
      }

      if (empty($conversation)) {
        return new JsonResponse(
          ["error" => $this->t("No user messages provided.")],
          400
        );
      }

      // Set the latest user message.
      $latestUserMessage = $conversation[count($conversation) - 1];
      $this->aiAssistantClient->setUserMessage($latestUserMessage);

      // Process the user's message.
      try {
        $response = $this->aiAssistantClient->process();

        // Handle the response, which could be a ChatMessage or an array of messages.
        $normalizedResponse = $response->getNormalized();
        $assistantResponseText = "";

        if ($normalizedResponse instanceof ChatMessage) {
          $assistantResponseText = $normalizedResponse->getText();
        } elseif (is_array($normalizedResponse)) {
          foreach ($normalizedResponse as $message) {
            if ($message instanceof ChatMessage) {
              $assistantResponseText .= $message->getText();
            }
          }
        }

        // Set the assistant message for logging or further processing.
        $this->aiAssistantClient->setAssistantMessage($assistantResponseText);

        // Decide response type based on the request.
        if ($response_type === "stream") {
          return $this->createStreamedResponse(
            $assistantResponseText,
            $this->aiAssistantClient->getThreadsKey()
          );
        } else {
          // Default to JSON response.
          return new JsonResponse([
            "text" => $assistantResponseText,
            "assistant_id" => $this->aiAssistantClient->getThreadsKey(),
          ]);
        }
      } catch (\Exception $e) {
        $this->logger->error("Error processing AI assistant: @message", [
          "@message" => $e->getMessage(),
        ]);
        return new JsonResponse(
          ["error" => $this->t("Error processing AI assistant.")],
          500
        );
      }
    } else {
      // No messages provided in the request.
      return new JsonResponse(
        ["error" => $this->t("No messages provided.")],
        400
      );
    }
  }

  /**
   * Creates a StreamedResponse for the assistant's reply.
   *
   * @param string $assistantResponseText
   *   The text generated by the AI assistant.
   * @param string $threadsKey
   *   The thread identifier.
   *
   * @return \Symfony\Component\HttpFoundation\StreamedResponse
   *   The streamed response.
   */
  private function createStreamedResponse(
    string $assistantResponseText,
    string $threadsKey
  ): StreamedResponse {
    $response = new StreamedResponse();

    // Set headers for streaming.
    $response->headers->set("Content-Type", "text/event-stream");
    $response->headers->set("Cache-Control", "no-cache");
    $response->headers->set("Connection", "keep-alive");

    $response->setCallback(function () use (
      $assistantResponseText,
      $threadsKey
    ) {
      // Disable PHP output buffering.
      while (ob_get_level() > 0) {
        ob_end_flush();
      }

      // Ensure implicit flush is enabled.
      ini_set("implicit_flush", "1");
      ob_implicit_flush(true);

      // Split the response text into chunks.
      $chunks = str_split($assistantResponseText, 20); // Adjust chunk size as needed.
      foreach ($chunks as $chunk) {
        // Prepare the SSE-compliant event.
        $eventData = [
          "text" => $chunk,
          // Optionally, include other fields like 'html' if needed.
        ];

        // Send the event in SSE format.
        echo "data: " . json_encode($eventData) . "\n\n";

        // Flush the output buffers to send the chunk immediately.
        flush();

        // Optional: Introduce a delay to simulate real-time streaming.
        usleep(50000); // 50ms
      }

      // Optionally, send the thread ID as a final event.
      $threadEvent = [
        "thread_id" => $threadsKey,
      ];
      echo "data: " . json_encode($threadEvent) . "\n\n";
      flush();
    });

    return $response;
  }
}
