<?php

namespace Drupal\advance_script_manager\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AdvanceScriptController.
 */
class AdvanceScriptController extends ControllerBase {

  /**
   * Drupal\Core\Database\Driver\mysql\Connection definition.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

  /**
   * Drupal\Core\Messenger\MessengerInterface definition.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->database = $container->get('database');
    $instance->messenger = $container->get('messenger');
    return $instance;
  }

  /**
   * Build.
   *
   * @return array
   *   Return Hello string.
   */
  public function build() {
    $form['form'] = $this->formBuilder()->getForm('\Drupal\advance_script_manager\Form\SearchscriptsForm');

    $form['form2'] = $this->formBuilder()->getForm('\Drupal\advance_script_manager\Form\ListscriptsForm');

    return $form;
  }

}
