<?php

/**
 * @file
 * Markdown Easy module hook definitions.
 */

use League\CommonMark\MarkdownConverter;

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Modify the Markdown converter configuration.
 *
 * @param \League\CommonMark\MarkdownConverter $converter
 *   The Markdown converter whose configuration is to be modified.
 *
 * @ingroup markdown_easy
 */
function hook_markdown_easy_config_modify(MarkdownConverter &$converter) {
}

/**
 * @} End of "addtogroup hooks".
 */
