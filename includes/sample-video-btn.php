<?php
/**
 * includes/sample-video-btn.php
 * Tombol "Coba dengan Sample Video" — V9.1.0
 */
if (empty($isLoggedIn)) return;

$samplePath = __DIR__ . '/../assets/sample/sample.mp4';
if (!file_exists($samplePath)) return;

$sampleSizeMB = round(filesize($samplePath) / 1024 / 1024, 1);
?>
<!-- ============================================================
     SAMPLE VIDEO BUTTON
     ============================================================ -->
<div class="sample-video-wrap" id="sampleVideoWrap" style="display:none;">
    <div class="sample-video-card">
        <div class="sample-video-icon">
            <i class="fas fa-magic"></i>
        </div>
        <div class="sample-video-body">
            <div class="sample-video-title">
                🎬 Belum punya video? <strong>Coba sample!</strong>
            </div>
            <div class="sample-video-desc">
                Kami sudah siapkan video demo <?= $sampleSizeMB ?> MB untuk Anda coba.
                Klik tombol di samping — AI akan langsung analisis.
            </div>
        </div>
        <button type="button" class="sample-video-btn" id="sampleVideoBtn">
            <i class="fas fa-play"></i> Coba Sample Video
        </button>
    </div>
</div>

<style>
.sample-video-wrap {
    margin: 20px auto;
    max-width: 960px;
    animation: sampleSlide 0.4s ease;
}
@keyframes sampleSlide {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}
.sample-video-card {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 20px 24px;
    background: linear-gradient(135deg, rgba(6,182,212,0.08), rgba(139,92,246,0.05));
    border: 1px solid rgba(6,182,212,0.25);
    border-radius: 18px;
    position: relative;
    overflow: hidden;
}
.sample-video-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, #06b6d4, #8b5cf6, transparent);
}
.sample-video-icon {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    background: linear-gradient(135deg, #06b6d4, #8b5cf6);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
    box-shadow: 0 8px 24px rgba(6,182,212,0.3);
}
.sample-video-body { flex: 1; min-width: 0; }
.sample-video-title {
    font-size: 1rem;
    font-weight: 700;
    color: #f1f5f9;
    margin-bottom: 4px;
    letter-spacing: -0.3px;
}
.sample-video-title strong { color: #06b6d4; }
.sample-video-desc {
    font-size: 0.82rem;
    color: #94a3b8;
    line-height: 1.5;
}
.sample-video-btn {
    padding: 12px 22px;
    border-radius: 50px;
    border: none;
    background: linear-gradient(135deg, #06b6d4, #8b5cf6);
    color: white;
    font-size: 0.85rem;
    font-weight: 700;
    cursor: pointer;
    font-family: inherit;
    transition: 0.2s;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 16px rgba(6,182,212,0.35);
}
.sample-video-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(6,182,212,0.5);
}
.sample-video-btn:disabled {
    opacity: 0.6;
    cursor: wait;
}
@media (max-width: 640px) {
    .sample-video-card {
        flex-direction: column;
        text-align: center;
        padding: 20px;
    }
    .sample-video-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>