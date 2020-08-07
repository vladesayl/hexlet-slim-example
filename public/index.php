<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
// Контейнеры в этом курсе не рассматриваются (это тема связанная с самим ООП), но если вам интересно, то посмотрите DI Container
use DI\Container;
use Slim\Views\Twig;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Slim\Middleware\MethodOverrideMiddleware;
//namespace App;


require __DIR__ . '/../vendor/autoload.php';

session_start();
include 'validator.php';

// $validator = validate();
$app = AppFactory::create();

$container = new Container();
AppFactory::setContainer($container);

$fileName = 'users.txt';
$container->set('renderer', function () {
    // Параметром передается базовая директория в которой будут храниться шаблоны
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$container->set('view', function() {
    return Twig::create('path/to/templates', ['cache' => 'path/to/cache']);
});
$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});


$app = AppFactory::createFromContainer($container);
// $app->add(TwigMiddleware::createFromContainer($app));
$app->addErrorMiddleware(true, true, true);
$router = $app->getRouteCollector()->getRouteParser();
$app->add(MethodOverrideMiddleware::class);

$app->get('/', function (Request $request, Response $response, $args) use ($router) {

    $response->getBody()->write("Hello world!");
    return $response;
  
})->setName('hello');


// $app->get('/users/{id}', function ($request, $response, $args) use ($filename){
    
//     $params = ['id' => $args['id'], 'nik' => 'nik-' . $args['id'], 'file' => $filename];
//     // Указанный путь считается относительно базовой директории для шаблонов, заданной на этапе конфигурации
//     // $this доступен внутри анонимной функции благодаря https://php.net/manual/ru/closure.bindto.php
//     return $this->get('renderer')->render($response, 'users/show.phtml', $params);
// });


$app->get('/users', function ($request, $response, $args) use ($users) {
    $term = $request->getQueryParam('term');
    
        foreach ($users as $user) {
            if (strpos($user, $term) === 0 || strpos($user, $term) > 0) {
                $findUsers[] = $user;
            }
        }
    $params = ['users' => $findUsers];
    return $this->get('renderer')->render($response, 'users/index.phtml', $params);
});

$app->post('/users', function ($request, $response) use ($router){
  
    $user = $request->getParsedBodyParam('user');
    $id = mt_rand(1, 1000);
    $users = json_decode($request->getCookieParam('users', json_encode([])), true);
    print_r($_COOKIE);
    $errors = validate($user);

        
    if (count($errors) === 0) {
        
        $users[$id] = ['nickname' => $user['nickname'], 'email' => $user['email']];
        var_dump($id);
        $encodedUsers = json_encode($users);

        
        $this->get('flash')->addMessage('success', 'User has been created');
        $url = $router->urlFor('users/new');
        return $response->withHeader('Set-Cookie', "users={$encodedUsers}")
        ->withRedirect($url);

    }   
  
    $params = [
        'user' => $user,
        'users' => $users,
        'errors' => $errors,
        'id' => $id
    ];

    $response = $response->withStatus(422);
    return $this->get('renderer')->render($response, "users/new.phtml", $params);
})->setName('users');

$app->delete('/users', function ($request, $response, array $args) use ($router){
    // $repo = new App\SchoolRepository();
  
    $user = array();

   $encodedUsers = json_encode($user);
   $url = $router->urlFor('users/new');
  return $response->withHeader('Set-Cookie', "users={$encodedUsers}")
        ->withRedirect($url);
});

$app->get('/users/new', function ($request, $response) {
    $params = [
        'user' => ['nickname' => '', 'email' => '']
    ];
    $messages = $this->get('flash')->getMessages();
    print_r($messages['success'][0]);
    print_r($_COOKIE);
    return $this->get('renderer')->render($response, "users/new.phtml", $params);
})->setName('users/new');


$app->post('/users/aut', function ($request, $response) {
    // Информация о добавляемом товаре
    $item = $request->getParsedBodyParam('user');

    // Добавление нового товара
    $_SESSION['user'][] = $item['email'];

    return $response->withRedirect('/');
});


// $app->patch('/users/{id}', function ($request, $response, array $args)  use ($fileName) {
//     // $repo = new App\SchoolRepository();
//     $id = $args['id'];
//     $getFile = file_get_contents($fileName);
//     $users = (explode("\n", $getFile));

//     $data = $request->getParsedBodyParam('user');

//   $errors = validate($data);

//     if (count($errors) === 0) {
//         // Ручное копирование данных из формы в нашу сущность
//         $user['name'] = $data['name'];

//         $this->get('flash')->addMessage('success', 'User has been updated');
//         $repo->save($user);
//         $url = $router->urlFor('editUser', ['id' => $user['id']]);
//         return $response->withRedirect($url);
//     }

//     $params = [
//         'userData' => $data,
//         'user' => $user,
//         'errors' => $errors
//     ];

//     $response = $response->withStatus(422);
//     return $this->get('renderer')->render($response, 'users/edit.phtml', $params);
// });

$app->run();









// $app->post('/users', function ($request, $response) use ($fileName, $router){
  
//     $user = $request->getParsedBodyParam('user');
//     $id = mt_rand(1, 1000);

//     $getFile = file_get_contents($fileName);
//     $file = file($fileName);
//     print_r($file);
//     $explodeGetFile = (explode("\n", $getFile));
    
//     $lastUser = $explodeGetFile[count($explodeGetFile) - 1];
//     foreach (json_decode($lastUser) as $key => $value) {
//         $array[] = $value;
        
//     }
    
//     $newId = $array[2] + 1;
//     $user['id'] = "$newId";
  
//     //
//     $errors = validate($user);

        
//     if (count($errors) === 0) {
        
//         if(filesize($fileName) == 0) {
//             file_put_contents ( $fileName , json_encode($user), FILE_APPEND);
//         } else {
//             file_put_contents ( $fileName , PHP_EOL . json_encode($user), FILE_APPEND);   
//         }
//         $this->get('flash')->addMessage('success', 'User has been created');
//         $url = $router->urlFor('users/new');
//         return $response->withRedirect($url);
//     }   
  
//     $params = [
//         'user' => $user,
//         'array' => $array,
//         'errors' => $errors
//     ];

//     $response = $response->withStatus(422);
//     return $this->get('renderer')->render($response, "users/new.phtml", $params);
// })->setName('users');
