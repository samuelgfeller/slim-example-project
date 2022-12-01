<?php


namespace App\Domain\Home;

use App\Domain\Utility\Mailer;

/**
 * This service serves when I want to test php concepts, syntax or else while developing
 */
class PhpDevTester
{
    public function __construct(
        private readonly Mailer $mailer
    ) { }

    public function testInheritanceInjection()
    {

    }
}