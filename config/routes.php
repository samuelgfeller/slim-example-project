<?php

/**
 * Routes configuration.
 *
 * Documentation: https://samuel-gfeller.ch/docs/Slim-Routing
 */

use App\Core\Application\Middleware\UserAuthenticationMiddleware;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    // Home page
    $app->redirect('/hello[/{name}]', '/', 301)->setName('hello-page');

    // Login routes
    $app->get('/login', \App\Module\Authentication\Login\Action\LoginPageAction::class)->setName('login-page');
    $app->post('/login', \App\Module\Authentication\Login\Action\LoginSubmitAction::class)
        ->setName('login-submit');
    $app->get('/logout', \App\Module\Authentication\Logout\LogoutPageAction::class)->setName('logout');

    // Dashboard page
    $app->get('/', \App\Module\Dashboard\Action\DashboardPageAction::class)->setName('home-page')->add(
        UserAuthenticationMiddleware::class
    );
    // Ajax route to toggle panel visibility
    $app->put('/dashboard-toggle-panel', \App\Module\Dashboard\Action\DashboardTogglePanelProcessAction::class)
        ->setName('dashboard-toggle-panel');

    // Verification of the link sent by email after registration
    $app->get(
        '/register-verification',
        \App\Module\Authentication\Register\Action\RegisterVerifyProcessAction::class
    )->setName('register-verification');

    $app->get(
        '/unlock-account',
        \App\Module\Authentication\UnlockAccount\Action\AccountUnlockProcessAction::class
    )->setName('account-unlock-verification');

    $app->post(// Url password-forgotten hardcoded in login-main.js
        '/password-forgotten',
        \App\Module\Authentication\PasswordReset\Action\PasswordForgottenEmailSubmitAction::class
    )->setName('password-forgotten-email-submit');
    // Set the new password page after clicking on email link with token
    $app->get('/reset-password', \App\Module\Authentication\PasswordReset\Action\PasswordResetPageAction::class)
        ->setName('password-reset-page');
    // Submit new password after clicking on email link with token (reset-password hardcoded in login-main.js)
    $app->post(
        '/reset-password',
        \App\Module\Authentication\PasswordReset\Action\NewPasswordResetSubmitAction::class
    )->setName('password-reset-submit');
    // Submit new password when authenticated
    $app->put(
        '/change-password/{user_id:[0-9]+}',
        \App\Module\Authentication\PasswordReset\Action\PasswordChangeSubmitAction::class
    )->setName('change-password-submit')->add(UserAuthenticationMiddleware::class);

    // Fetch gettext translations
    // Without UserAuthenticationMiddleware as translations are also needed for non-protected pages such as password reset
    $app->get('/translate', \App\Module\Localization\Action\TranslateAction::class)
        ->setName('translate');

    // User routes
    $app->group('/users', function (RouteCollectorProxy $group) {
        $group->get('/list', \App\Module\User\Action\Page\UserListPageAction::class)
            ->setName('user-list-page');
        $group->get('', \App\Module\User\Action\Ajax\UserFetchListAction::class)
            ->setName('user-list');

        $group // User create form is rendered by the client and loads the available dropdown options via Ajax
        ->get('/dropdown-options', \App\Module\User\Action\Ajax\UserCreateDropdownOptionsFetchAction::class)
            ->setName('user-create-dropdown');

        $group->get('/activity', \App\Module\User\Action\Ajax\UserActivityFetchListAction::class)
            ->setName('user-get-activity');

        $group->post('', \App\Module\User\Action\Ajax\UserCreateAction::class)
            ->setName('user-create-submit');
        // Route name has to be in the format: "[table_name]-read-page" and argument "[table-name]-id" to link from user activity
        $group->get('/{user_id:[0-9]+}', \App\Module\User\Action\Page\UserReadPageAction::class)
            ->setName('user-read-page');
        $group->put('/{user_id:[0-9]+}', \App\Module\User\Action\Ajax\UserUpdateAction::class)
            ->setName('user-update-submit');
        $group->delete('/{user_id:[0-9]+}', \App\Module\User\Action\Ajax\UserDeleteAction::class)
            ->setName('user-delete-submit');
    })->add(UserAuthenticationMiddleware::class);

    $app->get('/profile', \App\Module\User\Action\Page\UserReadPageAction::class)
        ->setName('profile-page')->add(UserAuthenticationMiddleware::class);

    // Client routes
    $app->group('/clients', function (RouteCollectorProxy $group) {
        $group->get('/{client_id:[0-9]+}', \App\Module\Client\Read\Action\ClientReadPageAction::class)
            ->setName('client-read-page');

        $group->get('', \App\Module\Client\ReadList\Action\ClientFetchListAction::class)->setName('client-list');

        // Client create form is rendered by the client and loads the available dropdown options via Ajax
        $group->get(
            '/dropdown-options',
            \App\Module\Client\Create\Action\ClientCreateDropdownOptionsFetchAction::class
        )->setName('client-create-dropdown');

        $group->post('', \App\Module\Client\Create\Action\ClientCreateAction::class)
            ->setName('client-create-submit');
        $group->put('/{client_id:[0-9]+}', \App\Module\Client\Update\Action\ClientUpdateAction::class)
            ->setName('client-update-submit');
        $group->delete('/{client_id:[0-9]+}', \App\Module\Client\Delete\Action\ClientDeleteAction::class)
            ->setName('client-delete-submit');
    })->add(UserAuthenticationMiddleware::class);

    // Client list page action
    $app->get('/clients/list', \App\Module\Client\ReadList\Action\ClientListPageAction::class)->setName(
        'client-list-page'
    )->add(UserAuthenticationMiddleware::class);

    // Note routes
    $app->group('/notes', function (RouteCollectorProxy $group) {
        $group->get('', \App\Module\Note\Action\Ajax\NoteFetchListAction::class)->setName('note-list');
        $group->get('/{note_id:[0-9]+}', \App\Module\Note\Action\Page\NoteReadPageAction::class)->setName(
            'note-read-page'
        );
        $group->post('', \App\Module\Note\Action\Ajax\NoteCreateAction::class)->setName(
            'note-create-submit'
        );
        $group->put('/{note_id:[0-9]+}', \App\Module\Note\Action\Ajax\NoteUpdateAction::class)
            ->setName('note-update-submit');
        $group->delete('/{note_id:[0-9]+}', \App\Module\Note\Action\Ajax\NoteDeleteAction::class)
            ->setName('note-delete-submit');
    })->add(UserAuthenticationMiddleware::class);

    // API routes
    $app->group('/api', function (RouteCollectorProxy $group) {
        // Options route for CORS preflight requests required, otherwise exception Method not allowed is thrown.
        $group->options('/{routes:.+}', function ($request, $response) {
            return $response;
        });

        // Client creation API call
        $group->post('/clients', \App\Module\Client\Create\Action\ApiClientCreateAction::class)
            ->setName('api-client-create-submit');
    })// Cross-Origin Resource Sharing (CORS) middleware. Allow another domain to access '/api' routes.
    // If an error occurs, the CORS middleware will not be executed and the exception caught and a response
    // sent without the appropriate access control header. I don't know how to execute a certain middleware
    // added to a route group only before the error middleware which is added last in the middleware.php file.
    ->add(\App\Core\Application\Middleware\CorsMiddleware::class);

    /**
     * Catch-all route to serve a 404 Not Found page if none of the routes match
     * This route must be defined last.
     */
    $app->map(
        ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'],
        '/{routes:.+}',
        function ($request, $response) {
            throw new HttpNotFoundException(
                $request,
                'Route "' . $request->getUri()->getHost() . $request->getUri()->getPath() . '" not found.'
            );
        }
    );
};
