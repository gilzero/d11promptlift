<?php

namespace Drupal\typogrify\TwigExtension;

use Drupal\typogrify\SmartyPants;
use Drupal\typogrify\Typogrify as TypogrifyBase;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * A class providing Drupal Twig extensions.
 *
 * This provides a Twig extension that applies the filters provided by the
 * Typogrify module.
 */
class Typogrify extends AbstractExtension {

  /**
   * {@inheritdoc}
   */
  public function getFilters() {
    return [
      new TwigFilter(
        'typogrify',
        [$this, 'filter'],
        ['pre_escape' => 'html', 'is_safe' => ['html']],
      ),
    ];
  }

  /**
   * Filter text by Typogrify.
   *
   * @param string $text
   *   The text to be processed.
   * @param string[] $options
   *   Filters to be used for filtering. If empty, all filters will be used.
   *   Possible values: amp, widont, smartypants, caps, initial_quotes, dash.
   *
   * @return string
   *   The filtered string.
   */
  public static function filter($text, array $options = []) {
    if (empty($options)) {
      return TypogrifyBase::filter($text);
    }

    if (in_array('amp', $options)) {
      $text = TypogrifyBase::amp($text);
    }

    if (in_array('widont', $options)) {
      $text = TypogrifyBase::widont($text);
    }

    if (in_array('smartypants', $options)) {
      $text = SmartyPants::process($text);
    }

    if (in_array('caps', $options)) {
      $text = TypogrifyBase::caps($text);
    }

    if (in_array('initial_quotes', $options)) {
      $text = TypogrifyBase::initialQuotes($text);
    }

    if (in_array('dash', $options)) {
      $text = TypogrifyBase::dash($text);
    }

    return $text;
  }

}
