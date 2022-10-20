<?php


namespace App\Domain\Client\Service;


use App\Domain\Client\Authorization\ClientAuthorizationChecker;
use App\Domain\Client\Exception\NotAllowedException;
use App\Domain\Exceptions\ForbiddenException;
use App\Domain\Factory\LoggerFactory;
use App\Infrastructure\Authentication\UserRoleFinderRepository;
use App\Infrastructure\Client\ClientUpdaterRepository;
use http\Client;
use Psr\Log\LoggerInterface;

class ClientUpdater
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly ClientUpdaterRepository $clientUpdaterRepository,
        private readonly UserRoleFinderRepository $userRoleFinderRepository,
        private readonly ClientValidator $clientValidator,
        private readonly ClientFinder $clientFinder,
        LoggerFactory $logger,
        private readonly ClientAuthorizationChecker $clientAuthorizationChecker,

    ) {
        $this->logger = $logger->addFileHandler('error.log')->createInstance('post-service');
    }

    /**
     * Change something or multiple things on post
     *
     * @param int $clientId id of post being changed
     * @param array|null $clientValues values that user wants to change
     * @param int $loggedInUserId
     * @return bool if update was successful
     */
    public function updateClient(int $clientId, null|array $clientValues, int $loggedInUserId): bool
    {
        // Working with array and not ClientData object to be able to differentiate values that user wants to set to null
        $this->clientValidator->validateClientUpdate($clientValues);

        // Find note in db to compare its ownership
        $clientFromDb = $this->clientFinder->findClient($clientId);

        if ($this->clientAuthorizationChecker->isGrantedToUpdateClient($clientValues, $clientFromDb->userId)) {
            $updateData = [];
            // Additional check (next to malformed body in action) to be sure that only columns that may be updated are
            // in the final $updateData array
            foreach ($clientValues as $column => $value) {
                // Check that keys are one of the database columns that may be updated
                if (in_array($column, [
                    'client_status_id',
                    'user_id',
                    'first_name',
                    'last_name',
                    'phone',
                    'location',
                    'birthdate',
                    'email',
                    'sex'
                ])) {
                    $updateData[$column] = $value;
                } else {
                    throw new NotAllowedException('Not allowed to change client column ' . $column);
                }
            }

            if (isset($updateData['birthdate'])) {
                // Change datetime format if set
                $updateData['birthdate'] = (new \DateTime($updateData['birthdate']))->format('Y-m-d');
            }

            return $this->clientUpdaterRepository->updateClient($updateData, $clientId);
        }

// User does not have needed rights to access area or function
        $this->logger->notice(
            'User ' . $loggedInUserId . ' tried to update client with id: ' . $clientId .
            ' but isn\'t allowed.'
        );
        throw new ForbiddenException('Not allowed to change that client.');
    }
}