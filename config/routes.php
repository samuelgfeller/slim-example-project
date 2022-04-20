<?php

use App\Application\Actions\PreflightAction;
use App\Application\Middleware\UserAuthenticationMiddleware;
use Odan\Session\Middleware\SessionMiddleware;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    // Home page
//    $app->redirect('/', 'hello', 301)->setName('home-page');
    $app->get('/hello[/{name}]', \App\Application\Actions\Hello\HelloAction::class)->setName('hello');
    $app->get('/', \App\Application\Actions\Hello\HelloAction::class)->setName('home-page');

    // Authentication - pages and Ajax submit
    $app->get('/register', \App\Application\Actions\Authentication\Page\RegisterAction::class)->setName(
        'register-page'
    );
    $app->post('/register', \App\Application\Actions\Authentication\RegisterSubmitAction::class)->setName(
        'register-submit'
    );

    $app->get('/login', \App\Application\Actions\Authentication\Page\LoginAction::class)->setName('login-page');
    $app->post('/login', \App\Application\Actions\Authentication\LoginSubmitAction::class)->setName('login-submit');
    $app->get('/logout', \App\Application\Actions\Authentication\LogoutAction::class)->setName('logout')->add(
        SessionMiddleware::class
    );

    $app->get('/profile', \App\Application\Actions\User\Page\UserViewProfileAction::class)->setName(
        'profile-page'
    )->add(
        UserAuthenticationMiddleware::class
    );

    // Authentication - email verification - token
    $app->get('/register-verification', \App\Application\Actions\Authentication\RegisterVerifyAction::class)->setName(
        'register-verification'
    );
    $app->get(
        '/register-check-email',
        \App\Application\Actions\Authentication\Page\RegisterCheckEmailAction::class
    )->setName('register-check-email-page');

    $app->get('/unlock-account', \App\Application\Actions\Authentication\AccountUnlockAction::class)->setName(
        'account-unlock-verification'
    );

    $app->get(
        '/password-forgotten',
        \App\Application\Actions\Authentication\Page\PasswordForgottenAction::class
    )->setName(
        'password-forgotten-page'
    );
    $app->post(
        '/password-forgotten',
        \App\Application\Actions\Authentication\PasswordForgottenEmailSubmitAction::class
    )->setName(
        'password-forgotten-email-submit'
    );
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
    $app->post('/change-password', \App\Application\Actions\Authentication\ChangePasswordSubmitAction::class)->setName(
        'change-password-submit'
    )->add(UserAuthenticationMiddleware::class);


    $app->group('/users', function (RouteCollectorProxy $group) {
        $group->options('', PreflightAction::class); // Allow preflight requests
        $group->get('', \App\Application\Actions\User\UserListAction::class)->setName('user-list');

        $group->options('/{user_id:[0-9]+}', PreflightAction::class); // Allow preflight requests
        $group->get('/{user_id:[0-9]+}', \App\Application\Actions\User\Page\UserViewProfileAction::class);
        $group->put('/{user_id:[0-9]+}', \App\Application\Actions\User\UserSubmitUpdateAction::class)->setName(
            'user-update-submit'
        );
        $group->delete('/{user_id:[0-9]+}', \App\Application\Actions\User\UserDeleteAction::class)->setName(
            'user-delete-submit'
        );
    })->add(UserAuthenticationMiddleware::class);

    // Post CRUD routes; I try to keep this as REST as possible so the page actions have urls other than /posts
    $app->group('/posts', function (RouteCollectorProxy $group) {
        // Post requests where user DOESN'T need to be authenticated
        $group->get('', \App\Application\Actions\Post\Ajax\PostListAction::class)->setName('post-list');

        $group->get('/{post_id:[0-9]+}', \App\Application\Actions\Post\Ajax\PostReadAction::class)->setName(
            'post-read'
        );
        // Post requests where user DOES need to be authenticated
        $group->post('', \App\Application\Actions\Post\Ajax\PostCreateAction::class)->setName(
            'post-submit-create'
        )->add(
            UserAuthenticationMiddleware::class
        );
        $group->put('/{post_id:[0-9]+}', \App\Application\Actions\Post\Ajax\PostUpdateAction::class)->add(
            UserAuthenticationMiddleware::class
        )->setName('post-submit-update');
        $group->delete('/{post_id:[0-9]+}', \App\Application\Actions\Post\Ajax\PostDeleteAction::class)->add(
            UserAuthenticationMiddleware::class
        )->setName('post-submit-delete');
    });
    // Page actions routes outside /posts as they are needed by Ajax after page load
    $app->get('/all-posts', \App\Application\Actions\Post\PostListAllPageAction::class)->setName('post-list-all-page');
    $app->get('/own-posts', \App\Application\Actions\Post\PostListOwnPageAction::class)->setName(
        'post-list-own-page'
    )->add(UserAuthenticationMiddleware::class);


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
            <br>Basepath: "'. $app->getBasePath() .'"'
        );
    }
    );
};
