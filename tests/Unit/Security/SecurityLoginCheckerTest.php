<?php

namespace App\Test\Unit\Security;

use App\Domain\Security\Enum\SecurityType;
use App\Domain\Security\Exception\SecurityException;
use App\Domain\Security\Repository\LoginLogFinderRepository;
use App\Domain\Security\Service\SecurityLoginChecker;
use App\Test\Traits\AppTestTrait;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Selective\TestTrait\Traits\MockTestTrait;

/**
 * Threats:
 *  - Rapid fire attacks (when bots try to log in with 1000 different passwords on one user account)
 *  - Distributed brute force attacks (try to log in 1000 different users with most common password).
 *
 * Testing whole function performLoginSecurityCheck() and performEmailAbuseCheck() and not sub-functions directly as
 * they are private mainly because here (https://stackoverflow.com/a/2798203/9013718 comments), they say:
 * > You should not test protected/private members directly. They belong to the internal implementation of the class,
 * > and should not be coupled with the test. This makes refactoring impossible, and eventually you don't test what
 * > needs to be tested. You need to test them indirectly using public methods.
 * I thought it would make sense to test each function separately to avoid the following complex test function and
 * I don't want those sub-functions to be public as the security check is always done in its entirety from outside.
 * But probably there are things I'm missing out on, and it seems that the internet agrees that it's a bad practice.
 */
class SecurityLoginCheckerTest extends TestCase
{
    use AppTestTrait;
    use MockTestTrait;

    /**
     * Covered in this test:
     * - [Login from ip] Test with every defined threshold of login failure and success requests coming from the same
     *    ip. Throttled same as rapid fire
     * - [Login with user] Test with every defined (in provider) threshold of login failure and success requests
     *    concerning the same user (target email).
     *
     * Data provider is very important in this test. It will call this function with all the different kinds of user
     * request amounts where an exception must be thrown.
     *
     * @dataProvider \App\Test\Provider\Security\LoginRequestProvider::individualLoginThrottlingTestCases()
     *
     * @param int|string $delay
     * @param array{
     *     logins_by_email: array{successes: int, failures: int},
     *     logins_by_ip: array{successes: int, failures: int},
     * } $ipAndEmailLogSummary
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testPerformLoginSecurityCheckIndividual(int|string $delay, array $ipAndEmailLogSummary): void
    {
        $loginLogFinderRepository = $this->mock(LoginLogFinderRepository::class);

        // Very important to return summary of requests otherwise global check fails
        $loginLogFinderRepository->method('getGlobalLoginAmountSummary')->willReturn(
            ['login_total' => 21, 'login_failures' => 0] // 0 percent failures so global check won't fail
        );

        // Actual test
        // Provider alternates between ip stats with content exceeding threshold (new threshold on each run)
        $loginLogFinderRepository->method('getLoginSummaryFromEmailAndIp')->willReturn($ipAndEmailLogSummary);

        // lastRequest has to be defined here. In the provider "created_at" seconds often differs from assertion
        $loginLogFinderRepository->method('findLatestLoginTimestampFromUserOrIp')->willReturn(date('Y-m-d H:i:s'));

        $securityService = $this->container->get(SecurityLoginChecker::class);

        // Assert
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('Exceeded maximum of tolerated login requests');

        // In try catch to assert exception attributes
        try {
            $securityService->performLoginSecurityCheck('email.does@not-matter.com', null, true);
        } catch (SecurityException $se) {
            self::assertSame(SecurityType::USER_LOGIN, $se->getSecurityType());
            $delayMessage = 'Remaining delay not matching expected';
            // ? If tests fail, check that 'login_throttle_rule' at the top of provider matches the values in config
            self::assertEqualsWithDelta($delay, $se->getRemainingDelay(), 1, $delayMessage);
            // Throw exception as expectException is set up to assert that exception is thrown
            throw $se;
        }
    }

    /**
     * Threat: Distributed brute force attacks (try to log in 1000 different users with most common password).
     *
     * Covered in this test:
     *  - Global login failures exceeding allowed threshold
     */
    public function testPerformLoginSecurityCheckGlobal(): void
    {
        $loginLogFinderRepository = $this->mock(LoginLogFinderRepository::class);

        // We have to make sure other security checks won't fail when they're executed
        // User summary should be 0 as global is tested here
        $emptyLogSummary = [
            'logins_by_email' => ['successes' => 0, 'failures' => 0],
            'logins_by_ip' => ['successes' => 0, 'failures' => 0],
        ];
        $loginLogFinderRepository->method('getLoginSummaryFromEmailAndIp')->willReturn($emptyLogSummary);

        // Actual test starts here
        // Login amount stats used to calculate threshold
        // This amount doesn't matter (could be other int as long as calculated threshold from it is more than 20)
        // If failure percentage is 20%, min val (for exception to be thrown) is 105 as it results in a threshold of 21
        // If failure percentage is 10%, the minimum value for an exception to be thrown is 210 (and 200 failures)
        $totalLogins = 1000;
        $loginAmountStats = [
            'total_amount' => $totalLogins,
            // Failure amount threshold has to be less than actual failures, but we'll add 1 just in case
            'failures' => ($totalLogins / 100 *
                    $this->container->get('settings')['security']['login_failure_percentage']) + 1,
            'successes' => 0, // Value doesn't matter if failures exceed or are equal the threshold
        ];
        $loginLogFinderRepository->method('getGlobalLoginAmountSummary')->willReturn($loginAmountStats);

        /** @var SecurityLoginChecker $securityService */
        $securityService = $this->container->get(SecurityLoginChecker::class);

        // Exception assertions
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('Maximum amount of tolerated unrestricted login requests reached site-wide.');

        // In try catch to assert exception attributes
        try {
            $securityService->performLoginSecurityCheck('email.does@not-matter.com');
        } catch (SecurityException $se) {
            self::assertSame(SecurityType::GLOBAL_LOGIN, $se->getSecurityType());
            self::assertSame('captcha', $se->getRemainingDelay());
            // Throw because it's expected to verify that exception is thrown
            throw $se;
        }
    }
}
