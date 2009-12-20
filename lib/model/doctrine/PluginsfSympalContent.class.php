<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class PluginsfSympalContent extends BasesfSympalContent
{
  protected
    $_allPermissions,
    $_route;

  public static function createNew($type)
  {
    if (is_string($type))
    {
      $type = Doctrine_Core::getTable('sfSympalContentType')->findOneByName($type);

      if (!$type)
      {
        throw new InvalidArgumentException(sprintf('Could not find Sympal Content Type named "%s"', $type));
      }
    }

    if (!$type instanceof sfSympalContentType)
    {
      throw new InvalidArgumentException(sprintf('Invalid Content Type', $type));
    }

    $name = $type->name;

    $content = new sfSympalContent();
    $content->Type = $type;
    $content->$name = new $name();

    return $content;
  }

  public function hasField($name)
  {
    $result = $this->_table->hasField($name);
    if (!$result)
    {
      $className = $this->getContentTypeClassName();
      $table = Doctrine_Core::getTable($className);
      if ($table->hasField($name))
      {
        $result = true;
      }
    }
    return $result;
  }

  public function getUrl()
  {
    return $this->getRoute();
  }

  public function getPubDate()
  {
    return strtotime($this->date_published);
  }

  public function getContentTypeClassName()
  {
    $contentTypes = sfSympalContext::getInstance()->getSympalConfiguration()->getContentTypes();
    if (isset($contentTypes[$this['content_type_id']]))
    {
      return $contentTypes[$this['content_type_id']];
    } else {
      throw new sfException('Invalid content type id "'.$this['content_type_id'].'". Id was not found in content type cache.');
    }
  }

  public function getAllPermissions()
  {
    if (!$this->_allPermissions)
    {
      $this->_allPermissions = array();
      foreach ($this->Groups as $group)
      {
        foreach ($group->Permissions as $permission)
        {
          $this->_allPermissions[] = $permission->name;
        }
      }
      foreach ($this->Permissions as $permission)
      {
        $this->_allPermissions[] = $permission->name;
      }
    }
    return $this->_allPermissions;
  }

  public function __toString()
  {
    return $this->getHeaderTitle();
  }

  public function getTitle()
  {
    return $this->getHeaderTitle();
  }

  public function getRelatedMenuItem()
  {
    if ($this->get('master_menu_item_id', false))
    {
      return $this->MasterMenuItem;
    } else {
      $menuItem = $this->_get('MenuItem');
      if ($menuItem && $menuItem->exists())
      {
        return $menuItem;
      } else {
        return false;
      }
    }
  }

  public function getMainMenuItem()
  {
    if ($menuItem = $this->getRelatedMenuItem())
    {
      return $menuItem;
    } else {
      $q = Doctrine_Core::getTable('sfSympalMenuItem')
        ->createQuery('m')
        ->innerJoin('m.Site s WITH s.slug = ?', sfConfig::get('app_sympal_config_site_slug', sfConfig::get('sf_app')))
        ->andWhere('m.content_type_id = ?', $this->content_type_id)
        ->orWhere('m.is_primary = true')
        ->orderBy('m.is_primary DESC')
        ->limit(1);

      return $q->fetchOne();
    }
  }

  public function getRecord()
  {
    if ($this['Type']['name'])
    {
      Doctrine_Core::initializeModels(array($this['Type']['name']));
      return $this[$this['Type']['name']];
    } else {
      return false;
    }
  }

  public function getTemplate()
  {
    if ($this->get('content_template_id', false))
    {
      return $this->_get('Template');
    }
    if ($this->hasReference('Type'))
    {
      return $this->Type->getTemplate();
    }
  }

  public function publish()
  {
    $this->is_published = true;
    $this->date_published = new Doctrine_Expression('NOW()');
    $this->save();
    $this->refresh();
  }

  public function unpublish()
  {
    $this->is_published = false;
    $this->date_published = null;
    $this->save();
  }

  public function getHeaderTitle()
  {
    if ($record = $this->getRecord())
    {
      $guesses = array('name',
                       'title',
                       'username',
                       'subject');

      // we try to guess a column which would give a good description of the object
      foreach ($guesses as $descriptionColumn)
      {
        try
        {
          return (string) $record->get($descriptionColumn);
        } catch (Exception $e) {}
      }
      return (string) $this;
    }

    return sprintf('No description for object of class "%s"', $this->getTable()->getComponentName());
  }

  public function getEditRoute()
  {
    if ($this->exists())
    {
      return '@sympal_content_edit?id='.$this['id'];
    } else {
      throw new sfException('You cannot get the edit route of a object that does not exist.');
    }
  }

  public function getFeedDescription()
  {
    return sfSympalContext::getInstance()
      ->getRenderer($this)
      ->render();
  }

  public function getAuthorName()
  {
    return $this->getCreatedBy()->getName();
  }

  public function getAuthorEmail()
  {
    return $this->getCreatedBy()->getEmailAddress();
  }

  public function getUniqueId()
  {
    return $this->getId().'-'.$this->getSlug();
  }

  public function getRouteName()
  {
    if ($this['custom_path'])
    {
      return '@sympal_content_' . str_replace('-', '_', $this['slug']);
    } else if ($this['Type']['default_path']) {
      return $this['Type']['route_name'];
    } else if ($this['slug']) {
      return '@sympal_content_view';
    }
  }

  public function getRoutePath()
  {
    if ($path = $this['custom_path'])
    {
      if ($path != '/')
      {
        $path .= '.:sf_format';
      }
      return $path;
    } else if ($path = $this['Type']['route_path']) {
      return $path;
    } else if ($this['slug']) {
      return '/content/:slug';
    }
  }

  public function getAutomaticUrlHierarchy()
  {
    $menuItem = $this->getMainMenuItem();
    unset($this->MenuItem);
    if ($menuItem)
    {
      $url = str_replace(' / ', '/', $menuItem->getBreadcrumbs($this)->getPathAsString());
      $e = explode('/', $url);
      unset($e[0]);
      $e = array_map(array('Doctrine_Inflector', 'urlize'), $e);
      $url = implode('/', $e);
      if ($url)
      {
        return '/'.$url;
      }
    }
  }

  public function getRoute($routeString = null, $path = null)
  {
    if (!$this->_route)
    {
      if (!$this->exists() || !$this['slug'])
      {
        return false;
      }

      if (method_exists($this->getContentTypeClassName(), 'getRoute'))
      {
        return $this->getRecord()->getRoute();
      }

      if (is_null($routeString))
      {
        $routeString = $this->getRouteName();
      }

      if (is_null($path))
      {
        $path = $this->getRoutePath();
      }

      $route = new sfRoute($path);
      $variables = $route->getVariables();

      $this->_route = $this->_fillRoute($routeString, $variables);
    }

    return $this->_route;
  }

  public function getEvaluatedRoutePath()
  {
    $route = url_for($this->getRoute());
    $replace = sfContext::getInstance()->getRequest()->getScriptName();

    return str_replace($replace, null, $route);
  }

  protected function _fillRoute($route, $variables)
  {
    $values = array();
    foreach (array_keys($variables) as $name)
    {
      if ($name == 'slug' && $this->hasField('i18n_slug') && $i18nSlug = $this->i18n_slug)
      {
        $values[$name] = $i18nSlug;
      } else if ($this->hasField($name)) {
        $values[$name] = $this->$name;
      } else if (method_exists($this, $method = 'get'.sfInflector::camelize($name))) {
        $values[$name] = $this->$method();
      }
    }

    if (!empty($values))
    {
      return $route.'?'.http_build_query($values);
    } else {
      return $route;
    }
  }

  public function getLayout()
  {
    if ($layout = $this->_get('layout')) {
      return $layout;
    } else if ($layout = $this->getType()->getLayout()) {
      return $layout;
    } else if ($layout = $this->getSite()->getLayout()) {
      return $layout;
    } else {
      return sfSympalConfig::get('default_layout', null, $this->getSite()->getSlug());
    }
  }

  public function loadMetaData(sfWebResponse $response)
  {
    // page title
    if ($pageTitle = $this['page_title'])
    {
      $response->setTitle($pageTitle);
    } else if ($pageTitle = $this['Site']['page_title']) {
      $response->setTitle($pageTitle);
    }

    // meta keywords
    if ($metaKeywords = $this['meta_keywords'])
    {
      $response->addMeta('keywords', $metaKeywords);
    } else if ($metaKeywords = $this['Site']['meta_keywords']) {
      $response->addMeta('keywords', $metaKeywords);
    }

    // meta description
    if ($metaDescription = $this['meta_description'])
    {
      $response->addMeta('description', $metaDescription);
    } else if ($metaDescription = $this['Site']['meta_description']) {
      $response->addMeta('description', $metaDescription);
    }
  }

  public function trySettingTitleProperty($value)
  {
    foreach (array('title', 'name', 'subject', 'header') as $name)
    {
      try {
        $this->$name = $value;
      } catch (Exception $e) {}
    }
  }

  public function postSave($event)
  {
    @unlink(sfConfig::get('sf_cache_dir').'/'.sfConfig::get('sf_app').'/'.sfConfig::get('sf_environment').'/config/config_routing.yml.php');
  }

  public static function slugBuilder($text, $content)
  {
    if ($record = $content->getRecord())
    {
      try {
        return $record->slugBuilder($text);
      } catch (Doctrine_Record_UnknownPropertyException $e) {
        return Doctrine_Inflector::urlize($text);
      }
    } else {
      return Doctrine_Inflector::urlize($text);
    }
  }

  public function getSiteId()
  {
    return $this->_get('site_id');
  }

  public function getContentTypeId()
  {
    return $this->_get('content_type_id');
  }

  public function getContentTemplateId()
  {
    return $this->_get('content_template_id');
  }

  public function getMasterMenuItemId()
  {
    return $this->_get('master_menu_item_id');
  }

  public function getLastUpdatedBy()
  {
    return $this->_get('LastUpdatedBy');
  }

  public function getLastUpdatedById()
  {
    return $this->_get('last_updated_by_id');
  }

  public function getCreatedBy()
  {
    return $this->_get('CreatedBy');
  }

  public function getCreatedById()
  {
    return $this->_get('created_by_id');
  }

  public function getIsPublished()
  {
    return $this->_get('is_published');
  }

  public function getDatePublished()
  {
    return $this->_get('date_published');
  }

  public function getCustomPath()
  {
    return $this->_get('custom_path');
  }

  public function getPageTitle()
  {
    return $this->_get('page_title');
  }

  public function getMetaKeywords()
  {
    return $this->_get('meta_keywords');
  }

  public function getMetaDescription()
  {
    return $this->_get('meta_description');
  }

  public function getI18nSlug()
  {
    return $this->_get('i18n_slug');
  }

  public function getMasterMenuItem()
  {
    return $this->_get('MasterMenuItem');
  }

  public function getSite()
  {
    return $this->_get('Site');
  }

  public function getType()
  {
    return $this->_get('Type');
  }

  public function getGroups()
  {
    return $this->_get('Groups');
  }

  public function getPermissions()
  {
    return $this->_get('Permissions');
  }

  public function getMenuItem()
  {
    return $this->_get('MenuItem');
  }

  public function getSlots()
  {
    return $this->_get('Slots');
  }

  public function getContentGroups()
  {
    return $this->_get('ContentGroups');
  }
}