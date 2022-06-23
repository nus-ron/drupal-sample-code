<?php

namespace Drupal\fountains_gifts_api\Normalizer;

use Drupal\jsonapi\Normalizer\ContentEntityNormalizer;
use Drupal\jsonapi\ResourceType\ResourceType;
use Drupal\menu_link_content\MenuLinkContentInterface;

class MenuLinkContentEntityNormalizer extends ContentEntityNormalizer {

  protected $mainInterfaceOrClass = MenuLinkContentInterface::class;

  protected function getValues($entity, $bundle, ResourceType $resource_type) {

    if ($bundle != 'menu_link_content') {
      return parent::getValues($entity, $bundle, $resource_type);
    }

    $url_object = $entity->getUrlObject();
    $url = $url_object->toString();
    if ($url_object->isRouted() && ($params = $url_object->getRouteParameters()) && isset($params['node'])) {
      $node = $this->entityTypeManager->getStorage('node')->load($params['node']);
      if (!$node->access('view')) {
        $url = null;
      }
    }

    $entity->set('url', $url);
    return parent::getValues($entity, $bundle, $resource_type);
  }

}
