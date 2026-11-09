<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribed — Softlixx Email Network</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background-color: #0b0f19;
            color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .card-custom {
            background-color: #111827;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
            max-width: 480px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container p-4">
        <div class="card card-custom mx-auto p-4 text-center shadow-lg">
            <div class="mb-3">
                <span class="d-inline-flex p-3 rounded-circle bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                    <i class="bi bi-check-circle fs-1"></i>
                </span>
            </div>
            <h4 class="fw-bold text-white mb-2">You Have Been Unsubscribed</h4>
            <p class="text-secondary small mb-3">
                Your email address <strong class="text-white"><?= htmlspecialchars($email ?? '') ?></strong> has been successfully removed from our communication list.
            </p>
            <div class="alert alert-dark border-secondary border-opacity-25 text-secondary small text-start mb-4">
                <i class="bi bi-shield-check text-success me-2"></i>You will not receive any further automated outreach emails from our team regarding this campaign.
            </div>
            <div>
                <a href="https://softlixx.com" class="btn btn-outline-secondary btn-sm">Return to Softlixx</a>
            </div>
        </div>
    </div>
</body>
</html>
