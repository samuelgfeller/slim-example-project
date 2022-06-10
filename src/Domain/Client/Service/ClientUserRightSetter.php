<?php

namespace App\Domain\Client\Service;

use App\Domain\Authentication\Service\UserRoleFinder;
use App\Domain\Client\Data\ClientResultAggregateData;
use App\Domain\Post\Data\UserPostData;
use Odan\Session\SessionInterface;

/**
 * The client should know when to display edit and delete icons
 * Admins can edit all posts, users only their own
 */
class ClientUserRightSetter
{
    public function __construct(
        private SessionInterface $session,
        private UserRoleFinder $userRoleFinder,
    ) { }

    /**
     * Add mutation rights attribute to given Data object with corresponding to
     * logged-in user permissions.
     *
     * I'm not sure if that is a good practice to accept collections OR single objects, both in the same function,
     * but I have seen this in a PHP function and thought it was practical.
     * @param ClientResultAggregateData[]|ClientResultAggregateData $clientResultAggregateData
     *
     * @return void In PHP, an object variable doesn't contain the object itself as value. It only contains an object
     * identifier meaning the reference is passed and changes are made on the original reference that can be used further
     * https://www.php.net/manual/en/language.oop5.references.php; https://stackoverflow.com/a/65805372/9013718
     */
    public function defineUserRightsOnClients(array|ClientResultAggregateData $clientResultAggregateData): void
    {
        if (is_array($clientResultAggregateData)) {
            foreach ($clientResultAggregateData as $clientResult) {
                $this->defineUserRightsOnClient($clientResult);
            }
        } else {
            $this->defineUserRightsOnClient($clientResultAggregateData);
        }
    }

    /**
     * Add userUpdateRight attribute to given UserPostData with
     * logged-in user mutation right.
     *
     * @param ClientResultAggregateData $clientResultAggregateData
     */
    private function defineUserRightsOnClient(ClientResultAggregateData $clientResultAggregateData): void
    {
        // Default is no rights
//        $clientResultAggregateData->userMutationRight = UserPostData::MUTATION_PERMISSION_NONE;
//
//        if (($loggedInUserId = $this->session->get('user_id')) !== null) {
//            $userRole = $this->userRoleFinder->getUserRoleById($loggedInUserId);
//
//            if ($clientResultAggregateData->userId === $loggedInUserId || $userRole === 'admin') {
//                $clientResultAggregateData->userMutationRight = UserPostData::MUTATION_PERMISSION_ALL;
//            }
//        }
    }
}