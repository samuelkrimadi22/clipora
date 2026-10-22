<?php

header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/header.php';

$userPlan = $_SESSION['user_plan'] ?? 'free';
$isPremium = in_array($userPlan, ['pro', 'business', 'weekly', 'monthly']);
$isAdminUser = !empty($_SESSION['is_admin']);

// V8.9.22: Cek guest quota (3 video lifetime per IP)
$guestHasTried = false;
$guestUsedCount = 0;
$guestTotalQuota = 3;
$guestRemaining = 3;
$guestFile = __DIR__ . '/logs/guest_trial.json';
if (file_exists($guestFile)) {
    try {
        $guestData = json_decode(file_get_contents($guestFile), true) ?: [];
        $userIp = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        $userIp = trim(explode(',', $userIp)[0]);
        $userIp = str_replace(['::ffff:', '::1'], ['', 'localhost'], $userIp);

        if (is_array($guestData) && !empty($guestData)) {
            if (array_is_list($guestData)) {
                $guestHasTried = in_array($userIp, $guestData);
                $guestUsedCount = $guestHasTried ? 1 : 0;
            } else {
                $guestUsedCount = (int)($guestData[$userIp] ?? 0);
                $guestHasTried = ($guestUsedCount > 0);
            }
            $guestRemaining = max(0, $guestTotalQuota - $guestUsedCount);
        }
    } catch (Exception $e) {
        $guestHasTried = false;
        $guestUsedCount = 0;
        $guestRemaining = $guestTotalQuota;
    }
}

// V9.38.3: Cookie redirect - handle refresh setelah render
if (isset($_COOKIE['clipora_render_done']) && !empty($_GET['project'])) {
    setcookie('clipora_render_done', '', time() - 3600, '/');
    $cleanUrl = strtok($_SERVER['REQUEST_URI'], '?');
    header('Location: ' . $cleanUrl);
    exit;
}
?>

<!-- ============================================================ -->
<!-- FORCE DEFINE showConfirm DI INDEX.PHP (ANTI RACE CONDITION)  -->
<!-- ============================================================ -->
<script>
window.showConfirm = function(options = {}) {
    return new Promise(function(resolve) {
        var overlay = document.getElementById('confirmOverlay');
        if (!overlay) {
            console.warn('[showConfirm] confirmOverlay tidak ditemukan, fallback ke confirm()');
            resolve(confirm(options.message || 'Konfirmasi?'));
            return;
        }
        
        var title = document.getElementById('confirmTitle');
        var message = document.getElementById('confirmMessage');
        var icon = document.getElementById('confirmIcon');
        var okBtn = document.getElementById('confirmOk');
        var cancelBtn = document.getElementById('confirmCancel');
        
        if (title) title.textContent = options.title || 'Konfirmasi';
        if (message) message.textContent = options.message || 'Apakah Anda yakin?';
        if (icon) {
            var iconMap = { warning: 'fa-exclamation-triangle', danger: 'fa-exclamation-circle', info: 'fa-question-circle' };
            icon.className = 'fas ' + (iconMap[options.type] || iconMap.info);
        }
        if (okBtn) okBtn.textContent = options.okText || 'Ya, Lanjutkan';
        if (cancelBtn) cancelBtn.textContent = options.cancelText || 'Batal';
        
        overlay.classList.add('active');
        
        var cleanup = function() {
            overlay.classList.remove('active');
            if (okBtn) okBtn.removeEventListener('click', onOk);
            if (cancelBtn) cancelBtn.removeEventListener('click', onCancel);
            overlay.removeEventListener('click', onOverlay);
        };
        var onOk = function() { cleanup(); resolve(true); };
        var onCancel = function() { cleanup(); resolve(false); };
        var onOverlay = function(e) { if (e.target === overlay) onCancel(); };
        
        if (okBtn) okBtn.addEventListener('click', onOk);
        if (cancelBtn) cancelBtn.addEventListener('click', onCancel);
        overlay.addEventListener('click', onOverlay);
    });
};
console.log('🛡️ [INDEX.PHP] showConfirm defined inline');
</script>

<!-- ============================================================ -->
<!-- INLINE CSS — KHUSUS INDEX.PHP                                -->
<!-- ============================================================ -->
<style>
/* ============================================ */
/* V15.0.0: INDEX PAGE — 900px (PERMANEN)        */
/* ============================================ */

.index-main {
    max-width: 1400px !important;
    width: 100% !important;
    margin: 0 auto !important;
    padding: 40px 20px 60px !important;
    display: flex !important;
    flex-direction: column !important;
    box-sizing: border-box !important;
}

.index-main .index-role-banner,
.index-main .index-trust-badges {
    max-width: 900px !important;
    margin-left: auto !important;
    margin-right: auto !important;
    box-sizing: border-box !important;
}

.index-main > * {
    max-width: 100% !important;
    box-sizing: border-box !important;
}

@media (max-width: 1024px) {
    .index-main {
        padding: 30px 16px 50px !important;
        max-width: 100% !important;
    }
}

@media (max-width: 640px) {
    .index-main {
        padding: 20px 12px 40px !important;
    }
    .index-main .index-role-banner {
        padding: 20px 16px !important;
    }
}

/* ============ ROLE BANNER ============ */
.index-role-banner {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 24px 28px;
    margin: 0 auto 20px;
    max-width: 960px;
    border-radius: 22px;
    position: relative;
    overflow: hidden;
    animation: roleBannerSlideIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    width: 100%;
    box-sizing: border-box;
}
@keyframes roleBannerSlideIn {
    from { opacity: 0; transform: translateY(-12px); }
    to { opacity: 1; transform: translateY(0); }
}
.index-role-banner::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
}

/* ============ VARIANTS ============ */
.index-role-banner.guest {
    background: linear-gradient(135deg, rgba(245,158,11,0.10), rgba(245,158,11,0.02));
    border: 1px solid rgba(245,158,11,0.30);
    box-shadow: 0 8px 32px rgba(245,158,11,0.08);
}
.index-role-banner.guest::before {
    background: linear-gradient(90deg, transparent, #f59e0b, transparent);
}
.index-role-banner.guest .index-role-icon {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    box-shadow: 0 8px 24px rgba(245,158,11,0.35);
}

.index-role-banner.guest.exhausted {
    background: linear-gradient(135deg, rgba(239,68,68,0.10), rgba(239,68,68,0.02));
    border-color: rgba(239,68,68,0.30);
}
.index-role-banner.guest.exhausted::before {
    background: linear-gradient(90deg, transparent, #ef4444, transparent);
}
.index-role-banner.guest.exhausted .index-role-icon {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    box-shadow: 0 8px 24px rgba(239,68,68,0.35);
    animation: firePulse 1.8s ease-in-out infinite;
}
@keyframes firePulse {
    0%, 100% { box-shadow: 0 8px 24px rgba(239,68,68,0.35); }
    50% { box-shadow: 0 8px 32px rgba(245,158,11,0.55); }
}

.index-role-banner.free {
    background: linear-gradient(135deg, rgba(6,182,212,0.08), rgba(6,182,212,0.02));
    border: 1px solid rgba(6,182,212,0.25);
    box-shadow: 0 8px 32px rgba(6,182,212,0.06);
}
.index-role-banner.free::before {
    background: linear-gradient(90deg, transparent, #06b6d4, transparent);
}
.index-role-banner.free .index-role-icon {
    background: linear-gradient(135deg, #06b6d4, #0891b2);
    box-shadow: 0 8px 24px rgba(6,182,212,0.3);
}

.index-role-banner.premium {
    background: linear-gradient(135deg, rgba(245,158,11,0.15), rgba(245,158,11,0.05));
    border: 1px solid rgba(245,158,11,0.35);
    box-shadow: 0 8px 32px rgba(245,158,11,0.12);
}
.index-role-banner.premium::before {
    background: linear-gradient(90deg, transparent, #f59e0b, #d97706, #f59e0b, transparent);
}
.index-role-banner.premium .index-role-icon {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    box-shadow: 0 8px 24px rgba(245,158,11,0.40);
}

/* ============ ICON ============ */
.index-role-icon {
    width: 62px;
    height: 62px;
    border-radius: 18px;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    flex-shrink: 0;
}

/* ============ BODY ============ */
.index-role-body {
    flex: 1;
    min-width: 0;
}
.index-role-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    line-height: 1.3;
}
.index-role-title strong { font-weight: 800; }
.index-role-banner.guest .index-role-title strong { color: #f59e0b; }
.index-role-banner.guest.exhausted .index-role-title strong { color: #ef4444; }
.index-role-banner.free .index-role-title strong { color: #06b6d4; }
.index-role-banner.premium .index-role-title strong { color: #f59e0b; }

.index-role-desc {
    font-size: 0.84rem;
    color: var(--text-muted);
    line-height: 1.55;
    margin-bottom: 10px;
}
.index-role-desc strong { color: var(--text); }

.index-role-features {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
    font-size: 0.73rem;
    color: var(--text-muted);
    margin-top: 6px;
}
.index-role-features span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.index-role-features i {
    font-size: 0.72rem;
    opacity: 0.9;
}
.index-role-banner.guest .index-role-features i { color: #f59e0b; }
.index-role-banner.guest.exhausted .index-role-features i { color: #ef4444; }
.index-role-banner.free .index-role-features i { color: #06b6d4; }
.index-role-banner.premium .index-role-features i { color: #f59e0b; }

/* ============ BADGE ============ */
.index-role-badge {
    font-size: 0.62rem;
    font-weight: 800;
    letter-spacing: 0.8px;
    padding: 3px 10px;
    border-radius: 20px;
    text-transform: uppercase;
    white-space: nowrap;
}
.index-role-badge.free {
    background: rgba(6,182,212,0.15);
    color: #06b6d4;
    border: 1px solid rgba(6,182,212,0.3);
}
.index-role-badge.premium {
    background: linear-gradient(135deg, rgba(139,92,246,0.2), rgba(245,158,11,0.2));
    color: #fbbf24;
    border: 1px solid rgba(139,92,246,0.4);
}

/* ============ QUOTA BAR ============ */
.index-quota-wrap {
    margin: 12px 0 8px;
    max-width: 320px;
}
.index-quota-bar {
    width: 100%;
    height: 6px;
    background: rgba(255,255,255,0.06);
    border-radius: 10px;
    overflow: hidden;
}
.index-quota-fill {
    height: 100%;
    background: linear-gradient(90deg, #06b6d4, #0891b2);
    border-radius: 10px;
    transition: width 0.4s ease, background 0.3s;
}
.index-quota-fill.warning { background: linear-gradient(90deg, #f59e0b, #d97706); }
.index-quota-fill.danger { background: linear-gradient(90deg, #ef4444, #dc2626); }

/* ============ STYLE MEMORY WIDGET ============ */
.index-style-memory {
    display: none;
    align-items: center;
    gap: 10px;
    margin: 12px 0 8px;
    padding: 10px 14px;
    background: linear-gradient(135deg, rgba(245,158,11,0.15), rgba(6,182,212,0.08));
    border: 1px solid rgba(139,92,246,0.3);
    border-radius: 12px;
    max-width: 400px;
    font-size: 0.78rem;
    color: #c4b5fd;
    animation: styleMemSlide 0.4s ease;
}
@keyframes styleMemSlide {
    from { opacity: 0; transform: translateX(-8px); }
    to { opacity: 1; transform: translateX(0); }
}
.index-style-memory.active {
    display: inline-flex;
}
.index-style-memory i.fa-brain {
    font-size: 1rem;
    color: #a78bfa;
    flex-shrink: 0;
}
.index-style-memory .sm-text {
    flex: 1;
    min-width: 0;
    line-height: 1.4;
}
.index-style-memory .sm-text strong {
    color: #e9d5ff;
    font-weight: 700;
}
.index-style-memory .sm-progress {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.7rem;
    color: #a78bfa;
    opacity: 0.85;
    margin-top: 2px;
}
.index-style-memory .sm-dots {
    display: inline-flex;
    gap: 2px;
}
.index-style-memory .sm-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: rgba(139,92,246,0.25);
    transition: 0.3s;
}
.index-style-memory .sm-dot.filled {
    background: linear-gradient(135deg, #a78bfa, #7c3aed);
    box-shadow: 0 0 6px rgba(139,92,246,0.6);
}

/* ============ ACTIONS ============ */
.index-role-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
    flex-shrink: 0;
    align-items: stretch;
    min-width: 220px;
}

.index-role-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 13px 22px;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 700;
    text-decoration: none;
    transition: 0.25s;
    white-space: nowrap;
    border: none;
    cursor: pointer;
    font-family: inherit;
    position: relative;
    overflow: hidden;
}
.index-role-btn:hover { transform: translateY(-2px); }

.index-role-btn.primary {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
    box-shadow: 0 4px 16px rgba(245,158,11,0.35);
    animation: primaryPulse 2s ease-in-out infinite;
}
@keyframes primaryPulse {
    0%, 100% { box-shadow: 0 4px 16px rgba(245,158,11,0.35); }
    50% { box-shadow: 0 6px 24px rgba(245,158,11,0.55); }
}
.index-role-btn.primary:hover {
    box-shadow: 0 8px 28px rgba(245,158,11,0.6);
    animation: none;
}

.index-role-btn.outline {
    background: transparent;
    border: 1px solid rgba(245,158,11,0.35);
    color: #f59e0b;
    padding: 10px 20px;
    font-size: 0.78rem;
}
.index-role-btn.outline:hover {
    background: rgba(245,158,11,0.1);
    border-color: #f59e0b;
}

.index-role-btn.upgrade {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
    box-shadow: 0 4px 16px rgba(245,158,11,0.35);
}
.index-role-btn.upgrade:hover {
    box-shadow: 0 8px 24px rgba(245,158,11,0.5);
}

.index-role-btn.premium {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
    box-shadow: 0 4px 16px rgba(245,158,11,0.40);
}
.index-role-btn.premium:hover {
    box-shadow: 0 8px 24px rgba(139,92,246,0.5);
}

/* ============ MICRO-COPY ============ */
.index-micro-copy {
    font-size: 0.7rem;
    color: var(--text-muted);
    text-align: center;
    margin-top: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}
.index-micro-copy i {
    color: var(--success);
    font-size: 0.65rem;
}

/* ============ TRUST BADGES ============ */
.index-trust-badges {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 20px;
    max-width: 960px;
    margin: 0 auto 30px;
    padding: 0 20px;
    font-size: 0.72rem;
    color: var(--text-muted);
}
.index-trust-badges span {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.index-trust-badges i {
    color: var(--success);
    font-size: 0.75rem;
}

/* ============ RESPONSIVE ============ */
@media (max-width: 768px) {
    .index-role-banner {
        flex-direction: column;
        text-align: center;
        padding: 24px 20px;
        gap: 16px;
    }
    .index-role-icon {
        width: 56px;
        height: 56px;
        font-size: 1.4rem;
    }
    .index-role-title {
        justify-content: center;
        font-size: 0.98rem;
    }
    .index-role-features {
        justify-content: center;
    }
    .index-quota-wrap {
        margin-left: auto;
        margin-right: auto;
    }
    .index-style-memory {
        margin-left: auto;
        margin-right: auto;
    }
    .index-role-actions {
        width: 100%;
        min-width: unset;
    }
    .index-role-btn {
        width: 100%;
    }
    .index-trust-badges {
        gap: 12px;
        font-size: 0.68rem;
    }
}

/* ============================================================ */
/* V4.0.0: LEBARKAN CONTAINER UTAMA (AMAN & TARGETTED)          */
/* ============================================================ */

@media (min-width: 1024px) {
    main.index-main {
        max-width: 1100px !important;
        width: 100% !important;
        margin: 0 auto !important;
        padding: 40px 20px 60px !important;
    }

    .upload-container,
    .video-preview-wrapper,
    #uploadSection,
    .upload-box,
    .video-container {
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 auto !important;
    }

    .control-panel,
    #controlPanel {
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 auto !important;
        box-sizing: border-box !important;
    }

    .control-panel input[type="text"],
    .control-panel select {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }

    #rapikanBtn {
        width: 100% !important;
        max-width: 100% !important;
        padding: 16px 24px !important;
        font-size: 1.05rem !important;
    }
}

/* ============================================================ */
/* V6.0.0: LEBARKAN PANEL & VIDEO (TANPA MERUSAK POSISI)        */
/* ============================================================ */

@media (min-width: 1024px) {
    main.index-main {
        max-width: 1100px !important;
        width: 100% !important;
        margin: 0 auto !important;
        padding: 40px 20px 60px !important;
    }

    .control-panel,
    #controlPanel,
    video,
    .video-preview,
    .upload-box {
        max-width: 100% !important;
        width: 100% !important;
        margin-left: auto !important;
        margin-right: auto !important;
        box-sizing: border-box !important;
    }

    .control-panel select,
    .control-panel input[type="text"] {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }

    .checkbox-group {
        display: flex !important;
        align-items: center !important;
        width: auto !important;
    }
    
    .checkbox-group input[type="checkbox"] {
        width: 18px !important;
        height: 18px !important;
        flex-shrink: 0 !important;
    }

    #rapikanBtn {
        width: 100% !important;
        max-width: 100% !important;
        padding: 16px 24px !important;
        font-size: 1.05rem !important;
    }
}

/* ============================================ */
/* FIX: MENGUNCI TINGGI KOTAK UPLOAD            */
/* ============================================ */
/* Pastikan container utamanya membungkus dengan rapi */
body, html {
    height: auto !important;
    min-height: 100vh !important;
}

/* Paksa kotak upload (area dashed) untuk tidak melar */
#uploadArea, 
.upload-area {
    max-height: 400px !important; /* Maksimal tinggi 400px */
    height: auto !important;
    min-height: 250px !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: center !important;
    align-items: center !important;
    margin-bottom: 40px !important;
    border: 2px dashed rgba(245, 158, 11, 0.4) !important;
    border-radius: 16px !important;
}

/* Pastikan wadah (container) di atasnya tidak ikut melar */
.main-container, 
.container, 
main {
    height: auto !important;
    min-height: unset !important;
}
</style>

<main class="index-main">

    <!-- ============ SERVER STATUS ============ -->
    <div id="serverStatus" class="server-status online" style="display:none;text-align:center;margin:20px auto;">
        <i class="fas fa-circle"></i> Cek server...
    </div>

    <?php require_once __DIR__ . '/includes/hero.php'; ?>

    <!-- ============================================================ -->
    <!-- ROLE BANNER                                                    -->
    <!-- ============================================================ -->
        <?php if (!$isLoggedIn): ?>
        <!-- ========== GUEST ========== -->
        <div class="index-role-banner guest <?= $guestRemaining <= 0 ? 'exhausted' : '' ?>">
            <div class="index-role-icon guest">
                <i class="fas fa-<?= $guestRemaining <= 0 ? 'fire' : 'gift' ?>"></i>
            </div>
            <div class="index-role-body">
                <?php if ($guestRemaining <= 0): ?>
                    <div class="index-role-title">
                        📥 <strong>Kuota Gratis Anda Habis</strong>
                    </div>
                    <div class="index-role-desc">
                        Anda sudah pakai <strong>3 video gratis</strong>. Daftar <strong>GRATIS 30 detik</strong> → dapat <strong>kuota video harian (durasi 2 menit)</strong> + AI Auto-Cut lengkap + subtitle otomatis.
                    </div>
                    <div class="index-role-features">
                        <span><i class="fas fa-infinity"></i> Gratis selamanya</span>
                    </div>
                <?php elseif ($guestUsedCount > 0): ?>
                    <div class="index-role-title">
                        🎉 <strong>Sisa <?= $guestRemaining ?> Video GRATIS</strong> — dari total <?= $guestTotalQuota ?>
                    </div>
                    <div class="index-role-desc">
                        Anda sudah pakai <strong><?= $guestUsedCount ?> video</strong>. Masih ada <strong><?= $guestRemaining ?> video gratis (maks 1 menit)</strong>. Daftar GRATIS → dapat <strong>3 video/hari (durasi 2 menit)</strong>.
                    </div>
                    <div class="index-role-features">
                        <span><i class="fas fa-infinity"></i> Gratis selamanya</span>
                    </div>
                <?php else: ?>
                    <div class="index-role-title">
                        🎉 <strong>3 Video GRATIS</strong> — Tanpa Daftar, Langsung Coba!
                    </div>
                    <div class="index-role-desc">
                        Coba dulu <strong>3 video (maks 1 menit)</strong>, buktikan hasilnya. Kalau puas, daftar GRATIS → dapat <strong>3 video/hari (durasi 2 menit)</strong>. Tanpa kartu kredit, tanpa ribet.
                    </div>
                    <div class="index-role-features">
                        <span><i class="fas fa-bolt"></i> Mulai dalam 30 detik</span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="index-role-actions">
                <a href="register.php" class="index-role-btn primary">
                    <i class="fas fa-rocket"></i> <?= $guestRemaining <= 0 ? 'Klaim 3 Video/Hari' : ($guestUsedCount > 0 ? 'Daftar, Dapat 3/Hari' : 'Coba Gratis Sekarang') ?>
                </a>
                <div class="index-micro-copy">
                    <i class="fas fa-shield-alt"></i> Tanpa kartu kredit • Gratis selamanya
                </div>
                <a href="harga.php" class="index-role-btn outline">
                    <i class="fas fa-crown"></i> Lihat Paket Premium
                </a>
            </div>
        </div>

        <!-- ========== TRUST BADGES ========== -->
        <div class="index-trust-badges">
            <span><i class="fas fa-bolt"></i> Proses cepat &lt; 2 menit</span>
            <span><i class="fas fa-lock"></i> Video aman & terhapus otomatis</span>
            <span><i class="fas fa-headset"></i> Support WhatsApp 24/7</span>
        </div>

    <?php elseif ($isPremium): ?>
        <!-- ========== PREMIUM USER ========== -->
        <div class="index-role-banner premium">
            <div class="index-role-icon premium">
                <i class="fas fa-crown"></i>
            </div>
            <div class="index-role-body">
                <div class="index-role-title">
                    👑 Anda <strong>PRO</strong> Member
                    <span class="index-role-badge premium">PREMIUM</span>
                </div>
                <div class="index-role-desc">
                    Upload tanpa batas • Prioritas proses • Kualitas hingga 1080p Full HD • Tanpa watermark • <strong>AI Insights lengkap</strong>
                </div>
                <div class="index-role-features">
                    <span><i class="fas fa-bolt"></i> Prioritas Proses</span>
                    <span><i class="fas fa-infinity"></i> Unlimited Upload</span>
                    <span><i class="fas fa-shield-alt"></i> Tanpa Watermark</span>
                    <span><i class="fas fa-sparkles"></i> AI Semantic & Keyword</span>
                </div>

                <div class="index-style-memory" id="styleMemoryWidgetPremium">
                    <i class="fas fa-brain"></i>
                    <div class="sm-text">
                        <span id="smTextPremium">AI belajar dari edit Anda...</span>
                    </div>
                </div>
            </div>
            <div class="index-role-actions">
                <a href="dashboard.php" class="index-role-btn premium">
                    <i class="fas fa-chart-line"></i> Buka Dashboard
                </a>
            </div>
        </div>

    <?php else: ?>
        <!-- ========== FREE USER (LOGIN) ========== -->
        <div class="index-role-banner free">
            <div class="index-role-icon free">
                <i class="fas fa-bolt"></i>
            </div>
            <div class="index-role-body">
                <div class="index-role-title">
                <i class="fas fa-bolt" style="color:#f59e0b;"></i> Paket <strong>FREE</strong> - Kuota Harian
                    <span class="index-role-badge free" id="quotaBadge">3/3 tersisa</span>
                </div>
                <div class="index-role-desc" id="quotaDesc">
                    Memuat kuota...
                </div>
                <div class="index-quota-wrap">
                    <div class="index-quota-bar">
                        <div class="index-quota-fill" id="quotaFill" style="width:100%"></div>
                    </div>
                </div>

                <div class="index-style-memory" id="styleMemoryWidget">
                    <i class="fas fa-brain"></i>
                    <div class="sm-text">
                        <span id="smTextFree">AI belajar dari edit Anda...</span>
                        <div class="sm-progress" id="smProgressFree" style="display:none;">
                            <div class="sm-dots" id="smDotsFree"></div>
                            <span id="smCountFree">0/5 video</span>
                        </div>
                    </div>
                </div>

                <div class="index-role-features">
                    <span><i class="fas fa-clock"></i> Maks <strong>2 menit</strong>/video</span>
                    <span><i class="fas fa-video"></i> Kualitas 720p</span>
                    <span><i class="fas fa-water"></i> Ada Watermark</span>
                </div>
            </div>
            <div class="index-role-actions">
                <a href="harga.php" class="index-role-btn upgrade">
                    <i class="fas fa-crown"></i> Upgrade ke Premium
                </a>
                <div class="index-micro-copy">
                    <i class="fas fa-sparkles"></i> AI Insights + 1080p + Tanpa Watermark
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php require_once __DIR__ . '/includes/sample-video-btn.php'; ?>
    <?php require_once __DIR__ . '/includes/upload.php'; ?>
    <?php require_once __DIR__ . '/includes/control_panel.php'; ?>

<!-- V10.0.0: Progress Box (di luar controlPanel agar tidak ter-hide) -->
<div class="progress-box" id="progressContainer" style="display:none;">
    <div class="progress-bar">
        <div class="progress-fill" id="progressFill">0%</div>
    </div>
    <div class="progress-status">
        <div class="spinner"></div><span id="progressText">Memproses video...</span>
    </div>
    <div class="progress-text" id="progressDetail"></div>
</div>

</main>

<?php require_once __DIR__ . '/includes/onboarding-modal.php'; ?>
<link rel="stylesheet" href="assets/css/onboarding.css">
<script src="assets/js/onboarding.js"></script>
<script src="assets/js/voiceover.js?v=<?= time() ?>"></script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>

<!-- ============================================================ -->
<!-- ✅ INLINE QUOTA + STYLE MEMORY LOADER                        -->
<!-- ============================================================ -->
<?php
if ($isLoggedIn && !$isAdminUser):
?>
<script>
(function() {
    'use strict';

    console.log('🚀 [Inline Loader] Started — Quota + Style Memory');

    const API = '<?= API_URL ?>';
    const TOKEN = (window.CLIPORA_AUTH && window.CLIPORA_AUTH.token) || '';

    if (!TOKEN) {
        console.warn('⚠️ Token tidak ditemukan — user perlu login ulang');
        return;
    }

    async function fetchQuota() {
        try {
            const r = await fetch(API + '/user/quota', {
                headers: { 'Authorization': 'Bearer ' + TOKEN },
                cache: 'no-store'
            });
            if (!r.ok) return null;
            const d = await r.json();
            return d;
        } catch (e) {
            console.error('❌ Fetch quota error:', e);
            return null;
        }
    }

    function updateQuotaUI(quota) {
        const remaining = quota.remaining !== undefined ? quota.remaining : (quota.total - quota.used);
        const total = quota.total || 3;

        const badge = document.getElementById('quotaBadge');
        if (badge) {
            badge.textContent = `${remaining}/${total} tersisa`;
            badge.style.background = remaining === 0 ? 'rgba(239,68,68,0.15)' :
                                     remaining <= 1 ? 'rgba(245,158,11,0.15)' :
                                     'rgba(6,182,212,0.15)';
            badge.style.color = remaining === 0 ? '#ef4444' :
                                remaining <= 1 ? '#f59e0b' :
                                '#06b6d4';
            badge.style.borderColor = remaining === 0 ? 'rgba(239,68,68,0.3)' :
                                      remaining <= 1 ? 'rgba(245,158,11,0.3)' :
                                      'rgba(6,182,212,0.3)';
        }

        const desc = document.getElementById('quotaDesc');
        if (desc) {
            if (remaining === 0) {
                desc.innerHTML = '😍” <strong style="color:#ef4444;">Kuota harian habis!</strong> Reset besok atau <a href="harga.php" style="color:var(--primary);font-weight:600;">upgrade sekarang</a>.';
            } else if (remaining === 1) {
                desc.innerHTML = 'Sisa <strong>1 video</strong> hari ini. <a href="harga.php" style="color:var(--primary);font-weight:600;">Upgrade untuk unlimited</a>.';
            } else {
                desc.innerHTML = `Anda punya <strong>${remaining} video</strong> tersisa hari ini. Reset otomatis setiap tengah malam.`;
            }
        }

        const fill = document.getElementById('quotaFill');
        if (fill) {
            const pct = total > 0 ? (remaining / total) * 100 : 0;
            fill.style.width = pct + '%';
            fill.className = 'index-quota-fill' + (remaining === 0 ? ' danger' : remaining <= 1 ? ' warning' : '');
        }

        const rapikanBtn = document.getElementById('rapikanBtn');
        const rapikanBtnText = document.getElementById('rapikanBtnText');
        if (rapikanBtn) {
            if (remaining <= 0) {
                rapikanBtn.disabled = true;
                rapikanBtn.style.opacity = '0.5';
                rapikanBtn.style.cursor = 'not-allowed';
                rapikanBtn.style.background = 'linear-gradient(135deg, #64748b, #475569)';
                rapikanBtn.style.boxShadow = 'none';
                if (rapikanBtnText) rapikanBtnText.textContent = 'Kuota Habis — Upgrade untuk Lanjut';
                const notice = document.getElementById('quotaExhaustedNotice');
                if (notice) notice.style.display = 'block';
            } else {
                rapikanBtn.disabled = false;
                rapikanBtn.style.opacity = '1';
                rapikanBtn.style.cursor = 'pointer';
                rapikanBtn.style.background = '';
                rapikanBtn.style.boxShadow = '';
                if (rapikanBtnText) rapikanBtnText.textContent = 'Rapikan Video Sekarang';
                const notice = document.getElementById('quotaExhaustedNotice');
                if (notice) notice.style.display = 'none';
            }
        }
    }

    async function fetchStyleMemory() {
        try {
            const r = await fetch(API + '/user/style-memory', {
                headers: { 'Authorization': 'Bearer ' + TOKEN },
                cache: 'no-store'
            });
            if (!r.ok) return null;
            return await r.json();
        } catch (e) {
            console.warn('❌ Fetch style memory error:', e);
            return null;
        }
    }

    function renderStyleMemory(memory) {
        if (!memory) return;

        const totalVideos = memory.total_videos || 0;
        const categories = memory.categories || {};
        const learnedCount = Object.values(categories).filter(c => c.total_shown >= 5).length;

        const widgetFree = document.getElementById('styleMemoryWidget');
        if (widgetFree) {
            if (totalVideos > 0) {
                widgetFree.classList.add('active');

                const textEl = document.getElementById('smTextFree');
                const progEl = document.getElementById('smProgressFree');
                const dotsEl = document.getElementById('smDotsFree');
                const countEl = document.getElementById('smCountFree');

                if (totalVideos >= 5 && learnedCount > 0) {
                    textEl.innerHTML = `<strong>AI sudah belajar dari ${totalVideos} video Anda!</strong> Hasil edit berikutnya akan lebih sesuai selera.`;
                    progEl.style.display = 'none';
                } else {
                    textEl.innerHTML = `<strong>AI sedang belajar gaya edit Anda</strong> — makin banyak video, makin pintar.`;
                    progEl.style.display = 'flex';
                    countEl.textContent = `${totalVideos}/5 video`;

                    dotsEl.innerHTML = '';
                    for (let i = 0; i < 5; i++) {
                        const dot = document.createElement('span');
                        dot.className = 'sm-dot' + (i < totalVideos ? ' filled' : '');
                        dotsEl.appendChild(dot);
                    }
                }
            } else {
                widgetFree.classList.remove('active');
            }
        }

        const widgetPrem = document.getElementById('styleMemoryWidgetPremium');
        if (widgetPrem) {
            if (totalVideos > 0) {
                widgetPrem.classList.add('active');
                const textEl = document.getElementById('smTextPremium');
                if (totalVideos >= 5 && learnedCount > 0) {
                    textEl.innerHTML = `<strong>AI sudah belajar dari ${totalVideos} video Anda!</strong> Hasil edit makin personal.`;
                } else {
                    textEl.innerHTML = `AI belajar gaya edit Anda — <strong>${totalVideos}/5 video</strong>`;
                }
            } else {
                widgetPrem.classList.remove('active');
            }
        }

        console.log(`🧠 [StyleMemory] total=${totalVideos}, learned=${learnedCount}`);
    }

    (async () => {
        const [quotaData, styleData] = await Promise.all([
            fetchQuota(),
            fetchStyleMemory()
        ]);

        if (quotaData && quotaData.success && quotaData.quota) {
            updateQuotaUI(quotaData.quota);
        }

        if (styleData && styleData.success && styleData.memory) {
            renderStyleMemory(styleData.memory);
        }

        setInterval(async () => {
            const freshQuota = await fetchQuota();
            if (freshQuota && freshQuota.success && freshQuota.quota) {
                updateQuotaUI(freshQuota.quota);
            }
            const freshStyle = await fetchStyleMemory();
            if (freshStyle && freshStyle.success && freshStyle.memory) {
                renderStyleMemory(freshStyle.memory);
            }
        }, 30000);
    })();

    window.refreshQuotaInline = async function() {
        const [quotaData, styleData] = await Promise.all([fetchQuota(), fetchStyleMemory()]);
        if (quotaData && quotaData.success && quotaData.quota) updateQuotaUI(quotaData.quota);
        if (styleData && styleData.success && styleData.memory) renderStyleMemory(styleData.memory);
    };
})();
</script>
<?php endif; ?>

<!-- V9.40.1: Cleanup no project -->
<script>
(function() {
    try {
        var _urlP = new URLSearchParams(window.location.search).get('project');
        if (!_urlP) {
            console.log('[V9.40.1] No project - cleanup modal & state');
            ['downloadModal', 'acDownloadNotif'].forEach(function(id) {
                var el = document.getElementById(id);
                if (el) {
                    el.style.display = 'none';
                    if (id === 'acDownloadNotif') el.remove();
                }
            });
            ['resultBox', 'renderProgress', 'progressBox'].forEach(function(id) {
                var el = document.getElementById(id);
                if (el) el.style.display = 'none';
            });
            try {
                sessionStorage.removeItem('clipora_render_done');
                sessionStorage.removeItem('clipora_just_downloaded');
            } catch (e) {}
        }
    } catch (e) {}
})();
</script>