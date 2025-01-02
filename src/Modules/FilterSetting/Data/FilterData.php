<?php

namespace App\Modules\FilterSetting\Data;

/**
 * Filter chip data.
 */
class FilterData
{
    public string $name;
    public string $paramName;
    public ?string $paramValue;
    // Category is the title of the group of filters, e.g. "Status" or "User"
    public ?string $category;
    public bool $authorized;

    public function __construct(array $data)
    {
        $this->name = $data['name'] ?? null;
        $this->paramName = $data['paramName'] ?? null;
        $this->paramValue = $data['paramValue'] ?? null;
        $this->category = $data['category'] ?? null;
        $this->authorized = $data['authorized'] ?? null;
    }
}
