<?php

namespace Econemon\Bootsy\ApplicationBundle\Service;

interface TemplateMailerInterface
{
  public function sendTemplateAsMail($templateName, $context, $subject, $to, $from, $mimeType = NULL);
}