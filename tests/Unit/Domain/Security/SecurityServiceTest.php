<?php

namespace App\Test\Unit\Domain\Security;

use App\Domain\Security\SecurityException;
use App\Domain\Security\SecurityService;
use App\Infrastructure\Security\RequestTrackRepository;
use App\Test\AppTestTrait;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;

/**
 * Threats:
 *  - Rapid fire attacks (when bots try to log in with 1000 different passwords on one user account)
 *  - Email abuse (sending a lot of emails may be costly)
 *  - Distributed brute force attacks (try to log in 1000 different users with most common password)
 *
 * Testing whole function performLoginSecurityCheck() and performEmailAbuseCheck() and not sub-functions directly as
 * they are private mainly because here (https://stackoverflow.com/a/2798203/9013718 comments), they say:
 * > You should not test protected/private members directly. They belong to the internal implementation of the class,
 * > and should not be coupled with the test. This makes refactoring impossible and eventually you don't test what
 * > needs to be tested. You need to test them indirectly using public methods.
 * I thought it would make sense to test each function separately to avoid the following complex test function and
 * I don't want those sub-functions to be public as the security check is always done in its entirety from outside.
 * But probably there are things I'm missing out on and it seems that the internet agrees that it's a bad practice.
 */
class SecurityServiceTest extends TestCase
{
    use AppTestTrait;

    /**
     * Covered in this test:
     * - [Login from ip] Test with every defined threshold of login failure and success requests coming from the same
     *    ip. Throttled same as rapid fire
     * - [Login with user] Test with every defined (in provider) threshold of login failure and success requests
     *    concerning the same user (target email)
     *
     * Data provider is very important in this test. It will call this function with all the different kinds of user
     * request amounts where an exception must be thrown.
     * @dataProvider \App\Test\Provider\RequestTrackProvider::userLoginProvider()
     *
     * @param int|string $delay
     * @param array $ipRequestStats
     * @param array $userRequestStats
     */
    public function testPerformLoginSecurityCheck_user(
        int|string $delay,
        array $ipRequestStats,
        array $userRequestStats
    ): void {
        $requestTrackRepository = $this->mock(RequestTrackRepository::class);

        // Very important to return stats otherwise global check fails
        $requestTrackRepository->method('getGlobalLoginAmountStats')->willReturn(
            ['login_total' => 10, 'login_failures' => 0] // 0 percent failures so global check won't fail
        );

        // Actual test
        // Provider first makes $ipRequestStats filled with each values exceeding threshold (new threshold on each run)
        $requestTrackRepository->method('getIpRequestStats')->willReturn($ipRequestStats);
        // Vice versa $userRequestStats are 0 values when ip values are tested but full later for user tests
        $requestTrackRepository->method('getUserRequestStats')->willReturn($userRequestStats);

        // lastRequest has to be defined here. In the provider "created_at" seconds often differs from assertion
        $lastRequest = [
            'id' => 12,
            'email' => 'email.does@not-matter.com',
            'ip_address' => 2130706433, // 127.0.0.1 as unsigned int
            'sent_email' => 1,
            'is_login' => 'success', // Not relevant for individual login and email test
            'created_at' => date('Y-m-d H:i:s'), // Current time so delay will be the original length
        ];
        $requestTrackRepository->method('findLatestLoginRequestFromUserOrIp')->willReturn($lastRequest);

        /** @var SecurityService $securityService */
        $securityService = $this->container->get(SecurityService::class);

        // Assert
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('Exceeded maximum of tolerated login requests.');

        // In try catch to assert exception attributes
        try {
            $securityService->performLoginSecurityCheck('email.does@not-matter.com');
        } catch (SecurityException $se) {
            self::assertSame(SecurityException::USER_LOGIN, $se->getType());
            $delayMessage = 'Remaining delay not matching. ' .
                'May be because mock created_at time and assertion were done in different seconds so please try again';
            self::assertSame($delay, $se->getRemainingDelay(), $delayMessage);
            // Throw because it's expected to verify that exception is thrown
            throw $se;
        }
    }

    /**
     * Threat: Distributed brute force attacks (try to log in 1000 different users with most common password)
     *
     * Covered in this test:
     *  - Global login failures exceeding allowed threshold
     */
    public function testPerformLoginSecurityCheck_global(): void
    {
        $requestTrackRepository = $this->mock(RequestTrackRepository::class);

        // Preparation; making sure other security checks won't fail
        // User stats should be 0 as global is tested here
        $emptyStats = ['request_amount' => 0, 'sent_emails' => 0, 'login_failures' => 0, 'login_successes' => 0];
        $requestTrackRepository->method('getIpRequestStats')->willReturn($emptyStats);
        $requestTrackRepository->method('getUserRequestStats')->willReturn($emptyStats);

        // Actual test starts here
        // Login amount stats used to calculate threshold
        $totalLogins = 1000; // This amount doesn't matter (could be other int); the later calculated ratio does
        $loginAmountStats = [
            'login_total' => $totalLogins,
            // Allowed failures amount have to be LESS than actual failures so this should trigger exception as its same
            'login_failures' => $totalLogins / 100 *
                $this->container->get('settings')['security']['login_failure_percentage']
        ];
        $requestTrackRepository->method('getGlobalLoginAmountStats')->willReturn($loginAmountStats);

        /** @var SecurityService $securityService */
        $securityService = $this->container->get(SecurityService::class);

        // Exception assertions
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('Maximum amount of tolerated unrestricted login requests reached site-wide.');

        // In try catch to assert exception attributes
        try {
            $securityService->performLoginSecurityCheck('email.does@not-matter.com');
        } catch (SecurityException $se) {
            self::assertSame(SecurityException::GLOBAL_LOGIN, $se->getType());
            self::assertSame('captcha', $se->getRemainingDelay());
            // Throw because it's expected to verify that exception is thrown
            throw $se;
        }
    }

    /**
     * Covered in this test:
     *  - [Individual Email abuse] Test with every defined threshold of requests sending an email from a specific ip or
     *    concerning an email
     *
     * Data provider is very important in this test. It will call this function with all the different kinds of user
     * request amounts where an exception must be thrown.
     * @dataProvider \App\Test\Provider\RequestTrackProvider::userEmailProvider()
     *
     * @param int|string $delay
     * @param array $ipRequestStats
     * @param array $userRequestStats
     */
    public function testPerformEmailAbuseCheck_user(
        int|string $delay,
        array $ipRequestStats,
        array $userRequestStats
    ): void {
        // Preparation; making sure other security checks won't fail
        $requestTrackRepository = $this->mock(RequestTrackRepository::class);
        // Very important to return stats otherwise global check fails
        $requestTrackRepository->method('getGlobalSentEmailAmount')->willReturn('0');

        // Actual test
        // Provider first makes $ipRequestStats filled with each values exceeding threshold (new threshold on each run)
        $requestTrackRepository->method('getIpRequestStats')->willReturn($ipRequestStats);
        // Vice versa $userRequestStats are 0 values when ip values are tested but full later for user tests
        $requestTrackRepository->method('getUserRequestStats')->willReturn($userRequestStats);

        // lastRequest has to be defined here. In the provider "created_at" seconds often differs from assertion
        $lastRequest = [
            'id' => 12,
            'email' => 'email.does@not-matter.com',
            'ip_address' => 2130706433, // 127.0.0.1 as unsigned int
            'sent_email' => 1,
            'is_login' => 'success', // Not relevant for login test
            'created_at' => date('Y-m-d H:i:s'), // Current time so delay will be the original length
        ];
        // Relevant for email abuse tests
        $requestTrackRepository->method('findLatestEmailRequestFromUserOrIp')->willReturn($lastRequest);

        /** @var SecurityService $securityService */
        $securityService = $this->container->get(SecurityService::class);

        // Assertions
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('Exceeded maximum of tolerated emails.');
        // In try catch to assert exception attributes
        try {
            $securityService->performEmailAbuseCheck('email.does@not-matter.com');
        } catch (SecurityException $se) {
            self::assertSame(SecurityException::USER_EMAIL, $se->getType());
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
     *  - Second iteration: email amount reaching MONTHLY threshold (and thus fail)
     *
     * @dataProvider \App\Test\Provider\RequestTrackProvider::globalEmailStatsProvider()
     *
     * Values same as threshold as exception is thrown if it equals or is greater than threshold
     * @param string $dailyEmailAmount too many daily emails
     * @param string $monthlyEmailAmount too many monthly emails
     */
    public function testPerformSecurityCheck_globalEmailAbuse(
        string $dailyEmailAmount,
        string $monthlyEmailAmount
    ): void {
        $requestTrackRepository = $this->mock(RequestTrackRepository::class);

        // Preparation; making sure other security checks won't fail
        // User stats should be 0 as global is tested here
        $emptyStats = ['request_amount' => 0, 'sent_emails' => 0, 'login_failures' => 0, 'login_successes' => 0];
        $requestTrackRepository->method('getIpRequestStats')->willReturn($emptyStats);
        $requestTrackRepository->method('getUserRequestStats')->willReturn($emptyStats);

        // Actual test starts here
        // In the first test iteration the provider sets the daily amount and leaves monthly blank
        // The second time this test is executed the provider sets monthly amount and leaves daily blank
        $requestTrackRepository->method('getGlobalSentEmailAmount')->willReturnOnConsecutiveCalls(
            $dailyEmailAmount,
            $monthlyEmailAmount
        );

        /** @var SecurityService $securityService */
        $securityService = $this->container->get(SecurityService::class);

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
            self::assertSame(SecurityException::GLOBAL_EMAIL, $se->getType());
            self::assertSame('captcha', $se->getRemainingDelay());
            // Throw because it's expected to verify that exception is thrown
            throw $se;
        }
    }

    // todo test success as well because if security always fails its not fun to the enduser
}
