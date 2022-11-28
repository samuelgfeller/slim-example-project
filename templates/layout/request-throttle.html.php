<?php
/**
 * @var int|string $throttleDelay number of seconds user has to wait or 'captcha'
 * @var string $formErrorMessage form error message
 */

?>
<?php
if (isset($throttleDelay)) { ?>
    <div class="error-div-below-form">
        <?php // Display throttle message if there is not already a formErrorMessage
        if (is_numeric($throttleDelay) && !isset($formErrorMessage)) { ?>
            <strong class="err-msg" id="throttle-delay-msg">Please wait <span
                        class="throttle-time-span"><?= $throttleDelay ?></span>
                seconds before trying again</strong>
        <?php
        } elseif ($throttleDelay === 'captcha') {

            if (!isset($formErrorMessage)){ ?>
                <strong class="err-msg" id="throttle-delay-msg">Please fill out the captcha and try again</strong>
            <?php
            } ?>
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