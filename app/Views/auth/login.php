<?php use App\Core\Csrf; ?>
<?php
$old = $old ?? [];
$errors = $errors ?? [];
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>
<h1 class="text-2xl font-bold mb-4">Login</h1>

<form method="POST" class="space-y-4 max-w-md">
  <?php Csrf::inputField(); ?>

  <?php if (!empty($errors['auth'])): ?>
    <div class="bg-red-50 border border-red-200 text-red-800 rounded px-4 py-2 text-sm">
      <?= $h($errors['auth']) ?>
    </div>
  <?php endif; ?>

  <div>
    <label class="block text-sm">Email</label>
    <input type="email" name="email" class="mt-1 w-full border rounded px-3 py-2"
           value="<?= $h($old['email'] ?? '') ?>" required>
    <?php if (!empty($errors['email'])): ?>
      <div class="text-xs text-red-600 mt-1"><?= $h($errors['email']) ?></div>
    <?php endif; ?>
  </div>

  <div>
    <label class="block text-sm">Password</label>
    <input type="password" name="password" class="mt-1 w-full border rounded px-3 py-2" required>
    <?php if (!empty($errors['password'])): ?>
      <div class="text-xs text-red-600 mt-1"><?= $h($errors['password']) ?></div>
    <?php endif; ?>
  </div>

  <div class="flex items-center gap-3">
    <button class="px-4 py-2 bg-blue-600 text-white rounded">Login</button>
    <a href="/register" class="text-sm text-gray-600 underline">Create an account</a>
  </div>
</form>
