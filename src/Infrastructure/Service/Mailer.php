<?php

namespace App\Infrastructure\Service;

use App\Application\Data\UserNetworkSessionData;
use App\Domain\Security\Repository\EmailLoggerRepository;
use Slim\Views\PhpRenderer;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Mailer class with added method to get the html string from a template and added logging in the send() function.
 * Test sender score: https://www.mail-tester.com/.
 */
final readonly class Mailer
{
    private ?int $loggedInUserId;

    public function __construct(
        private MailerInterface $mailer,
        private PhpRenderer $phpRenderer,
        private EmailLoggerRepository $emailLoggerRepository,
        private UserNetworkSessionData $userNetworkSessionData
    ) {
        $this->loggedInUserId = $this->userNetworkSessionData->userId ?? null;
    }

    /**
     * Returns rendered HTML of given template path.
     * Using PHP-View template parser allows access to the attributes from PhpViewMiddleware
     * like uri and route.
     *
     * @param string $templatePath PHP-View path relative to template path defined in config
     * @param array $templateData ['varName' => 'data', 'otherVarName' => 'otherData', ]
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
     * Send and log email.
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
        // $cc = $email->getCc();
        // $bcc = $email->getBcc();

        // Log email request
        $this->emailLoggerRepository->logEmailRequest(
            $email->getFrom()[0]->getAddress(),
            $email->getTo()[0]->getAddress(),
            $email->getSubject() ?? '',
            $this->loggedInUserId
        );
    }
}
