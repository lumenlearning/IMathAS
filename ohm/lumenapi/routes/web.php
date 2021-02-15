<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

<<<<<<< Updated upstream
use App\Repositories\Interfaces\AssessmentRepositoryInterface;

=======
>>>>>>> Stashed changes
$router->get('/api', function () use ($router) {
    return $router->app->version();
});

$router->post('api/v1/token', [
    'uses' => 'AuthController@getToken'
]);

$router->group(
    ['prefix' => 'api/v1', 'middleware' => 'jwt.auth'],
    function() use ($router) {

        $router->get('/questions/{questionId}', [
            'uses' => 'QuestionController@getQuestions'
        ]);

        $router->post('/question/{questionId}', [
            'uses' => 'QuestionController@getQuestion'
        ]);
<<<<<<< Updated upstream
=======

        $router->post('/question/{questionId}/score', [
            'uses' => 'QuestionController@scoreQuestion'
        ]);
>>>>>>> Stashed changes
    }
);