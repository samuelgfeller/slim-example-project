<?php
/**
 * @var int|string $throttleDelay number of seconds user has to wait or 'captcha'
 * @var string $formErrorMessage form error message
 */

?>
<?php
if (isset($throttleDelay)) { ?>
    <div class="error-div-below-form">
        <?php
        // Display throttle message if there is not already a formErrorMessage
        if (!isset($formErrorMessage)) {
            $userThrottleMessage = is_numeric($throttleDelay) ?
                sprintf(__('wait %s'), '<span class="throttle-time-span">' . html($throttleDelay) . '</span>s')
                : __('fill out the captcha');
            ?>
            <strong class="err-msg" id="throttle-delay-msg">Please <?= $userThrottleMessage ?> and try again.</strong>
            <?php
        }
        if ($throttleDelay === 'captcha') { ?>
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
            <script>
                // Display captcha
                document.getElementById('recaptcha').style.display = 'inline';
            </script>
            <?php
        } ?>
    </div>
    <?php
} ?>