<?php
/**
 * Created by PhpStorm.
 * User: dobrik
 * Date: 1/26/18
 * Time: 2:46 PM
 */

namespace Woxapp\Scaffold\Domain\Services;

use Phalcon\DiInterface;

class SwiftMailerService
{

    protected $sender;
    protected $mailer;
    protected $logger;

    public function __construct(DiInterface $dependencyInjector)
    {
        $this->logger = $dependencyInjector->get('logger');

        $config = $dependencyInjector->getConfig();
        $transport = new \Swift_SmtpTransport(
            $config->path('external.smtp.host'),
            $config->path('external.smtp.port'),
            'ssl'
        );

        $transport->setStreamOptions(['ssl' => ['allow_self_signed' => true, 'verify_peer' => false, 'verify_peer_name' => false]]);
        $transport->setUsername($config->path('external.smtp.username'));
        $transport->setPassword($config->path('external.smtp.password'));
        $this->sender = $config->path('external.smtp.sender');

        $this->mailer = new \Swift_Mailer($transport);
    }

    public function setSender(string $sender)
    {
        $this->sender = $sender;
        return $this;
    }

    public function getSender(): string
    {
        return $this->sender;
    }

    public function sendMessage(array $recipients, string $subject, string $body): bool
    {
        $message = (new \Swift_Message($subject))
            ->setFrom($this->getSender())
            ->setTo($recipients)
            ->setBody($body);

        try {
            $this->mailer->send($message);
        } catch (\Exception $e) {
            throw $e;
            $this->logger->alert("Send email error: " . $e->getMessage());
        }

        return true;
    }
}