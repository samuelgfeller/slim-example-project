<?php

namespace App\Test\TestCase\Security\Unit;

use App\Core\Infrastructure\Settings\Settings;
use App\Module\Security\Email\Repository\EmailLogFinderRepository;
use App\Module\Security\Email\Service\SecurityEmailChecker;
use App\Module\Security\Enum\SecurityType;
use App\Module\Security\Exception\SecurityException;
use App\Test\Trait\AppTestTrait;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use TestTraits\Trait\MockTestTrait;

/**
 * Threats:
 *  - Email abuse (sending a lot of emails may be costly).
 *
 * Testing whole performEmailAbuseCheck() function and not sub-functions directly as
 * they are private. Reasons are summarized in the class doc of SecurityLoginCheckerTest.php
 */
class SecurityEmailCheckerTest extends TestCase
{
    use AppTestTrait;
    use MockTestTrait;

    /**
     * Covered in this test:
     *  - [Individual email abuse] Test sending an email from a user or to an address with every defined threshold.
     *
     * The Data Provider calls this function with all the different variation of email
     * request amounts where an exception must be thrown.
     *
     * @param int|string $delay
     * @param int $emailLogAmountInTimeSpan
     * @param array $securitySettings
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[DataProviderExternal(\App\Test\TestCase\Security\Provider\EmailRequestProvider::class, 'individualEmailThrottlingTestCases')]
    public function testPerformEmailAbuseCheckIndividual(
        int|string $delay,
        int $emailLogAmountInTimeSpan,
        array $securitySettings,
    ): void {
        // Settings for login throttling
        $this->mock(Settings::class)->method('get')->willReturn($securitySettings);

        // Preparation; making sure other security checks won't fail
        $requestFinderRepository = $this->mock(EmailLogFinderRepository::class);
        // Important to return stats otherwise global check fails
        $requestFinderRepository->method('getGlobalSentEmailAmount')->willReturn(0);

        // Actual test
        // Provider alternates between ip stats with content exceeding threshold (new threshold on each run)
        // email stats being empty and then vice versa
        $requestFinderRepository->method('getLoggedEmailCountInTimespan')->willReturn($emailLogAmountInTimeSpan);

        // Set return values for the last email request (with current time so delay will be the original length)
        $requestFinderRepository->method('findLatestEmailRequest')->willReturn(date('Y-m-d H:i:s'));

        $securityService = $this->container->get(SecurityEmailChecker::class);

        // Assertions
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('Exceeded maximum of tolerated emails.');
        // In try catch to assert exception attributes
        try {
            $securityService->performEmailAbuseCheck('email.does@not-matter.com');
        } catch (SecurityException $se) {
            self::assertSame(SecurityType::USER_EMAIL, $se->getSecurityType());
            $delayMessage = 'Remaining delay not matching. ' .
                'May be because mock created_at time and assertion were done in different seconds so please try again';
            self::assertSame($delay, $se->getRemainingDelay(), $delayMessage);
            // Throw because it's expected to verify that exception is thrown
            throw $se;
        }
    }

    /**
     * Threat: Distributed email abuse (sending few emails with a lot of different users)
     * Covered in this test:
     *  - First iteration: email amount reaching DAILY threshold (and thus fail)
     *  - Second iteration: email amount reaching MONTHLY threshold (and thus fail).
     *
     * Values same as threshold, as exception is thrown if it equals or is greater than threshold
     *
     * @param int $todayEmailAmount too many emails for today
     * @param int $thisMonthEmailAmount too many emails for this month
     * @param array $securitySettings
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[DataProviderExternal(\App\Test\TestCase\Security\Provider\EmailRequestProvider::class, 'globalEmailStatsProvider')]
    public function testPerformEmailAbuseCheckGlobal(
        int $todayEmailAmount,
        int $thisMonthEmailAmount,
        array $securitySettings,
    ): void {
        // Settings for login throttling
        $this->mock(Settings::class)->method('get')->willReturn($securitySettings);

        $requestFinderRepository = $this->mock(EmailLogFinderRepository::class);

        // Preparation; making sure other security checks won't fail
        // Logged email for user is set to 0 as only global is tested here
        $requestFinderRepository->method('getLoggedEmailCountInTimespan')->willReturn(0);

        // In the first test iteration, the provider sets the daily amount and leaves monthly blank
        // The second time this test is executed, the provider sets monthly amount and leaves daily blank
        $requestFinderRepository->method('getGlobalSentEmailAmount')->willReturnOnConsecutiveCalls(
            $todayEmailAmount,
            $thisMonthEmailAmount
        );

        $securityService = $this->container->get(SecurityEmailChecker::class);

        // Exception assertions
        $this->expectException(SecurityException::class);
        // For the daily amount test, $monthlyEmailAmount is the same as daily.
        // If it's more, it means that this test iteration is for monthly amount
        if ($thisMonthEmailAmount > $todayEmailAmount) {
            $this->expectExceptionMessage('Maximum amount of unrestricted email sending monthly reached site-wide.');
        } // The least possible monthly values is the same as daily which is given by the provider for the daily test
        elseif ($thisMonthEmailAmount === $todayEmailAmount) {
            $this->expectExceptionMessage('Maximum amount of unrestricted email sending daily reached site-wide.');
        } else {
            self::fail('Monthly email expected to be either greater than daily or the same');
        }

        // In try catch to assert exception attributes
        try {
            $securityService->performEmailAbuseCheck('email.does@not-matter.com');
        } catch (SecurityException $se) {
            self::assertSame(SecurityType::GLOBAL_EMAIL, $se->getSecurityType());
            self::assertSame('captcha', $se->getRemainingDelay());
            // Throw because it's expected to verify that exception is thrown
            throw $se;
        }
    }
}
