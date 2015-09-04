<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Tests\ImageFieldFormatterTest.
 */

namespace Drupal\entity_embed\Tests;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormState;
use Drupal\entity_embed\EntityHelperTrait;

/**
 * Tests the image field formatter provided by entity_embed.
 *
 * @group entity_embed
 */
class ImageFieldFormatterTest extends EntityEmbedTestBase {
  use EntityHelperTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('entity_embed', 'file', 'image', 'node');

  /**
   * Created file entity.
   *
   * @var \Drupal\file\FileInterface
   */
  protected $image;

  /**
   * Created file entity.
   *
   * @var \Drupal\file\FileInterface
   */
  protected $file;

  protected function setUp() {
    parent::setUp();
    $this->image = $this->getTestFile('image');
    $this->file = $this->getTestFile('text');
  }

  /**
   * Tests image field formatter display plugin.
   */
  public function testImageFieldFormatter() {
    // Ensure that image field formatters are available as display plugins.
    $plugin_options = $this->displayPluginManager()->getDefinitionOptionsForEntity($this->image);
    $this->assertTrue(array_key_exists('image:image', $plugin_options), "The 'Image' plugin is available.");

    // Ensure that image plugin is not available to files other than images.
    $plugin_options = $this->displayPluginManager()->getDefinitionOptionsForEntity($this->file);
    $this->assertFalse(array_key_exists('image:image', $plugin_options), "The 'Image' plugin is not available for text file.");

    // Ensure that 'file' plugins are available for images too.
    $this->assertTrue(array_key_exists('file:file_table', $plugin_options), "The 'Table of files' plugin is available.");
    $this->assertFalse(array_key_exists('file:file_rss_enclosure', $plugin_options), "The 'RSS enclosure' plugin is not available.");
    $this->assertTrue(array_key_exists('file:file_default', $plugin_options), "The 'Generic file' plugin is available.");
    $this->assertTrue(array_key_exists('file:file_url_plain', $plugin_options), "The 'URL to file' plugin is available.");

    // Ensure that correct form attributes are returned for the image plugin.
    $form = array();
    $form_state = new FormState();
    $display = $this->displayPluginManager()->createInstance('image:image', array());
    $display->setContextValue('entity', $this->image);
    $conf_form = $display->buildConfigurationForm($form, $form_state);
    $this->assertIdentical(array_keys($conf_form), array(
      'image_style',
      'image_link',
      'alt',
      'title',
    ));
    $this->assertIdentical($conf_form['image_style']['#type'], 'select');
    $this->assertIdentical($conf_form['image_style']['#title'], 'Image style');
    $this->assertIdentical($conf_form['image_link']['#type'], 'select');
    $this->assertIdentical($conf_form['image_link']['#title'], 'Link image to');
    $this->assertIdentical($conf_form['alt']['#type'], 'textfield');
    $this->assertIdentical($conf_form['alt']['#title'], 'Alternate text');
    $this->assertIdentical($conf_form['title']['#type'], 'textfield');
    $this->assertIdentical($conf_form['title']['#title'], 'Title');

    // Test entity embed using 'Image' display plugin.
    $alt_text = "This is sample description";
    $title = "This is sample title";
    $embed_settings = array('image_link' => 'file');
    $content = '<div data-entity-type="file" data-entity-uuid="' . $this->image->uuid() . '" data-entity-embed-display="image:image" data-entity-embed-settings=\'' . Json::encode($embed_settings) . '\' alt="' . $alt_text . '" title="' . $title . '">This placeholder should not be rendered.</div>';
    $settings = array();
    $settings['type'] = 'page';
    $settings['title'] = 'Test entity embed with image:image';
    $settings['body'] = array(array('value' => $content, 'format' => 'custom_format'));
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw($alt_text, 'Alternate text for the embedded image is visible when embed is successful.');
    $this->assertNoText(strip_tags($content), 'Placeholder does not appears in the output when embed is successful.');
    $this->assertLinkByHref(file_create_url($this->image->getFileUri()), 0, 'Link to the embedded image exists.');

    // Embed all three field types in one, to ensure they all render correctly.
    $content = '<div data-entity-type="node" data-entity-uuid="' . $this->node->uuid() . '" data-entity-embed-display="entity_reference:entity_reference_label"></div>';
    $content .= '<div data-entity-type="file" data-entity-uuid="' . $this->file->uuid() . '" data-entity-embed-display="file:file_default"></div>';
    $content .= '<div data-entity-type="file" data-entity-uuid="' . $this->image->uuid() . '" data-entity-embed-display="image:image"></div>';
    $settings = array();
    $settings['type'] = 'page';
    $settings['title'] = 'Test node entity embedded first then a file entity';
    $settings['body'] = array(array('value' => $content, 'format' => 'custom_format'));
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
  }

}
