<?php

namespace Econemon\Bootsy\ApplicationBundle\View;

class MainView
{
  /**
   * @var string
   */
  protected $pageTitle;

  /**
   * @var string
   */
  protected $logoImageSrc;

  /**
   * @var string
   */
  protected $claimHeadline;

  // TODO
  protected $session;

  /**
   * @var MenuItem
   */
  protected $menu = array();

  /**
   * @return MenuItem
   */
  public function getMenu()
  {
    return $this->menu;
  }

  /**
   * @param MenuItem $menu
   */
  public function setMenu($menu)
  {
    $this->menu = $menu;
  }

  /**
   * @return string
   */
  public function getPageTitle()
  {
    return $this->pageTitle;
  }

  /**
   * @param string $pageTitle
   */
  public function setPageTitle($pageTitle)
  {
    $this->pageTitle = $pageTitle;
  }

  /**
   * @param string $logoImageSrc
   */
  public function setLogoImageSrc($logoImageSrc)
  {
    $this->logoImageSrc = $logoImageSrc;
  }

  /**
   * @param string $claimHeadline
   */
  public function setClaimHeadline($claimHeadline)
  {
    $this->claimHeadline = $claimHeadline;
  }

  public function getLogo()
  {
    $logo = array();

    if (!empty($this->logoImageSrc)) {
      $logo['image'] = array(
        'src' => $this->logoImageSrc,
      );
    }

    if (!empty($this->claimHeadline)) {
      $logo['headline'] = $this->claimHeadline;
    }

    return $logo;
  }
}