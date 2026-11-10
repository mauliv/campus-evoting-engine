<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CampusBallot Core - Collegiate E-Voting Platform</title>
    <link rel="icon" href="images/favicon.ico">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">

    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f8fafc;
            color: #1e293b;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        .hero-banner {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: #ffffff;
            padding: 3.5rem 1rem 3rem;
            text-align: center;
        }

        .hero-logo {
            width: 80px;
            height: 80px;
            margin-bottom: 1rem;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.15));
        }

        .hero-title {
            font-size: 2.4rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 0.5rem;
        }

        .hero-subtitle {
            font-size: 1.15rem;
            opacity: 0.92;
            max-width: 650px;
            margin: 0 auto 1.5rem;
        }

        .badge-pill-custom {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(4px);
            padding: 0.35rem 1rem;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .main-container {
            max-width: 1080px;
            margin: -2rem auto 3rem;
            padding: 0 1rem;
        }

        .action-card {
            background: #ffffff;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
            border: 1px solid #e2e8f0;
            height: 100%;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.25rem;
        }

        .icon-blue { background: #dbeafe; color: #1d4ed8; }
        .icon-green { background: #dcfce7; color: #15803d; }
        .icon-purple { background: #f3e8ff; color: #7e22ce; }

        .spec-list {
            list-style: none;
            padding-left: 0;
            margin-top: 1rem;
            font-size: 0.92rem;
            color: #475569;
        }

        .spec-list li {
            padding: 0.4rem 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .spec-list li i {
            color: #10b981;
            margin-right: 0.5rem;
        }

        footer {
            text-align: center;
            padding: 2.5rem 1rem;
            color: #64748b;
            font-size: 0.9rem;
            border-top: 1px solid #e2e8f0;
            background: #ffffff;
        }
    </style>
</head>
<body>

<!-- HERO BANNER -->
<section class="hero-banner">
    <div class="container">
        <img src="images/logo.svg" alt="CampusBallot Logo" class="hero-logo">
        <div class="badge-pill-custom mb-3">Enterprise Open-Source Core v2.5</div>
        <h1 class="hero-title">CampusBallot Core</h1>
        <p class="hero-subtitle">
            High-concurrency federated e-voting platform engineered for university student elections. 
            Deterministic multi-tier ballot routing, dual-key audit unlock, and real-time telemetry.
        </p>
    </div>
</section>

<!-- MAIN MODULE NAVIGATION -->
<main class="main-container">
    <div class="row g-4">

        <!-- Voting Booth -->
        <div class="col-md-4">
            <div class="action-card text-center d-flex flex-column">
                <div class="feature-icon icon-blue mx-auto">
                    <i class="bi bi-box-arrow-in-right"></i>
                </div>
                <h3 class="h5 fw-bold mb-2">Voting Booth</h3>
                <p class="text-muted small flex-grow-1">
                    Atomic single-page ballot routing engine delivering up to 5 concurrent electoral college tiers based on student department mapping.
                </p>
                <div class="d-grid gap-2 mt-3">
                    <a href="system/verification/index.php" class="btn btn-primary fw-semibold">
                        <i class="bi bi-person-check-fill me-1"></i> Enter via SSO Auth
                    </a>
                    <a href="system/booth/ballot.php" class="btn btn-outline-secondary btn-sm">
                        Direct Booth Test
                    </a>
                </div>
            </div>
        </div>

        <!-- Telemetry -->
        <div class="col-md-4">
            <div class="action-card text-center d-flex flex-column">
                <div class="feature-icon icon-green mx-auto">
                    <i class="bi bi-speedometer2"></i>
                </div>
                <h3 class="h5 fw-bold mb-2">Turnout Telemetry</h3>
                <p class="text-muted small flex-grow-1">
                    Live participation monitor showing voter turnout percentages across all faculties, updating synchronously with zero caching lag.
                </p>
                <div class="d-grid gap-2 mt-3">
                    <a href="system/telemetry/turnout.php" class="btn btn-success fw-semibold">
                        <i class="bi bi-graph-up me-1"></i> Live Turnout Monitor
                    </a>
                </div>
            </div>
        </div>

        <!-- Tabulation -->
        <div class="col-md-4">
            <div class="action-card text-center d-flex flex-column">
                <div class="feature-icon icon-purple mx-auto">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <h3 class="h5 fw-bold mb-2">Official Tabulation</h3>
                <p class="text-muted small flex-grow-1">
                    Dual-Key ceremonial unlock system requiring joint cryptographic clearance from Election Commission & Supervisory Board to reveal candidate counts.
                </p>
                <div class="d-grid gap-2 mt-3">
                    <a href="system/tabulation/index.php" class="btn btn-dark fw-semibold">
                        <i class="bi bi-key-fill me-1"></i> Plenary Tabulation
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- TECHNICAL HIGHLIGHTS -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="action-card">
                <h4 class="h5 fw-bold mb-3"><i class="bi bi-cpu-fill text-primary me-2"></i>Engineering Architecture Highlights</h4>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="spec-list">
                            <li><i class="bi bi-check-circle-fill"></i> <strong>5-Ballot Federated Routing:</strong> Universal presidential executive + faculty legislative + departmental associations in a single roundtrip.</li>
                            <li><i class="bi bi-check-circle-fill"></i> <strong>Concurrency Engine:</strong> Zero-ORM native PHP with PostgreSQL row-level locks preventing race-condition double voting under peak traffic.</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="spec-list">
                            <li><i class="bi bi-check-circle-fill"></i> <strong>Dual-Key Custody:</strong> Split authorization prevents unilateral tally leaks before official plenary closing.</li>
                            <li><i class="bi bi-check-circle-fill"></i> <strong>Health & Telemetry:</strong> Built-in <a href="system/healthcheck.php">/system/healthcheck.php</a> endpoint for load balancer readiness probes.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<footer>
    <div class="container">
        <p class="mb-1"><strong>CampusBallot Core</strong> · Open-Source Federated E-Voting Platform</p>
        <p class="mb-0 text-muted small">Released under the MIT License · Designed for University Student Elections</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
