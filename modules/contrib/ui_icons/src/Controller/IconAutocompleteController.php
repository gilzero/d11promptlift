<?php

declare(strict_types=1);

namespace Drupal\ui_icons\Controller;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Drupal\Core\Theme\Icon\IconDefinitionInterface;
use Drupal\ui_icons\IconSearch;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for UI Icons routes.
 */
class IconAutocompleteController extends ControllerBase {

  public function __construct(
    private readonly IconSearch $iconSearch,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('ui_icons.search'),
    );
  }

  /**
   * Menu callback for UI Icons autocompletion.
   *
   * This function inspects the 'q' query parameter for the string to use to
   * search for icons, and allowed_icon_pack if set.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The route request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A JSON response containing the autocomplete suggestions for Icons.
   */
  public function handleSearchIcons(Request $request): JsonResponse {
    $query = trim((string) $request->query->get('q', ''));

    if (empty($query)) {
      return new JsonResponse([]);
    }

    $allowed_icon_pack = [];
    if ($request->query->get('allowed_icon_pack', NULL)) {
      $allowed_icon_pack = explode('+', (string) $request->query->get('allowed_icon_pack', ''));
    }

    $max_result = (int) $request->query->get('max_result', IconSearch::SEARCH_MAX_RESULT);
    $result = $this->iconSearch->search(
      $query,
      $allowed_icon_pack,
      $max_result,
     [$this::class, 'createResultEntry']
    );

    return new JsonResponse($result);
  }

  /**
   * Create icon result.
   *
   * @param \Drupal\Core\Theme\Icon\IconDefinitionInterface $icon
   *   The icon to process.
   * @param \Drupal\Core\Render\Markup $renderable
   *   The icon preview renderable.
   *
   * @return array|null
   *   The icon result with keys 'value' and 'label' for autocomplete.
   */
  public static function createResultEntry(IconDefinitionInterface $icon, Markup $renderable): ?array {
    $label = sprintf('%s (%s)', $icon->getLabel(), $icon->getPackLabel() ?? $icon->getPackId());
    $param = ['@icon' => $renderable, '@name' => $label];
    $label = new FormattableMarkup('<span class="ui-menu-icon">@icon</span> @name', $param);

    return ['value' => $icon->getId(), 'label' => $label];
  }

}
