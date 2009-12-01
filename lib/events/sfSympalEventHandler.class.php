<?php

class sfSympalEventHandler
{
  public function __construct(sfEventDispatcher $dispatcher)
  {
    $dispatcher->connect('sympal.load_admin_bar', array($this, 'loadAdminBar'));
    $dispatcher->connect('sympal.load_config_form', array($this, 'loadConfig'));
    $dispatcher->connect('sympal.load_tools', array($this, 'loadTools'));
    $dispatcher->connect('command.post_command', array($this, 'changeBaseFormDoctrine'));
  }

  public function loadAdminBar(sfEvent $event)
  {
    $menu = $event['menu'];

    $user = sfContext::getInstance()->getUser();

    $icon = $menu->getChild('Icon');
    $icon->addChild('Feedback', 'http://sympal.uservoice.com', array('onClick' => 'UserVoice.Popin.show(); return false;'));
    $icon->addChild('Go To Homepage', '@sympal_homepage');
    $icon->addChild('Signout', '@sympal_signout', 'confirm=Are you sure you wish to signout?');
    $icon->addChild('Logged in as '.$user->getSympalUser()->getUsername());

    $help = $icon->addChild('Help')
      ->setCredentials(array('ViewDeveloperInformation'));

    $help->addChild('Sympal '.sfSympal::VERSION)
      ->setCredentials(array('ViewDeveloperInformation'));

    $help->addChild('symfony '.SYMFONY_VERSION)
      ->setCredentials(array('ViewDeveloperInformation'));

    $help->addChild('Doctrine '.Doctrine_Core::VERSION)
      ->setCredentials(array('ViewDeveloperInformation'));

    $help->addChild('About Sympal', 'http://www.symfony-project.com/plugins/sfSympalPlugin', 'target=_BLANK')
      ->setCredentials(array('ViewDeveloperInformation'));

    $help->addChild('About Symfony', 'http://www.symfony-project.com/about', 'target=_BLANK')
      ->setCredentials(array('ViewDeveloperInformation'));

    $help->addChild('Documentation', 'http://www.symfony-project.com/plugins/sfSympalPlugin', 'target=_BLANK')
      ->setCredentials(array('ViewDeveloperInformation'));

    $help->addChild('Doctrine Documentation', 'http://www.doctrine-project.org/documentation', 'target=_BLANK')
      ->setCredentials(array('ViewDeveloperInformation'));

    $help->addChild('symfony Documentation', 'http://www.symfony-project.org/doc', 'target=_BLANK')
      ->setCredentials(array('ViewDeveloperInformation'));

    $help->addChild('Report Doctrine Bug', 'http://trac.doctrine-project.org', 'target=_BLANK')
      ->setCredentials(array('ViewDeveloperInformation'));

    $help->addChild('Report symfony Bug', 'http://trac.symfony-project.com', 'target=_BLANK')
      ->setCredentials(array('ViewDeveloperInformation'));

    if (sfSympalToolkit::isEditMode())
    {
      $content = $menu->addChild('Content', '@sympal_content')
        ->setCredentials(array('ManageContent'));
      $contentTypes = Doctrine_Core::getTable('ContentType')->findAll();
      $content->addChild('Create New Content', '@sympal_content_new');
      foreach ($contentTypes as $contentType)
      {
        $node = $content->addChild($contentType->getLabel(), '@sympal_content_list_type?type='.$contentType->getSlug());
        $node->addChild('Create', '@sympal_content_create_type?type='.$contentType->getSlug());
        $node->addChild('List', '@sympal_content_list_type?type='.$contentType->getSlug());
      }
    }

    $administration = $menu->getChild('Administration');

    $administration->addChild('Dashboard', '@sympal_dashboard')
      ->setCredentials(array('ViewDashboard'));

    $administration->addChild('Sites', '@sympal_sites')
      ->setCredentials(array('ManageSites'));

    $administration->addChild('Configuration', '@sympal_config')
      ->setCredentials(array('ManageConfiguration'));

    $administration->addChild('Route Manager', '@sympal_routes')
      ->setCredentials(array('ManageRoutes'));

    $content = $administration->addChild('Content Setup')
      ->setCredentials(array('ManageContentSetup'));
    $content->addChild('Types', '@sympal_content_types');
    $content->addChild('Templates', '@sympal_content_templates');
    $content->addChild('Slot Types', '@sympal_content_slot_types');
  }

  public function loadConfig(sfEvent $event)
  {
    $form = $event->getSubject();

    $array = sfSympalFormToolkit::getLayoutWidgetAndValidator();
    $form->addSetting(null, 'default_layout', 'Default Layout', $array['widget'], $array['validator']);
    $form->addSetting(null, 'disallow_php_in_content', 'Disable PHP in Content', 'InputCheckbox', 'Boolean');
    $form->addSetting(null, 'rows_per_page', 'Rows Per Page');
    $form->addSetting(null, 'recaptcha_public_key', 'Recaptcha Public Key');
    $form->addSetting(null, 'recaptcha_private_key', 'Recaptcha Private Key');
    $form->addSetting(null, 'breadcrumbs_separator', 'Breadcrumbs Separator');
    $form->addSetting(null, 'config_form_class', 'Config Form Class');
    $form->addSetting(null, 'default_from_email_address', 'Default From Address');

    $form->addSetting('plugin_api', 'username', 'Username or API Key');
    $form->addSetting('plugin_api', 'password');
  }

  public function loadTools(sfEvent $event)
  {
    $menu = $event['menu'];
    $content = $event['content'];
    $lock = $event['lock'];
    $user = sfContext::getInstance()->getUser();
    $request = sfContext::getInstance()->getRequest();

    $contentEditor = $menu->addChild($content['Type']['label'] . ' Editor')
      ->setCredentials(array('ManageContent'));

    if ($content['locked_by'])
    {
       if ($content['locked_by'] == $user->getSympalUser()->getId())
       {
         if ($request->getParameter('module') == 'sympal_content')
         {
           $contentEditor->addChild(image_tag('/sf/sf_admin/images/edit.png').' Edit '.$content['Type']['label'].' Inline', $content->getRoute());
         } else {
           $contentEditor->addChild(image_tag('/sf/sf_admin/images/edit.png').' Edit '.$content['Type']['label'].' Backend', '@sympal_content_edit?id='.$content['id']);
         }
       } else {
         $contentEditor->addChild($content['Type']['label'].' is currently locked by "'.$content['LockedBy']['username'].'" and cannot be edited.');
       }
    }

    if ($content['is_published'])
    {
      $contentEditor->addChild(image_tag('/sf/sf_admin/images/cancel.png').' Un-Publish', '@sympal_unpublish_content?id='.$content['id']);
    } else {
      $contentEditor->addChild(image_tag('/sf/sf_admin/images/tick.png').' Publish', '@sympal_publish_content?id='.$content['id']);
    }

    $contentType = $menu->addChild($content['Type']['label'].' Content')
      ->setCredentials(array('ManageContent'));
    $contentType->addChild(image_tag('/sf/sf_admin/images/add.png').' Create', '@sympal_content_create_type?type='.$content['Type']['slug']);

    if (sfSympalConfig::isI18nEnabled())
    {
      $menu->addChild('Change Language')
        ->addChild(get_component('sympal_editor', 'language'));
    }
  }

  public function changeBaseFormDoctrine(sfEvent $event)
  {
    $subject = $event->getSubject();
    if ($subject instanceof sfDoctrineBuildFormsTask)
    {
      $find = 'abstract class BaseFormDoctrine extends sfFormDoctrine
{
  public function setup()
  {
';

      $replace = 'abstract class BaseFormDoctrine extends BaseFormDoctrineSympal
{
  public function setup()
  {
    parent::setup();
';

      $path = sfConfig::get('sf_lib_dir').'/form/doctrine/BaseFormDoctrine.class.php';
      file_put_contents($path, str_replace($find, $replace, file_get_contents($path)));
    }
  }
}