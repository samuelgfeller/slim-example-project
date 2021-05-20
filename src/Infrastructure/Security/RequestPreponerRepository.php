<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Infrastructure\DataManager;

class RequestPreponerRepository
{

    public function __construct(private DataManager $dataManager)
    {
    }

    /**
     * Set the created_at time to x amount of seconds earlier
     * Used in testing to simulate waiting delay
     *
     * @param int $seconds
     * @return bool
     */
    public function preponeLastRequest(int $seconds): bool
    {
        $query = $this->dataManager->newQuery();
        $query->update('request_track')->set(
            [
                'created_at' => $query->newExpr('DATE_SUB(NOW(), INTERVAL :sec SECOND)')
            ]
        )->orderDesc('id')->limit(1)->bind(':sec', $seconds, 'integer');
        return $query->execute()->rowCount() > 0;
    }

}
