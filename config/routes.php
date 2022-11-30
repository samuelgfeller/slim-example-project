<?php

use App\Application\Middleware\UserAuthenticationMiddleware;
use Odan\Session\Middleware\SessionMiddleware;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    // Home page
//    $app->redirect('/', 'hello', 301)->setName('home-page');
    $app->get('/hello[/{name}]', \App\Application\Actions\Hello\HelloAction::class)->setName('hello');
    $app->post('/hello', function ($request, $response) {
        // var_dump($request->getParsedBody(), $request->getQueryParams());
        return $response;
    })->setName('hello-test-post');
    $app->get('/', \App\Application\Actions\Hello\HelloAction::class)->setName('home-page')->add(
        UserAuthenticationMiddleware::class
    );
    $app->get('/test', \App\Application\Actions\Hello\PhpDevTestAction::class)->setName('test');


    $app->get('/login', \App\Application\Actions\Authentication\Page\LoginAction::class)->setName('login-page');
    $app->post('/login', \App\Application\Actions\Authentication\LoginSubmitAction::class)->setName('login-submit');
    $app->get('/logout', \App\Application\Actions\Authentication\LogoutAction::class)->setName('logout')->add(
        SessionMiddleware::class
    );

    // Authentication - email verification - token
    $app->get('/register-verification', \App\Application\Actions\Authentication\RegisterVerifyAction::class)->setName(
        'register-verification'
    );

    $app->get('/unlock-account', \App\Application\Actions\Authentication\AccountUnlockAction::class)->setName(
        'account-unlock-verification'
    );

    $app->post(// Url password-forgotten hardcoded in login-main.js
        '/password-forgotten',
        \App\Application\Actions\Authentication\PasswordForgottenEmailSubmitAction::class
    )->setName('password-forgotten-email-submit');
    // Set new password page when forgotten
    $app->get('/reset-password', \App\Application\Actions\Authentication\Page\PasswordResetAction::class)->setName(
        'password-reset-page'
    );
    // Submit new password
    $app->post('/reset-password', \App\Application\Actions\Authentication\PasswordResetSubmitAction::class)->setName(
        'password-reset-submit'
    );

    // Change password page when authenticated
    $app->get('/change-password', \App\Application\Actions\Authentication\Page\ChangePasswordAction::class)->setName(
        'change-password-page'
    )->add(UserAuthenticationMiddleware::class);
    // Submit new password when authenticated (post and not put as form submit)
    $app->put(
        '/change-password/{user_id:[0-9]+}',
        \App\Application\Actions\User\Ajax\ChangePasswordSubmitAction::class
    )->setName(
        'change-password-submit'
    )->add(UserAuthenticationMiddleware::class);

    $app->group('/users', function (RouteCollectorProxy $group) {
        // $group->options('', PreflightAction::class); // Allow preflight requests
        $group->get('/list', \App\Application\Actions\User\UserListPageAction::class)
            ->setName('user-list-page');
        $group->get('', \App\Application\Actions\User\Ajax\UserListAction::class)
            ->setName('user-list');

        $group->get(
            '/dropdown-options',
            \App\Application\Actions\User\Ajax\UserListDropdownOptionsAction::class
        )->setName('user-list-dropdown');

        $group->post('', \App\Application\Actions\User\Ajax\UserSubmitCreateAction::class)
            ->setName('user-create-submit');
        // Route name has to be in the format: "[table_name]-read-page" and argument "[table-name]-id" to link from user activity
        $group->get('/{user_id:[0-9]+}', \App\Application\Actions\User\UserReadPageAction::class)
            ->setName('user-read-page');
        $group->put('/{user_id:[0-9]+}', \App\Application\Actions\User\Ajax\UserSubmitUpdateAction::class)
            ->setName('user-update-submit');
        $group->delete('/{user_id:[0-9]+}', \App\Application\Actions\User\Ajax\UserSubmitDeleteAction::class)
            ->setName('user-delete-submit');
    })->add(UserAuthenticationMiddleware::class);

    $app->get('/profile', \App\Application\Actions\User\UserReadPageAction::class)
        ->setName('profile-page')->add(UserAuthenticationMiddleware::class);

    // Client routes; page actions may be like /clients and that's not an issue as API routes would have 'api' in the url anyway
    $app->group('/clients', function (RouteCollectorProxy $group) {
        // Route name has to be in the format: "[table_name]-read-page" and argument "[table-name]-id" to link from user activity
        $group->get('/{client_id:[0-9]+}', \App\Application\Actions\Client\ClientReadPageAction::class)
            ->setName('client-read-page');

        $group->get('', \App\Application\Actions\Client\Ajax\ClientListAction::class)->setName('client-list');
        // Client create and update form are rendered by the client and need to have the dropdown options
        $group->get(
            '/dropdown-options',
            \App\Application\Actions\Client\Ajax\ClientListDropdownOptionsAction::class
        )->setName('client-list-dropdown');
        /* For api response action:
         json_encode transforms object with public attributes to camelCase which matches Google recommendation
         https://stackoverflow.com/a/19287394/9013718 */
        $group->post('', \App\Application\Actions\Client\Ajax\ClientCreateAction::class)
            ->setName('client-create-submit');
        $group->put('/{client_id:[0-9]+}', \App\Application\Actions\Client\Ajax\ClientUpdateAction::class)
            ->setName('client-update-submit');
        $group->delete('/{client_id:[0-9]+}', \App\Application\Actions\Client\Ajax\ClientDeleteAction::class)
            ->setName('client-delete-submit');
    })->add(UserAuthenticationMiddleware::class);

    // Page actions routes outside /posts as they are needed by Ajax after page load
    // All clients with status whose status is not closed
    $app->get('/clients/list', \App\Application\Actions\Client\ClientListPageAction::class)->setName(
        'client-list-page'
    )->add(UserAuthenticationMiddleware::class);
    $app->get(
        '/clients-assigned-to-me',
        \App\Application\Actions\Client\ClientListAssignedToMePageAction::class
    )->setName(
        'client-list-assigned-to-me-page'
    )->add(UserAuthenticationMiddleware::class);

    // Note routes
    $app->group('/notes', function (RouteCollectorProxy $group) {
        $group->get('', \App\Application\Actions\Note\Ajax\NoteListAction::class)->setName('note-list');
        $group->get('/{note_id:[0-9]+}', \App\Application\Actions\Note\NoteReadAction::class)->setName(
            'note-read-page'
        );
        $group->post('', \App\Application\Actions\Note\Ajax\NoteCreateAction::class)->setName(
            'note-submit-creation'
        );
        $group->put('/{note_id:[0-9]+}', \App\Application\Actions\Note\Ajax\NoteUpdateAction::class)
            ->setName('note-submit-modification');
        $group->delete('/{note_id:[0-9]+}', \App\Application\Actions\Note\Ajax\NoteDeleteAction::class)
            ->setName('note-submit-delete');
    })->add(UserAuthenticationMiddleware::class);

//    $app->get( '/favicon.ico', function ($request, $response) {
//        $response->getBody()->write('https://samuel-gfeller.ch/wp-content/uploads/2020/08/cropped-favicon_small-32x32.png');
//
//        return $response;
//    });

    /**
     * Catch-all route to serve a 404 Not Found page if none of the routes match
     * NOTE: make sure this route is defined last
     * //     */
    $app->map(
        ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) use ($app) {
        throw new HttpNotFoundException(
            $request, 'Route "' . $request->getUri()->getHost() . $request->getUri()->getPath() . '" not found.
            <br>Basepath: "' . $app->getBasePath() . '"'
        );
    }
    );
};
