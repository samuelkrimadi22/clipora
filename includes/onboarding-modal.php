<?php
/**
 * includes/onboarding-modal.php
 * Welcome modal untuk user baru — V9.1.0
 * Cuma dirender kalau user login.
 */
if (empty($isLoggedIn)) return;
?>
<!-- ============================================================
     PANDUAN AWAL MODAL (V9.31.0: Ganti text)
     ============================================================ -->
<div class="onboarding-overlay" id="onboardingOverlay" style="display:none;">
    <div class="onboarding-card">
        <div class="onboarding-emoji">🎉</div>

        <h2 class="onboarding-title">Selamat Datang di Clipora!/h2>

        <p class="onboarding-subtitle">
            Dalam 2 menit, Anda akan belajar cara merapikan video
            otomatis dengan AI. Yuk mulai!
        </p>

        <div class="onboarding-checklist">
            <div class="onboarding-check-item">
                <div class="check-icon">1</div>
                <span>Upload & rapikan video pertama</span>
            </div>
            <div class="onboarding-check-item">
                <div class="check-icon">2</div>
                <span>Pakai fitur Auto-Clip untuk bikin banyak klip</span>
            </div>
            <div class="onboarding-check-item">
                <div class="check-icon">3</div>
                <span>Optimasi hasil dengan AI Style Memory</span>
            </div>
        </div>

        <div class="onboarding-actions">
            <button type="button" class="onboarding-btn onboarding-btn-secondary"
                    onclick="window.onboardingSkip && window.onboardingSkip()">
                ⏭️ Lewati
            </button>
            <button type="button" class="onboarding-btn onboarding-btn-primary"
                    onclick="window.onboardingStart && window.onboardingStart()">
                🎬 Mulai Tour
            </button>
        </div>

        <div class="onboarding-progress">
            <div class="onboarding-dot active"></div>
            <div class="onboarding-dot"></div>
            <div class="onboarding-dot"></div>
            <div class="onboarding-dot"></div>
            <div class="onboarding-dot"></div>
        </div>
    </div>
</div>