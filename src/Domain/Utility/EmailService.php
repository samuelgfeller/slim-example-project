<?php

namespace App\Domain\Utility;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Slim\Views\PhpRenderer;

class EmailService
{

    public function __construct(private PHPMailer $mailer, private PhpRenderer $phpRenderer)
    {
    }

    /**
     * Add subject and message body to mailer
     *
     * @param string $subject
     * @param string $message
     */
    public function setSubjectAndMessage(string $subject, string $message): void
    {
        $this->mailer->isHTML(); // Default is plain text

        $this->mailer->Subject = $subject;
        $this->mailer->Body = $message;
        $this->mailer->AltBody = strip_tags($message);
    }

    /**
     * Define content by template path that will be parsed by PHP-View
     * The advantage from using PHP-View is that we have the helper functions
     * like uri and route and being able to use the template path defined in config.
     * Remember to set subject too when working with this function.
     *
     * @param string $templatePath PHP-View path relative to template path defined in config
     * @param array $templateData['varName' => 'data', 'otherVarName' => 'otherData',]
     * @throws \Throwable
     */
    public function setContentFromTemplate(string $templatePath, array $templateData): void
    {
        // Prepare and fetch template
        $this->phpRenderer->setLayout(''); // Making sure there is no default layout
        foreach ($templateData as $key => $data){
            $this->phpRenderer->addAttribute($key, $data);
        }
        $parsedTemplate = $this->phpRenderer->fetch($templatePath);

        // Add content to mailer
        $this->mailer->isHTML(); // Default is plain text
        $this->mailer->Body = $parsedTemplate;
        $this->mailer->AltBody = strip_tags($parsedTemplate);
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
        $this->mailer->addAddress($toAddress, $toName);

        return $this->mailer->send();
    }



    // Below are the functions that directly call PHPMailer methods without logic

    /**
     * Set email subject
     *
     * @param string $subject
     */
    public function setSubject(string $subject): void
    {
        $this->mailer->Subject = $subject;
    }

    /**
     * Add an attachment from a path on the filesystem.
     *
     * @param string $attachmentPath
     * @param string $name
     * @return bool Returns false if the file could not be found or read.
     * @throws Exception
     *
     */
    public function addAttachment(string $attachmentPath, $name = ''): bool
    {
        return $this->mailer->addAttachment($attachmentPath, $name);
    }

    /**
     * Set sender (From) address and name
     *
     * @param string $from
     * @param string $fromName
     * @throws Exception
     */
    public function setFrom(string $from, string $fromName = ''): void
    {
        $this->mailer->setFrom($from, $fromName);
    }

    /**
     * Add a "Reply-To" address.
     *
     * @param string $address The email address to reply to
     * @param string $name
     * @throws Exception
     * @return bool true on success, false if address already used or invalid in some way
     */
    public function addReplyTo($address, $name = ''): bool
    {
        return $this->mailer->addReplyTo($address, $name);
    }

    /**
     * Add a "To" address
     *
     * @param string $address The email address to send to
     * @param string $name Name of the recipient
     *
     * @return bool true on success, false if address already used or invalid in some way
     * @throws Exception
     */
    public function addAddress(string $address, string $name = ''): bool
    {
        return $this->mailer->addAddress($address, $name);
    }

    /**
     * Add a "CC" address.
     *
     * @param string $address Email of the address to be in CC
     * @param string $name Name of the CC recipient
     *
     * @return bool true on success, false if address already used or invalid in some way
     * @throws Exception
     */
    public function addCC(string $address, string $name = ''): bool
    {
        return $this->mailer->addCC($address, $name);
    }

    /**
     * Add a "BCC" address.
     *
     * @param string $address Email of the address to be in BCC
     * @param string $name Name of the BCC recipient
     *
     * @return bool true on success, false if address already used or invalid in some way
     * @throws Exception
     */
    public function addBCC(string $address, string $name = ''): bool
    {
        return $this->mailer->addBCC($address, $name);
    }

    /**
     * Create a message and send it.
     *
     * @throws Exception
     *
     * @return bool false on error - See the ErrorInfo property for details of the error
     */
    public function send(): bool
    {
        return $this->mailer->send();
    }

}