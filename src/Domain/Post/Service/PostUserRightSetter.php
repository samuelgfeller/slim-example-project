<?php

namespace App\Domain\Post\Service;

use App\Domain\Authentication\Service\UserRoleFinder;
use App\Domain\Post\Data\UserPostData;
use Odan\Session\SessionInterface;

/**
 * The client should know when to display edit and delete icons
 * Admins can edit all posts, users only their own
 */
class PostUserRightSetter
{
    public function __construct(
        private SessionInterface $session,
        private UserRoleFinder $userRoleFinder,
    ) { }

    /**
     * Add userUpdateRight attribute to given UserPostData with
     * logged-in user mutation right.
     *
     * I'm not sure if that is a good practice to accept collections and single objects both in the same function,
     * but I have seen this in a PHP function and thought it was practical.
     * @param UserPostData[]|UserPostData $userPostData
     *
     * @return void In PHP, an object variable doesn't contain the object itself as value. It only contains an object
     * identifier meaning the reference is passed and changes are made on the original reference that can be used further
     * https://www.php.net/manual/en/language.oop5.references.php; https://stackoverflow.com/a/65805372/9013718
     */
    public function setUserRightsOnPosts(array|UserPostData $userPostData): void
    {
        if (is_array($userPostData)) {
            foreach ($userPostData as $userPost) {
                $this->setUserRightsOnPost($userPost);
            }
        } else {
            $this->setUserRightsOnPost($userPostData);
        }
    }

    /**
     * Add userUpdateRight attribute to given UserPostData with
     * logged-in user mutation right.
     *
     * @param UserPostData $userPost

     */
    private function setUserRightsOnPost(UserPostData $userPost): void
    {
        // Default is no rights
        $userPost->userMutationRight = UserPostData::MUTATION_PERMISSION_NONE;

        if (($loggedInUserId = $this->session->get('user_id')) !== null) {
            $userRole = $this->userRoleFinder->getUserRoleById($loggedInUserId);

            if ($userPost->userId === $loggedInUserId || $userRole === 'admin') {
                $userPost->userMutationRight = UserPostData::MUTATION_PERMISSION_ALL;
            }
        }
    }
}