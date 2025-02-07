<?php

namespace Drupal\prism\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a filter to highlight code with the prism.js library.
 *
 * @Filter(
 *   id = "prism_filter",
 *   title = @Translation("Highlight code using prism.js"),
 *   description = @Translation("Highlights code inside <code>[prism:~][/prism:~]</code> tags, or <code>&lt;pre&gt;&lt;code class='language-*'&gt;</code> tags."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE,
 *   settings = {
 *     "always_include_prism_library" = FALSE
 *   }
 * )
 */
class PrismFilter extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['always_include_prism_library'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Always include Prism.js library'),
      '#default_value' => $this->settings['always_include_prism_library'],
      '#description' => $this->t('Include the prism.js and prism.css files even if there are no <code>[prism:]</code> tags in the text. Allows for highlighting of HTML <code>&lt;pre&gt;&lt;code class="language-*" /&gt;</code> blocks.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $processed_text = new FilterProcessResult($text);
    $tags = [];
    $include_library = (bool) $this->settings['always_include_prism_library'];

    // Detect prism tags.
    if (preg_match_all('/\[prism:([^\|\\]]+)\|?([^\\]]*)?\]/i', $text, $tag_match)) {
      $tags = $tag_match[1];
    }

    // If we have prism tags we create a wrapper for every language code block
    // and attach the prism library.
    if ($tags) {
      $include_library = TRUE;

      foreach (array_unique($tags) as $tag) {
        // Ahhh.
        if (preg_match_all('#((?<!\[)\[)(prism:' . $tag . ')((\s+[^\]]*)*)(\])(.*?)((?<!\[)\[/\2\s*\]|$)#s', $text, $match)) {
          foreach ($match[6] as $key => $value) {
            $code = '<div class="prism-wrapper" rel="' . $tag . '"><pre><code class="language-' . $tag . '">' . Html::escape($value) . '</code></pre></div>';
            $text = str_replace($match[0][$key], $code, $text);
          }
        }
      }
    }

    // If there were any existing <code> tags or if the above replaced [prism]
    // with <code>.
    $include_library |= (\stristr($text, '<code') !== FALSE);

    if ($include_library) {
      $processed_text->setProcessedText($text);
      $processed_text->addAttachments([
        'library' => [
          'prism/drupal.prism',
        ],
      ]);
    }

    return $processed_text;
  }

}
