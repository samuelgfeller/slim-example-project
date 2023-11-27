<?php
/** @var \Odan\Session\FlashInterface $flash */
?>

<aside id="flash-container">
    <?php
    // Display flash messages if there are any
    foreach ($flash?->all() ?? [] as $flashCategory => $flashMessages) {
        foreach ($flashMessages as $msg) { ?>
            <dialog class="flash <?= $flashCategory /* success, error, info, warning */ ?>">
                <figure class="flash-fig" draggable="false">
                    <?php
                    // If it was possible to set the base path for css, the `content:` tag could be used?>
                    <img class="<?= $flashCategory === "success" ? "open" : '' ?>" draggable="false"
                         src="assets/general/page-component/flash-message/img/flash-checkmark.svg" alt="success">
                    <img class="<?= $flashCategory === "error" ? "open" : '' ?>" draggable="false"
                         src="assets/general/page-component/flash-message/img/flash-error.svg" alt="error">
                    <img class="<?= $flashCategory === "info" ? "open" : '' ?>" draggable="false"
                         src="assets/general/page-component/flash-message/img/flash-info.svg" alt="info">
                    <img class="<?= $flashCategory === "warning" ? "open" : '' ?>" draggable="false"
                         src="assets/general/page-component/flash-message/img/flash-warning.svg" alt="warning">
                </figure>
                <!-- Elements in flash-message div have to be stuck together, all spaces are interpreted literally and
                 display in DOM -->
                <div class="flash-message"><h3><?= html(ucfirst($flashCategory)) /* Serves as default, is overwritten in  */
            ?> message</h3><p><?= // No line break between h3 and p
            /* Flash messages are hardcoded strings on the server, and html is used to format them,
                   so it should be interpreted. This is the only exception where html() for escaping is not used*/
            $msg ?></p></div>
                <span class="flash-close-btn">&times;</span>
            </dialog>
            <?php
        }
    } ?>
</aside>