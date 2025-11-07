<?php
// routes/web.php

use Bramus\Router\Router;

use App\Controllers\AuthController;
use App\Controllers\PlanController;
use App\Controllers\DashboardController;
use App\Controllers\ExportController;

$router = new Router();

/*
|--------------------------------------------------------------------------
| Basic
|--------------------------------------------------------------------------
*/
$router->get('/', function () {
    echo "<h1>TradeSmart Base Works!</h1><p><a href='/dashboard'>Go to Dashboard</a></p>";
});

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/
$auth = new AuthController();
$router->get('/register', fn() => $auth->showRegister());
$router->post('/register', fn() => $auth->register());
$router->get('/login', fn() => $auth->showLogin());
$router->post('/login', fn() => $auth->login());
$router->get('/logout', fn() => $auth->logout());

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
$dash = new DashboardController();
$router->get('/dashboard', fn() => $dash->index());

/*
|--------------------------------------------------------------------------
| Plans
|--------------------------------------------------------------------------
*/
$plan = new PlanController();

$router->mount('/plans', function () use ($router, $plan) {

    $router->get('/', fn() => $plan->index());
    $router->get('/create', fn() => $plan->create());
    $router->post('/store', fn() => $plan->store());
    $router->get('/(\d+)', fn($id) => $plan->show($id));

    // actions
    $router->get('/(\d+)/validate', fn($id) => $plan->validateForm($id));
    $router->post('/(\d+)/validate', fn($id) => $plan->validateSubmit($id));
    $router->get('/(\d+)/execute', fn($id) => $plan->executeForm($id));
    $router->post('/(\d+)/execute', fn($id) => $plan->executeSubmit($id));
    $router->get('/(\d+)/cancel', fn($id) => $plan->cancelForm($id));
    $router->post('/(\d+)/cancel', fn($id) => $plan->cancelSubmit($id));
    $router->get('/(\d+)/adjust', fn($id) => $plan->adjustForm($id));
    $router->post('/(\d+)/adjust', fn($id) => $plan->adjustSubmit($id));

    // journal
    $router->get('/(\d+)/journal', fn($id) => $plan->journalForm($id));
    $router->post('/(\d+)/journal', fn($id) => $plan->journalSubmit($id));

    // positions
    $router->get('/(\d+)/positions/create', fn($planId) => $plan->positionForm($planId));
    $router->post('/(\d+)/positions/create', fn($planId) => $plan->positionStore($planId));

    // exits
    $router->get('/positions/(\d+)/exits/create', fn($posId) => $plan->exitForm($posId));
    $router->post('/positions/(\d+)/exits/create', fn($posId) => $plan->exitStore($posId));
});

/*
|--------------------------------------------------------------------------
| Exports
|--------------------------------------------------------------------------
*/
$export = new ExportController();
$router->get('/plans/export\.csv', fn() => $export->plansCsv());
$router->get('/plans/(\d+)/events\.csv', fn($id) => $export->planEventsCsv($id));
$router->get('/plans/(\d+)/positions\.csv', fn($id) => $export->planPositionsCsv($id));
$router->get('/plans/(\d+)/exits\.csv', fn($id) => $export->planExitsCsv($id));

return $router;
