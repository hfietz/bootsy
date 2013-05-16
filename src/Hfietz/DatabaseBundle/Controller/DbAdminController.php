<?php
namespace Hfietz\DatabaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DbAdminController extends Controller
{
  public function versionsAction()
  {
    return new Response('<h1>Hi, this is the DB versions page</h1>');
  }
}