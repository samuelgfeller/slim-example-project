<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Domain\User\User;
use App\Infrastructure\DataManager;
use Cake\Database\Connection;

class RequestTrackRepository extends DataManager
{

    public function __construct(Connection $conn = null)
    {
        parent::__construct($conn);
        $this->table = 'request_track';
    }

    /**
     * @param string $email
     * @param string $ip
     * @param bool $sentEmail whether an email was sent or not
     * @return string
     */
    public function newRequest(string $email, string $ip, bool $sentEmail): string
    {
        $query = $this->newInsertQuery();

        return $query->insert(
            [
                'email',
                'ip_address',
                'sent_email',
            ]
        )->values(
            [
                'email' => $email,
                'ip_address' => $query->newExpr("INET_ATON(:ip)"),
                'sent_email' => $sentEmail,
            ]
        )->bind(':ip', $ip, 'string')->execute()->lastInsertId();
    }
}
