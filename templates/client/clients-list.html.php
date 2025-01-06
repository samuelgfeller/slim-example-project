<?php
/**
 * @var $this \Slim\Views\PhpRenderer Rendering engine
 * @var $clientListFilters array{
 *     inactive: array{\App\Module\FilterSetting\Data\FilterData[]},
 *     active: array{\App\Module\FilterSetting\Data\FilterData[]},
 *     } client list filters
 *
 * @var $clientCreatePrivilege \App\Module\Authorization\Enum\Privilege create or none
 */

$this->setLayout('layout/layout.html.php');


// Define assets that should be included
// Populate variable $css for layout which then generates the HTML code to include assets
$this->addAttribute('css', [
    'assets/general/page-component/form/form.css',
    'assets/general/page-component/filter-chip/filter-chip.css',
    'assets/general/page-component/skeleton-loader/skeleton-loader.css',
    'assets/general/page-component/button/plus-button.css',
    'assets/general/page-component/modal/form-modal.css',
    'assets/client/list/client-list.css',
    'assets/client/list/client-list-skeleton-loader.css',
]);
// Js files that import things from other js files
$this->addAttribute(
    'jsModules',
    [
        'assets/client/list/client-list-main.js',
        'assets/client/create/client-create-main.js',
    ]
);

?>
<div class="vertical-center">
    <h1><?= __('Clients') ?></h1>
    <?php
    if (str_contains($clientCreatePrivilege, 'C')) { ?>
        <div class="plus-btn" id="create-client-btn"></div>
        <?php
    } ?>
</div>
<input autocomplete="none" id="name-search-input" type="search" placeholder="<?= __('Search by name') ?>">
<div class="filter-chip-container">
    <div id="active-client-filter-chips-div" class="active-filter-chips-div">
        <button id="add-filter-btn">+ <?= __('Filter') ?></button>
        <?php
        foreach ($clientListFilters['active'] as $filterCategory => $filtersInCategory) {
            /** @var \App\Module\FilterSetting\Data\FilterData $filterData */
            foreach ($filtersInCategory as $filterId => $filterData) { ?>
                <div class="filter-chip filter-chip-active">
                <span data-filter-id="<?= html($filterId) ?>" data-param-name="<?= html($filterData->paramName) ?>"
                      data-param-value="<?= html($filterData->paramValue) ?>"
                      data-category="<?= html($filterData->category) ?>"><?= html($filterData->name) ?></span>
                </div>
                <?php
            }
        } ?>

    </div>
    <div id="available-filter-div">
        <span id="no-more-available-filters-span"><?= __('No more filters') ?></span>
        <?php
        foreach ($clientListFilters['inactive'] as $filterCategory => $filtersInCategory) {
            echo $filterCategory ?
                '<span class="filter-chip-container-label" data-category="' . html($filterCategory) .
                '">' . html($filterCategory) . '</span>' : '';
            /** @var \App\Module\FilterSetting\Data\FilterData $filterData */
            foreach ($filtersInCategory as $filterId => $filterData) { ?>
                <div class="filter-chip">
            <span data-filter-id="<?= html($filterId) ?>" data-param-name="<?= html($filterData->paramName) ?>"
                  data-param-value="<?= html($filterData->paramValue) ?>"
                  data-category="<?= html($filterData->category) ?>"
            ><?= html($filterData->name) ?></span>
                </div>
                <?php
            }
        } ?>
    </div>
</div>


<!-- Client visibility scope is either "own" or "all" depending on the if current page shows only own clients or all.
All clients and own clients pages are quite similar and share the same create form and modal box. After the creation of
a client, they are re-loaded in the background (async) to be up to date with the server -->
<div id="client-list-wrapper" data-client-filter="all">

</div>

