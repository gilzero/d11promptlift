<?php

namespace Drupal\ai_deepchat\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an AI form block.
 *
 * @Block(
 *   id = "ai_deepchat_block",
 *   admin_label = @Translation("AI DeepChat"),
 *   category = @Translation("AI")
 * )
 */
class ChatFormBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected FormBuilderInterface $formBuilder;

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The AI Assistant API runner.
   *
   * @var \Drupal\ai_assistant_api\AiAssistantApiRunner
   */
  protected $aiAssistantRunner;

  /**
   * The file url generator.
   *
   * @var \Drupal\Core\File\FileUrlGenerator
   */
  protected $fileUrlGenerator;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $plugin = new static($configuration, $plugin_id, $plugin_definition);
    $plugin->entityTypeManager = $container->get('entity_type.manager');
    $plugin->formBuilder = $container->get('form_builder');
    $plugin->currentUser = $container->get('current_user');
    $plugin->aiAssistantRunner = $container->get('ai_assistant_api.runner');
    $plugin->fileUrlGenerator = $container->get('file_url_generator');
    return $plugin;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'ai_assistant' => NULL,
      'bot_name' => 'Generic Chatbot',
      'bot_image' => '/core/misc/druplicon.png',
      'use_username' => TRUE,
      'default_username' => 'User',
      'use_avatar' => TRUE,
      'default_avatar' => '/core/misc/favicon.ico',
      'first_message' => 'Hello! How can I help you today?',
      'stream' => TRUE,
      'toggle_state' => 'remember',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $all = $this->entityTypeManager->getStorage('ai_assistant')->loadMultiple();
    $assistants = [];
    foreach ($all as $id => $ai_assistant) {
      $assistants[$id] = $ai_assistant->label();
    }

    $form['ai_assistant'] = [
      '#type' => 'select',
      '#title' => $this->t('AI Assistant'),
      '#description' => $this->t('Select the AI Assistant to use for this chat form. You can create new %link.', [
        '%link' => Link::createFromRoute($this->t('AI Assistants here'), 'entity.ai_assistant.collection', [], [
          'attributes' => [
            'target' => '_blank',
          ],
        ])->toString(),
      ]),
      '#options' => $assistants,
      '#default_value' => $this->configuration['ai_assistant'],
    ];

    $form['bot_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bot name'),
      '#description' => $this->t('The name of the bot.'),
      '#default_value' => $this->configuration['bot_name'],
    ];

    $form['bot_image'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bot image'),
      '#description' => $this->t('The image of the bot.'),
      '#default_value' => $this->configuration['bot_image'],
    ];

    $form['default_username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default User name'),
      '#description' => $this->t('The name of the user, if not fetched from the user or if not logged in.'),
      '#default_value' => $this->configuration['default_username'],
    ];

    $form['use_username'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use username'),
      '#description' => $this->t('Use the username in the chat messages if logged in.'),
      '#default_value' => $this->configuration['use_username'],
    ];

    $form['default_avatar'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default Avatar'),
      '#description' => $this->t('The avatar of the user, if not fetched from the user or if not logged in.'),
      '#default_value' => $this->configuration['default_avatar'],
    ];

    $form['use_avatar'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use avatar'),
      '#description' => $this->t('Use the avatar in the chat messages if logged in.'),
      '#default_value' => $this->configuration['use_avatar'],
    ];

    $form['first_message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First Message'),
      '#description' => $this->t('The first message to start things of.'),
      '#default_value' => $this->configuration['first_message'],
    ];

    $form['stream'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Stream'),
      '#description' => $this->t('Stream the messages in real-time.'),
      '#default_value' => $this->configuration['stream'],
    ];

    $form['toggle_state'] = [
      '#type' => 'select',
      '#title' => $this->t('Toggle state'),
      '#description' => $this->t('The state of the toggle button.'),
      '#options' => [
        'remember' => $this->t('Remember'),
        'open' => $this->t('Opened'),
        'close' => $this->t('Closed'),
      ],
      '#default_value' => $this->configuration['toggle_state'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['ai_assistant'] = $form_state->getValue('ai_assistant');
    $this->configuration['bot_name'] = $form_state->getValue('bot_name');
    $this->configuration['bot_image'] = $form_state->getValue('bot_image');
    $this->configuration['use_username'] = $form_state->getValue('use_username');
    $this->configuration['default_username'] = $form_state->getValue('default_username');
    $this->configuration['use_avatar'] = $form_state->getValue('use_avatar');
    $this->configuration['default_avatar'] = $form_state->getValue('default_avatar');
    $this->configuration['first_message'] = $form_state->getValue('first_message');
    $this->configuration['stream'] = $form_state->getValue('stream');
    $this->configuration['toggle_state'] = $form_state->getValue('toggle_state');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $assistant = $this->entityTypeManager->getStorage('ai_assistant')->load($this->configuration['ai_assistant']);
    $this->aiAssistantRunner->setAssistant($assistant);
    // Check if the assistant is setup and that the user has access to it.
    if (!$this->aiAssistantRunner->isSetup() || !$this->aiAssistantRunner->userHasAccess()) {
      return [];
    }
    $this->aiAssistantRunner->streamedOutput($this->configuration['stream']);
    $block = [];

    $block['#theme'] = 'ai_deepchat';
    $block['#attached']['library'][] = 'ai_deepchat/chat';
    $block['#settings'] = $this->configuration;

    $block['#attached']['drupalSettings']['ai_deepchat']['bot_name'] = $this->configuration['bot_name'];
    $block['#attached']['drupalSettings']['ai_deepchat']['bot_image'] = $this->configuration['bot_image'];
    $block['#attached']['drupalSettings']['ai_deepchat']['default_username'] = $this->configuration['default_username'];
    $block['#attached']['drupalSettings']['ai_deepchat']['default_avatar'] = $this->configuration['default_avatar'];
    $block['#attached']['drupalSettings']['ai_deepchat']['toggle_state'] = $this->configuration['toggle_state'];
    $user = $this->currentUser->getAccount();
    // Override username if the user is authenticated and configured.
    if ($user->isAuthenticated() && $this->configuration['use_username']) {
      $block['#attached']['drupalSettings']['ai_deepchat']['default_username'] = $user->getDisplayName();
    }
    // Override avatar if the user is authenticated and configured and exist.
    if ($user->isAuthenticated() && $this->configuration['use_avatar']) {
      $userEntity = $this->entityTypeManager->getStorage('user')->load($user->id());
      if (!empty($userEntity->user_picture->entity)) {
        $block['#attached']['drupalSettings']['ai_deepchat']['default_avatar'] = $this->fileUrlGenerator->generateAbsoluteString($userEntity->user_picture->entity->getFileUri());
      }
    }

    return $block;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
