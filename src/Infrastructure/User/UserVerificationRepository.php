<?php
declare(strict_types=1);

namespace App\Infrastructure\User;

use App\Infrastructure\DataManager;
use Cake\Database\Connection;

/**
 * Needed to define the table
 */
class UserVerificationRepository extends DataManager
{

    public function __construct(Connection $conn = null)
    {
        parent::__construct($conn);
        $this->table = 'user_verification';
    }

    /**
     * @param $verificationId
     * @return string
     */
    public function getUserIdFromVerification($verificationId): string
    {
        return $this->findById($verificationId, ['user_id'])['user_id'];
    }
}
