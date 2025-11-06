<?php
use App\Core\Csrf;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Register - TradeSmart</title>
</head>
<body>
  <h2>Register</h2>

  <?php if (!empty($error)): ?>
    <div style="color:crimson; margin-bottom:1rem;"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form method="POST" action="/register" autocomplete="off">
    <?php Csrf::inputField(); ?>

    <label>
      Name<br>
      <input type="text" name="name" required>
    </label>
    <br><br>

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

    <button type="submit">Register</button>
  </form>

  <p>Already have an account? <a href="/login">Login</a></p>
</body>
</html>
