<?php

namespace App\Test\Traits\ProjectCustom;

use Selective\TestTrait\Traits\DatabaseTestTrait;

/**
 * Authentication throttling
 */
trait SecurityTestTrait
{
    use DatabaseTestTrait;

    /**
     * Set the created_at time to x amount of seconds earlier
     * Used in testing to simulate waiting delay
     *
     * @param int $seconds
     * @return bool
     */
    public function preponeLastRequest(int $seconds): bool
    {

        $this->getConnection()
        $query = $this->queryFactory->newQuery();
        $query->update('user_request')->set(
            [
                'created_at' => $query->newExpr('DATE_SUB(NOW(), INTERVAL :sec SECOND)')
            ]
        )->orderDesc('id')->limit(1)->bind(':sec', $seconds, 'integer');
        return $query->execute()->rowCount() > 0;
}
}