<?php

namespace App\Test\Unit\Domain\Security;

use App\Domain\Security\SecurityException;
use App\Domain\Security\SecurityService;
use App\Infrastructure\Security\RequestTrackRepository;
use App\Test\AppTestTrait;
use PHPUnit\Framework\TestCase;
use Selective\TestTrait\Traits\DatabaseTestTrait;

class SecurityServiceTest extends TestCase
{
    use AppTestTrait;
    use DatabaseTestTrait;

    /**
     * Threats:
     *  - Rapid fire attacks (when bots try to log in with 1000 different passwords on one user account)
     *  - Email abuse (sending a lot of emails may be costly for hoster)
     * Covered in this test:
     *  - [Login from ip] Test with every defined threshold of login failure and success requests coming from the same
     *    ip. Throttled same as rapid fire
     *  - [Login with user] Test with every defined (in provider) threshold of login failure and success requests
     *    concerning the same user (target email)
     *  - [Individual Email abuse] Test with every defined threshold of requests sending an email from a specific ip or
     *    concerning an email
     *
     * Behaviour: Throttle log in attempts per user
     *  - After x amount of login requests or sent emails in an hour, user has to wait a certain delay before trying again
     *  - For each next login request or next email sent in the same hour, the user has to wait the same delay
     *  - Until it eventually increases after value y
     *  - If login or email requests continue, at amount z captcha is required from the user
     *  - This rule applies to login requests on a specific user or login requests coming from a specific ip
     *
     * Testing whole function performSecurityCheck() and not sub-functions directly as they are private mainly because
     * here (https://stackoverflow.com/a/2798203/9013718 comments), they say.
     * > You should not test protected/private members directly. They belong to the internal implementation of the class,
     * > and should not be coupled with the test. This makes refactoring impossible and eventually you don't test what
     * > needs to be tested. You need to test them indirectly using public methods.
     * I thought it would make sense to test each function separately to avoid the following complex test function and
     * I don't want those sub-functions to be public as the security check is always done in its entirety from outside.
     * But probably there are things I'm missing out on and it seems that the internet agrees that it's a bad practice.
     *
     * Data provider is very important in this test. It will call this function with all the different kinds of user
     * request amounts where an exception must be thrown. This is a generic test function, to change the tested
     * content, the provider should be adapted.
     * @dataProvider \App\Test\Provider\RequestTrackProvider::userLoginAndEmailProvider()
     *
     * request limit not needed as it's expected that error is thrown and that only happens if limit reached
     * @param int|string $delay delay in seconds or 'captcha'
     * @param array $globalRequestStats has to be included in provider as globalRequestsStats have to be at least as low
     * as the same value of user stats. It wouldn't make sense otherwise.
     * @param array $ipRequestStats array with stats (request_amount, sent_emails, login_failures, login_successes)
     * @param array $userRequestStats array with stats (request_amount, sent_emails, login_failures, login_successes)
     * @param string $testType USER_LOGIN|USER_EMAIL if provided data is for an email abuse test or login test
     */
    public function testPerformSecurityCheck_userRapidFireAndEmailAbuse(
        int|string $delay,
        array $globalRequestStats,
        array $ipRequestStats,
        array $userRequestStats,
        string $testType
    ): void {
        // Preparation; making sure other security checks won't fail
        $requestTrackRepository = $this->mock(RequestTrackRepository::class);
        // Mock with 0 stats to prevent undefined index notice
        $requestTrackRepository->method('getGlobalRequestStats')->willReturn($globalRequestStats);
        // Very important to return stats otherwise global check fails
        $requestTrackRepository->method('getLoginAmountStats')->willReturn(
            ['login_total' => 10, 'login_failures' => 0] // 0 percent failures so global check won't fail
        );
        $requestTrackRepository->method('getGlobalSentEmailAmount')->willReturn('0');

        // Actual test
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        // Provider first makes $ipRequestStats filled with each values exceeding threshold (new threshold on each run)
        $requestTrackRepository->method('getIpRequestStats')->willReturn($ipRequestStats);
        // Vice versa $userRequestStats are 0 values when ip values are tested but full later for user tests
        $requestTrackRepository->method('getUserRequestStats')->willReturn($userRequestStats);

        $lastRequest = [
            'id' => 12,
            'email' => 'email.does@not-matter.com',
            'ip_address' => 2130706433, // 127.0.0.1 as unsigned int
            'sent_email' => 1,
            'is_login' => 'success', // Not relevant for login test
            'created_at' => date('Y-m-d H:i:s'), // Current time so delay will be the original length
        ];
        if ($testType === SecurityException::USER_LOGIN) {
            // Relevant for login tests
            $requestTrackRepository->method('findLatestLoginRequestFromUserOrIp')->willReturn($lastRequest);
            $this->expectExceptionMessage('Exceeded maximum of tolerated login requests.');
            $exceptionType = SecurityException::USER_LOGIN;
        } elseif ($testType === SecurityException::USER_EMAIL) {
            // Relevant for email abuse tests
            $requestTrackRepository->method('findLatestEmailRequestFromUserOrIp')->willReturn($lastRequest);
            $this->expectExceptionMessage('Exceeded maximum of tolerated emails.');
            $exceptionType = SecurityException::USER_EMAIL;
        } else {
            self::fail('$testType must be either "user_email" or "user_login"');
        }

        /** @var SecurityService $securityService */
        $securityService = $this->container->get(SecurityService::class);

        $this->expectException(SecurityException::class);
        // In try catch to assert exception attributes
        try {
            $securityService->performSecurityCheck('email.does@not-matter.com');
        } catch (SecurityException $se) {
            self::assertSame($exceptionType, $se->getType());
            $delayMessage = 'Remaining delay not matching. ' .
                'May be because mock created_at time and assertion were done in different seconds so please try again';
            self::assertSame($delay, $se->getRemainingDelay(), $delayMessage);
            // Throw because it's expected to verify that exception is thrown
            throw $se;
        }
        // Not so ideal test case as it contains quite complex code; it's almost as test function needs its own unit test
    }


    /**
     * Threat: Distributed brute force attacks (try to log in 1000 different users with most common password)
     * Covered in this test:
     *  - Global login failures exceeding allowed threshold
     */
    public function testPerformSecurityCheck_globalLogin(): void
    {
        // Preparation; making sure other security checks won't fail
        $requestTrackRepository = $this->mock(RequestTrackRepository::class);
        // User stats should be 0 as global is tested here
        $emptyStats = ['request_amount' => 0, 'sent_emails' => 0, 'login_failures' => 0, 'login_successes' => 0];
        $requestTrackRepository->method('getIpRequestStats')->willReturn($emptyStats);
        $requestTrackRepository->method('getUserRequestStats')->willReturn($emptyStats);
        // Since Login is tested here it has to be made sure that email abuse check doesn't fail
        $requestTrackRepository->method('getGlobalSentEmailAmount')->willReturn('0');
        // Not needed to mock getGlobalStats as it's not used for the login check

        // Actual test starts here
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        // Login amount stats used to calculate threshold
        $totalLogins = 1000; // This amount doesn't matter (could be other int); the later calculated ratio does
        $loginAmountStats = [
            'login_total' => $totalLogins,
            // Allowed failures amount have to be LESS than actual failures so this should trigger exception as its same
            'login_failures' => $totalLogins / 100 *
                $this->container->get('settings')['security']['login_failure_percentage']
        ];
        $requestTrackRepository->method('getLoginAmountStats')->willReturn($loginAmountStats);

        /** @var SecurityService $securityService */
        $securityService = $this->container->get(SecurityService::class);

        // Exception assertions
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('Maximum amount of tolerated unrestricted login requests reached site-wide.');

        // In try catch to assert exception attributes
        try {
            $securityService->performSecurityCheck('email.does@not-matter.com');
        } catch (SecurityException $se) {
            self::assertSame(SecurityException::GLOBAL_LOGIN, $se->getType());
            self::assertSame('captcha', $se->getRemainingDelay());
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
    public function testPerformSecurityCheck_globalEmailAbuse(string $dailyEmailAmount, string $monthlyEmailAmount): void {
        // Preparation; making sure other security checks won't fail
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $requestTrackRepository = $this->mock(RequestTrackRepository::class);
        // User stats should be 0 as global is tested here
        $emptyStats = ['request_amount' => 0, 'sent_emails' => 0, 'login_failures' => 0, 'login_successes' => 0];
        $requestTrackRepository->method('getIpRequestStats')->willReturn($emptyStats);
        $requestTrackRepository->method('getUserRequestStats')->willReturn($emptyStats);
        // Since email abuse is tested here it has to be made sure that global login check doesn't fail
        $requestTrackRepository->method('getLoginAmountStats')->willReturn(
            ['login_total' => 0, 'login_failures' => 0]
        );

        // Actual test starts here
        // In the first test iteration the provider sets the daily amount and leaves monthly blank
        // The second time this test is executed the provider sets monthly amount and leaves daily blank
        $requestTrackRepository->method('getGlobalSentEmailAmount')->willReturnOnConsecutiveCalls(
            $dailyEmailAmount, $monthlyEmailAmount
        );

        /** @var SecurityService $securityService */
        $securityService = $this->container->get(SecurityService::class);

        // Exception assertions
        $this->expectException(SecurityException::class);
        // For the daily amount test, $monthlyEmailAmount is the same as daily. If its more it means that this test
        // iteration is about monthly amount
        if ($monthlyEmailAmount > $dailyEmailAmount){
            $this->expectExceptionMessage('Maximum amount of unrestricted email sending monthly reached site-wide.');
        } // The least possible monthly values is the same as daily which is given by the provider for the daily test
        elseif($monthlyEmailAmount === $dailyEmailAmount) {
            $this->expectExceptionMessage('Maximum amount of unrestricted email sending daily reached site-wide.');
        } else {
            self::fail('Monthly email expected to be either greater than daily or the same');
        }

        // In try catch to assert exception attributes
        try {
            $securityService->performSecurityCheck('email.does@not-matter.com');
        } catch (SecurityException $se) {
            self::assertSame(SecurityException::GLOBAL_EMAIL, $se->getType());
            self::assertSame('captcha', $se->getRemainingDelay());
            // Throw because it's expected to verify that exception is thrown
            throw $se;
        }
    }

    // todo test success as well because if security always fails its not fun to the enduser
}
