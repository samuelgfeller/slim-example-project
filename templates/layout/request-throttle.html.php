<?php
/**
 * @var int|string $throttleDelay number of seconds user has to wait or 'captcha'
 */

?>
<?php
if (isset($throttleDelay)) { ?>
    <div id="throttle-div">
        <?php
        if (is_numeric($throttleDelay)) { ?>
            <strong class="err-msg" id="throttle-delay-msg">Please wait <span
                        id="delay-time"><?= $throttleDelay ?></span>
                seconds before trying again</strong>
        <?php
        } elseif ($throttleDelay === 'captcha') { ?>
            <strong class="err-msg" id="throttle-delay-msg">Please fill out the captcha and try again</strong>
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