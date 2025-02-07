<?php

namespace Drupal\patterncss_ui;

/**
 * Provides an interface defining a pattern manager.
 */
interface PatternCssManagerInterface {

  /**
   * Returns if this Pattern css selector is added.
   *
   * @param string $pattern
   *   The Pattern css selector to check.
   *
   * @return bool
   *   TRUE if the Pattern css selector is added, FALSE otherwise.
   */
  public function isPattern($pattern);

  /**
   * Finds an added pattern css selector by its ID.
   *
   * @return string|false
   *   Either the added pattern selector or FALSE if none exist with that ID.
   */
  public function loadPattern();

  /**
   * Add a Pattern css selector.
   *
   * @param int $pattern_id
   *   The Pattern id for edit.
   * @param string $pattern
   *   The Pattern selector to add.
   * @param string $label
   *   The label of pattern selector.
   * @param string $segment
   *   The segment of pattern.
   * @param string $comment
   *   The comment for pattern options.
   * @param int $changed
   *   The expected modification time.
   * @param int $status
   *   The status for pattern.
   * @param string $options
   *   The Pattern selector options.
   *
   * @return int|null|string
   *   The last insert ID of the query, if one exists.
   */
  public function addPattern($pattern_id, $pattern, $label, $segment, $comment, $changed, $status, $options);

  /**
   * Remove a Pattern css selector.
   *
   * @param int $pattern_id
   *   The Pattern id to remove.
   */
  public function removePattern($pattern_id);

  /**
   * Finds all added Pattern css selector.
   *
   * @param array $header
   *   The pattern header to sort selector and label.
   * @param string $search
   *   The pattern search key to filter selector.
   * @param int|null $segment
   *   The pattern segment to filter selector.
   * @param int|null $status
   *   The pattern status to filter selector.
   *
   * @return \Drupal\Core\Database\StatementInterface
   *   The result of the database query.
   */
  public function findAll(array $header, $search, $segment, $status);

  /**
   * Finds an added Pattern css selector by its ID.
   *
   * @param int $pattern_id
   *   The ID for an added Pattern selector.
   *
   * @return string|false
   *   Either the added Pattern selector or FALSE if none exist with that ID.
   */
  public function findById($pattern_id);

}
