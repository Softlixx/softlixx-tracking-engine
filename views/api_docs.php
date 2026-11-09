<div class="row mb-4">
    <div class="col-12">
        <h3 class="fw-bold text-white mb-1"><i class="bi bi-code-slash text-info me-2"></i>Python & REST API Documentation</h3>
        <p class="text-secondary small mb-0">Direct integration endpoints for your local Python lead scraper, automated outreach sequence, and SMTP delivery pipelines.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card bg-dark border-secondary border-opacity-25 shadow-sm h-100">
            <div class="card-header border-secondary border-opacity-25 bg-darker py-3">
                <span class="badge bg-success me-2">POST</span>
                <span class="font-monospace text-white fw-bold">/api/v1/sync-client</span>
            </div>
            <div class="card-body">
                <p class="text-secondary small">Synchronizes a local client record with the tracking system. Returns the tracking `client_id`.</p>
                <div class="bg-darker p-3 rounded border border-secondary border-opacity-25 font-monospace text-xs text-light mb-3">
<pre class="m-0 text-info"># Request Headers:
Authorization: Bearer <?= htmlspecialchars($appConfig['api_key'] ?? 'YOUR_API_KEY') ?>
Content-Type: application/json

# JSON Payload:
{
  "external_client_id": "client_1794212",
  "business_name": "Sollair Solar Project",
  "email": "info@sollair.ca",
  "website": "https://sollair.ca",
  "industry": "Solar Installation Contractors",
  "city": "Airdrie",
  "state": "Alberta",
  "country": "Canada"
}</pre>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card bg-dark border-secondary border-opacity-25 shadow-sm h-100">
            <div class="card-header border-secondary border-opacity-25 bg-darker py-3">
                <span class="badge bg-primary me-2">POST</span>
                <span class="font-monospace text-white fw-bold">/api/v1/register-email</span>
            </div>
            <div class="card-body">
                <p class="text-secondary small">Registers an outgoing email before sending. Generates a unique tracking token and tracked links.</p>
                <div class="bg-darker p-3 rounded border border-secondary border-opacity-25 font-monospace text-xs text-light mb-3">
<pre class="m-0 text-info"># JSON Payload:
{
  "campaign_name": "Alberta Solar Contractors Outreach",
  "client_id": 1,
  "recipient_email": "info@sollair.ca",
  "subject": "Case Study for Alberta Solar Installers",
  "email_sequence": 1,
  "email_html": "&lt;p&gt;Check our portfolio at &lt;a href='https://softlixx.com'&gt;softlixx.com&lt;/a&gt;&lt;/p&gt;"
}

# Response:
{
  "status": "success",
  "campaign_email_id": 42,
  "tracking_token": "a1b2c3d4...",
  "pixel_html": "&lt;img src='https://tracking.softlixx.com/open/a1b2c3d4...' width='1' height='1' style='display:none;' alt='' /&gt;",
  "tracked_html": "...",
  "unsubscribe_url": "https://tracking.softlixx.com/unsubscribe/a1b2c3d4..."
}</pre>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card bg-dark border-secondary border-opacity-25 shadow-sm h-100">
            <div class="card-header border-secondary border-opacity-25 bg-darker py-3">
                <span class="badge bg-warning text-dark me-2">POST</span>
                <span class="font-monospace text-white fw-bold">/api/v1/mark-sent</span>
            </div>
            <div class="card-body">
                <p class="text-secondary small">Call immediately after SMTP accepts the message (250 OK) to confirm deliverability.</p>
                <div class="bg-darker p-3 rounded border border-secondary border-opacity-25 font-monospace text-xs text-light mb-3">
<pre class="m-0 text-info"># JSON Payload:
{
  "campaign_email_id": 42
}

# Response:
{
  "status": "success",
  "delivery_status": "sent",
  "sent_at": "2026-11-09 15:30:00"
}</pre>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card bg-dark border-secondary border-opacity-25 shadow-sm h-100">
            <div class="card-header border-secondary border-opacity-25 bg-darker py-3">
                <span class="badge bg-info text-dark me-2">GET</span>
                <span class="font-monospace text-white fw-bold">/api/v1/campaign-stats</span>
            </div>
            <div class="card-body">
                <p class="text-secondary small">Fetches high-level metrics, human open rates, click-through rates, and reply statistics.</p>
                <div class="bg-darker p-3 rounded border border-secondary border-opacity-25 font-monospace text-xs text-light mb-3">
<pre class="m-0 text-info"># Query Parameter:
?campaign_id=1

# Response:
{
  "status": "success",
  "campaign": { "id": 1, "name": "Alberta Solar Contractors Outreach" },
  "stats": {
    "total_sent": 25,
    "human_opens": 18,
    "open_rate": "72.0%",
    "clicks": 9,
    "click_rate": "36.0%",
    "replies": 3
  }
}</pre>
                </div>
            </div>
        </div>
    </div>
</div>
