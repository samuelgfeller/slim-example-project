<?php
/** @var \Odan\Session\FlashInterface $flash */

// Client side flash message generation in general.js
?>

<aside id="flash-container">
    <?php
    //    Display errors if there are some
    foreach ($flash->all() as $key => $flashCategory) {
        foreach ($flashCategory as $msg) { ?>
            <dialog class="flash <?= $key /* success, error, info, warning */ ?>">
                <figure class="flash-fig" draggable="false">
                    <?php
                    // Sadly I cannot use the `content:` tag because its impossible set basepath for css ?>
                    <img class="<?= $key === "success" ? "open" : '' ?>" draggable="false"
                         src="assets/general/page-component/flash-message/img/flash-checkmark.svg" alt="success">
                    <img class="<?= $key === "error" ? "open" : '' ?>" draggable="false"
                         src="assets/general/page-component/flash-message/img/flash-error.svg" alt="error">
                    <img class="<?= $key === "info" ? "open" : '' ?>" draggable="false"
                         src="assets/general/page-component/flash-message/img/flash-info.svg" alt="info">
                    <img class="<?= $key === "warning" ? "open" : '' ?>" draggable="false"
                         src="assets/general/page-component/flash-message/img/flash-warning.svg" alt="warning">
                </figure>
                <!-- Elements in flash-message div have to be stuck together, all spaces are interpreted literally and
                 display in DOM -->
                <div class="flash-message"><h3><?= html(ucfirst($key)) /* Gets overwritten in css, serves as default */
                        ?> message</h3><p><?= /* Flash messages are written serverside so no xss risk and html should be interpreted*/
                        $msg ?></p></div>
                <span class="flash-close-btn">&times;</span>
            </dialog>
            <?php
        }
    } ?>
</aside>