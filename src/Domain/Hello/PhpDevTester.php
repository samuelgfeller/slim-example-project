<?php


namespace App\Domain\Hello;

use App\Domain\Utility\Mailer;

/**
 * This service serves when I want to test php concepts, syntax or else while developing
 */
class PhpDevTester
{
    public function __construct(
        private Mailer $mailer
    ) { }

    public function testInheritanceInjection()
    {
        return $this->mailer->Subject = '';
    }
}