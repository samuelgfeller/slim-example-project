<?php

namespace App\Domain\FilterSetting\Data;

class FilterData
{
    public string $name;
    public string $paramName;
    public ?string $paramValue;
    public ?string $category; // May be null
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
