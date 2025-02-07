<?php

/**
 * @file
 * Hooks for the PatternCSS module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Provide patterncss patterns.
 *
 * By implementing hook_patterncss_patterns(), a module can add
 * pattern name to the list and extent pattern with new custom css.
 *
 * @param array $pattern_names
 *   List of new pattern names in array.
 *
 * @return array
 *   A render array that can be added into Pattern CSS names.
 */
function hook_patterncss_patterns(array $pattern_names = []) {
  $pattern_names = [];

  return $pattern_names;
}

/**
 * @} End of "addtogroup hooks".
 */
