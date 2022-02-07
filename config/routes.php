<?php

use App\Application\Actions\PreflightAction;
use App\Application\Middleware\UserAuthenticationMiddleware;
use Slim\App;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->redirect('/', 'hello', 301)->setName('home');

    $app->get('/login', \App\Application\Actions\Authentication\LoginAction::class)->setName('login-page');
    $app->post('/login', \App\Application\Actions\Authentication\LoginSubmitAction::class)->setName('login-submit');

    $app->get('/logout', \App\Application\Actions\Authentication\LogoutAction::class)->setName('logout');

    $app->get('/register', \App\Application\Actions\Authentication\RegisterAction::class)->setName('register-page');
    $app->post('/register', \App\Application\Actions\Authentication\RegisterSubmitAction::class)->setName(
        'register-submit'
    );
    $app->get('/register-verification', \App\Application\Actions\Authentication\RegisterVerifyAction::class)->setName(
        'register-verification'
    );
    $app->get(
        '/register-check-email',
        \App\Application\Actions\Authentication\RegisterCheckEmailAction::class
    )->setName(
        'register-check-email-page'
    );

    $app->get('/profile', \App\Application\Actions\User\UserViewProfileAction::class)->setName('profile');

    $app->group(
        '/users',
        function (RouteCollectorProxy $group) {
            $group->options('', PreflightAction::class); // Allow preflight requests
            $group->get('', \App\Application\Actions\User\UserListAction::class)->setName('user-list');

            $group->options('/{user_id:[0-9]+}', PreflightAction::class); // Allow preflight requests
            $group->get('/{user_id:[0-9]+}', \App\Application\Actions\User\UserViewProfileAction::class);
            $group->put('/{user_id:[0-9]+}', \App\Application\Actions\User\UserSubmitUpdateAction::class)->setName(
                'user-update-submit'
            );
            $group->delete('/{user_id:[0-9]+}', \App\Application\Actions\User\UserDeleteAction::class);
        }
    )->add(UserAuthenticationMiddleware::class);



    $app->group(
        '/posts',
        function (RouteCollectorProxy $group) {
            // Post requests where user DOESN'T need to be authenticated
            $group->get('', \App\Application\Actions\Post\PostListAction::class)->setName('post-list-all');

            $group->get('/{post_id:[0-9]+}', \App\Application\Actions\Post\PostReadAction::class)->setName(
                'post-read'
            );

            // Post requests where user DOES need to be authenticated
            $group->post('', \App\Application\Actions\Post\PostCreateAction::class)->add(UserAuthenticationMiddleware::class);
            $group->put('/{post_id:[0-9]+}', \App\Application\Actions\Post\PostUpdateAction::class)->add(
                UserAuthenticationMiddleware::class
            );
            $group->delete('/{post_id:[0-9]+}', \App\Application\Actions\Post\PostDeleteAction::class)->add(
                UserAuthenticationMiddleware::class
            );
        }
    );

    // option 1 /posts?user=xxx and then $request->getQueryParams('user'); but that would mean that the user has to know its id
    // option 2 /own-posts and get user id from session
    $app->get('/own-posts', \App\Application\Actions\Post\PostListOwnAction::class)->setName(
        'post-list-own'
    )->add(UserAuthenticationMiddleware::class);

    $app->get('/hello[/{name}]', \App\Application\Actions\Hello\HelloAction::class)->setName('hello');

//    $app->get( '/favicon.ico', function ($request, $response) {
//        $response->getBody()->write('https://samuel-gfeller.ch/wp-content/uploads/2020/08/cropped-favicon_small-32x32.png');
//
//        return $response;
//    });

    /**
     * Catch-all route to serve a 404 Not Found page if none of the routes match
     * NOTE: make sure this route is defined last
     */ //    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
//        throw new HttpNotFoundException($request, 'Route "'.
//                                                $request->getUri()->getHost().$request->getUri()->getPath().'" not found.');
//    });
};
