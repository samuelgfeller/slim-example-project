<?php

namespace App\Test\Unit\Security;

use App\Domain\Security\Data\RequestData;
use App\Domain\Security\Data\RequestStatsData;
use App\Domain\Security\Enum\SecurityType;
use App\Domain\Security\Exception\SecurityException;
use App\Domain\Security\Service\SecurityEmailChecker;
use App\Infrastructure\Security\EmailRequestFinderRepository;
use App\Test\Traits\AppTestTrait;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

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

    /**
     * Covered in this test:
     *  - [Individual Email abuse] Test with every defined threshold of requests sending an email from a specific ip or
     *    concerning an email.
     *
     * Data provider is very important in this test. It will call this function with all the different kinds of user
     * request amounts where an exception must be thrown.
     *
     * @dataProvider \App\Test\Provider\Security\UserRequestProvider::individualEmailThrottlingTestCases()
     *
     * @param int|string $delay
     * @param array{email_stats: RequestStatsData, ip_stats: RequestStatsData} $ipAndEmailRequestStats
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testPerformEmailAbuseCheckIndividual(int|string $delay, array $ipAndEmailRequestStats): void
    {
        // Preparation; making sure other security checks won't fail
        $requestFinderRepository = $this->mock(EmailRequestFinderRepository::class);
        // Very important to return stats otherwise global check fails
        $requestFinderRepository->method('getGlobalSentEmailAmount')->willReturn(0);

        // Actual test
        // Provider alternates between ip stats with content exceeding threshold (new threshold on each run)
        // email stats being empty and then vice versa
        $requestFinderRepository->method('getEmailRequestStatsFromEmailAndIp')->willReturn($ipAndEmailRequestStats);

        // lastRequest has to be defined here. In the provider "created_at" seconds often differs from assertion
        $lastRequest = new RequestData(
            [
                'id' => 12,
                'email' => 'email.does@not-matter.com',
                'ip_address' => 2130706433, // 127.0.0.1 as unsigned int
                'sent_email' => 1,
                'is_login' => 'success', // Not relevant for login test
                'created_at' => date('Y-m-d H:i:s'), // Current time so delay will be the original length
            ]
        );
        // Relevant for email abuse tests
        $requestFinderRepository->method('findLatestEmailRequestFromUserOrIp')->willReturn($lastRequest);

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
     * @dataProvider \App\Test\Provider\Security\UserRequestProvider::globalEmailStatsProvider()
     *
     * Values same as threshold as exception is thrown if it equals or is greater than threshold
     *
     * @param int $dailyEmailAmount too many daily emails
     * @param int $monthlyEmailAmount too many monthly emails
     */
    public function testPerformEmailAbuseCheckGlobal(
        int $dailyEmailAmount,
        int $monthlyEmailAmount
    ): void {
        $requestFinderRepository = $this->mock(EmailRequestFinderRepository::class);

        // Preparation; making sure other security checks won't fail
        // User stats should be 0 as global is tested here
        $emptyStatsData = new RequestStatsData(
            ['request_amount' => 0, 'sent_emails' => 0, 'login_failures' => 0, 'login_successes' => 0]
        );
        $emptyEmailAndIpStats = [
            'email_stats' => $emptyStatsData,
            'ip_stats' => $emptyStatsData,
        ];
        $requestFinderRepository->method('getEmailRequestStatsFromEmailAndIp')
            ->willReturn($emptyEmailAndIpStats);

        // Actual test starts here
        // In the first test iteration the provider sets the daily amount and leaves monthly blank
        // The second time this test is executed the provider sets monthly amount and leaves daily blank
        $requestFinderRepository->method('getGlobalSentEmailAmount')->willReturnOnConsecutiveCalls(
            $dailyEmailAmount,
            $monthlyEmailAmount
        );

        $securityService = $this->container->get(SecurityEmailChecker::class);

        // Exception assertions
        $this->expectException(SecurityException::class);
        // For the daily amount test, $monthlyEmailAmount is the same as daily. If its more it means that this test
        // iteration is about monthly amount
        if ($monthlyEmailAmount > $dailyEmailAmount) {
            $this->expectExceptionMessage('Maximum amount of unrestricted email sending monthly reached site-wide.');
        } // The least possible monthly values is the same as daily which is given by the provider for the daily test
        elseif ($monthlyEmailAmount === $dailyEmailAmount) {
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
