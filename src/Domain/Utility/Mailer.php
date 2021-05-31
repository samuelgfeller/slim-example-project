<?php

namespace App\Domain\Utility;

use App\Infrastructure\Security\RequestCreatorRepository;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Slim\Views\PhpRenderer;

class Mailer extends PHPMailer
{

    public function __construct(
        bool $exceptions,
        private PhpRenderer $phpRenderer,
        private RequestCreatorRepository $requestCreatorRepository
    )
    {
        parent::__construct($exceptions);
    }

    /**
     * Add subject and message body to mailer
     *
     * @param string $subject
     * @param string $message
     */
    public function setSubjectAndMessage(string $subject, string $message): void
    {
        $this->isHTML(); // Default is plain text

        $this->Subject = $subject;
        $this->Body = $message;
        $this->AltBody = strip_tags($message);
    }

    /**
     * Define content by template path that will be parsed by PHP-View
     * The advantage from using PHP-View is that we have the helper functions
     * like uri and route and being able to use the template path defined in config.
     * Remember to set subject too when working with this function.
     *
     * @param string $templatePath PHP-View path relative to template path defined in config
     * @param array $templateData ['varName' => 'data', 'otherVarName' => 'otherData',]
     */
    public function setContentFromTemplate(string $templatePath, array $templateData): void
    {
        // Prepare and fetch template
        $this->phpRenderer->setLayout(''); // Making sure there is no default layout
        foreach ($templateData as $key => $data) {
            $this->phpRenderer->addAttribute($key, $data);
        }
        $parsedTemplate = $this->phpRenderer->fetch($templatePath);

        // Add content to mailer
        $this->isHTML(); // Default is plain text
        $this->Body = $parsedTemplate;
        $this->AltBody = strip_tags($parsedTemplate);
    }

    /**
     * Shortcut function to add one address and send
     *
     * @param string $toAddress
     * @param string $toName
     *
     * @return bool
     *
     * @throws Exception
     */
    public function sendTo(string $toAddress, string $toName = ''): bool
    {
        $this->addAddress($toAddress, $toName);

        if (false !== $this->send()) {
            // Insert that there was an email request for security
            $this->requestCreatorRepository->insertEmailRequest($toAddress, $_SERVER['REMOTE_ADDR']);
            return true;
        }
        return false;
    }
}