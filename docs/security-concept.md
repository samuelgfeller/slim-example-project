# Security Concept

## Validation
To ensure the integrity and security of user-submitted data, a validation process 
is implemented. The `cakephp/validation` component is used for this purpose.

## Escaping
Values displayed to the user in are first escaped to prevent XSS attacks.  
In the PhpRenderer-templates the function `html()` is used and in the JS-templates
the function `escapeHtml()` serves the same purpose.

# Throttling

## Authentication

### Login requests on specific user or coming from specific IP

* `$settings['security']['login_throttle'] => [4 => 10, 9 => 120, 12 => 'captcha']` defines the threshold values (key)
  and delay in seconds (value) when threshold is reached
* Above thresholds are for login failures within the given timespan `$settings['security']['timespan']`. 
  After waiting the time specified in `[timespan]`, the user is able to log in again without throttling.
* When retrieving the user and ip logs, the failed login attempts in the last `[timespan]` are summed meaning 
  that if there were 5 fails, 1 success and then 2 fails, it counts as 7 fails. 

### Global login rules (distributed brute force)

* `$settings['security']['login_failure_percentage'] => 20` is the login failure threshold ratio in percentage 
  meaning that the ratio of failed in comparaison to total login requests has to be less than the given percentage.
* If the threshold is reached, all users have to fill out the captcha form before being able to log in.
* Total login value is taken from past month.
* This throttle is active **only when there is a significant amount of failed login requests**. This is
  when the calculated failure threshold (so total site-wide failed login requests divided by 
  `login_failure_percentage`) is more than 20. So if the defined failure ratio is set to 20%, there need 
  to be more than 100 globally failed login attempts in the past month (20% of 100 is 20).

### Registration
* After filling out the registration form, user is created with status `unverified`.
* Email is sent with verification token.
* After the link is opened, it will set the status of the user on `active` and user may log in. 

## Email spamming

### Email requests for specific email address or coming from IP

* `$settings['security']['user_email_throttle'] => [5 => 2, 10 => 4, 20 => 'captcha']` defines the threshold 
  values (key) and delay in seconds (value) when threshold is reached.
* Above threshold applies to email sent in the past given timespan `$settings['security']['timespan']`
  (same as login).

### Global email rules

* Optional
    * Daily global limit `$settings['security']['global_daily_email_threshold'] => 300`
    * Monthly global limit `$settings['security']['global_monthly_email_threshold'] => 1000`
* When threshold is reached, captcha is required for anyone wanting to send emails.


---

### Resources
* https://stackoverflow.com/questions/549/the-definitive-guide-to-form-based-website-authentication
* https://stackoverflow.com/questions/2090910/how-can-i-throttle-user-login-attempts-in-php
* https://stackoverflow.com/questions/479233/what-is-the-best-distributed-brute-force-countermeasure
