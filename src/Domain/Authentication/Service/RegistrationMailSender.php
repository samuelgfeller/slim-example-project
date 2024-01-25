<?php

namespace App\Domain\Authentication\Service;

use App\Infrastructure\Service\LocaleConfigurator;
use App\Infrastructure\Service\Mailer;
use App\Infrastructure\Utility\Settings;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * This class manages the emails sent in relation to registration.
 *
 * This is a centralized Service class containing email content and sending action.
 * Advantages (compared to having the email content and sending action in the service class that calls this class):
 *  - Prevents other service classes from having the email responsibility (Single-responsibility principle)
 *  - Contents are centralized and can be changed easier without having to search them all over the code
 */
class RegistrationMailSender
{
    private Email $email;

    public function __construct(
        private readonly Mailer $mailer,
        private readonly LocaleConfigurator $localeConfigurator,
        Settings $settings
    ) {
        $settings = $settings->get('public')['email'];
        // Create email object
        $this->email = new Email();
        // Send auth emails from domain
        $this->email->from(new Address($settings['main_sender_address'], $settings['main_sender_name']))->replyTo(
            $settings['main_contact_address']
        )->priority(Email::PRIORITY_HIGH);
    }

    /**
     * Send verification token.
     *
     * @param string $email
     * @param string $fullName
     * @param string $language
     * @param array $queryParams
     *
     * @throws TransportExceptionInterface
     */
    public function sendRegisterVerificationToken(string $email, string $fullName, string $language, array $queryParams): void
    {
        // Change language to one the user is being registered with
        $originalLocale = setlocale(LC_ALL, 0);
        $this->localeConfigurator->setLanguage($language);

        // Send verification mail in the language that was selected for the user
        $this->email->subject(__('Account created'))
            ->html(
                $this->mailer->getContentFromTemplate(
                    'authentication/email/' . $this->localeConfigurator->getLanguageCodeForPath() .
                    'new-account.email.php',
                    ['userFullName' => $fullName, 'queryParams' => $queryParams]
                )
            )->to(new Address($email, $fullName));
        // Send email
        $this->mailer->send($this->email);

        // Reset locale
        $this->localeConfigurator->setLanguage($originalLocale);
    }
}
