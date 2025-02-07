<?php

declare(strict_types=1);

namespace Drupal\Tests\markdown_easy\Unit;

use Drupal\markdown_easy\MarkdownUtility;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the MarkdownUtility class.
 *
 * @group markdown_easy
 */
class MarkdownUtilityTest extends UnitTestCase {

  /**
   * Data provider for testing the getFilterWeights method.
   *
   * @return array<int, array<int, array<string, mixed>|null>>
   *   An array of test cases.
   */
  public function filterWeightsDataProvider(): array {
    return [
      [
        [
          'filter_html' => ['status' => FALSE],
          'filter_autop' => ['status' => FALSE],
          'markdown_easy' => ['status' => FALSE],
        ],
        [
          'filter_html' => NULL,
          'filter_autop' => NULL,
          'markdown_easy' => NULL,
        ],
      ],
      [
        [
          'filter_html' => ['status' => TRUE, 'weight' => 10],
          'filter_autop' => ['status' => FALSE],
          'markdown_easy' => ['status' => FALSE],
        ],
        [
          'filter_html' => 10,
          'filter_autop' => NULL,
          'markdown_easy' => NULL,
        ],
      ],
      [
        [
          'filter_html' => ['status' => TRUE, 'weight' => 10],
          'filter_autop' => ['status' => TRUE, 'weight' => 20],
          'markdown_easy' => ['status' => TRUE, 'weight' => 30],
        ],
        [
          'filter_html' => 10,
          'filter_autop' => 20,
          'markdown_easy' => 30,
        ],
      ],
    ];
  }

  /**
   * Tests the getFilterWeights method.
   *
   * @param array<int, array<string, mixed>|null> $filters
   *   The list of filters enabled on the text format.
   * @param array<int, array<string, mixed>|null> $expectedWeights
   *   The expected weights for the filters we care about.
   *
   * @dataProvider filterWeightsDataProvider
   * @test
   */
  public function testGetFilterWeights(array $filters, array $expectedWeights): void {
    $markdownUtility = new MarkdownUtility();
    $this::assertEquals($expectedWeights, $markdownUtility->getFilterWeights($filters));
  }

}
