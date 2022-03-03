<?php

use App\Application\Actions\PreflightAction;
use App\Application\Middleware\UserAuthenticationMiddleware;
use Slim\App;
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

    $app->get('/profile', \App\Application\Actions\User\UserViewProfileAction::class)->setName('profile')
        ->add(UserAuthenticationMiddleware::class);

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

    // Post CRUD routes; I try to keep this as REST as possible so the page actions have urls other than /posts
    $app->group(
        '/posts',
        function (RouteCollectorProxy $group) {
            // Post requests where user DOESN'T need to be authenticated
            $group->get('', \App\Application\Actions\Post\Ajax\PostListAction::class)->setName('post-list-all');

            $group->get('/{post_id:[0-9]+}', \App\Application\Actions\Post\Ajax\PostReadAction::class)->setName(
                'post-read'
            );

            // Post requests where user DOES need to be authenticated
            $group->post('', \App\Application\Actions\Post\Ajax\PostCreateAction::class)->setName(
                'post-submit-create')->add(
                UserAuthenticationMiddleware::class
            );
            $group->put('/{post_id:[0-9]+}', \App\Application\Actions\Post\Ajax\PostUpdateAction::class)->add(
                UserAuthenticationMiddleware::class
            );
            $group->delete('/{post_id:[0-9]+}', \App\Application\Actions\Post\Ajax\PostDeleteAction::class)->add(
                UserAuthenticationMiddleware::class
            );
        }
    );
    // Page actions routes outside /posts as they are needed by Ajax after page load
    $app->get('/all-posts', \App\Application\Actions\Post\PostListAllPageAction::class)->setName('post-list-all-page');
    $app->get('/own-posts', \App\Application\Actions\Post\PostListOwnPageAction::class)->setName(
        'post-list-own-page'
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
     * //     */
//    $app->map(
//        ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'],
//        '/{routes:.+}',
//        function ($request, $response) {
//            throw new HttpNotFoundException(
//                $request, 'Route "' .
//                        $request->getUri()->getHost() . $request->getUri()->getPath() . '" not found.'
//            );
//        }
//    );
};
