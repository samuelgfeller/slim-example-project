# Security Concept

## Validation
Sensitive values submitted by users are validated before being interpreted and worked with. 

## Escaping
Values displayed to the user in are first escaped to prevent XSS attacks. 

# Throttling

## Authentication

### Login requests on specific user or coming from specific IP

* `$settings['security']['login_throttle'] => [4 => 10, 9 => 120, 12 => 'captcha']` defines the threshold values (key)
  and delay in seconds (value) when threshold is reached
* Above thresholds are for login failures AND login successes within given timespan
  `$settings['security']['timespan']` (at the time, it's enough to wait [timespan] to be able to try to log in again
  without throttling)
* When retrieving the user and ip stats, the failed login attempts in the last [timespan] are summed meaning if there were 5 fails, 1 success and then 2 fails again it counts as 
7 fails. This does not make a lot of sense and can be annoying but its the least complex one and I thought that in the real world the case where someone loggs in and then, in 
the same hour again with the wrong creds multiple times this case is extremly infrequent. Still it bothers me and I created [a ticket](https://samuel-gfeller.atlassian.net/browse/SLE-150).

### Global login rules (distributed brute force)

* `$settings['security']['login_failure_percentage'] => 20` is the login failure threshold ratio in percentage meaning that
  failed login requests to total login requests have to be less than the given percentage.
* If the threshold is reached, all users have to fill out captcha before being able to login.
* Total logins value is taken from past month
* This throttle kicks in **only if the calculated failure threshold is more than 20** meaning that
if the defined failure ratio is 20%, there need to be at least 105 globally failed login attempts

### Registration
* After filling out the registration form, user is created with status `unverified`.
* Email is sent with verification token.
* After the link is opened, it will set the status of the user on `active` and user may log in. 

## Email abuse

### Email requests for specific email address or coming from IP

* `$settings['security']['user_email_throttle'] => [5 => 2, 10 => 4, 20 => 'captcha']` defines the threshold values (key)
  and delay in seconds (value) when threshold is reached.
* Above threshold applies to email sent in the past given timespan `$settings['security']['timespan']`
  (same as login)

### Global email rules

* Optional
    * Daily global limit `$settings['security']['global_daily_email_threshold'] => 300`
    * Monthly global limit `$settings['security']['global_monthly_email_threshold'] => 1000`
* When threshold is reached, captcha is required for anyone wanting to send emails


---

### Resources
* https://stackoverflow.com/questions/549/the-definitive-guide-to-form-based-website-authentication
* https://stackoverflow.com/questions/2090910/how-can-i-throttle-user-login-attempts-in-php
* https://stackoverflow.com/questions/479233/what-is-the-best-distributed-brute-force-countermeasure
