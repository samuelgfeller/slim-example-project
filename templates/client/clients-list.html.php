<?php
/**
 * @var $this \Slim\Views\PhpRenderer Rendering engine
 * @var $clientListFilters array client list filters
 */

$this->setLayout('layout.html.php');

// Define assets that should be included
// Populate variable $css for layout which then generates the HTML code to include assets
$this->addAttribute('css', [
//    'assets/general/css/loader/three-dots-loader.css',
    // post.css has to come last to overwrite other styles
    'assets/general/css/form.css',
    'assets/general/css/filter-chip.css',
    'assets/general/css/content-placeholder.css',
    'assets/general/css/plus-button.css',
    'assets/general/css/modal/form-modal.css',
    'assets/client/client-list.css',
    'assets/client/client-list-loading-placeholder.css',
    'assets/client/client-create-modal.css',
]);
$this->addAttribute(
    'js',
    [
        'assets/general/js/modal.js',
        'assets/general/js/filter-chip.js',
    ]
);
// Js files that import things from other js files
$this->addAttribute(
    'jsModules',
    [
        'assets/client/js/list/client-list-main.js',
        'assets/client/js/create/client-create-main.js',
    ]
);

?>
<div class="vertical-center">
    <h1>Clients</h1>
    <div class="plus-btn" id="create-client-btn"></div>
</div>

<div id="active-filter-chips-div">
    <button id="add-filter-btn">+ Filter</button>
    <?php
    foreach ($clientListFilters['active'] as $id => $name) { ?>
        <div class="filter-chip filter-chip-active"><span data-id="<?= $id ?>"><?= $name ?></span></div>
        <?php
    } ?>

</div>
<div id="available-filter-div">
    <span id="no-more-available-filters-span">No more filters</span>
    <?php
    foreach ($clientListFilters['inactive'] as $id => $name) { ?>
        <div class="filter-chip"><span data-id="<?= $id ?>"><?= $name ?></span></div>
        <?php
    } ?>
</div>

<div id="create-client-div">
    <div id="modal">
        <div id="modal-box">
            <div id="modal-header"><span id="close-modal">&times;</span>
                <!--Header -->
                <h2>Create client</h2>
                <!--/Header -->
            </div>
            <div id="modal-body">
                <!--    Body -->
                <div class="modal-form wide-modal-form">
                    <div class="wide-modal-form-input-group">
                        <label>First name</label>
                        <input type="text" placeholder="Hans" class="form-input">
                    </div>
                    <div class="wide-modal-form-input-group">
                        <label>Last name</label>
                        <input type="text" placeholder="Zimmer" class="form-input">
                    </div>
                    <div class="wide-modal-form-input-group">
                        <label for="create-message-textarea" class="form-label">Main note</label>
                        <textarea rows="4" cols="50" name="message" id="create-message-textarea" class="form-input"
                                  placeholder="Your message here." minlength="4" maxlength="500" required></textarea>
                    </div>
                    <div class="wide-modal-form-input-group">
                        <label>Location</label>
                        <input type="text" placeholder="Basel" class="form-input">
                    </div>
                    <div class="wide-modal-form-input-group">
                        <label>Phone number</label>
                        <input type="text" placeholder="061 422 32 11" class="form-input">
                    </div>
                    <div class="wide-modal-form-input-group">
                        <label>E-Mail</label>
                        <input type="text" placeholder="mail@example.com" class="form-input">
                    </div>
                    <div class="wide-modal-form-input-group">
                        <label>Assigned user</label>
                        <select name="user_id" class="form-select" id="assigned-user">
                            <option value="22">Samuel Olivier</option>
                            <option value="25">Nicolas</option>
                            <option value="27" selected="">Hans T.</option>
                            <option value="29">Hans Zi.</option>
                            <option value="30">Hans Ze.</option>
                        </select>
                    </div>
                    <div class="wide-modal-form-input-group">
                        <label>Status</label>
                        <select name="client_status_id" class="form-select">
                            <option value="1" selected="">Action pending</option>
                            <option value="2">Done</option>
                        </select>
                    </div>


                </div>
                <!-- /Body -->
            </div>
            <div id="modal-footer">
                <!--Footer -->
                <button type="button" id="submit-btn-create-client" class="submit-btn modal-submit-btn">Create client
                </button>
                <div class="clearfix">
                </div>
                <!-- /Footer -->
            </div>
        </div>
    </div>

    <!-- Post visibility scope is either "own" or "all" depending on the if current page shows only own posts or all posts.
    All posts and own posts pages are quite similar and share the same create form and modal box. After the creation of
    a post they are re-loaded in the background (async) to be up-to-date with the server -->
    <div id="client-wrapper" data-client-filter="all">

    </div>

