<?php use App\Core\Csrf; ?>
<?php
$old = $old ?? [];
$errors = $errors ?? [];
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>
<h1 class="text-2xl font-bold mb-4">Register</h1>

<form method="POST" class="space-y-4 max-w-md">
  <?php Csrf::inputField(); ?>

  <div>
    <label class="block text-sm">Name</label>
    <input name="name" class="mt-1 w-full border rounded px-3 py-2"
           value="<?= $h($old['name'] ?? '') ?>" required>
    <?php if (!empty($errors['name'])): ?>
      <div class="text-xs text-red-600 mt-1"><?= $h($errors['name']) ?></div>
    <?php endif; ?>
  </div>

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

  <div>
    <label class="block text-sm">Confirm Password</label>
    <input type="password" name="password_confirmation" class="mt-1 w-full border rounded px-3 py-2" required>
    <?php if (!empty($errors['password_confirmation'])): ?>
      <div class="text-xs text-red-600 mt-1"><?= $h($errors['password_confirmation']) ?></div>
    <?php endif; ?>
  </div>

  <div class="flex items-center gap-3">
    <button class="px-4 py-2 bg-emerald-600 text-white rounded">Create Account</button>
    <a href="/login" class="text-sm text-gray-600 underline">Already have an account?</a>
  </div>
</form>
