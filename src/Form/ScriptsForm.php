<?php

namespace Drupal\advance_script_manager\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ScriptsForm.
 */
class ScriptsForm extends ConfigFormBase {

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
   * The entitytypemanager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->database = $container->get('database');
    $instance->messenger = $container->get('messenger');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'advance_script_manager.scripts',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'scripts_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('advance_script_manager.scripts');
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Script name'),
      '#size' => 64,
      '#required' => 'true',
      '#description' => $this->t('Enter a name to describe the code block'),
    ];
    $form['enable_code_script'] = [
      '#type' => 'radios',
      '#title' => $this->t('Enable code script'),
      '#options' => [
        '1' => $this->t('Active'),
        '2' => $this->t('Disabled'),
      ],
      '#description' => $this->t('Scripts code snippets are disabled by default, so you won\'t accidentally make the code live.'),
      '#default_value' => 2,
    ];
    $form['script_code'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Java script code'),
      '#description'   => $this->t('<p>You can add multiple <strong>scripts</strong> here with multiple ways, For example: </p><p>1. &lt;script type="text/javascript" src="http://www.example.com/script.js"&gt;&lt;/script&gt;</p><p> 2. &lt;script type="text/javascript" src="/script.js"&gt;&lt;/script&gt;</p><p> 3. &lt;script type="text/javascript"&gt;console.log("HFS Header");&lt;/script&gt;</p>'),
      '#rows'          => 10,
    ];
    $form['css_code'] = [
      '#type' => 'textarea',
      '#title' => $this->t('CSS code'),
      '#description'   => $this->t('<p>You can add multiple <strong>stylesheets</strong> here with multiple ways, For example: </p><p>1. &lt;link type="text/css" rel="stylesheet" href="http://www.example.com/style.css" media="all" /&gt;</p><p> 2. &lt;link type="text/css" rel="stylesheet" href="/style.css" media="all" /&gt;</p><p> 3. &lt;style&gt;#header { color: grey; }&lt;/style&gt;</p>'),
      '#rows'          => 10,
    ];
    $form['visibility_settings'] = [
      '#type' => 'select',
      '#title' => $this->t('Visibility settings'),
      '#options' => [
        'Header' => $this->t('Header'),
        'Footer' => $this->t('Footer'),
        'Body' => $this->t('Body'),
      ],
      '#size' => 1,
      '#default_value' => 'Header',
    ];
    $form['pages_settings'] = [
      '#type' => 'radios',
      '#title' => $this->t('Invoke script code on specific pages'),
      '#options' => [
        'all' => $this->t('All pages expected those listed'),
        'only' => $this->t('Only the listed pages'),
      ],
    ];
    $form['visibility_pages'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Pages'),
      '#description' => $this->t('Specify the pages by using their paths. Enter one path per line. &ltfront&gt is the front page'),
    ];
    $form['user_roles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('User Roles'),
      '#options' => $this->fetchAllRoles(),
    ];
    $form['content_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Content Types'),
      '#options' => $this->fetchContentTypes(),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // parent::submitForm($form, $form_state);
    //    $this->config('advance_script_manager.scripts')
    //      ->set('name', $form_state->getValue('name')['value'])
    //      ->set('enable_code_script', $form_state->getValue('enable_code_script'))
    //      ->set('', $form_state->getValue(''))
    //      ->save();
    $value = $form_state->getValues();
    $content_types = implode(",", $value['content_types']);
    $user_roles = implode(",", $value['user_roles']);
    $field = [
      'script_name'   => trim($value['name']),
      'script_code' => trim($value['script_code']),
      'css_code' => trim($value['css_code']),
      'visibility_section' => trim($value['visibility_settings']),
      'pages_settings' => $value['pages_settings'],
      'visibility_pages' => trim($value['visibility_pages']),
      'content_type' => $content_types,
      'user_roles' => $user_roles,
      'status' => $value['enable_code_script'],
    ];
    $res = $this->database->insert('advance_script_manager')
      ->fields($field)
      ->execute();
    if ($res) {
      $this->messenger->addMessage($this->t('Script config saved'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function fetchAllRoles() {
    $allroles = $this->entityTypeManager->getStorage('user_role')->loadMultiple();
    $data = [];
    foreach ($allroles as $value) {
      $data[$value->id()] = $value->label();
    }
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function fetchContentTypes() {
    $allTypes = $this->entityTypeManager->getStorage('node_type')->loadMultiple();
    $data = [];
    foreach ($allTypes as $value) {
      $data[$value->label()] = $value->label();
    }
    return $data;
  }

}