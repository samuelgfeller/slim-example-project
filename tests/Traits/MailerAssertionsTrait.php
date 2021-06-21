<?php

namespace App\Test\Traits;

use PHPUnit\Framework\Constraint\LogicalNot;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mailer\Event\MessageEvents;
use Symfony\Component\Mailer\EventListener\MessageLoggerListener;
use Symfony\Component\Mailer\Test\Constraint as MailerConstraint;
use Symfony\Component\Mime\RawMessage;
use Symfony\Component\Mime\Test\Constraint as MimeConstraint;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Symfony email assertions.
 * Source: https://github.com/symfony/symfony/blob/5.4/src/Symfony/Bundle/FrameworkBundle/Test/MailerAssertionsTrait.php
 */
trait MailerAssertionsTrait
{
    public static function assertEmailIsQueued(MessageEvent $event, string $message = ''): void
    {
        self::assertThat($event, new MailerConstraint\EmailIsQueued(), $message);
    }

    public static function assertEmailIsNotQueued(MessageEvent $event, string $message = ''): void
    {
        self::assertThat($event, new LogicalNot(new MailerConstraint\EmailIsQueued()), $message);
    }

    public static function assertEmailAttachmentCount(RawMessage $email, int $count, string $message = ''): void
    {
        self::assertThat($email, new MimeConstraint\EmailAttachmentCount($count), $message);
    }

    public static function assertEmailTextBodyContains(RawMessage $email, string $text, string $message = ''): void
    {
        self::assertThat($email, new MimeConstraint\EmailTextBodyContains($text), $message);
    }

    public static function assertEmailTextBodyNotContains(RawMessage $email, string $text, string $message = ''): void
    {
        self::assertThat($email, new LogicalNot(new MimeConstraint\EmailTextBodyContains($text)), $message);
    }

    public static function assertEmailHtmlBodyContains(RawMessage $email, string $text, string $message = ''): void
    {
        self::assertThat($email, new MimeConstraint\EmailHtmlBodyContains($text), $message);
    }

    public static function assertEmailHtmlBodyNotContains(RawMessage $email, string $text, string $message = ''): void
    {
        self::assertThat($email, new LogicalNot(new MimeConstraint\EmailHtmlBodyContains($text)), $message);
    }

    public static function assertEmailHasHeader(RawMessage $email, string $headerName, string $message = ''): void
    {
        self::assertThat($email, new MimeConstraint\EmailHasHeader($headerName), $message);
    }

    public static function assertEmailNotHasHeader(RawMessage $email, string $headerName, string $message = ''): void
    {
        self::assertThat($email, new LogicalNot(new MimeConstraint\EmailHasHeader($headerName)), $message);
    }

    public static function assertEmailHeaderSame(
        RawMessage $email,
        string $headerName,
        string $expectedValue,
        string $message = ''
    ): void {
        self::assertThat($email, new MimeConstraint\EmailHeaderSame($headerName, $expectedValue), $message);
    }

    public static function assertEmailHeaderNotSame(
        RawMessage $email,
        string $headerName,
        string $expectedValue,
        string $message = ''
    ): void {
        self::assertThat(
            $email,
            new LogicalNot(new MimeConstraint\EmailHeaderSame($headerName, $expectedValue)),
            $message
        );
    }

    public static function assertEmailAddressContains(
        RawMessage $email,
        string $headerName,
        string $expectedValue,
        string $message = ''
    ): void {
        self::assertThat($email, new MimeConstraint\EmailAddressContains($headerName, $expectedValue), $message);
    }


    /**
     * Get all messages from event dispatcher
     * Function must be non-static otherwise to be in object context to get container
     *
     * @param string|null $transport
     * @return RawMessage[]
     */
    public function getMailerMessages(string $transport = null): array
    {
        return $this->getMessageMailerEvents()->getMessages($transport);
    }

    /**
     * Get one message from event dispatcher
     * Function must be non-static otherwise to be in object context to get container
     *
     * @param int $index
     * @param string|null $transport
     * @return RawMessage|null
     */
    public function getMailerMessage(int $index = 0, string $transport = null): ?RawMessage
    {
        return $this->getMailerMessages($transport)[$index] ?? null;
    }

    /**
     * Retrieves message logger listener events containing emails
     * Note: Modified function to work with our DI container
     * Source: https://github.com/odan/slim4-tutorial/issues/36#issuecomment-862305628
     *
     * @return MessageEvents
     */
    private function getMessageMailerEvents(): MessageEvents
    {
        $dispatcher = $this->container->get(EventDispatcherInterface::class);

        /** @var EventSubscriberInterface[] $listeners */
        foreach ($dispatcher->getListeners() as $listeners) {
            foreach ($listeners as $listener) {
                $listenerInstance = $listener[0];

                if (!$listenerInstance instanceof MessageLoggerListener) {
                    continue;
                }

                return $listenerInstance->getEvents();
            }
        }

        throw new \RuntimeException('The Mailer event dispatcher must be enabled to make email assertions.');
    }


}