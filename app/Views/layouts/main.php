<?php
// session might already be started; start if not
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}
$user = $_SESSION['user'] ?? null;
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($title ?? 'TradeSmart') ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Tailwind CDN for dev; swap to built CSS later if you want -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-900 min-h-screen flex flex-col">

  <!-- Top Nav -->
  <header class="bg-white border-b">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
      <a href="/dashboard" class="text-xl font-bold">TradeSmart</a>

      <?php
$current = strtok($_SERVER['REQUEST_URI'], '?'); // current path (no query)
$activeClass = 'bg-gray-900 text-white hover:bg-gray-900';
$linkClass = 'px-3 py-1 rounded hover:bg-gray-100 transition-colors';
?>
<nav class="flex items-center gap-2">
  <a href="/dashboard" 
     class="<?= str_starts_with($current, '/dashboard') ? "$linkClass $activeClass" : $linkClass ?>">
     Dashboard
  </a>

  <a href="/plans"
     class="<?= ($current === '/plans' || str_starts_with($current, '/plans/')) ? "$linkClass $activeClass" : $linkClass ?>">
     Plans
  </a>

  <a href="/plans/create"
     class="<?= str_starts_with($current, '/plans/create') ? "$linkClass $activeClass" : $linkClass ?>">
     New Plan
  </a>

  <a href="/plans/export.csv"
     class="<?= str_ends_with($current, '/plans/export.csv') ? "$linkClass $activeClass" : $linkClass ?>">
     Export CSV
  </a>
</nav>


      <div class="flex items-center gap-3">
        <?php if ($user): ?>
          <span class="text-sm text-gray-600">Hi, <?= htmlspecialchars($user['name'] ?? $user['email']) ?></span>
          <a class="px-3 py-1 rounded bg-gray-900 text-white" href="/logout">Logout</a>
        <?php else: ?>
          <a class="px-3 py-1 rounded bg-blue-600 text-white" href="/login">Login</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <!-- Page content -->
  <main class="flex-1">

    <div class="max-w-6xl mx-auto px-4 py-6">
      <?php

      use App\Core\Flash;

      $flashes = Flash::read(); // returns [] if none
      if (!empty($flashes)):
      ?>
        <div class="mb-4 space-y-2">
          <?php foreach ($flashes as $f):
            $t = $f['type'] ?? 'info';
            $base = 'rounded border px-4 py-3 text-sm';
            $styles = [
              'success' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
              'error'   => 'bg-red-50 border-red-200 text-red-800',
              'warning' => 'bg-amber-50 border-amber-200 text-amber-800',
              'info'    => 'bg-sky-50 border-sky-200 text-sky-800',
            ];
            $cls = ($styles[$t] ?? $styles['info']) . ' ' . $base;
          ?>
            <div class="<?= $cls ?>">
              <?= htmlspecialchars($f['message'], ENT_QUOTES, 'UTF-8') ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?= $content /* content from the view file */ ?>
    </div>
  </main>

  <footer class="bg-white border-t">
    <div class="max-w-6xl mx-auto px-4 py-4 text-xs text-gray-500">
      &copy; <?= date('Y') ?> TradeSmart
    </div>
  </footer>
</body>

</html>