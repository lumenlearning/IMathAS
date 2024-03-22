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

$router->get('/api', function () use ($router) {
    return $router->app->version();
});

$router->post('api/v1/token', [
    'uses' => 'AuthController@getToken'
]);

$router->group(
    ['prefix' => 'api/v1', 'middleware' => 'jwt.auth'],
    function() use ($router) {

        $router->post('/question', [
            'uses' => 'QuestionController@getQuestion'
        ]);

        $router->post('/questions', [
            'uses' => 'QuestionController@getAllQuestions'
        ]);

        $router->post('/question/score', [
            'uses' => 'QuestionController@scoreQuestion'
        ]);

        $router->post('/questions/score', [
            'uses' => 'QuestionController@scoreAllQuestions'
        ]);

        $router->post('/questions/mga_imports', [
            'uses' => 'QuestionImportController@importMgaQuestions'
        ]);

        $router->get('/enrollments', [
            'uses' => 'EnrollmentController@getAllEnrollments'
        ]);

        $router->get('/enrollments/{id}', [
            'uses' => 'EnrollmentController@getEnrollmentById'
        ]);

        $router->put('/enrollments/{id}', [
            'uses' => 'EnrollmentController@updateEnrollmentById'
        ]);
    }
);
