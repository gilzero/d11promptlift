<?php

namespace Drupal\facets;

use Drupal\Core\Config\Entity\DraggableListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Component\Utility\Html;
use Drupal\Core\Url;
use Drupal\facets_summary\Entity\FacetsSummary;
use Drupal\facets_summary\FacetsSummaryInterface;

/**
 * Builds a listing of facet entities.
 */
class FacetListBuilder extends DraggableListBuilder {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'facets_overview';
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    if ($entity instanceof FacetInterface) {

      if ($entity->access('update') && $entity->hasLinkTemplate('edit-form')) {
        $operations['edit'] = [
          'title' => $this->t('Edit'),
          'weight' => 10,
          'url' => $entity->toUrl('edit-form'),
        ];
      }
      if ($entity->access('update') && $entity->hasLinkTemplate('settings-form')) {
        $operations['settings'] = [
          'title' => $this->t('Facet settings'),
          'weight' => 20,
          'url' => $entity->toUrl('settings-form'),
        ];
      }
      if ($entity->access('update') && $entity->hasLinkTemplate('clone-form')) {
        $operations['clone'] = [
          'title' => $this->t('Clone facet'),
          'weight' => 90,
          'url' => $entity->toUrl('clone-form'),
        ];
      }
      if ($entity->access('delete') && $entity->hasLinkTemplate('delete-form')) {
        $operations['delete'] = [
          'title' => $this->t('Delete'),
          'weight' => 100,
          'url' => $entity->toUrl('delete-form'),
        ];
      }
    }
    elseif ($entity instanceof FacetsSummaryInterface) {
      $operations['edit'] = [
        'title' => $this->t('Edit'),
        'weight' => 10,
        'url' => $entity->toUrl('edit-form'),
      ];
      if ($entity->access('update') && $entity->hasLinkTemplate('settings-form')) {
        $operations['settings'] = [
          'title' => $this->t('Facet Summary settings'),
          'weight' => 20,
          'url' => $entity->toUrl('settings-form'),
        ];
      }
      if ($entity->access('delete') && $entity->hasLinkTemplate('delete-form')) {
        $operations['delete'] = [
          'title' => $this->t('Delete'),
          'weight' => 100,
          'url' => $entity->toUrl('delete-form'),
        ];
      }
    }

    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [
      'type' => $this->t('Type'),
      'machine_name' => $this->t('Machine name'),
      'title' => [
        'data' => $this->t('Title'),
      ],
    ];
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\facets\FacetInterface $entity */
    $facet_configs = \Drupal::entityTypeManager()
      ->getStorage('facets_facet')
      ->load($entity->getConfigTarget());
    $row = [
      'type' => [
        '#theme_wrappers' => [
          'container' => [
            '#attributes' => ['class' => 'facets-type'],
          ],
        ],
        '#type' => 'markup',
        '#markup' => 'Facet',
      ],
      'machine_name' => ['#markup' => $entity->id()],
      'title' => [
        '#type' => 'link',
        '#title' => $facet_configs->get('name'),
        '#suffix' => '<div>' . $entity->getFieldAlias() . ' - ' . $entity->getWidget()['type'] . '</div>',
        '#attributes' => [
          'class' => ['search-api-title'],
        ],
      ] + $this->facetUrlRenderArray($entity),
      '#attributes' => [
        'title' => $this->t('ID: @name', ['@name' => $entity->id()]),
        'class' => [
          'facet',
        ],
      ],
    ];
    return array_merge_recursive($row, parent::buildRow($entity));
  }

  /**
   * Builds an array of facet summary for display in the overview.
   */
  public function buildFacetSummaryRow(FacetsSummaryInterface $entity) {
    $row = parent::buildRow($entity);
    return [
      'type' => [
        '#theme_wrappers' => [
          'container' => [
            '#attributes' => ['class' => 'facets-summary-type'],
          ],
        ],
        '#type' => 'markup',
        '#markup' => 'Facets Summary',
      ],
      'machine_name' => ['#markup' => $entity->id()],
      'title' => [
        '#theme_wrappers' => [
          'container' => [
            '#attributes' => ['class' => 'facets-title'],
          ],
        ],
        '#type' => 'link',
        '#title' => $entity->label(),
        '#attributes' => [
          'class' => ['search-api-title'],
        ],
        '#wrapper_attributes' => [
          'colspan' => 2,
        ],
      ] + $this->facetUrlRenderArray($entity),
      'operations' => $row['operations'],
      '#attributes' => [
        'title' => $this->t('ID: @name', ['@name' => $entity->id()]),
        'class' => [
          'facet',
        ],
      ],
    ];
  }

  /**
   * Builds an array of facet sources for display in the overview.
   */
  public function buildFacetSourceRow(array $facet_source = []) {
    return [
      'type' => [
        '#theme_wrappers' => [
          'container' => [
            '#attributes' => ['class' => 'facets-type'],
          ],
        ],
        '#type' => 'markup',
        '#markup' => 'Facet source',
      ],
      'machine_name' => ['#markup' => $facet_source['id']],
      'title' => [
        '#theme_wrappers' => [
          'container' => [
            '#attributes' => ['class' => 'facets-title'],
          ],
        ],
        '#type' => 'markup',
        '#markup' => $facet_source['label'],
        '#wrapper_attributes' => [
          'colspan' => 2,
        ],
      ],
      'operations' => [
        'data' => Link::createFromRoute(
          $this->t('Configure'),
          'entity.facets_facet_source.edit_form',
          ['facets_facet_source' => $facet_source['id']]
        )->toRenderable(),
      ],
      '#attributes' => [
        'class' => ['facet-source', 'facet-source-' . $facet_source['id']],
        'no_striping' => TRUE,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $groups = $this->loadGroups();

    $form['facets_3_exposed_filters'] = ['#markup' => '<div class="messages messages--warning">' . t('<strong>New in Facets 3: </strong>For new sites it is recommended to use the "Facets Exposed Filters" submodule for Facets on Views. This module uses native Views filters instead, which has many advantages.<br><a target="_blank" href="@documentation_url">Click here</a> for documentation on how to use this new workflow.', ['@documentation_url' => 'https://project.pages.drupalcode.org/facets/exposed_filters']) . '</div>'];
    $form['facets_3_block_support'] = ['#markup' => '<p>' . t('Adding Facets as a block <a target="_blank" href="@documentation_url_facet_blocks">is still supported</a> for backwards compatibility.', ['@documentation_url_facet_blocks' => 'https://project.pages.drupalcode.org/facets/facet_blocks_support']) . '</p>'];

    $form['facets'] = [
      '#type' => 'table',
      '#header' => $this->buildHeader(),
      '#empty' => $groups['lone_facets'] ? '' : $this->t('There are no facet sources or facets defined.'),
      '#attributes' => [
        'class' => [
          'facets-groups-list',
        ],
      ],
    ];

    // When no facet sources are found, we should show a message that you can't
    // add facets yet.
    if (empty($groups['facet_source_groups'])) {
      return [
        '#markup' => $this->t(
          'You currently have no facet sources defined. You should start by adding a facet source before creating facets.<br />
           An example of a facet source is a view based on Search API or a Search API page.
           Other modules can also implement a facet source by providing a plugin that implements the FacetSourcePluginInterface.'
        ),
      ];
    }

    $form['#attached']['library'][] = 'facets/drupal.facets.admin_css';

    foreach ($groups['facet_source_groups'] as $facet_source_group) {
      $subgroup_class = Html::cleanCssIdentifier('facets-weight-' . $facet_source_group['facet_source']['id']);
      $delta = round(count($facet_source_group['facets']) / 2);

      $form['facets']['#tabledrag'][] = [
        'action' => 'order',
        'relationship' => 'sibling',
        'group' => 'weight',
        'subgroup' => $subgroup_class,
      ];
      $form['facets'][$facet_source_group['facet_source']['id']] = $this->buildFacetSourceRow($facet_source_group['facet_source']);
      foreach ($facet_source_group['facets'] as $facet) {
        if ($facet instanceof FacetInterface) {
          $form['facets'][$facet->id()] = $this->buildRow($facet);
          $form['facets'][$facet->id()]['weight']['#attributes']['class'][] = $subgroup_class;
          $form['facets'][$facet->id()]['weight']['#delta'] = $delta;
        }
        elseif ($facet instanceof FacetsSummaryInterface) {
          $form['facets'][$facet->id()] = $this->buildFacetSummaryRow($facet);
        }
      }
    }

    // Output the list of facets without a facet source separately.
    if (!empty($groups['lone_facets'])) {
      $subgroup_class = 'facets-weight-lone-facets';
      $form['facets']['#tabledrag'][] = [
        'action' => 'order',
        'relationship' => 'sibling',
        'group' => 'weight',
        'subgroup' => $subgroup_class,
      ];
      $form['facets']['lone_facets'] = [
        'type' => [
          '#theme_wrappers' => [
            'container' => [
              '#attributes' => ['class' => 'facets-type'],
            ],
          ],
          '#type' => 'markup',
          '#markup' => '<h3>' . $this->t('Facets not currently associated with any facet source') . '</h3>',
        ],
        '#wrapper_attributes' => [
          'colspan' => 4,
        ],
      ];
      /** @var \Drupal\facets\FacetInterface $facet */
      foreach ($groups['lone_facets'] as $facet) {
        // Facets core search moved into a separate project. Show a clean
        // message to notify users how to resolve their broken facets.
        if ($facet->getFacetSourceId() && substr($facet->getFacetSourceId(), 0, 16) == 'core_node_search') {
          $project_link = Link::fromTextAndUrl('https://www.drupal.org/project/facets_core_search', Url::fromUri('https://www.drupal.org/project/facets_core_search'))->toString();
          \Drupal::messenger()->addError(
            $this->t(
              'Core search facets has been moved to a separate project. You need to download and enable this module from @project_link to continue using your core search facets.',
              ['@project_link' => $project_link]),
            'error'
          );
        }
        $form['facets'][$facet->id()] = $this->buildRow($facet);
        $form['facets'][$facet->id()]['weight']['#attributes']['class'][] = $subgroup_class;
      }
    }

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $facets = $form_state->getValue('facets');
    $facets = is_string($facets) ? [] : $facets;
    $entities = $this->storage->loadMultiple(array_keys($facets));
    /** @var \Drupal\block\BlockInterface[] $entities */
    foreach ($entities as $entity_id => $entity) {
      $entity_values = $form_state->getValue(['facets', $entity_id]);
      $entity->setWeight($entity_values['weight']);
      $entity->save();
    }
    \Drupal::messenger()->addMessage($this->t('The facets have been updated.'));
  }

  /**
   * Loads facet sources and facets, grouped by facet sources.
   *
   * @return \Drupal\Core\Config\Entity\ConfigEntityInterface[][]
   *   An associative array with two keys:
   *   - facet sources: All available facet sources, each followed by all facets
   *     attached to it.
   *   - lone_facets: All facets that aren't attached to any facet source.
   */
  public function loadGroups() {
    $facet_source_plugin_manager = \Drupal::service('plugin.manager.facets.facet_source');
    $facets = $this->load();
    $facets_summaries = [];
    if (\Drupal::moduleHandler()->moduleExists('facets_summary')) {
      $facets_summaries = FacetsSummary::loadMultiple();
    }
    $facet_sources = $facet_source_plugin_manager->getDefinitions();

    $facet_source_groups = [];
    foreach ($facet_sources as $facet_source) {

      // For now, we hide the facet sources for views display default.
      // They should not be used to attach block facets.
      if (substr($facet_source["display_id"], 0, 14) == 'views_default:') {
        continue;
      }

      $facet_source_groups[$facet_source['id']] = [
        'facet_source' => $facet_source,
        'facets' => [],
      ];

      foreach ($facets as $facet) {
        /** @var \Drupal\facets\FacetInterface $facet */
        if ($facet->getFacetSourceId() == $facet_source['id']) {
          $facet_source_groups[$facet_source['id']]['facets'][$facet->id()] = $facet;
          // Remove this facet from $facet so it will finally only contain those
          // facets not belonging to any facet_source.
          unset($facets[$facet->id()]);
        }
      }

      foreach ($facets_summaries as $summary) {
        /** @var \Drupal\facets_summary\FacetsSummaryInterface $summary */
        if ($summary->getFacetSourceId() == $facet_source['id']) {
          $facet_source_groups[$facet_source['id']]['facets'][$summary->id()] = $summary;
          // Remove this facet from $facet so it will finally only contain those
          // facets not belonging to any facet_source.
          unset($facets_summaries[$summary->id()]);
        }
      }
    }

    return [
      'facet_source_groups' => $facet_source_groups,
      'lone_facets' => $facets,
    ];
  }

  /**
   * Prepare facet render url array.
   */
  public function facetUrlRenderArray(EntityInterface $entity): array {
    $output = [];
    if ($entity->toUrl('edit-form')->access()) {
      $linkEntityObject = Link::fromTextAndUrl($entity->label(), $entity->toUrl('edit-form'));
      $output['#url'] = $linkEntityObject->getUrl();
      $output['#options'] = $entity->toUrl('edit-form')->getOptions();
    }

    return $output;
  }

}
