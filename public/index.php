<?php
// public/index.php

// 1) Autoload first
require __DIR__ . '/../vendor/autoload.php';

// 2) All imports belong here (namespace scope), before any executable code
use Bramus\Router\Router;
use Dotenv\Dotenv;

use App\Core\Database;
use App\Core\Csrf;
use App\Core\AuthMiddleware;

use App\Controllers\AuthController;
use App\Controllers\PlanController;
use App\Controllers\DashboardController;

// 3) Bootstrap env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Optional: safer session cookie params
session_set_cookie_params([
  'lifetime' => 0,
  'path' => '/',
  'domain' => '',
  'secure' => false, // set true in HTTPS
  'httponly' => true,
  'samesite' => 'Lax'
]);

// 4) Router
$router = new Router();

// ---- Basic test
$router->get('/', function () {
  echo "<h1>TradeSmart Base Works!</h1><p><a href='/dashboard'>Go to Dashboard</a></p>";
});

// ---- Auth
$auth = new AuthController();
$router->get('/register', function () use ($auth) { $auth->showRegister(); });
$router->post('/register', function () use ($auth) { $auth->register(); });
$router->get('/login',    function () use ($auth) { $auth->showLogin(); });
$router->post('/login',   function () use ($auth) { $auth->login(); });
$router->get('/logout',   function () use ($auth) { $auth->logout(); });

// ---- Dashboard
$dash = new DashboardController();
$router->get('/dashboard', function () use ($dash) { $dash->index(); });

// ---- Plans
$plan = new PlanController();

$router->mount('/plans', function () use ($router, $plan) {

  // list
  $router->get('/', function () use ($plan) { $plan->index(); });

  // create
  $router->get('/create', function () use ($plan) { $plan->create(); });
  $router->post('/create', function () use ($plan) { $plan->store(); });

  // show
  $router->get('/(\d+)', function ($id) use ($plan) { $plan->show($id); });

  // actions
  $router->get('/(\d+)/validate', function ($id) use ($plan) { $plan->validateForm($id); });
  $router->post('/(\d+)/validate', function ($id) use ($plan) { $plan->validateSubmit($id); });

  $router->get('/(\d+)/execute', function ($id) use ($plan) { $plan->executeForm($id); });
  $router->post('/(\d+)/execute', function ($id) use ($plan) { $plan->executeSubmit($id); });

  $router->get('/(\d+)/cancel', function ($id) use ($plan) { $plan->cancelForm($id); });
  $router->post('/(\d+)/cancel', function ($id) use ($plan) { $plan->cancelSubmit($id); });

  $router->get('/(\d+)/adjust', function ($id) use ($plan) { $plan->adjustForm($id); });
  $router->post('/(\d+)/adjust', function ($id) use ($plan) { $plan->adjustSubmit($id); });

  // journal
  $router->get('/(\d+)/journal', function ($id) use ($plan) { $plan->journalForm($id); });
  $router->post('/(\d+)/journal', function ($id) use ($plan) { $plan->journalSubmit($id); });

  // positions
  $router->get('/(\d+)/positions/create', function ($planId) use ($plan) { $plan->positionForm($planId); });
  $router->post('/(\d+)/positions/create', function ($planId) use ($plan) { $plan->positionStore($planId); });

  // exits
  $router->get('/positions/(\d+)/exits/create', function ($posId) use ($plan) { $plan->exitForm($posId); });
  $router->post('/positions/(\d+)/exits/create', function ($posId) use ($plan) { $plan->exitStore($posId); });
});

// 5) Run
$router->run();
