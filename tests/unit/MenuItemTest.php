<?php

$app = 'sympal';
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$configuration->loadHelpers(array('Url', 'Tag'));

$t = new lime_test(9, new lime_output_color());

$table = Doctrine_Core::getTable('MenuItem');

$menuItems = $table
  ->createQuery('m')
  ->execute();

$menuItem = $table
  ->createQuery('m')
  ->where('m.slug = ?', 'about')
  ->fetchOne();

$t->is($menuItem->getIndentedName(), '- About');
$t->is((string) $menuItem, '- About');
$t->is($menuItem->getMainContent()->getHeaderTitle(), 'About');
$t->is($menuItem->getLabel(), 'About');
$t->is($menuItem->getItemRoute(), '@sympal_content_view_type_page?slug=about');
$t->is($menuItem->getBreadcrumbs()->getPathAsString(), 'Home / About');
$t->is($menuItem->getLayout(), 'sympal');

$hierarchy = sfSympalMenuSiteManager::toHierarchy($menuItems->toArray());

$t->is(isset($hierarchy[0]['__children']), true);

$array = $hierarchy[0];

$level = 0;
do {
  $t->is($array['level'], $level);

  $level = $level + 1;

  if (isset($array['__children']))
  {
    $array = $array['__children'];
  }
} while (isset($array['__children']));