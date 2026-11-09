<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Sign In - Softlixx Email Intelligence</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: #0b0f19;
      color: #f1f5f9;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      margin: 0;
    }
    .login-card {
      background: rgba(18, 24, 38, 0.95);
      border: 1px solid rgba(51, 65, 85, 0.5);
      border-radius: 1.25rem;
      backdrop-filter: blur(16px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
      width: 100%;
      max-width: 420px;
    }
    .form-control-dark {
      background-color: #090d16;
      border: 1px solid #334155;
      color: #ffffff;
      border-radius: 0.75rem;
      padding: 0.75rem 1rem;
      font-size: 0.875rem;
    }
    .form-control-dark:focus {
      background-color: #090d16;
      border-color: #6366f1;
      color: #ffffff;
      box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.25);
    }
    .btn-indigo {
      background: #4f46e5;
      color: white;
      font-weight: 600;
      border-radius: 0.75rem;
      padding: 0.75rem;
      border: none;
      transition: all 0.2s ease;
    }
    .btn-indigo:hover {
      background: #4338ca;
      box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4);
    }
  </style>
</head>
<body>

  <div class="login-card p-4 p-sm-5">
    
    <div class="text-center mb-4">
      <div class="rounded-3 p-2 bg-indigo-600 text-white d-inline-flex align-items-center justify-content-center shadow-sm mb-3" style="width: 48px; height: 48px;">
        <i data-lucide="zap" style="width: 24px; height: 24px;"></i>
      </div>
      <h4 class="fw-bold text-white mb-1">Softlixx Intelligence</h4>
      <p class="text-secondary small mb-0">Secure Campaign Telemetry & Tracking</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3 border-0 bg-danger bg-opacity-25 text-rose-300">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="/login">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
      
      <div class="mb-3">
        <label class="form-label small text-secondary fw-semibold">Username</label>
        <input type="text" name="username" class="form-control form-control-dark" placeholder="admin" required autofocus>
      </div>

      <div class="mb-4">
        <label class="form-label small text-secondary fw-semibold">Password</label>
        <input type="password" name="password" class="form-control form-control-dark" placeholder="••••••••••••" required>
      </div>

      <button type="submit" class="btn btn-indigo w-100 d-flex align-items-center justify-content-center gap-2">
        <i data-lucide="lock" style="width: 16px; height: 16px;"></i> Sign In to Dashboard
      </button>
    </form>

    <div class="mt-4 pt-3 border-top border-secondary border-opacity-25 text-center">
      <small class="text-secondary" style="font-size: 11px;">
        Hostinger Cloud Shared Hosting &bull; tracking.softlixx.com
      </small>
    </div>

  </div>

  <script>lucide.createIcons();</script>
</body>
</html>
