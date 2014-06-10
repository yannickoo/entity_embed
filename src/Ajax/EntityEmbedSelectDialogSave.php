<?php

/**
 * @file
 * Contains \Drupal\entity_embed\Ajax\EntityEmbedSelectDialogSave
 */

namespace Drupal\entity_embed\Ajax;

use Drupal\Core\Ajax\CommandInterface;

/**
 * Provides an AJAX command for saving the contents of an entity_embed dialog.
 *
 * This command is implemented in entity_embed.ajax.js in
 * Drupal.AjaxCommands.prototype.entityembedSelectDialogSave.
 */
class EntityEmbedSelectDialogSave implements CommandInterface {

  /**
   * An array of values that will be passed back to the editor by the dialog.
   *
   * @var string
   */
  protected $values;

  /**
   * Constructs a EntityEmbedSelectDialogSave object.
   *
   * @param string $values
   *   The values that should be passed to the form constructor in Drupal.
   */
  public function __construct($values) {
    $this->values = $values;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    return array(
      'command' => 'entityembedSelectDialogSave',
      'values' => $this->values,
    );
  }

}