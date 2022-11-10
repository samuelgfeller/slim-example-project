<?php
/**
 * @var $this \Slim\Views\PhpRenderer Rendering engine
 * @var $users \App\Domain\User\Data\UserResultData[] users
 * @var $userStatuses array all user statuses for dropdown with as key and value the name
 * @var $userRoles array all user roles for dropdown with as key the id and value the name
 */

use App\Domain\Authorization\Privilege;

$this->setLayout('layout.html.php');

// Define assets that should be included
// Populate variable $css for layout which then generates the HTML code to include assets
$this->addAttribute('css', [
    'assets/general/css/form.css',
    'assets/general/css/plus-button.css',
    'assets/general/css/modal/form-modal.css',
    'assets/client/client-create-modal.css',
    'assets/general/css/table/responsive-table.css',
    // post.css has to come last to overwrite other styles
]);
$this->addAttribute(
    'js',
    [
        'assets/general/js/modal.js',
    ]
);
// Js files that import things from other js files
$this->addAttribute(
    'jsModules',
    [
        'assets/user/list/js/user-list-main.js',
        // 'assets/client/js/create/client-create-main.js',
    ]
);

?>
<div class="vertical-center">
    <h1>Users</h1>
    <div class="plus-btn" id="create-user-btn"></div>
</div>

<div class="responsive-table-container">
    <table class="responsive-table">
        <thead>
        <tr>
            <th>First name</th>
            <th>Last name</th>
            <th class="column-hidden-on-mobile">Email</th>
            <th>Status</th>
            <th>Role</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($users as $user) { ?>
            <tr data-user-id="<?= $user->id ?>">
                <td><?= $user->firstName ?></td>
                <td><?= $user->surname ?></td>
                <td class="column-hidden-on-mobile"><?= $user->email ? '<a href="mailto:' . $user->email . '">' .
                        $user->email . '</a>' : '' ?></td>
                <td>
                    <select name="status" class="default-select"
                        <?= $user->statusPrivilege->hasPrivilege(Privilege::UPDATE) ? '' : 'disabled' ?>
                        >
                        <?php
                        // Client status select options
                        foreach ($userStatuses as $userStatus) {
                            $selected = $userStatus === $user->status ? 'selected' : '';
                            echo "<option value='$userStatus->value' $selected>" .
                                ucfirst($userStatus->value) . "</option>";
                        }
                        ?>
                    </select>
                </td>
                <td><select name="user_role_id" class="default-select"
                        <?= $user->userRolePrivilege->hasPrivilege(Privilege::UPDATE) ? '' : 'disabled' ?>
                    >
                        <?php
                        // Client status select options
                        foreach ($user->availableUserRoles as $id => $userRole) {
                            $selected = $id === $user->user_role_id ? 'selected' : '';
                            echo "<option value='$id' $selected>" .
                                ucfirst(str_replace('_', ' ', $userRole)) . "</option>";
                        }
                        ?>
                    </select></td>
            </tr>
            <?php
        } ?>
        </tbody>
    </table>
</div>