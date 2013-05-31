<?php

namespace Econemon\Bootsy\ApplicationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MainController extends Controller
{
  public function indexAction()
  {
    $view = array(
      'logo' => array(
        'headline' => 'ceta Bautagebuch',
      ),
      'menu' => array(
        array(
          #'target' => 'system',
          'label' => 'System',
          'submenu' => array(
            array(
              'target' => 'error_list',
              'label' => 'Fehlerlog',
            ),
            array(
              'target' => 'db_status',
              'label' => 'Datenbank-Status',
            ),
            array(
              'target' => 'db_versions',
              'label' => 'Datenbank-Versionen',
            ),
          ),
        ),
        array(
          #'target' => 'error_list',
          'label' => 'Fehlerseiten',
          'submenu' => array(
            array(
              'target' => 'test_404',
              'label' => 'Nicht gefunden',
            ),
            array(
              'target' => 'test_500',
              'label' => 'Serverfehler',
            ),
            array(
              'target' => 'test_error',
              'label' => 'Unbekannter Fehler',
            ),
          ),
        ),
      ),
    );

    return $this->render('EconemonBootsyApplicationBundle:Main:index.html.twig', $view);
  }
}