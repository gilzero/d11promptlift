<?php

namespace Drupal\patterncss_ui;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\PagerSelectExtender;
use Drupal\Core\Database\Query\TableSortExtender;

/**
 * Pattern manager.
 */
class PatternCssManager implements PatternCssManagerInterface {

  /**
   * The database connection used to check the selector against.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a PatternCssManager object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection which will be used to check the selector
   *   against.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public function isPattern($pattern) {
    return (bool) $this->connection->query("SELECT * FROM {patterncss} WHERE [selector] = :selector", [':selector' => $pattern])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function loadPattern() {
    $query = $this->connection
      ->select('patterncss', 'p')
      ->fields('p', ['pid', 'selector', 'segment', 'options'])
      ->condition('status', 1);

    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function addPattern($pattern_id, $pattern, $label, $segment, $comment, $changed, $status, $options) {
    $this->connection->merge('patterncss')
      ->key('pid', $pattern_id)
      ->fields([
        'selector' => $pattern,
        'label'    => $label,
        'segment'  => $segment,
        'comment'  => $comment,
        'changed'  => $changed,
        'status'   => $status,
        'options'  => $options,
      ])
      ->execute();

    return $this->connection->lastInsertId();
  }

  /**
   * {@inheritdoc}
   */
  public function removePattern($pattern_id) {
    $this->connection->delete('patterncss')
      ->condition('pid', $pattern_id)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function findAll($header = [], $search = '', $segment = NULL, $status = NULL) {
    $query = $this->connection
      ->select('patterncss', 'p')
      ->extend(PagerSelectExtender::class)
      ->extend(TableSortExtender::class)
      ->orderByHeader($header)
      ->limit(50)
      ->fields('p');

    if (!empty($search) && !empty(trim((string) $search)) && $search !== NULL) {
      $search = trim((string) $search);
      // Escape for LIKE matching.
      $search = $this->connection->escapeLike($search);
      // Replace wildcards with MySQL/PostgreSQL wildcards.
      $search = preg_replace('!\*+!', '%', $search);
      // Add selector and the label field columns.
      $group = $query->orConditionGroup()
        ->condition('selector', '%' . $search . '%', 'LIKE')
        ->condition('label', '%' . $search . '%', 'LIKE');
      // Run the query to find matching targets.
      $query->condition($group);
    }

    // Check if segment is set.
    if (!is_null($segment) && $segment != '') {
      $query->condition('segment', $segment);
    }

    // Check if status is set.
    if (!is_null($status) && $status != '') {
      $query->condition('status', $status);
    }

    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function findById($pattern_id) {
    return $this->connection->query("SELECT [selector], [label], [segment], [comment], [status], [options] FROM {patterncss} WHERE [pid] = :pid", [':pid' => $pattern_id])
      ->fetchAssoc();
  }

}
