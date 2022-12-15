<?php

namespace App\Domain\Dashboard\Data;

class DashboardData
{
    public string $title;
    public ?string $panelId;
    public ?string $panelClass;
    public ?string $panelHtmlContent;
    public bool $authorized;

    public function __construct(array $data)
    {
        $this->title = $data['title'] ?? null;
        $this->panelId = $data['panelId'] ?? null;
        $this->panelClass = $data['panelClass'] ?? null;
        $this->panelHtmlContent = $data['panelHtmlContent'] ?? null;
        $this->authorized = $data['authorized'] ?? null;
    }
}
