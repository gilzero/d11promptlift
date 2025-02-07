<?php

namespace Drupal\Tests\publication_date\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests publication_date install hook.
 *
 * @group publication_date
 */
class PublicationDateInstallationTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests that the publication date is correctly set on enabling of the module.
   */
  public function testPublicationDateInstall() {
    // Create a node that starts out as published.
    $published = $this->createNode(['status' => 1]);

    // Create a node that was never published.
    $unpublished = $this->createNode(['status' => 0]);

    // Create a node that only has one revision but has been edited after being
    // created. As a result the changed time is later than the creation time.
    $single_revision = $this->createNode(['status' => 1]);
    $single_revision->setNewRevision(FALSE);
    $single_revision->setCreatedTime(strtotime('2019-06-10 10:00'));
    $single_revision->setChangedTime(strtotime('2019-07-20 10:00'));
    $single_revision->save();

    // Create a node that is currently unpublished but has been published in a
    // previous revision. Start with an unpublished revision.
    $previously_published = $this->createNode(['status' => 0]);

    // Create an published revision.
    $previously_published->setNewRevision();
    $previously_published->setPublished();
    $previously_published->setRevisionCreationTime(strtotime('2019-07-20 10:00'));
    $previously_published->save();

    // Create a final unpublished revision.
    $previously_published->setNewRevision();
    $previously_published->setUnpublished();
    $previously_published->setRevisionCreationTime(strtotime('2019-07-20 11:00'));
    $previously_published->save();

    // Install the module. This should populate our 'published_at' field data.
    $this->container->get('module_installer')->install(['publication_date']);

    // The published node should have the right publication time set.
    $this->assertEquals($published->getCreatedTime(), $this->select('node_field_data')->condition('nid', $published->id())->fields('node_field_data', ['published_at'])->execute()->fetchField());

    // The unpublished node should have the default publication time.
    $this->isNull($this->select('node_field_data')->condition('nid', $unpublished->id())->fields('node_field_data', ['published_at'])->execute()->fetchField());

    // The node with a single revision which has been changed later should have
    // the publication time set to the time the node was created.
    $this->assertEquals($single_revision->getCreatedTime(), $this->select('node_field_data')->condition('nid', $single_revision->id())->fields('node_field_data', ['published_at'])->execute()->fetchField());

    // The previously published node should have the right publication time.
    $this->assertEquals(strtotime('2019-07-20 10:00'), $this->select('node_field_data')->condition('nid', $previously_published->id())->fields('node_field_data', ['published_at'])->execute()->fetchField());

    // The first revision of the previously published node was unpublished, so
    // it should have the default publication time.
    $revision_ids = $this->container->get('entity_type.manager')->getStorage('node')->revisionIds($previously_published);
    $this->isNull($this->select('node_field_revision')->condition('vid', $revision_ids[0])->fields('node_field_revision', ['published_at'])->execute()->fetchField());

    // The second revision of the previously published node was the first
    // published revision, so it should have the publication time.
    $this->assertEquals(strtotime('2019-07-20 10:00'), $this->select('node_field_revision')->condition('vid', $revision_ids[1])->fields('node_field_revision', ['published_at'])->execute()->fetchField());

    // The third revision of the previously published node is unpublished, but
    // should have the publication time set.
    $this->assertEquals(strtotime('2019-07-20 10:00'), $this->select('node_field_revision')->condition('vid', $revision_ids[2])->fields('node_field_revision', ['published_at'])->execute()->fetchField());
  }

  /**
   * Returns a select query for the given database table.
   *
   * @param string $table
   *   The database table for which to return the query.
   *
   * @return \Drupal\Core\Database\Query\SelectInterface
   *   The select query.
   */
  protected function select($table) {
    return $this->container->get('database')->select($table);
  }

}
