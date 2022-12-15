<?php

namespace App\Domain\User\Data;

use App\Domain\User\Enum\UserActivity;

class UserActivityData
{
    public ?int $id;
    public ?int $userId; // Has to be nullable when user id is unknown
    public ?UserActivity $action;
    public ?string $table;
    public ?int $rowId;
    public ?array $data;
    public ?\DateTimeImmutable $datetime;
    public ?string $ipAddress;
    public ?string $userAgent;

    // When returning the report to the client add the page url
    public ?string $pageUrl = null;
    public ?string $timeAndActionName = null; // Time in the correct format and action name with upper case

    /**
     * @param array $userActivityValues assoc values array with as key the column name
     */
    public function __construct(array $userActivityValues = [])
    {
        $this->id = $userActivityValues['id'] ?? null;
        $this->userId = $userActivityValues['user_id'] ?? null;
        $this->action = $userActivityValues['action'] ?? null ?
            UserActivity::tryFrom($userActivityValues['action']) : null;
        $this->table = $userActivityValues['table'] ?? null;
        $this->rowId = $userActivityValues['row_id'] ?? null;
        $this->data = $userActivityValues['data'] ?? null ?
            json_decode($userActivityValues['data'], true, 512, JSON_THROW_ON_ERROR) : null;
        $this->datetime = $userActivityValues['datetime'] ?? null ?
            new \DateTimeImmutable($userActivityValues['datetime']) : null;
        $this->ipAddress = $userActivityValues['ip_address'] ?? null;
        $this->userAgent = $userActivityValues['user_agent'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'action' => $this->action->value,
            'table' => $this->table,
            'row_id' => $this->rowId,
            'data' => $this->data ? json_encode($this->data, JSON_THROW_ON_ERROR) : null,
            // Datetime never needed for insert as it's done by the database
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
        ];
    }
}
