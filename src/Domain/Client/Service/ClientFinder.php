<?php


namespace App\Domain\Client\Service;


use App\Domain\Client\Data\ClientData;
use App\Infrastructure\Client\ClientFinderRepository;

class ClientFinder
{
    public function __construct(
        private ClientFinderRepository  $clientFinderRepository,
        private ClientUserRightSetter $postUserRightSetter,
    ) {
    }

    /**
     * Gives all undeleted posts from db with name of user
     *
     * @return ClientData[]
     */
    public function findAllClientsWithUsers(): array
    {
        $allClientResults = $this->clientFinderRepository->findAllClientsWithResultAggregate();

        // Add permissions on what logged-in user is allowed to do with object
        $this->postUserRightSetter->defineUserRightsOnClients($allClientResults);
        return $allClientResults;
    }

    /**
     * Find one post in the database
     *
     * @param $id
     * @return ClientData
     */
    public function findPost($id): ClientData
    {
        return $this->postFinderRepository->findPostById($id);
    }

    /**
     * Return all posts which are linked to the given user
     *
     * @param int $userId
     * @return ClientData[]
     */
    public function findAllClientsFromUser(int $userId): array
    {
        $allClients = $this->postFinderRepository->findAllPostsByUserId($userId);
        $this->changeDateFormat($allClients);
        $this->postUserRightSetter->defineUserRightsOnClients($allClients);
        return $allClients;
    }

    /**
     * Change created and updated date format from SQL datetime to
     * something we are used to see in Switzerland
     *
     * @param ClientData[] $clients
     * @param string $format If default format changes, it has to be adapted in PostListActionTest
     *
     * @return void
     */
    private function changeDateFormat(array $clients, string $format = 'd.m.Y H:i:s'): void
    {
        // Tested in PostListActionTest
        foreach ($clients as $client) {
            // Change updated at format
            $client->updatedAt = $client->updatedAt ? date(
                $format,
                strtotime($client->updatedAt)
            ) : null;
            // Change created at format
            $client->createdAt = $client->createdAt ? date(
                $format,
                strtotime($client->createdAt)
            ) : null;
        }
    }
}