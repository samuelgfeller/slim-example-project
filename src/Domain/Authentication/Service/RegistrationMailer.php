<?php

namespace App\Domain\Authentication\Service;

use App\Common\LocaleHelper;
use App\Domain\Service\Mailer;
use App\Domain\User\Data\UserData;
use App\Domain\Utility\Settings;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * This class manages the emails sent in relation to registration.
 *
 * This is a centralised Service class containing email content and sending action.
 * Advantages:
 *  - Prevents other service classes of having the email responsibility (Single-responsibility principle)
 *  - Contents are centralised and can be changed easier without having to search them all over the code
 */
class RegistrationMailer
{
    private Email $email;

    /**
     * RegistrationMailer constructor.
     *
     * @param Mailer $mailer email sender and helper
     * @param LocaleHelper $localeHelper
     * @param Settings $settings
     */
    public function __construct(
        private readonly Mailer $mailer,
        private readonly LocaleHelper $localeHelper,
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
     * @param UserData $user
     * @param array $queryParams
     *
     * @throws TransportExceptionInterface
     */
    public function sendRegisterVerificationToken(UserData $user, array $queryParams): void
    {
        // Change language to one the user is being registered with
        $originalLocale = setlocale(LC_ALL, 0);
        $this->localeHelper->setLanguage($user->language->value);

        // Send verification mail in the language that was selected for the user
        $this->email->subject(__('Account created'))
            ->html(
                $this->mailer->getContentFromTemplate(
                    'authentication/email/' . $this->localeHelper->getLanguageCodeForPath() .
                    'new-account.email.php',
                    ['user' => $user, 'queryParams' => $queryParams]
                )
            )->to(new Address($user->email, $user->getFullName()));
        // Send email
        $this->mailer->send($this->email);

        // Reset locale
        $this->localeHelper->setLanguage($originalLocale);
    }
}
