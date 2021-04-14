<?php
/**
 * @var int|string $throttleDelay number of seconds user has to wait or 'captcha'
 */
?>

<?php
if (isset($throttleDelay)) {
    if (is_numeric($throttleDelay)) { ?>
        <strong class="err-msg">Please wait <?= $throttleDelay ?> seconds before trying again</strong>
    <?php
    } elseif ($throttleDelay === 'captcha') { ?>
        <strong class="err-msg">Please fill out the captcha and try again</strong>
    <?php
    }
} ?>