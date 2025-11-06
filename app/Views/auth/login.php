<?php
use App\Core\Csrf;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Login - TradeSmart</title>
</head>
<body>
  <h2>Login</h2>

  <?php if (!empty($error)): ?>
    <div style="color:crimson; margin-bottom:1rem;"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form method="POST" action="/login" autocomplete="off">
    <?php Csrf::inputField(); ?>

    <label>
      Email<br>
      <input type="email" name="email" required>
    </label>
    <br><br>

    <label>
      Password<br>
      <input type="password" name="password" required>
    </label>
    <br><br>

    <button type="submit">Login</button>
  </form>

  <p>No account? <a href="/register">Register</a></p>
</body>
</html>
