<?php
/**
 * @var \Slim\Interfaces\RouteParserInterface $route
 * @var $this \Slim\Views\PhpRenderer Rendering engine
 * @var $clientAggregate \App\Domain\Client\Data\ClientResultAggregateData client
 * @var $dropdownValues App\Domain\Client\Data\ClientDropdownValuesData all statuses, users and sexes to populate dropdown
 */


$this->setLayout('layout.html.php');
?>

<?php
// Define assets that should be included
// Populate variable $css for layout which then generates the HTML code to include assets
$this->addAttribute('css', [
    'assets/general/css/form.css',
    'assets/general/css/alert-modal.css',
    'assets/general/css/loader/three-dots-loader.css',
    // page specific css has to come last to overwrite other styles
    'assets/client/client-read.css'
]);
$this->addAttribute('js', ['assets/client/client-read.js', 'assets/general/js/alert-modal.js']);
?>

<h1><?= $clientAggregate->first_name . ' ' . $clientAggregate->last_name ?></h1>

<div class="status-and-assigned-user-div">
    <!-- Status select options-->
    <label for="client-status" class="dropdown-label">Status</label>
    <select name="client_status" class="default-select">
        <?php
        // Client status select options
        foreach ($dropdownValues->statuses as $statusId => $statusName) {
            $selected = $statusId === $clientAggregate->client_status_id ? 'selected' : '';
            echo "<option value='$statusId' $selected>$statusName</option>";
        }
        ?>
    </select>

    <!-- Assigned user select options-->
    <label for="assigned-user" class="dropdown-label">Assigned to</label>
    <select name="assigned_user" class="default-select" id="assigned-user">
        <?php
        // Client status select options
        foreach ($dropdownValues->users as $id => $name) {
            $selected = $id === $clientAggregate->user_id ? 'selected' : '';
            echo "<option value='$id' $selected>$name</option>";
        }
        ?>
    </select>

</div>


Telefon-Nummer: <?= $clientAggregate->phone ?><br>
E-Mail: <?= $clientAggregate->email ?>
Ort: <?= $clientAggregate->location ?>
