<?php

namespace Plugins\Thesaurus;

use \Typemill\Plugin;

class Thesaurus extends Plugin
{

  public static function getSubscribedEvents()
  {
    return array(
      'onSettingsLoaded' => 'onSettingsLoaded',
      'onTwigLoaded' => 'onTwigLoaded'
    );
  }


  public function onSettingsLoaded($settings)
  {
    $this->settings = $settings->getData();
  }
  

  public static function addNewRoutes()
  {
    return array(
      array(
        'httpMethod'  => 'get', 
        'route'       => '/meanings_tool', 
        'class'       => 'Plugins\thesaurus\index:meanings'
      )
    );
  }

  
  public function onTwigLoaded()
  {
    if(!isset($_SESSION['user'])) {
      return;
    }

	/* get Twig Instance and add the cookieconsent template-folder to the path */
	$twig 	= $this->getTwig();
	$loader = $twig->getLoader();
	$loader->addPath(__DIR__ . '/templates');

    $this->addCSS('/thesaurus/public/thesaurus.css');
    $this->addJS('/thesaurus/public/script.js');
    $this->addJS('/thesaurus/public/vue-thesaurus.js');

    // https://github.com/apvarun/toastify-js
    $this->addJS('/thesaurus/public/toastify/toastify.min.js');
    $this->addCSS('/thesaurus/public/toastify/toastify.min.css');
  }

}

?>
