<?php

namespace App\Module\User\Service;

use App\Module\Authentication\Validation\AuthenticationValidator;
use App\Module\Exception\Domain\ValidationException;
use App\Module\User\Enum\UserLang;
use App\Module\User\Enum\UserStatus;
use App\Module\User\Enum\UserTheme;
use App\Module\User\Repository\UserFinderRepository;
use App\Module\User\Validation\Repository\ValidationUserRoleFinderRepository;
use Cake\Validation\Validator;

final readonly class UserValidator
{
    public function __construct(
        private UserFinderRepository $userFinderRepository,
        private ValidationUserRoleFinderRepository $validationUserRoleFinderRepository,
        private AuthenticationValidator $authenticationValidator,
    ) {
    }

    /**
     * Validate user registration and update.
     *
     * @param array $userValues
     * @param bool $isCreateMode
     */
    public function validateUserValues(array $userValues, bool $isCreateMode = true): void
    {
        $validator = new Validator();

        // Cake validation library automatically sets a rule that fields cannot be null as soon as there is any
        // validation rule set for the field. This is why we have to allowEmptyString to allow null.

        // For the user, there are no optional fields meaning that if any field is passed, it has to contain a value.
        $validator
            // First name and lastName are required to have values if they're given so no allowEmptyString
            ->requirePresence('first_name', $isCreateMode, __('Field is required'))
            ->minLength('first_name', 2, __('Minimum length is %d', 2))
            ->maxLength('first_name', 100, __('Maximum length is %d', 100))
            // Disallow empty strings as field is required
            ->notEmptyString('first_name', __('Required'))
            ->requirePresence('last_name', $isCreateMode, __('Field is required'))
            ->minLength('last_name', 2, __('Minimum length is %d', 2))
            ->maxLength('last_name', 100, __('Maximum length is %d', 100))
            ->notEmptyString('last_name', __('Required'))
            ->requirePresence('email', $isCreateMode, __('Field is required'))
            // email() automatically disallows empty strings
            ->email('email', false, __('Invalid email'))
            ->add('email', 'emailIsUnique', [
                'rule' => function ($value, $context) {
                    // Check if email already exists. On update requests, the user id is passed to exclude it from the check
                    return !$this->userFinderRepository->userWithEmailAlreadyExists(
                        $value,
                        $context['data']['id'] ?? null
                    );
                },
                'message' => __('Email already exists'),
            ])
            ->requirePresence('status', $isCreateMode, __('Field is required'))
            ->add('status', 'userStatusExists', [
                'rule' => function ($value, $context) {
                    // Check if given user status is one of the enum cases values
                    return in_array($value, UserStatus::values(), true);
                },
                'message' => __('Invalid option'),
            ])
            // Language is an optional field for creation and update
            ->requirePresence('language', false, __('Field is required'))
            ->add('language', 'languageIsAvailable', [
                'rule' => function ($value, $context) {
                    // Check if given user status is one of the enum cases values
                    return in_array($value, UserLang::values(), true);
                },
                'message' => __('Invalid option'),
            ])
            ->requirePresence('user_role_id', $isCreateMode, __('Field is required'))
            ->numeric('user_role_id', __('Invalid option'))
            ->add('user_role_id', 'exists', [
                'rule' => function ($value, $context) {
                    // Check if given user role exists
                    return $this->validationUserRoleFinderRepository->userRoleWithIdExists($value);
                },
                'message' => __('Invalid option'),
            ])
            // Theme is only relevant in update
            ->requirePresence('theme', false, __('Field is required'))
            ->add('theme', 'themeIsAvailable', [
                'rule' => function ($value, $context) {
                    // Check if given user status is one of the enum cases values
                    return in_array($value, UserTheme::values(), true);
                },
                'message' => __('Invalid option'),
            ]);
        // Add password validation rules to validator
        $this->authenticationValidator->addPasswordValidationRules($validator, $isCreateMode);

        // Validate and throw exception if there are errors
        $errors = $validator->validate($userValues);
        if ($errors) {
            throw new ValidationException($errors);
        }
    }
}
