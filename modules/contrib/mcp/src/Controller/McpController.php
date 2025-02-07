<?php

declare(strict_types=1);

namespace Drupal\mcp\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Drupal\mcp\McpService;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Returns responses for Model Context Protocol routes.
 */
final class McpController extends ControllerBase {

  private const MCP_PROTOCOL_VERSION = '2024-11-05';

  private const MCP_HEARTBEAT_INTERVAL = 1;

  private const MCP_HEARTBEAT_TIMEOUT = 60;

  /**
   * Enable Server-Sent Events or not.
   */
  private bool $sseEnabled;

  public function __construct(
    #[Autowire(service: 'mcp.service')]
    protected McpService $mcpService,
    #[Autowire(service: 'state')]
    protected StateInterface $state,
  ) {
    $this->sseEnabled = $this->config('mcp.settings')->get('enable_sse') ??
      TRUE;
  }

  /**
   * Send a message to the client.
   */
  private function sendSseMessage(string $event, mixed $data): void {
    echo "event: $event\ndata: $data\n\n";

    if (ob_get_level() > 0) {
      ob_flush();
    }
    flush();
  }

  /**
   * Build a JSON-RPC response.
   */
  private function buildJsonRpcResponse(
    int $id,
    mixed $result = NULL,
    ?array $error = NULL,
  ): array {
    $response = [
      'jsonrpc' => '2.0',
      'id'      => $id,
    ];

    if ($error !== NULL) {
      $response['error'] = $error;
    }
    else {
      $response['result'] = $result;
    }

    return $response;
  }

  /**
   * Handle incoming message based on type.
   */
  private function handleMessage(array $message): array {
    try {
      if (!isset($message['id']) || !isset($message['method'])) {
        throw new \InvalidArgumentException('Invalid message format');
      }

      return match ($message['method']) {
        'initialize' => $this->buildJsonRpcResponse(
          $message['id'],
          [
            'protocolVersion' => self::MCP_PROTOCOL_VERSION,
            'capabilities'    => [
              'resources' => new \stdClass(),
              'tools'     => new \stdClass(),
            ],
            "serverInfo"      => [
              'name'    => 'Drupal MCP Server',
              'version' => '0.0.1',
            ],
          ]
        ),
        'tools/list' => $this->buildJsonRpcResponse(
          $message['id'],
          ['tools' => $this->mcpService->getTools()]
        ),
        'tools/call' => $this->buildJsonRpcResponse(
          $message['id'],
          [
            'content' => $this->mcpService->executeTool(
              $message['params']['name'] ?? '',
              $message['params']['arguments'] ?? []
            ),
          ]
        ),
        'resources/list' => $this->buildJsonRpcResponse(
          $message['id'],
          ['resources' => $this->mcpService->getResources()]
        ),
        'resources/templates/list' => $this->buildJsonRpcResponse(
          $message['id'],
          ['resourceTemplates' => $this->mcpService->getResourceTemplates()]
        ),
        'resources/read' => $this->buildJsonRpcResponse(
          $message['id'],
          [
            'contents' => $this->mcpService->readResource(
              $message['params']['uri'] ?? ''
            ),
          ]
        ),
        default => throw new \InvalidArgumentException('Unknown message type'),
      };
    }
    catch (\Throwable $e) {
      return $this->buildJsonRpcResponse(
        $message['id'] ?? 0,
        NULL,
        [
          'code'    => -32603,
          'message' => $e->getMessage(),
        ]
      );
    }
  }

  /**
   * SSE endpoint.
   */
  public function get(): StreamedResponse|JsonResponse {
    if (!$this->sseEnabled) {
      return new JsonResponse(['error' => 'SSE is disabled'], 400);
    }

    $client_id = uniqid('mcp-client-', TRUE);
    $this->state->set("mcp.client_id.$client_id", TRUE);

    $response = new StreamedResponse();
    $response->headers->set('Content-Type', 'text/event-stream');
    $response->headers->set('Cache-Control', 'no-cache');
    $response->headers->set('Connection', 'keep-alive');
    $response->headers->set('X-Accel-Buffering', 'no');

    $response->setCallback(function () use ($client_id) {
      while (ob_get_level() > 0) {
        ob_end_flush();
      }
      ob_implicit_flush();

      $this->sendSseMessage(
        'endpoint',
        Url::fromRoute('mcp.post', [], [
          'query' => ['sessionId' => $client_id],
        ])->toString()
      );

      $lastHeartbeat = time();
      while ((time() - $lastHeartbeat) < self::MCP_HEARTBEAT_TIMEOUT) {
        if (!$this->state->has("mcp.client_id.$client_id")) {
          $this->state->delete("mcp.message.$client_id");
          break;
        }

        if (connection_status() !== CONNECTION_NORMAL) {
          $this->state->delete("mcp.client_id.$client_id");
          $this->state->delete("mcp.message.$client_id");

          break;
        }

        $this->state->resetCache();
        $message = $this->state->get("mcp.message.$client_id");
        if ($message) {
          $this->sendSseMessage('message', json_encode($message));
          $this->state->delete("mcp.message.$client_id");
          $lastHeartbeat = time();
        }

        sleep(self::MCP_HEARTBEAT_INTERVAL);
      }
    });

    return $response;
  }

  /**
   * Post endpoint for receiving messages from the client.
   */
  public function post(Request $request): JsonResponse {
    $message = json_decode($request->getContent(), TRUE);
    if (!$message) {
      return new JsonResponse(['error' => 'Invalid JSON'], 400);
    }

    $response = $this->handleMessage($message);

    if ($this->sseEnabled) {
      $client_id = $request->query->get('sessionId');
      if (!$client_id || !$this->state->has("mcp.client_id.$client_id")) {
        return new JsonResponse(['error' => 'Invalid session'], 400);
      }

      $this->state->set("mcp.message.$client_id", $response);

      return new JsonResponse(['status' => 'ok']);
    }

    return new JsonResponse($response);
  }

}
