<?php

use App\Application\Middleware\CorsMiddleware;
use App\Application\Middleware\UserAuthenticationMiddleware;
use Odan\Session\Middleware\SessionMiddleware;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    // Home page
    $app->redirect('/hello[/{name}]', '/', 301)->setName('hello-page');
    $app->get('/', \App\Application\Actions\Dashboard\DashboardPageAction::class)->setName('home-page')->add(
        UserAuthenticationMiddleware::class
    );

    $app->group('/api', function (RouteCollectorProxy $group) {
        // Client creation API call
        $group->post('/clients', \App\Application\Actions\Client\Ajax\ApiClientCreateAction::class)
            ->setName('api-client-create-submit');
        $group->options('/clients', function ($request, $response) {
            return $response;
        });
    })->add(CorsMiddleware::class);

    // Testing
    $app->get('/test', \App\Application\Actions\Dashboard\PhpDevTestAction::class)
        ->setName('test-get-request');
    $app->post('/test-post', function ($request, $response) {
        // var_dump($request->getParsedBody(), $request->getQueryParams());
        return $response;
    })->setName('test-post-request');

    $app->put('/dashboard-toggle-panel', \App\Application\Actions\Dashboard\DashboardTogglePanelAction::class)
        ->setName('dashboard-toggle-panel');

    $app->get('/login', \App\Application\Actions\Authentication\Page\LoginPageAction::class)->setName('login-page');
    $app->post('/login', \App\Application\Actions\Authentication\Submit\LoginSubmitAction::class)->setName(
        'login-submit'
    );
    $app->get('/logout', \App\Application\Actions\Authentication\Page\LogoutPageAction::class)->setName('logout')->add(
        SessionMiddleware::class
    );

    // Authentication - email verification - token
    $app->get(
        '/register-verification',
        \App\Application\Actions\Authentication\Submit\RegisterVerifySubmitAction::class
    )->setName(
        'register-verification'
    );

    $app->get('/unlock-account', \App\Application\Actions\Authentication\Submit\AccountUnlockAction::class)->setName(
        'account-unlock-verification'
    );

    $app->post(// Url password-forgotten hardcoded in login-main.js
        '/password-forgotten',
        \App\Application\Actions\Authentication\Submit\PasswordForgottenEmailSubmitAction::class
    )->setName('password-forgotten-email-submit');
    // Set new password page when forgotten
    $app->get('/reset-password', \App\Application\Actions\Authentication\Page\PasswordResetPageAction::class)->setName(
        'password-reset-page'
    );
    // Submit new password (reset-password hardcoded in login-main.js)
    $app->post(
        '/reset-password',
        \App\Application\Actions\Authentication\Submit\PasswordResetSubmitAction::class
    )->setName(
        'password-reset-submit'
    );

    // Submit new password when authenticated (post and not put as form submit)
    $app->put(
        '/change-password/{user_id:[0-9]+}',
        \App\Application\Actions\User\Ajax\ChangePasswordSubmitAction::class
    )->setName('change-password-submit')->add(UserAuthenticationMiddleware::class);

    $app->group('/users', function (RouteCollectorProxy $group) {
        // $group->options('', PreflightAction::class); // Allow preflight requests
        $group->get('/list', \App\Application\Actions\User\Page\UserListPageAction::class)
            ->setName('user-list-page');
        $group->get('', \App\Application\Actions\User\Ajax\UserListAction::class)
            ->setName('user-list');

        $group // User list dropdown options
        ->get('/dropdown-options', \App\Application\Actions\User\Ajax\UserListDropdownOptionsAction::class)
            ->setName('user-list-dropdown');

        $group->get('/activity', \App\Application\Actions\User\Ajax\ListUserActivityAction::class)
            ->setName('user-get-activity');

        $group->post('', \App\Application\Actions\User\Ajax\UserSubmitCreateAction::class)
            ->setName('user-create-submit');
        // Route name has to be in the format: "[table_name]-read-page" and argument "[table-name]-id" to link from user activity
        $group->get('/{user_id:[0-9]+}', \App\Application\Actions\User\Page\UserReadPageAction::class)
            ->setName('user-read-page');
        $group->put('/{user_id:[0-9]+}', \App\Application\Actions\User\Ajax\UserSubmitUpdateAction::class)
            ->setName('user-update-submit');
        $group->delete('/{user_id:[0-9]+}', \App\Application\Actions\User\Ajax\UserSubmitDeleteAction::class)
            ->setName('user-delete-submit');
    })->add(UserAuthenticationMiddleware::class);

    $app->get('/profile', \App\Application\Actions\User\Page\UserReadPageAction::class)
        ->setName('profile-page')->add(UserAuthenticationMiddleware::class);

    // Client routes; page actions may be like /clients and that's not an issue as API routes would have 'api' in the url anyway
    $app->group('/clients', function (RouteCollectorProxy $group) {
        // Route name has to be in the format: "[table_name]-read-page" and argument "[table-name]-id" to link from user activity
        $group->get('/{client_id:[0-9]+}', \App\Application\Actions\Client\Page\ClientReadPageAction::class)
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
    $app->get('/clients/list', \App\Application\Actions\Client\Page\ClientListPageAction::class)->setName(
        'client-list-page'
    )->add(UserAuthenticationMiddleware::class);

    // Note routes
    $app->group('/notes', function (RouteCollectorProxy $group) {
        $group->get('', \App\Application\Actions\Note\Ajax\NoteListFetchAction::class)->setName('note-list');
        $group->get('/{note_id:[0-9]+}', \App\Application\Actions\Note\Page\NoteReadPageAction::class)->setName(
            'note-read-page'
        );
        $group->post('', \App\Application\Actions\Note\Ajax\NoteCreateSubmitAction::class)->setName(
            'note-submit-creation'
        );
        $group->put('/{note_id:[0-9]+}', \App\Application\Actions\Note\Ajax\NoteUpdateSubmitAction::class)
            ->setName('note-submit-modification');
        $group->delete('/{note_id:[0-9]+}', \App\Application\Actions\Note\Ajax\NoteDeleteSubmitAction::class)
            ->setName('note-submit-delete');
    })->add(UserAuthenticationMiddleware::class);

//    $app->get( '/favicon.ico', function ($request, $response) {
//        $response->getBody()->write('https://samuel-gfeller.ch/wp-content/uploads/2020/08/cropped-favicon_small-32x32.png');
//
//        return $response;
//    });

    /**
     * Catch-all route to serve a 404 Not Found page if none of the routes match
     * NOTE: make sure this route is defined last.
     * //     */
    $app->map(
        ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'],
        '/{routes:.+}',
        function ($request, $response) use ($app) {
            throw new HttpNotFoundException(
                $request,
                'Route "' . $request->getUri()->getHost() . $request->getUri()->getPath() . '" not found.
            <br>Basepath: "' . $app->getBasePath() . '"'
            );
        }
    );
};
