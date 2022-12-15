<?php

namespace App\Domain\Utility;

use App\Infrastructure\Security\RequestCreatorRepository;
use Slim\Views\PhpRenderer;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Mailer class with added method to get the html string from a template and added request audit in the send() function
 * This class in not defined in the container and therefore is autowired. Configuration is defined in MailerInterface
 * Test sender score: https://www.mail-tester.com/.
 */
class Mailer
{
    /**
     * Mailer constructor.
     *
     * @param MailerInterface $mailer
     * @param PhpRenderer $phpRenderer
     * @param RequestCreatorRepository $requestCreatorRepository
     */
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly PhpRenderer $phpRenderer,
        private readonly RequestCreatorRepository $requestCreatorRepository
    ) {
    }

    /**
     * Define content by template path that will be parsed by PHP-View
     * The advantage from using PHP-View is that we have the helper functions
     * like uri and route and being able to use the template path defined in config.
     * Remember to set subject too when working with this function.
     *
     * @param string $templatePath PHP-View path relative to template path defined in config
     * @param array $templateData ['varName' => 'data', 'otherVarName' => 'otherData',]
     *
     * @return string html email content
     */
    public function getContentFromTemplate(string $templatePath, array $templateData): string
    {
        // Prepare and fetch template
        $this->phpRenderer->setLayout(''); // Making sure there is no default layout
        foreach ($templateData as $key => $data) {
            $this->phpRenderer->addAttribute($key, $data);
        }

        return $this->phpRenderer->fetch($templatePath);
    }

    /**
     * Function to send email and add insert to request tracking table.
     *
     * @param Email $email
     *
     * @throws TransportExceptionInterface
     *
     * @return void
     */
    public function send(Email $email): void
    {
        $this->mailer->send($email);
        // Insert that there was an email request for security checks
        $this->requestCreatorRepository->insertEmailRequest($email->getTo()[0]->getAddress(), $_SERVER['REMOTE_ADDR']);
    }
}
