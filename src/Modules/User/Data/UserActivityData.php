<?php

namespace App\Modules\User\Data;

use App\Modules\User\Enum\UserActivity;

class UserActivityData
{
    public ?int $id;
    public ?int $userId; // Has to be nullable when user id is unknown
    public UserActivity $action = UserActivity::UPDATED; // Default to updated
    public ?string $table;
    public ?int $rowId;
    public ?array $data;
    public ?\DateTimeImmutable $datetime;
    public ?string $ipAddress;
    public ?string $userAgent;

    // When returning the report to the frontend add the page url
    public ?string $pageUrl = null;
    public ?string $timeAndActionName = null; // Time in the correct format and action name with upper case

    public function __construct(array $userActivityValues = [])
    {
        $this->id = $userActivityValues['id'] ?? null;
        $this->userId = $userActivityValues['user_id'] ?? null;
        $this->action = UserActivity::tryFrom($userActivityValues['action'] ?? '') ?? UserActivity::UPDATED;
        $this->table = $userActivityValues['table'] ?? null;
        $this->rowId = $userActivityValues['row_id'] ?? null;
        $this->data = $userActivityValues['data'] ?? null ?
            json_decode($userActivityValues['data'], true, 512, JSON_THROW_ON_ERROR) : null;
        $this->datetime = $userActivityValues['datetime'] ?? null ?
            new \DateTimeImmutable($userActivityValues['datetime']) : null;
        $this->ipAddress = $userActivityValues['ip_address'] ?? null;
        $this->userAgent = $userActivityValues['user_agent'] ?? null;
    }

    public function toArrayForDatabase(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'action' => $this->action->value,
            'table' => $this->table,
            'row_id' => $this->rowId,
            'data' => $this->data ? json_encode($this->data, JSON_PARTIAL_OUTPUT_ON_ERROR) : null,
            // Datetime never needed for insert as it's done by the database
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
        ];
    }
}
