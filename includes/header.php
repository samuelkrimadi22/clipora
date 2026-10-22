<?php
// ============================================================
// V9.623.0: Admin = BUSINESS (akses penuh semua fitur untuk testing)
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
if (!empty($_SESSION['is_admin'])) {
}
// ============================================================

// V9.619.0 dihapus (digantikan V9.623.0)

// ============================================================
// V9.6.0 SECURITY: HTTP Security Headers
// ============================================================
if (!headers_sent()) {
    // Cegah clickjacking — hanya boleh embed dari domain sendiri
    header('X-Frame-Options: SAMEORIGIN');
    
    // Cegah MIME sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Referrer policy — jangan bocorkan URL ke third-party
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Permissions policy — batasi akses fitur browser
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    
    // XSS Protection (legacy, tapi bagus untuk browser lama)
    header('X-XSS-Protection: 1; mode=block');
}

/**
 * ============================================================
 * Clipora V8.8.0 - Header Template (Anti-Spam Notifications)
 * ============================================================
 */
require_once __DIR__ . '/../config/config.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$isActive = fn($page) => $currentPage === $page ? 'active' : '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title><?= APP_NAME ?> | AI Video Editor </title>
    <meta name="description" content="Clipora - AI video editor otomatis. Potong jeda, kata dobol, dan buat subtitle dengan AI.">

    <link rel="icon" type="image/png" href="/clipora/logo_clipora.png">
    <link rel="shortcut icon" href="/clipora/logo_clipora.png" type="image/png">
    <link rel="alternate icon" href="/favicon.ico">
    

    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/voiceover.css">
    <link rel="stylesheet" href="assets/css/voiceover-redesign.css?v=1">

        <!-- ============================================================ -->
    <!-- V10.0.0: MIDTRANS SNAP INTEGRATION                           -->
    <!-- ============================================================ -->
    <?php
    // Ambil konfigurasi dari .env
    $midtransIsProd = filter_var($_ENV['MIDTRANS_IS_PRODUCTION'] ?? false, FILTER_VALIDATE_BOOLEAN);
    $midtransClientKey = $_ENV['MIDTRANS_CLIENT_KEY'] ?? '';
    
    // Tentukan URL Snap (Production vs Sandbox)
    $snapJsUrl = $midtransIsProd 
        ? 'https://app.midtrans.com/snap/snap.js' 
        : 'https://app.sandbox.midtrans.com/snap/snap.js';
    ?>

    <?php if (!empty($midtransClientKey)): ?>
        <script type="text/javascript"
            src="<?= $snapJsUrl ?>"
            data-client-key="<?= htmlspecialchars($midtransClientKey) ?>">
        </script>
    <?php else: ?>
        <script>
            console.warn("⚠️ [Midtrans] Client Key tidak ditemukan di .env. Pembayaran tidak akan berfungsi.");
        </script>
    <?php endif; ?>
    <!-- ============================================================ -->



    <!-- V8.7.0 SECURITY: Inject auth token ke JS -->
    <script>
        window.CLIPORA_AUTH = {
            token: <?= json_encode($authToken ?? '') ?>,
            userId: <?= json_encode($userId ?? null) ?>,
            username: <?= json_encode($userName ?? null) ?>,
            plan: <?= json_encode($userPlan ?? 'free') ?>,
            isAdmin: <?= !empty($isAdmin) ? 'true' : 'false' ?>,
            isLoggedIn: <?= !empty($isLoggedIn) ? 'true' : 'false' ?>,
            apiUrl: <?= json_encode(API_URL) ?>,
            baseUrl: <?= json_encode(BASE_URL) ?>
        };
        
        // V2.0.0: Definisikan IS_ADMIN, IS_LOGGED_IN, USER_PLAN SEBELUM main.js
        window.IS_ADMIN = <?= !empty($isAdmin) ? 'true' : 'false' ?>;
        window.IS_LOGGED_IN = <?= !empty($isLoggedIn) ? 'true' : 'false' ?>;
        window.USER_PLAN = '<?= htmlspecialchars($userPlan ?? "free", ENT_QUOTES) ?>';
        const IS_ADMIN = window.IS_ADMIN;
        const IS_LOGGED_IN = window.IS_LOGGED_IN;
        const USER_PLAN = window.USER_PLAN;
        
        // V2.0.0: Definisikan BASE_URL & API_URL juga (untuk main.js)
        window.BASE_URL = <?= json_encode(BASE_URL) ?>;
        window.API_URL = <?= json_encode(API_URL) ?>;
        window.MAX_FILE_SIZE = <?= json_encode(MAX_FILE_SIZE) ?>;
        const BASE_URL = window.BASE_URL;
        const API_URL = window.API_URL;
        const MAX_FILE_SIZE = window.MAX_FILE_SIZE;
    </script>

<!-- V9.55.5: Auto-hide onboarding >1 hari -->
<script>
(function() {
    "use strict";
    try {
        var ONE_DAY = 24 * 60 * 60 * 1000;
        var KEY = "clipora_onboarding_completed_at";
        var completedAt = localStorage.getItem(KEY);
        var now = Date.now();

        // Kalau sudah lewat 1 hari, hide widget
        if (completedAt && (now - parseInt(completedAt, 10)) > ONE_DAY) {
            console.log("[V9.55.5] Onboarding >1 hari, hiding");
            var hideWidget = function() {
                var w = document.querySelector(".onboarding-widget");
                if (w) {
                    w.style.setProperty("display", "none", "important");
                    console.log("[V9.55.5] Widget hidden");
                    return true;
                }
                return false;
            };
            // Coba beberapa kali karena render async
            var attempts = 0;
            var iv = setInterval(function() {
                attempts++;
                if (hideWidget() || attempts > 20) clearInterval(iv);
            }, 200);
        } else if (!completedAt) {
            // Simpan timestamp kalau widget sudah completed
            var checkComplete = function() {
                var w = document.querySelector(".onboarding-widget.completed");
                if (w) {
                    localStorage.setItem(KEY, Date.now().toString());
                    console.log("[V9.55.5] Completion timestamp saved");
                    return true;
                }
                return false;
            };
            var attempts2 = 0;
            var iv2 = setInterval(function() {
                attempts2++;
                if (checkComplete() || attempts2 > 20) clearInterval(iv2);
            }, 300);
        }
    } catch (e) {
        console.warn("[V9.55.5] Error:", e.message);
    }
})();
</script>

<!-- V9.187.0: MAX SPECIFICITY - override semua -->


<!-- V9.190.0: MARGIN BERANDA - beranda turun dari header -->







<!-- V9.226.0: MOBILE GUIDE + AUTO-SCROLL -->
<style>
.mg-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.85);
    z-index: 99999;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
    backdrop-filter: blur(8px);
}
.mg-overlay.active { display: flex; }
.mg-card {
    background: linear-gradient(135deg, #14141f 0%, #0f0f1a 100%);
    border: 1px solid rgba(245, 158, 11, 0.3);
    border-radius: 20px;
    padding: 24px;
    max-width: 400px;
    width: 100%;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.7);
    animation: mgSlideIn 0.3s ease;
    box-sizing: border-box;
}
@keyframes mgSlideIn {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
.mg-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}
.mg-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1.4rem;
    flex-shrink: 0;
}
.mg-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #fff;
    margin: 0;
}
.mg-subtitle {
    font-size: 0.8rem;
    color: #94a3b8;
    margin: 2px 0 0;
}
.mg-step {
    display: flex;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}
.mg-step:last-of-type { border-bottom: none; }
.mg-step-num {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(245, 158, 11, 0.15);
    color: #f59e0b;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 700;
    flex-shrink: 0;
}
.mg-step-text {
    color: #cbd5e1;
    font-size: 0.88rem;
    line-height: 1.5;
}
.mg-step-text strong { color: #f59e0b; }
.mg-btn {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);
    border: none;
    border-radius: 12px;
    color: #fff;
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    margin-top: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-sizing: border-box;
}
.mg-skip {
    background: transparent;
    border: none;
    color: #64748b;
    font-size: 0.8rem;
    cursor: pointer;
    width: 100%;
    text-align: center;
    margin-top: 10px;
    padding: 8px;
}
</style>

<div class="mg-overlay" id="mgOverlay">
    <div class="mg-card">
        <div class="mg-header">
            <div class="mg-icon"><i class="fas fa-mobile-alt"></i></div>
            <div>
                <h3 class="mg-title">Panduan Cepat Mobile</h3>
                <p class="mg-subtitle">3 langkah pakai Thumbnail Studio</p>
            </div>
        </div>
        <div class="mg-step">
            <div class="mg-step-num">1</div>
            <div class="mg-step-text"><strong>Upload foto</strong> — tap tombol <strong>Pilih Foto</strong> di tab Desain</div>
        </div>
        <div class="mg-step">
            <div class="mg-step-num">2</div>
            <div class="mg-step-text"><strong>Edit</strong> — ganti tab <strong>Text</strong> atau <strong>Efek</strong> untuk border & icon</div>
        </div>
        <div class="mg-step">
            <div class="mg-step-num">3</div>
            <div class="mg-step-text"><strong>Download</strong> — tap tombol oranye di paling bawah</div>
        </div>
        <button class="mg-btn" onclick="window.__closeMgGuide && window.__closeMgGuide()">
            <i class="fas fa-check"></i> Mengerti!
        </button>
        <button class="mg-skip" onclick="window.__closeMgGuide && window.__closeMgGuide()">
            Lewati
        </button>
    </div>
</div>










<!-- V9.234.0: FINAL POLISH - Sidebar Mobile -->

</head>

<body>
<!-- V9.56.0: Splash Screen -->
<style id="cliporaSplashStyle">
#cliporaSplash {
    position: fixed;
    inset: 0;
    z-index: 999999;
    background: #0a1020;
    background-image: radial-gradient(circle at 50% 40%, rgba(245, 158, 11, 0.15) 0%, transparent 60%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 28px;
    opacity: 1;
    transition: opacity 0.5s ease, visibility 0.5s ease;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}
#cliporaSplash.hide {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
}
.cs-logo-wrap {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 140px;
    height: 140px;
}
.cs-logo-img {
    width: 96px;
    height: 96px;
    object-fit: contain;
    border-radius: 24px;
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.12), rgba(217, 119, 6, 0.05));
    padding: 14px;
    box-shadow: 0 20px 60px rgba(245, 158, 11, 0.4), 0 0 0 2px rgba(245, 158, 11, 0.2);
    animation: csLogoPulse 2s ease-in-out infinite;
}
.cs-logo-wrap::before,
.cs-logo-wrap::after {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: 2px solid rgba(245, 158, 11, 0.4);
    animation: csRipple 2.5s ease-out infinite;
}
.cs-logo-wrap::after {
    animation-delay: 1.25s;
}
@keyframes csLogoPulse {
    0%, 100% { transform: scale(1); box-shadow: 0 20px 60px rgba(245, 158, 11, 0.4), 0 0 0 2px rgba(245, 158, 11, 0.2); }
    50% { transform: scale(1.05); box-shadow: 0 24px 80px rgba(245, 158, 11, 0.6), 0 0 0 4px rgba(245, 158, 11, 0.3); }
}
@keyframes csRipple {
    0% { transform: scale(0.8); opacity: 0.8; }
    100% { transform: scale(1.6); opacity: 0; }
}
.cs-brand-name {
    font-size: 1.8rem;
    font-weight: 800;
    letter-spacing: -0.5px;
    background: linear-gradient(135deg, #fbbf24, #f59e0b, #d97706);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: csTextFade 0.8s ease-out 0.2s both;
}
.cs-tagline {
    font-size: 0.82rem;
    color: #94a3b8;
    letter-spacing: 2px;
    text-transform: uppercase;
    font-weight: 600;
    animation: csTextFade 0.8s ease-out 0.4s both;
}
@keyframes csTextFade {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}
.cs-progress {
    width: 220px;
    height: 3px;
    background: rgba(245, 158, 11, 0.15);
    border-radius: 3px;
    overflow: hidden;
    position: relative;
    animation: csTextFade 0.8s ease-out 0.6s both;
}
.cs-progress-bar {
    height: 100%;
    width: 0%;
    background: linear-gradient(90deg, #f59e0b, #d97706);
    border-radius: 3px;
    box-shadow: 0 0 12px rgba(245, 158, 11, 0.6);
    transition: width 0.3s ease;
}
.cs-loading-text {
    font-size: 0.72rem;
    color: #64748b;
    letter-spacing: 0.5px;
    animation: csTextFade 0.8s ease-out 0.8s both;
}
@media (max-width: 480px) {
    .cs-logo-img { width: 72px; height: 72px; padding: 10px; }
    .cs-logo-wrap { width: 110px; height: 110px; }
    .cs-brand-name { font-size: 1.4rem; }
    .cs-progress { width: 180px; }
}


/* V9.180.0: ADMIN ICON - sembunyikan label di desktop, tampil di mobile */
.admin-text {
    display: inline;
}
@media (max-width: 1100px) {
    .admin-text {
        display: none;
    }
}


/* ============================================================
   V9.180.1: HEADER POLISH - spacing & alignment
   ============================================================ */

/* Nav menu */
.nav-menu {
    gap: 2px !important;
    align-items: center !important;
    display: flex !important;
}

/* Menu link default */
.nav-menu > a {
    padding: 8px 12px !important;
    font-size: 0.86rem !important;
    border-radius: 8px !important;
    transition: all 0.2s ease !important;
    white-space: nowrap !important;
    text-decoration: none !important;
    color: #cbd5e1 !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
}

.nav-menu > a i {
    font-size: 0.9rem !important;
}

/* Hover state */
.nav-menu > a:hover {
    background: rgba(255, 255, 255, 0.05) !important;
    color: #fff !important;
}

/* Active state — orange highlight */
.nav-menu > a.active,
.nav-menu > a.nav-active {
    background: rgba(245, 158, 11, 0.12) !important;
    color: #f59e0b !important;
}

/* Auto-Pro badge */
.nav-menu .ac-badge {
    font-size: 0.58rem !important;
    padding: 2px 5px !important;
    margin-left: 3px !important;
    vertical-align: middle !important;
}

/* Admin link */
.nav-menu .admin-link {
    background: rgba(96, 165, 250, 0.08) !important;
    color: #60a5fa !important;
}
.nav-menu .admin-link:hover {
    background: rgba(96, 165, 250, 0.15) !important;
    color: #93c5fd !important;
}

/* Responsive — tablet */
@media (max-width: 1280px) {
    .nav-menu > a {
        padding: 8px 9px !important;
        font-size: 0.82rem !important;
    }
    .nav-menu > a i {
        font-size: 0.85rem !important;
    }
}

/* Responsive — mobile */
@media (max-width: 900px) {
    .nav-menu > a {
        padding: 7px 8px !important;
        font-size: 0.78rem !important;
    }
}

/* Logo spacing */
.nav-menu + * {
    margin-left: 8px;
}


/* ============================================================
   V9.182.0: SIDEBAR MOBILE FIX - Beranda tidak tertutup header
   ============================================================ */

@media (max-width: 1024px) {
    /* Sidebar mobile - tambah padding top */
    .nav-menu {
        padding-top: 12px !important;
        padding-bottom: 20px !important;
    }
    
    /* Item pertama (Beranda) - margin top biar tidak nempel header */
    .nav-menu > a:first-child {
        margin-top: 8px !important;
        border-top: 1px solid rgba(255, 255, 255, 0.06) !important;
        padding-top: 14px !important;
    }
    
    /* Smooth scroll */
    .nav-menu {
        -webkit-overflow-scrolling: touch !important;
        scroll-behavior: smooth !important;
    }
    
    /* Scrollbar subtle */
    .nav-menu::-webkit-scrollbar {
        width: 4px;
    }
    .nav-menu::-webkit-scrollbar-thumb {
        background: rgba(245, 158, 11, 0.3);
        border-radius: 2px;
    }
}


/* ============================================================
   V9.184.0: SIDEBAR HEADER OFFSET - fix "Beranda" tertutup
   ============================================================ */

@media (max-width: 1024px) {
    .nav-menu {
        /* Padding top = header height (73px) + extra spacing (20px) */
        padding-top: 90px !important;
        margin-top: -90px !important;
        padding-bottom: 40px !important;
    }
    
    /* Sticky header - z-index tinggi biar nutup */
    header, .navbar, .nav-header {
        z-index: 1000 !important;
    }
}
</style>
<div id="cliporaSplash">
    <div class="cs-logo-wrap">
        <img src="/clipora/logo_clipora.png" alt="Clipora" class="cs-logo-img">
    </div>
    <div class="cs-brand-name">Clipora</div>
    <div class="cs-tagline">Create Better Videos with AI</div>
    <div class="cs-progress">
        </div>
    <div class="cs-loading-text" id="csLoadingText">Memuat...</div>
</div>
<script>
(function() {
    "use strict";
    try {
        // Cek kalau sudah tampil di sesi ini → skip
        if (sessionStorage.getItem("clipora_splash_shown") === "1") {
            var existing = document.getElementById("cliporaSplash");
            if (existing) existing.remove();
            var styleEl = document.getElementById("cliporaSplashStyle");
            if (styleEl) styleEl.remove();
            return;
        }
        sessionStorage.setItem("clipora_splash_shown", "1");

        var splash = document.getElementById("cliporaSplash");
        var bar = document.getElementById("csProgressBar");
        var loadingText = document.getElementById("csLoadingText");
        if (!splash || !bar) return;

        // Animasi loading bar
        var steps = [
            { pct: 25, text: "Menyiapkan...", delay: 200 },
            { pct: 55, text: "Memuat aset...", delay: 500 },
            { pct: 85, text: "Hampir selesai...", delay: 900 },
            { pct: 100, text: "Siap!", delay: 1300 }
        ];

        steps.forEach(function(step) {
            setTimeout(function() {
                bar.style.width = step.pct + "%";
                if (loadingText) loadingText.textContent = step.text;
            }, step.delay);
        });

        // Hide setelah selesai
        setTimeout(function() {
            splash.classList.add("hide");
            setTimeout(function() {
                if (splash.parentNode) splash.remove();
                var styleEl = document.getElementById("cliporaSplashStyle");
                if (styleEl) styleEl.remove();
                console.log("[V9.56.0] Splash screen done");
            }, 600);
        }, 1700);

        // Force hide on long load (>5s)
        setTimeout(function() {
            if (splash && splash.parentNode) {
                splash.classList.add("hide");
                setTimeout(function() { if (splash.parentNode) splash.remove(); }, 600);
            }
        }, 5000);
    } catch (e) {
        console.warn("[V9.56.0] Splash error:", e.message);
        var w = document.getElementById("cliporaSplash");
        if (w) w.remove();
    }
})();
</script>


    <style>
        /* ============ NOTIF BELL ============ */
        .notif-wrap {
            position: relative;
            display: inline-flex;
            align-items: center;
        }

        .notif-btn {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border, rgba(255, 255, 255, 0.08));
            color: var(--text-muted, #94a3b8);
            cursor: pointer;
            position: relative;
            transition: 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .notif-btn:hover {
            background: rgba(245, 158, 11, 0.1);
            color: var(--primary, #f59e0b);
            border-color: rgba(245, 158, 11, 0.3);
        }

        .notif-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #ef4444;
            color: #fff;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 10px;
            font-size: 0.65rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #0a0a0f;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        .notif-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            width: 360px;
            max-width: calc(100vw - 20px);
            background: #12121a;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-8px);
            transition: 0.2s;
            z-index: 1000;
            overflow: hidden;
        }

        .notif-dropdown.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .notif-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 18px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            font-weight: 700;
            font-size: 0.9rem;
            color: #e8e8ef;
        }

        .notif-mark-all {
            background: none;
            border: none;
            color: #f59e0b;
            font-size: 0.75rem;
            cursor: pointer;
            font-weight: 600;
            font-family: inherit;
        }

        .notif-mark-all:hover {
            text-decoration: underline;
        }

        .notif-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .notif-item {
            padding: 14px 18px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            gap: 12px;
            position: relative;
        }

        .notif-item:hover {
            background: rgba(245, 158, 11, 0.04);
        }

        .notif-item.unread {
            background: rgba(245, 158, 11, 0.06);
        }

        .notif-item.unread::before {
            content: '';
            position: absolute;
            left: 6px;
            top: 50%;
            transform: translateY(-50%);
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #f59e0b;
        }

        .notif-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 0.9rem;
        }

        .notif-body {
            flex: 1;
            min-width: 0;
        }

        .notif-title {
            font-weight: 600;
            font-size: 0.85rem;
            color: #e8e8ef;
            margin-bottom: 3px;
        }

        .notif-msg {
            font-size: 0.78rem;
            color: #94a3b8;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .notif-time {
            font-size: 0.7rem;
            color: #64748b;
            margin-top: 4px;
        }

        .notif-empty {
            padding: 40px 20px;
            text-align: center;
            color: #94a3b8;
            font-size: 0.85rem;
        }

        .notif-empty i {
            font-size: 2rem;
            opacity: 0.3;
            display: block;
            margin-bottom: 10px;
        }

        /* ============ AUTO-CLIP MENU BADGE ============ */
        .nav-link-ac {
            position: relative;
        }

        .nav-link-ac .ac-badge {
            position: absolute;
            top: 6px;
            right: 6px;
            font-size: 0.5rem;
            font-weight: 800;
            padding: 1px 5px;
            border-radius: 6px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            letter-spacing: 0.4px;
            pointer-events: none;
        }

        @media(max-width:600px) {
            .notif-dropdown {
                width: 300px;
            }
        }

        /* ============================================================ */
/* V18.0.0: FIXED HEADER — TUTUP RUANG KOSONG TOTAL              */
/* ============================================================ */

/* 1. Header: fixed di atas, menutup ruang kosong */
header.header,
body > header.header,
body > header:first-of-type,
header {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    margin: 0 !important;
    padding: 0 !important;
    border-top: 0 !important;
    z-index: 9999 !important;
    background: rgba(10,15,28,0.95) !important;
    backdrop-filter: blur(20px) !important;
}

/* 2. Container di dalam header: padding yang cukup */
header.header > .container,
header > .container,
.header > .container {
    padding: 10px 24px !important;
    margin: 0 !important;
    max-width: 1400px !important;
    margin-left: auto !important;
    margin-right: auto !important;
}

/* 3. Navbar: hapus margin/padding atas */
header nav,
header .nav,
header .navbar {
    margin: 0 !important;
    padding: 0 !important;
}

/* 4. Body: beri padding-top agar konten tidak tertutup header */
body {
    padding-top: 70px !important; /* Sesuaikan dengan tinggi header */
    margin: 0 !important;
}

/* 5. Pastikan main content mulai tepat di bawah header */
body > main,
main {
    margin-top: 0 !important;
    padding-top: 0 !important;
}

/* 6. Mobile: kurangi padding-top body karena header lebih pendek */
@media (max-width: 768px) {
    body {
        padding-top: 62px !important; /* Header mobile biasanya lebih pendek */
    }
    
    header.header > .container,
    header > .container {
        padding: 8px 16px !important;
    }
}

/* 7. Splash screen: pastikan tidak menyisakan ruang */
#cliporaSplash {
    position: fixed !important;
    inset: 0 !important;
    z-index: 999999 !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* 8. Hapus elemen tersembunyi sebelum header */
body > *:not(header):not(main):not(footer):not(script):not(style):not(link):not(meta):not(title):not(noscript) {
    margin: 0 !important;
    padding: 0 !important;
    height: 0 !important;
    min-height: 0 !important;
    overflow: hidden !important;
}

/* ============================================================ */
/* V19.0.0: FINAL POLISH — RAPIKAN SPASI NAVBAR & KONTEN        */
/* ============================================================ */

/* 1. Beri jarak yang cukup antara header dan konten utama */
body {
    padding-top: 75px !important; /* Sedikit lebih tinggi dari tinggi header */
}

/* 2. Rapikan container di dalam header (spasi kiri-kanan) */
header.header > .container,
header > .container,
.header > .container {
    padding: 10px 24px !important;
    max-width: 1200px !important; /* Batasi lebar agar tidak terlalu melebar */
    margin-left: auto !important;
    margin-right: auto !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
}

/* 3. Rapikan navbar di dalam container */
header nav,
header .nav {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    width: 100% !important;
    gap: 16px !important;
}

/* 4. Rapikan logo */
.logo {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    flex-shrink: 0 !important;
    text-decoration: none !important;
}

/* 5. Rapikan nav menu (link-link di kanan) */
.nav-menu {
    display: flex !important;
    align-items: center !important;
    gap: 4px !important;
    margin-left: auto !important;
    flex-wrap: nowrap !important;
}

/* 6. Link nav: padding nyaman */
.nav-menu > a {
    padding: 8px 12px !important;
    border-radius: 8px !important;
    font-size: 0.86rem !important;
    white-space: nowrap !important;
}

/* 7. Mobile: kurangi padding-top body karena header lebih pendek */
@media (max-width: 768px) {
    body {
        padding-top: 65px !important;
    }
    
    header.header > .container,
    header > .container {
        padding: 8px 16px !important;
    }
    
    .nav-menu {
        gap: 2px !important;
    }
    
    .nav-menu > a {
        padding: 6px 8px !important;
        font-size: 0.78rem !important;
    }
}

/* 8. Pastikan konten utama (main) tidak tertutup */
main {
    margin-top: 0 !important;
    padding-top: 20px !important; /* Beri jarak tambahan dari header */
}

/* 9. Hero section: beri jarak atas yang cukup */
.hero {
    padding-top: 30px !important;
}

@media (max-width: 768px) {
    .hero {
        padding-top: 20px !important;
    }
}

/* ============================================================ */
/* V20.0.0: FINAL POLISH — NAVBAR LEBIH LEGA & RAPI             */
/* ============================================================ */

/* 1. Jarak menu di navbar lebih lega */
.nav-menu {
    gap: 8px !important;
}

/* 2. Link menu: padding sedikit lebih nyaman */
.nav-menu > a {
    padding: 10px 16px !important;
    border-radius: 10px !important;
    font-size: 0.88rem !important;
    transition: all 0.2s ease !important;
}

/* 3. Hover state: lebih menonjol */
.nav-menu > a:hover {
    background: rgba(245, 158, 11, 0.12) !important;
    color: #f59e0b !important;
    transform: translateY(-1px) !important;
}

/* 4. Active state: lebih jelas */
.nav-menu > a.active {
    background: rgba(245, 158, 11, 0.18) !important;
    color: #fbbf24 !important;
    font-weight: 700 !important;
}

/* 5. Logo sedikit lebih besar */
.logo-img {
    width: 48px !important;
    height: 48px !important;
}

.logo-text {
    font-size: 1.6rem !important;
}

/* 6. Tombol Admin: lebih menyatu dengan tema emas */
.nav-menu .admin-link {
    background: rgba(245, 158, 11, 0.08) !important;
    color: #fbbf24 !important;
    border: 1px solid rgba(245, 158, 11, 0.2) !important;
}

.nav-menu .admin-link:hover {
    background: rgba(245, 158, 11, 0.18) !important;
    color: #fcd34d !important;
    border-color: rgba(245, 158, 11, 0.4) !important;
}

/* 7. Tombol notifikasi (bell): ukuran lebih pas */
.notif-btn {
    width: 40px !important;
    height: 40px !important;
    border-radius: 10px !important;
}

/* 8. User dropdown: lebih lega */
.user-trigger {
    padding: 8px 14px !important;
    border-radius: 10px !important;
    gap: 8px !important;
}

/* 9. Container header: max-width lebih lega */
header.header > .container {
    max-width: 1300px !important;
    padding: 12px 24px !important;
}

/* 10. Responsive: mobile tetap rapi */
@media (max-width: 768px) {
    .nav-menu {
        gap: 4px !important;
    }
    
    .nav-menu > a {
        padding: 8px 10px !important;
        font-size: 0.8rem !important;
    }
    
    .logo-img {
        width: 40px !important;
        height: 40px !important;
    }
    
    .logo-text {
        font-size: 1.3rem !important;
    }
    
    header.header > .container {
        padding: 10px 16px !important;
    }
}

    </style>

    <!-- ============================================================
     HEADER
     ============================================================ -->

    <header class="header">
        <div class="container">
            <nav class="nav" aria-label="Navigasi utama">

                <!-- LOGO -->
                <a href="index.php" class="logo" aria-label="Clipora Home">
                    <div class="logo-img">
                        <img src="/clipora/logo_clipora.png" alt="Clipora Logo">
                    </div>
                    <div>
                        <span class="logo-text"><?= APP_NAME ?></span>
                        <span class="logo-slogan"><?= APP_TAGLINE ?></span>
                    </div>
                </a>

                <button class="hamburger" id="hamburgerBtn" aria-label="Buka menu" aria-expanded="false">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="nav-menu" id="navMenu">

                    <!-- ============ MENU UTAMA ============ -->
                    <a href="index.php" class="<?= $isActive('index.php') ?>">
                        <i class="fas fa-home"></i> Beranda
                    </a>

                    <?php if ($isLoggedIn): ?>
                        <a href="autoclip.php" class="nav-link-ac <?= $isActive('autoclip.php') ?>">
                            <i class="fas fa-magic"></i> Auto-Clip
                            <span class="ac-badge">PRO</span>
                        </a>

                        <a href="thumbnail-studio-v2.php" class="<?= $isActive('thumbnail-studio-v2.php') ?>">
                            <i class="fas fa-paint-brush"></i> Thumbnail
                        </a>
                        <!-- V9.243.0: Dashboard moved to dropdown -->
                    <a href="projects.php" class="<?= $isActive('projects.php') ?>">
                        <i class="fas fa-folder-open"></i> Proyek
                    </a>
                    <?php endif; ?>

                    <a href="harga.php" class="<?= $isActive('harga.php') ?>">
                        <i class="fas fa-crown"></i> Harga
                    </a>

                    <!-- V9.243.0: Panduan moved to dropdown -->

                    <!-- ============ NOTIFICATION BELL ============ -->
                    <?php if ($isLoggedIn): ?>
                        <div class="notif-wrap" id="notifWrap">
                            <button class="notif-btn" id="notifBtn" onclick="toggleNotif(event)" aria-label="Notifikasi">
                                <i class="fas fa-bell"></i>
                                <span class="notif-badge" id="notifBadge" style="display:none">0</span>
                            </button>
                            <div class="notif-dropdown" id="notifDropdown">
                                <div class="notif-header">
                                    <span>Notifikasi</span>
                                    <button class="notif-mark-all" onclick="markAllRead()">Tandai semua</button>
                                </div>
                                <div class="notif-list" id="notifList">
                                    <div class="notif-empty">Memuat...</div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- ============ ADMIN LINK ============ -->
                    <?php if ($isAdmin): ?>
                        <a href="admin/index.php" class="admin-link <?= $isActive('admin.php') ?>">
                            <i class="fas fa-shield-alt"></i><span class="admin-text"> Admin</span>
                        </a>
                    <?php endif; ?>

                    <!-- ============ AUTH SECTION ============ -->
                    <?php if ($isLoggedIn): ?>
                        <div class="user-dropdown" id="userDropdown">
                            <button class="user-trigger" id="userTrigger" aria-label="Menu user" aria-expanded="false">
                                <i class="fas fa-user-circle"></i>
                                <span class="user-name"><?= htmlspecialchars($userName) ?></span>
                                <span class="plan-badge plan-<?= htmlspecialchars($userPlan) ?>">
                                    <?= strtoupper($userPlan) ?>
                                </span>
                                <i class="fas fa-chevron-down" style="font-size: 0.7rem;"></i>
                            </button>

                            <div class="dropdown-menu" id="dropdownMenu">
                                    <!-- V9.243.0: Consolidated menu -->
                                    <a href="dashboard.php" class="<?= $isActive('dashboard.php') ?>">
                                        <i class="fas fa-tachometer-alt"></i> Dashboard
                                    </a>
                                    <a href="panduan.php" class="<?= $isActive('panduan.php') ?>">
                                        <i class="fas fa-book"></i> Panduan
                                    </a>
                            
                                <a href="style-memory.php" class="<?= $isActive('style-memory.php') ?>">
                                    <i class="fas fa-brain"></i> AI Style Memory
                                </a>
                                <a href="profile.php" class="<?= $isActive('profile.php') ?>">
                                    <i class="fas fa-user-cog"></i> Profil Saya
                                </a>
                                <a href="logout.php" class="text-danger">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn-login">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="register.php" class="btn-register">
                            <i class="fas fa-user-plus"></i> Daftar
                        </a>
                    <?php endif; ?>

                </div>

            </nav>
        </div>
    </header>

    <?php if ($isLoggedIn): ?>
        <!-- ============ NOTIFICATION SCRIPT (V8.8.0 anti-spam) ============ -->
        <script>
            (function() {
                const NOTIF_API = (window.CLIPORA_AUTH && window.CLIPORA_AUTH.apiUrl) || <?= json_encode(API_URL) ?>;
                const NOTIF_TOKEN = (window.CLIPORA_AUTH && window.CLIPORA_AUTH.token) || '';

                const notifBadge = document.getElementById('notifBadge');
                const notifList = document.getElementById('notifList');
                const notifDD = document.getElementById('notifDropdown');
                const notifWrap = document.getElementById('notifWrap');

                if (!notifWrap || !notifDD) return;

                // ============================================================
                // Anti-spam guard
                // ============================================================
                let _notifLoading = false;
                let _lastNotifLoad = 0;
                const NOTIF_MIN_INTERVAL = 30000;

                window.toggleNotif = function(e) {
                    e.stopPropagation();
                    notifDD.classList.toggle('active');
                    if (notifDD.classList.contains('active')) loadNotifications(true);
                };

                document.addEventListener('click', (e) => {
                    if (notifWrap && !notifWrap.contains(e.target)) {
                        notifDD.classList.remove('active');
                    }
                });

                async function loadNotifications(force = false) {
                    const now = Date.now();

                    if (_notifLoading && !force) return;
                    if (!force && now - _lastNotifLoad < NOTIF_MIN_INTERVAL) return;

                    _notifLoading = true;
                    try {
                        const r = await fetch(NOTIF_API + '/user/notifications', {
                            headers: {
                                'Authorization': 'Bearer ' + NOTIF_TOKEN
                            },
                            cache: 'no-store'
                        });
                        const d = await r.json();
                        if (!d.success) return;
                        _lastNotifLoad = Date.now();

                        if (d.unread_count > 0) {
                            notifBadge.textContent = d.unread_count > 99 ? '99+' : d.unread_count;
                            notifBadge.style.display = 'flex';
                        } else {
                            notifBadge.style.display = 'none';
                        }

                        if (d.notifications.length === 0) {
                            notifList.innerHTML = '<div class="notif-empty"><i class="fas fa-bell-slash"></i>Belum ada notifikasi</div>';
                            return;
                        }

                        notifList.innerHTML = d.notifications.map(n => `
                <div class="notif-item ${n.is_read ? '' : 'unread'}" onclick="readNotif(${n.id})">
                    <div class="notif-icon"><i class="fas ${iconFor(n.type)}"></i></div>
                    <div class="notif-body">
                        <div class="notif-title">${escapeHtml(n.title)}</div>
                        <div class="notif-msg">${escapeHtml(n.message || '')}</div>
                        <div class="notif-time">${timeAgo(n.created_at)}</div>
                    </div>
                </div>
            `).join('');
                    } catch (e) {
                        console.error('Load notif error:', e);
                    } finally {
                        _notifLoading = false;
                    }
                }

                window.readNotif = async function(id) {
                    try {
                        await fetch(NOTIF_API + '/user/notifications/' + id + '/read', {
                            method: 'PATCH',
                            headers: {
                                'Authorization': 'Bearer ' + NOTIF_TOKEN
                            }
                        });
                        loadNotifications(true);
                    } catch (e) {}
                };

                window.markAllRead = async function() {
                    try {
                        await fetch(NOTIF_API + '/user/notifications/read-all', {
                            method: 'POST',
                            headers: {
                                'Authorization': 'Bearer ' + NOTIF_TOKEN
                            }
                        });
                        loadNotifications(true);
                    } catch (e) {}
                };

                function iconFor(type) {
                    const map = {
                        'subscription': 'fa-crown',
                        'broadcast': 'fa-bullhorn',
                        'video': 'fa-video',
                        'system': 'fa-cog',
                        'quota': 'fa-tachometer-alt'
                    };
                    return map[type] || 'fa-bell';
                }

                function timeAgo(dateStr) {
                    const now = new Date();
                    const d = new Date(dateStr);
                    const diff = Math.floor((now - d) / 1000);
                    if (diff < 60) return 'Baru saja';
                    if (diff < 3600) return Math.floor(diff / 60) + 'm lalu';
                    if (diff < 86400) return Math.floor(diff / 3600) + 'j lalu';
                    if (diff < 604800) return Math.floor(diff / 86400) + 'h lalu';
                    return d.toLocaleDateString('id-ID');
                }

                function escapeHtml(str) {
                    const div = document.createElement('div');
                    div.textContent = str || '';
                    return div.innerHTML;
                }

                // Load pertama (force)
                loadNotifications(true);

                // Polling 60 detik
                setInterval(() => loadNotifications(false), 60000);

                // Load ulang saat tab jadi aktif
                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden) loadNotifications(false);
                });
            })();
        </script>
    <?php endif; ?>

<script>
(function() {
    'use strict';

    function resetSidebarScroll() {
        var nav = document.getElementById('navMenu');
        if (nav && window.innerWidth <= 1024) {
            nav.scrollTop = 0;
        }
    }

    var hamburger = document.querySelector('.nav-toggle, .hamburger, [id*="toggle"], [aria-label*="menu"]');
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            setTimeout(resetSidebarScroll, 50);
            setTimeout(resetSidebarScroll, 200);
            setTimeout(resetSidebarScroll, 500);
        });
    }

    function showGuide() {
        var overlay = document.getElementById('mgOverlay');
        if (!overlay) return;

        var KEY = 'clipora_mg_guide_shown_v1';
        var isMobile = window.innerWidth <= 1024;
        var hasSeen = false;
        try { hasSeen = !!localStorage.getItem(KEY); } catch(e) {}

        if (isMobile && !hasSeen) {
            setTimeout(function() {
                overlay.classList.add('active');
            }, 1200);
        }

        window.__closeMgGuide = function() {
            overlay.classList.remove('active');
            try { localStorage.setItem(KEY, '1'); } catch(e) {}
        };

        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) window.__closeMgGuide();
        });

        console.log('[V9.226.0] Mobile guide ready');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', showGuide);
    } else {
        showGuide();
    }
})();
</script>


<script>
(function() {
    'use strict';

        function aggressiveResetScroll() {
        if (window.innerWidth > 1024) return;
        var nav = document.getElementById('navMenu');
        if (!nav) return;

        // V9.230.0 FIX: JANGAN blur kalau user sedang ketik di input/textarea/select
        var active = document.activeElement;
        if (active && (
            active.tagName === 'INPUT' ||
            active.tagName === 'TEXTAREA' ||
            active.tagName === 'SELECT' ||
            active.isContentEditable ||
            active.closest('#voiceoverSection') ||
            active.closest('.setting-group') ||
            active.closest('#controlPanel')
        )) {
            return; // Skip blur — user sedang mengetik
        }

        // Blur hanya kalau bukan input
        if (active && active !== document.body) {
            try { active.blur(); } catch(e) {}
        }

        // Force scrollTop = 0
        if (nav.scrollTop !== 0) {
            nav.scrollTop = 0;
        }
    }

    // Reset super aggressive — banyak timing
    [50, 100, 200, 300, 500, 800, 1200, 2000, 3000].forEach(function(delay) {
        setTimeout(aggressiveResetScroll, delay);
    });

        // V9.230.0 FIX: Watchdog interval DIMATIKAN (terlalu agresif, bikin blur terus)
    // var resetInterval = setInterval(aggressiveResetScroll, 500);
    // setTimeout(function() { clearInterval(resetInterval); }, 5000);

        // V9.230.0 FIX: MutationObserver DIMATIKAN (terlalu agresif)
    // if (typeof MutationObserver !== 'undefined') {
    //     var nav = document.getElementById('navMenu');
    //     if (nav) {
    //         var observer = new MutationObserver(aggressiveResetScroll);
    //         observer.observe(nav, { attributes: true, attributeFilter: ['class', 'style'] });
    //     }
    // }

    // Reset saat hamburger diklik
    document.addEventListener('click', function(e) {
        if (e.target.closest('.nav-toggle, .hamburger, [aria-label*="menu"], [id*="toggle"]')) {
            setTimeout(aggressiveResetScroll, 50);
            setTimeout(aggressiveResetScroll, 200);
            setTimeout(aggressiveResetScroll, 500);
        }
    });

    // Disable scroll restoration
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }

    console.log('[V9.229.0] Aggressive reset active');
})();
</script>



<!-- V9.234.0 removed by V9.235.0 -->

<!-- V9.235.0 removed by V9.236.0 -->

