<?php

namespace Econemon\Bootsy\ApplicationBundle\Service;

use Econemon\Bootsy\ApplicationBundle\Exception\DefensiveCodeException;
use Swift_Message;

class TemplateMailer implements TemplateMailerInterface
{
  /**
   * @var \Swift_Mailer
   */
  protected $swift;

  /**
   * @var \Twig_Environment
   */
  protected $twig;

  protected static $mpFileNameExtToMime = array(
    'html' => 'text/html',
    'txt' => 'text/plain',
  );

  public function sendTemplateAsMail($templateName, $context, $subject, $to, $from, $mimeType = NULL)
  {
    $template = $this->twig->loadTemplate($templateName);
    $htmlBody = $template->render($context);

    $message = Swift_Message::newInstance()
      ->setSubject($subject)
      ->setFrom($from)
      ->setTo($to);

    if (NULL === $mimeType) {
      $suffix = '.twig';
      $fileNameExtension = substr(strrchr(substr($templateName, 0, -1 * strlen($suffix)), '.'), 1);
      // TODO: maybe there's some existing, more mature code in the standard lib or in Symfony that could be used
      if (array_key_exists($fileNameExtension, self::$mpFileNameExtToMime)) {
        $mimeType = self::$mpFileNameExtToMime[$fileNameExtension];
      } else {
        throw DefensiveCodeException::forUnexpectedValue('fileNameExtension', $fileNameExtension, array_keys(self::$mpFileNameExtToMime));
      }
    }

    $message->setBody($htmlBody, $mimeType);

    $this->swift->send($message);
  }

  /**
   * @param \Swift_Mailer $swift
   */
  public function setSwift($swift)
  {
    $this->swift = $swift;
  }

  /**
   * @param \Twig_Environment $twig
   */
  public function setTwig($twig)
  {
    $this->twig = $twig;
  }

}