<div class="container-fluid p-2">
  
  <!-- TOP HEADER -->
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h4 class="fw-bold text-white mb-1">Campaign Intelligence & Telemetry</h4>
      <p class="text-secondary mb-0 small">Real-time open tracking, link click monitoring & automated privacy proxy telemetry.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <span class="badge badge-soft-emerald px-3 py-2 rounded-pill font-mono">
        <i data-lucide="check-circle-2" class="me-1 d-inline" style="width: 14px; height: 14px;"></i> System Live: tracking.softlixx.com
      </span>
      <a href="/api-docs" class="btn btn-sm btn-outline-secondary px-3 py-1.5 rounded-3 d-flex align-items-center gap-1.5">
        <i data-lucide="terminal" style="width: 14px; height: 14px;"></i> API Specs
      </a>
    </div>
  </div>

  <!-- KPI SUMMARY CARDS -->
  <div class="row g-3 mb-4">
    
    <!-- 1. Total Dispatched -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card-glass p-3 h-100">
        <div class="d-flex align-items-center justify-content-between text-secondary mb-2">
          <span class="small fw-semibold text-uppercase font-mono">Total Dispatched</span>
          <div class="rounded-2 p-1.5 bg-indigo-500 bg-opacity-10 text-indigo-400">
            <i data-lucide="send" style="width: 16px; height: 16px;"></i>
          </div>
        </div>
        <h3 class="fw-bold text-white mb-1"><?= number_format($kpis['total_sent'] ?? 0) ?></h3>
        <small class="text-secondary font-mono" style="font-size: 11px;">
          Across <?= number_format($kpis['total_campaigns'] ?? 0) ?> Active Campaigns
        </small>
      </div>
    </div>

    <!-- 2. Open Events (Human vs Bot) -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card-glass p-3 h-100">
        <div class="d-flex align-items-center justify-content-between text-secondary mb-2">
          <span class="small fw-semibold text-uppercase font-mono">Open Events</span>
          <div class="rounded-2 p-1.5 bg-emerald-500 bg-opacity-10 text-emerald-400">
            <i data-lucide="eye" style="width: 16px; height: 16px;"></i>
          </div>
        </div>
        <div class="d-flex align-items-baseline gap-2 mb-1">
          <h3 class="fw-bold text-white mb-0"><?= number_format($kpis['total_human_opens'] ?? 0) ?></h3>
          <span class="badge badge-soft-emerald font-mono" style="font-size: 10px;">Human Verified</span>
        </div>
        <small class="text-secondary font-mono" style="font-size: 11px;">
          +<?= number_format($kpis['total_bot_opens'] ?? 0) ?> Automated / Privacy Proxies
        </small>
      </div>
    </div>

    <!-- 3. Unique Clicks -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card-glass p-3 h-100">
        <div class="d-flex align-items-center justify-content-between text-secondary mb-2">
          <span class="small fw-semibold text-uppercase font-mono">Tracked Clicks</span>
          <div class="rounded-2 p-1.5 bg-blue-500 bg-opacity-10 text-blue-400">
            <i data-lucide="mouse-pointer-click" style="width: 16px; height: 16px;"></i>
          </div>
        </div>
        <div class="d-flex align-items-baseline gap-2 mb-1">
          <h3 class="fw-bold text-white mb-0"><?= number_format($kpis['total_clicks'] ?? 0) ?></h3>
          <span class="badge badge-soft-blue font-mono" style="font-size: 10px;">
            <?= $kpis['ctr'] ?? '0' ?>% CTR
          </span>
        </div>
        <small class="text-secondary font-mono" style="font-size: 11px;">
          High-Intent Prospect Engagement
        </small>
      </div>
    </div>

    <!-- 4. Replies & Engagement -->
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card-glass p-3 h-100">
        <div class="d-flex align-items-center justify-content-between text-secondary mb-2">
          <span class="small fw-semibold text-uppercase font-mono">Client Inbound Replies</span>
          <div class="rounded-2 p-1.5 bg-amber-500 bg-opacity-10 text-amber-400">
            <i data-lucide="message-square" style="width: 16px; height: 16px;"></i>
          </div>
        </div>
        <div class="d-flex align-items-baseline gap-2 mb-1">
          <h3 class="fw-bold text-white mb-0"><?= number_format($kpis['total_replies'] ?? 0) ?></h3>
          <span class="badge badge-soft-amber font-mono" style="font-size: 10px;">
            <?= $kpis['reply_rate'] ?? '0' ?>% Rate
          </span>
        </div>
        <small class="text-secondary font-mono" style="font-size: 11px;">
          <?= number_format($kpis['total_suppressions'] ?? 0) ?> Suppressed Recipients
        </small>
      </div>
    </div>

  </div>

  <!-- CHARTS ROW -->
  <div class="row g-3 mb-4">
    
    <!-- 1. Activity Trends Line Chart -->
    <div class="col-12 col-xl-8">
      <div class="card-glass p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h6 class="fw-bold text-white mb-0">Engagement & Activity Timeline</h6>
          <span class="badge bg-secondary bg-opacity-25 text-secondary font-mono" style="font-size: 11px;">Last 7 Days</span>
        </div>
        <div style="height: 280px;">
          <canvas id="timelineChart"></canvas>
        </div>
      </div>
    </div>

    <!-- 2. Human vs Bot Quality Ratio -->
    <div class="col-12 col-xl-4">
      <div class="card-glass p-3 h-100">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h6 class="fw-bold text-white mb-0">Traffic Quality Classification</h6>
          <i data-lucide="shield-check" class="text-secondary" style="width: 16px; height: 16px;"></i>
        </div>
        <div style="height: 220px;" class="d-flex justify-content-center">
          <canvas id="qualityDoughnut"></canvas>
        </div>
        <div class="d-flex justify-content-around text-center mt-3 pt-2 border-top border-secondary border-opacity-25">
          <div>
            <small class="text-secondary d-block font-mono" style="font-size: 10px;">HUMAN</small>
            <span class="fw-bold text-emerald-400 font-mono"><?= $kpis['human_pct'] ?? '0' ?>%</span>
          </div>
          <div>
            <small class="text-secondary d-block font-mono" style="font-size: 10px;">PRIVACY / BOTS</small>
            <span class="fw-bold text-indigo-400 font-mono"><?= $kpis['bot_pct'] ?? '0' ?>%</span>
          </div>
          <div>
            <small class="text-secondary d-block font-mono" style="font-size: 10px;">UNOPENED</small>
            <span class="fw-bold text-secondary font-mono"><?= $kpis['unopened_pct'] ?? '0' ?>%</span>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- RECENT AUDIT EVENTS TABLE -->
  <div class="card-glass p-3 mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <div class="d-flex align-items-center gap-2">
        <i data-lucide="radio" class="text-emerald-400 animate-pulse" style="width: 18px; height: 18px;"></i>
        <h6 class="fw-bold text-white mb-0">Live Audit Telemetry Stream</h6>
      </div>
      <a href="/events" class="small text-indigo-400 text-decoration-none">View All Telemetry &rarr;</a>
    </div>

    <div class="table-responsive">
      <table class="table table-dark-custom align-middle mb-0" style="font-size: 12px;">
        <thead>
          <tr>
            <th>Timestamp</th>
            <th>Event</th>
            <th>Recipient</th>
            <th>Campaign</th>
            <th>Type</th>
            <th>Telemetry / Signature</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentEvents)): ?>
            <tr>
              <td colspan="7" class="text-center py-4 text-secondary">
                No telemetry events logged yet. Connect your local Python engine to start tracking!
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($recentEvents as $ev): ?>
              <tr>
                <td class="font-mono text-secondary"><?= htmlspecialchars(date('M d, H:i:s', strtotime($ev['event_timestamp']))) ?></td>
                <td>
                  <?php if ($ev['event_type'] === 'open'): ?>
                    <span class="badge badge-soft-emerald px-2 py-1"><i data-lucide="eye" class="me-1" style="width: 12px; height: 12px;"></i> Open</span>
                  <?php elseif ($ev['event_type'] === 'click'): ?>
                    <span class="badge badge-soft-blue px-2 py-1"><i data-lucide="mouse-pointer-click" class="me-1" style="width: 12px; height: 12px;"></i> Click</span>
                  <?php elseif ($ev['event_type'] === 'reply'): ?>
                    <span class="badge badge-soft-amber px-2 py-1"><i data-lucide="message-square" class="me-1" style="width: 12px; height: 12px;"></i> Reply</span>
                  <?php else: ?>
                    <span class="badge badge-soft-indigo px-2 py-1"><?= htmlspecialchars(strtoupper($ev['event_type'])) ?></span>
                  <?php endif; ?>
                </td>
                <td class="fw-semibold text-white"><?= htmlspecialchars($ev['recipient_email']) ?></td>
                <td class="text-secondary"><?= htmlspecialchars($ev['campaign_name'] ?? 'General') ?></td>
                <td>
                  <?php if ($ev['is_automated']): ?>
                    <span class="badge bg-secondary bg-opacity-25 text-warning font-mono" style="font-size: 10px;">🤖 <?= htmlspecialchars($ev['detection_reason'] ?: 'Automated Proxy') ?></span>
                  <?php else: ?>
                    <span class="badge badge-soft-emerald font-mono" style="font-size: 10px;">👤 Human Interaction</span>
                  <?php endif; ?>
                </td>
                <td class="text-secondary font-mono text-truncate" style="max-width: 260px;" title="<?= htmlspecialchars($ev['user_agent'] ?? '') ?>">
                  <?= htmlspecialchars($ev['user_agent'] ?: 'Direct Socket') ?>
                </td>
                <td>
                  <a href="/clients/view?id=<?= $ev['client_id'] ?? 0 ?>" class="btn btn-xs btn-outline-secondary py-0.5 px-2 font-mono" style="font-size: 11px;">Dossier</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- INITIALIZE CHARTS -->
<script>
  // Timeline Line Chart
  const timelineCtx = document.getElementById('timelineChart').getContext('2d');
  new Chart(timelineCtx, {
    type: 'line',
    data: {
      labels: <?= json_encode($chartData['labels'] ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']) ?>,
      datasets: [
        {
          label: 'Emails Sent',
          data: <?= json_encode($chartData['sent'] ?? [0, 0, 0, 0, 0, 0, 0]) ?>,
          borderColor: '#818cf8',
          backgroundColor: 'rgba(129, 140, 248, 0.1)',
          tension: 0.3,
          fill: true
        },
        {
          label: 'Human Opens',
          data: <?= json_encode($chartData['opens'] ?? [0, 0, 0, 0, 0, 0, 0]) ?>,
          borderColor: '#34d399',
          backgroundColor: 'transparent',
          tension: 0.3
        },
        {
          label: 'Tracked Clicks',
          data: <?= json_encode($chartData['clicks'] ?? [0, 0, 0, 0, 0, 0, 0]) ?>,
          borderColor: '#60a5fa',
          backgroundColor: 'transparent',
          tension: 0.3
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { labels: { color: '#94a3b8', font: { family: 'Inter', size: 11 } } }
      },
      scales: {
        x: { grid: { color: 'rgba(51, 65, 85, 0.2)' }, ticks: { color: '#94a3b8' } },
        y: { grid: { color: 'rgba(51, 65, 85, 0.2)' }, ticks: { color: '#94a3b8', stepSize: 1 }, beginAtZero: true }
      }
    }
  });

  // Quality Doughnut Chart
  const qualityCtx = document.getElementById('qualityDoughnut').getContext('2d');
  new Chart(qualityCtx, {
    type: 'doughnut',
    data: {
      labels: ['Human Opens', 'Privacy/Bots', 'Unopened'],
      datasets: [{
        data: [
          <?= (int)($kpis['total_human_opens'] ?? 0) ?>,
          <?= (int)($kpis['total_bot_opens'] ?? 0) ?>,
          <?= max(0, (int)($kpis['total_sent'] ?? 0) - (int)($kpis['total_human_opens'] ?? 0) - (int)($kpis['total_bot_opens'] ?? 0)) ?>
        ],
        backgroundColor: ['#10b981', '#6366f1', '#334155'],
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      cutout: '75%'
    }
  });
</script>
