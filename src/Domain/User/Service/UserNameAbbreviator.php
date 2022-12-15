<?php

namespace App\Domain\User\Service;

use App\Domain\User\Data\UserData;

class UserNameAbbreviator
{
    public function __construct(
    ) {
    }

    /**
     * Recursive function that builds abbreviation for lastname.
     *
     * @param string $lastName
     * @param UserData[] $usersToCheck
     *
     * @return string
     */
    private function buildLastNameAbbreviation(string $lastName, array $usersToCheck): string
    {
        $abbreviatedLastName = '';
        foreach ($usersToCheck as $userToCheck) {
            // Check given lastname against all other lastnames that have the same firstname
            $buildLastName = static function (string $lastName, string $lastNameToCheck, int $i = 1) use (
                &$buildLastName
            ): string {
                // When $i (amount of letters) of last name to abbreviate is the same as the full name,
                // there is no short form and the function must end as it would cause infinite recursion.
                // Checks if short form ($i letters from the beginning of surname) is contained in name to check
                if (strlen($lastName) > $i && str_contains($lastNameToCheck, substr($lastName, 0, $i))) {
                    // Increase number of letters that should be used of the last name as it exists in the name to check
                    $i++;
                    $shortName = $buildLastName($lastNameToCheck, $lastName, $i);
                } else {
                    // Return first $i letters of lastname
                    $shortName = substr($lastName, 0, $i);
                }

                return $shortName;
            };
            // Always privilege longest lastname abbreviation as it means that this length necessary
            if (strlen($builtLastName = $buildLastName($lastName, $userToCheck->surname)) > strlen($abbreviatedLastName)) {
                $abbreviatedLastName = $builtLastName;
            }
        }
        // If lastname abbreviation not full lastname, add .
        if ($abbreviatedLastName !== $lastName) {
            $abbreviatedLastName .= '.';
        }

        return $abbreviatedLastName;
    }

    /**
     * Find all the names of users for the dropdown.
     * Firstnames are privileged but if there is a duplicate,
     * the first last name chars are added.
     *
     * @param UserData[] $users original users
     *
     * @return array{user_id: string} array of users with abbreviated full names
     */
    public function abbreviateUserNames(array $users): array
    {
        $outputNames = [];
        $groupedUsers = [];

        // Group users by first name
        foreach ($users as $user) {
            // Use first_name as array key for duplicates to be grouped
            $groupedUsers[$user->firstName][$user->id] = $user;
        }

        // Loop over the ordered user array
        /** @var UserData[] $usersWithIdenticalFirstName */
        foreach ($groupedUsers as $firstName => $usersWithIdenticalFirstName) {
            // If there is only one entry it means that it's a unique first name
            if (count($usersWithIdenticalFirstName) === 1) {
                // reset() returns the first value of the array
                $userWithUniqueFirstName = reset($usersWithIdenticalFirstName);
                $outputNames[$userWithUniqueFirstName->id] = $userWithUniqueFirstName->firstName;
                continue;
            }

            // Duplicates
            foreach ($usersWithIdenticalFirstName as $userId => $user) {
                // Make copy of users with identical first name to unset it and pass only the "other" users to the function
                $usersToCheckAgainst = $usersWithIdenticalFirstName;
                // Remove currently iterated user from users to be checked against array
                unset($usersToCheckAgainst[$userId]);
                // Call recursive function which compares last name of currently iterated user with other users with same
                // first name and returns the shortest version of non-duplicate lastname
                $lastNameAbbr = $this->buildLastNameAbbreviation($user->surname, $usersToCheckAgainst);
                $outputNames[(int)$userId] = $user->firstName . ' ' . $lastNameAbbr;
            }
        }

        return $outputNames;
    }
}
