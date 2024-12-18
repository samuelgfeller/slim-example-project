<?php

/**
 * Routes configuration.
 *
 * Documentation: https://samuel-gfeller.ch/docs/Slim-Routing
 */

use App\Application\Middleware\UserAuthenticationMiddleware;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    // Home page
    $app->redirect('/hello[/{name}]', '/', 301)->setName('hello-page');

    // Login routes
    $app->get('/login', \App\Application\Action\Authentication\Page\LoginPageAction::class)->setName('login-page');
    $app->post('/login', \App\Application\Action\Authentication\Ajax\LoginSubmitAction::class)
        ->setName('login-submit');
    $app->get('/logout', \App\Application\Action\Authentication\Page\LogoutPageAction::class)->setName('logout');

    // Dashboard page
    $app->get('/', \App\Application\Action\Dashboard\DashboardPageAction::class)->setName('home-page')->add(
        UserAuthenticationMiddleware::class
    );
    // Ajax route to toggle panel visibility
    $app->put('/dashboard-toggle-panel', \App\Application\Action\Dashboard\DashboardTogglePanelProcessAction::class)
        ->setName('dashboard-toggle-panel');

    // Verification of the link sent by email after registration
    $app->get(
        '/register-verification',
        \App\Application\Action\Authentication\Ajax\RegisterVerifyProcessAction::class
    )->setName('register-verification');

    $app->get(
        '/unlock-account',
        \App\Application\Action\Authentication\Ajax\AccountUnlockProcessAction::class
    )->setName('account-unlock-verification');

    $app->post(// Url password-forgotten hardcoded in login-main.js
        '/password-forgotten',
        \App\Application\Action\Authentication\Ajax\PasswordForgottenEmailSubmitAction::class
    )->setName('password-forgotten-email-submit');
    // Set the new password page after clicking on email link with token
    $app->get('/reset-password', \App\Application\Action\Authentication\Page\PasswordResetPageAction::class)
        ->setName('password-reset-page');
    // Submit new password after clicking on email link with token (reset-password hardcoded in login-main.js)
    $app->post(
        '/reset-password',
        \App\Application\Action\Authentication\Ajax\NewPasswordResetSubmitAction::class
    )->setName('password-reset-submit');
    // Submit new password when authenticated
    $app->put(
        '/change-password/{user_id:[0-9]+}',
        \App\Application\Action\User\Ajax\PasswordChangeSubmitAction::class
    )->setName('change-password-submit')->add(UserAuthenticationMiddleware::class);

    // Fetch gettext translations
    // Without UserAuthenticationMiddleware as translations are also needed for non-protected pages such as password reset
    $app->get('/translate', \App\Application\Action\Common\TranslateAction::class)
        ->setName('translate');

    // User routes
    $app->group('/users', function (RouteCollectorProxy $group) {
        $group->get('/list', \App\Application\Action\User\Page\UserListPageAction::class)
            ->setName('user-list-page');
        $group->get('', \App\Application\Action\User\Ajax\UserFetchListAction::class)
            ->setName('user-list');

        $group // User create form is rendered by the client and loads the available dropdown options via Ajax
        ->get('/dropdown-options', \App\Application\Action\User\Ajax\UserCreateDropdownOptionsFetchAction::class)
            ->setName('user-create-dropdown');

        $group->get('/activity', \App\Application\Action\User\Ajax\UserActivityFetchListAction::class)
            ->setName('user-get-activity');

        $group->post('', \App\Application\Action\User\Ajax\UserCreateAction::class)
            ->setName('user-create-submit');
        // Route name has to be in the format: "[table_name]-read-page" and argument "[table-name]-id" to link from user activity
        $group->get('/{user_id:[0-9]+}', \App\Application\Action\User\Page\UserReadPageAction::class)
            ->setName('user-read-page');
        $group->put('/{user_id:[0-9]+}', \App\Application\Action\User\Ajax\UserUpdateAction::class)
            ->setName('user-update-submit');
        $group->delete('/{user_id:[0-9]+}', \App\Application\Action\User\Ajax\UserDeleteAction::class)
            ->setName('user-delete-submit');
    })->add(UserAuthenticationMiddleware::class);

    $app->get('/profile', \App\Application\Action\User\Page\UserReadPageAction::class)
        ->setName('profile-page')->add(UserAuthenticationMiddleware::class);

    // Client routes
    $app->group('/clients', function (RouteCollectorProxy $group) {
        $group->get('/{client_id:[0-9]+}', \App\Application\Action\Client\Page\ClientReadPageAction::class)
            ->setName('client-read-page');

        $group->get('', \App\Application\Action\Client\Ajax\ClientFetchListAction::class)->setName('client-list');

        // Client create form is rendered by the client and loads the available dropdown options via Ajax
        $group->get(
            '/dropdown-options',
            \App\Application\Action\Client\Ajax\ClientCreateDropdownOptionsFetchAction::class
        )->setName('client-create-dropdown');

        $group->post('', \App\Application\Action\Client\Ajax\ClientCreateAction::class)
            ->setName('client-create-submit');
        $group->put('/{client_id:[0-9]+}', \App\Application\Action\Client\Ajax\ClientUpdateAction::class)
            ->setName('client-update-submit');
        $group->delete('/{client_id:[0-9]+}', \App\Application\Action\Client\Ajax\ClientDeleteAction::class)
            ->setName('client-delete-submit');
    })->add(UserAuthenticationMiddleware::class);

    // Client list page action
    $app->get('/clients/list', \App\Application\Action\Client\Page\ClientListPageAction::class)->setName(
        'client-list-page'
    )->add(UserAuthenticationMiddleware::class);

    // Note routes
    $app->group('/notes', function (RouteCollectorProxy $group) {
        $group->get('', \App\Application\Action\Note\Ajax\NoteFetchListAction::class)->setName('note-list');
        $group->get('/{note_id:[0-9]+}', \App\Application\Action\Note\Page\NoteReadPageAction::class)->setName(
            'note-read-page'
        );
        $group->post('', \App\Application\Action\Note\Ajax\NoteCreateAction::class)->setName(
            'note-create-submit'
        );
        $group->put('/{note_id:[0-9]+}', \App\Application\Action\Note\Ajax\NoteUpdateAction::class)
            ->setName('note-update-submit');
        $group->delete('/{note_id:[0-9]+}', \App\Application\Action\Note\Ajax\NoteDeleteAction::class)
            ->setName('note-delete-submit');
    })->add(UserAuthenticationMiddleware::class);

    // API routes
    $app->group('/api', function (RouteCollectorProxy $group) {
        // Options route for CORS preflight requests required, otherwise exception Method not allowed is thrown.
        $group->options('/{routes:.+}', function ($request, $response) {
            return $response;
        });

        // Client creation API call
        $group->post('/clients', \App\Application\Action\Client\Api\ApiClientCreateAction::class)
            ->setName('api-client-create-submit');
    })// Cross-Origin Resource Sharing (CORS) middleware. Allow another domain to access '/api' routes.
    // If an error occurs, the CORS middleware will not be executed and the exception caught and a response
    // sent without the appropriate access control header. I don't know how to execute a certain middleware
    // added to a route group only before the error middleware which is added last in the middleware.php file.
    ->add(\App\Application\Middleware\CorsMiddleware::class);

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
