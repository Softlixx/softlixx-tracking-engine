<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'Softlixx Email Intelligence') ?></title>
  
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Google Fonts & Lucide Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
  <script src="https://unpkg.com/lucide@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

  <style>
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      background-color: #0b0f19;
      color: #f1f5f9;
    }
    .font-mono { font-family: 'JetBrains Mono', monospace; }
    .card-glass {
      background: rgba(18, 24, 38, 0.95);
      border: 1px solid rgba(51, 65, 85, 0.4);
      border-radius: 1rem;
      backdrop-filter: blur(12px);
    }
    .sidebar-link {
      color: #94a3b8;
      transition: all 0.2s ease;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.65rem 1rem;
      border-radius: 0.75rem;
      font-weight: 500;
      font-size: 0.875rem;
    }
    .sidebar-link:hover, .sidebar-link.active {
      color: #ffffff;
      background: rgba(99, 102, 241, 0.15);
      border: 1px solid rgba(99, 102, 241, 0.3);
    }
    .sidebar-link.active {
      background: #4f46e5;
      color: #ffffff;
      border: none;
      box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3);
    }
    .badge-soft-emerald { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
    .badge-soft-indigo { background: rgba(99, 102, 241, 0.15); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.3); }
    .badge-soft-amber { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
    .badge-soft-rose { background: rgba(244, 63, 94, 0.15); color: #fb7185; border: 1px solid rgba(244, 63, 94, 0.3); }
    .badge-soft-blue { background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); }
    .table-dark-custom {
      --bs-table-bg: transparent;
      --bs-table-color: #cbd5e1;
      --bs-table-border-color: rgba(51, 65, 85, 0.5);
    }
    .table-dark-custom th {
      color: #94a3b8;
      font-weight: 600;
      font-size: 0.75rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      border-bottom: 1px solid rgba(51, 65, 85, 0.6);
    }
  </style>
</head>
<body class="d-flex h-full">

  <!-- SIDEBAR NAVIGATION -->
  <aside class="d-flex flex-column flex-shrink-0 p-3 card-glass m-3" style="width: 260px; height: calc(100vh - 2rem);">
    <div class="d-flex align-items-center gap-2.5 px-2 py-2 mb-4 border-bottom border-secondary border-opacity-25 pb-3">
      <div class="rounded-3 p-2 bg-indigo-600 text-white shadow-sm d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
        <i data-lucide="zap" style="width: 20px; height: 20px;"></i>
      </div>
      <div>
        <h6 class="mb-0 fw-bold text-white tracking-wide">Softlixx</h6>
        <small class="text-secondary font-mono" style="font-size: 11px;">Email Intelligence</small>
      </div>
    </div>

    <ul class="nav nav-pills flex-column gap-1 mb-auto">
      <li>
        <a href="/" class="sidebar-link <?= ($activeNav ?? '') === 'dashboard' ? 'active' : '' ?>">
          <i data-lucide="layout-dashboard" style="width: 18px; height: 18px;"></i>
          <span>Overview</span>
        </a>
      </li>
      <li>
        <a href="/campaigns" class="sidebar-link <?= ($activeNav ?? '') === 'campaigns' ? 'active' : '' ?>">
          <i data-lucide="send" style="width: 18px; height: 18px;"></i>
          <span>Campaigns</span>
        </a>
      </li>
      <li>
        <a href="/clients" class="sidebar-link <?= ($activeNav ?? '') === 'clients' ? 'active' : '' ?>">
          <i data-lucide="users" style="width: 18px; height: 18px;"></i>
          <span>Client CRM</span>
        </a>
      </li>
      <li>
        <a href="/events" class="sidebar-link <?= ($activeNav ?? '') === 'events' ? 'active' : '' ?>">
          <i data-lucide="activity" style="width: 18px; height: 18px;"></i>
          <span>Live Telemetry</span>
        </a>
      </li>
      <li>
        <a href="/suppressions" class="sidebar-link <?= ($activeNav ?? '') === 'suppressions' ? 'active' : '' ?>">
          <i data-lucide="shield-ban" style="width: 18px; height: 18px;"></i>
          <span>Suppressions</span>
        </a>
      </li>
      <li>
        <a href="/api-docs" class="sidebar-link <?= ($activeNav ?? '') === 'api-docs' ? 'active' : '' ?>">
          <i data-lucide="code" style="width: 18px; height: 18px;"></i>
          <span>Python API Specs</span>
        </a>
      </li>
    </ul>

    <hr class="border-secondary border-opacity-25">
    
    <div class="d-flex align-items-center justify-content-between px-2 pt-1">
      <div class="d-flex align-items-center gap-2">
        <div class="rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center text-white" style="width: 32px; height: 32px; font-size: 12px; font-weight: 700;">
          SA
        </div>
        <div>
          <div class="text-white fw-semibold" style="font-size: 12px;"><?= htmlspecialchars($_SESSION['softlixx_name'] ?? 'Admin') ?></div>
          <div class="text-secondary" style="font-size: 10px;">Hostinger Shared</div>
        </div>
      </div>
      <a href="/logout" class="text-secondary hover-text-white" title="Logout">
        <i data-lucide="log-out" style="width: 16px; height: 16px;"></i>
      </a>
    </div>
  </aside>

  <!-- MAIN VIEW CONTAINER -->
  <main class="flex-grow-1 p-3 overflow-y-auto" style="height: 100vh;">
    <?= $content ?? '' ?>
  </main>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    lucide.createIcons();
  </script>
</body>
</html>
