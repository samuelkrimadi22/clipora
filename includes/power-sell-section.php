<?php
/**
 * ============================================================
 * V9.3.0: POWER SELL SECTION
 * Section konversi tinggi — value stack, comparison, live activity, guarantee
 * Include di harga.php atau landing page kapan saja
 * ============================================================
 *
 * Pemakaian:
 *   <?php require_once __DIR__ . '/includes/power-sell-section.php'; ?>
 *
 * Parameter (opsional):
 *   $psMonthlyPrice    = 35000   // harga normal
 *   $psDiscountPrice   = 25000   // harga promo (kalau ada)
 *   $psWhatsapp        = '6282398106023'
 */

// Fallback — pakai settingan dari parent kalau tidak di-set
$psMonthlyPrice  = $psMonthlyPrice  ?? ($appSettings['price_monthly'] ?? 35000);
$psDiscountPrice = $psDiscountPrice ?? ($appSettings['early_price_monthly'] ?? 0);
$psWhatsapp      = $psWhatsapp      ?? ($appSettings['whatsapp_admin'] ?? '6282398106023');

$psEffectivePrice = ($psDiscountPrice > 0 && $psDiscountPrice < $psMonthlyPrice)
    ? $psDiscountPrice
    : $psMonthlyPrice;

$psHasPromo = $psEffectivePrice < $psMonthlyPrice;

$formatRp = fn($n) => number_format((int)$n, 0, ',', '.');
?>

<style>
/* ============================================================ */
/* POWER SELL SECTION — V9.3.0                                   */
/* ============================================================ */
.ps-section {
    max-width: 1000px;
    margin: 60px auto;
    padding: 0 20px;
}

/* ============ HEADER ============ */
.ps-header {
    text-align: center;
    margin-bottom: 40px;
}
.ps-header-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, rgba(245,158,11,0.15), rgba(239,68,68,0.08));
    border: 1px solid rgba(245,158,11,0.35);
    padding: 6px 18px;
    border-radius: 30px;
    font-size: 0.75rem;
    font-weight: 800;
    color: #fbbf24;
    margin-bottom: 16px;
    letter-spacing: 0.5px;
    animation: psPulse 2s ease-in-out infinite;
}
@keyframes psPulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(245,158,11,0.3); }
    50% { box-shadow: 0 0 0 8px rgba(245,158,11,0); }
}
.ps-header h2 {
    font-size: 2rem;
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 12px;
    letter-spacing: -0.8px;
}
.ps-header h2 span {
    background: linear-gradient(135deg, #fbbf24, #f59e0b, #ef4444);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.ps-header p {
    font-size: 0.95rem;
    color: var(--text-muted, #94a3b8);
    max-width: 620px;
    margin: 0 auto;
    line-height: 1.6;
}

/* ============ VALUE STACK ============ */
.ps-value-stack {
    background: linear-gradient(135deg, rgba(139,92,246,0.06), rgba(6,182,212,0.03));
    border: 1px solid rgba(139,92,246,0.25);
    border-radius: 20px;
    padding: 32px 28px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.ps-value-stack::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, #8b5cf6, #06b6d4, #f59e0b);
}
.ps-vs-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.1rem;
    font-weight: 800;
    color: #e8e8ef;
    margin-bottom: 20px;
}
.ps-vs-title i { color: #a78bfa; font-size: 1.1rem; }

.ps-vs-list {
    list-style: none;
    padding: 0;
    margin: 0 0 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.ps-vs-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: rgba(0,0,0,0.2);
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.05);
    transition: 0.2s;
}
.ps-vs-item:hover {
    background: rgba(139,92,246,0.06);
    border-color: rgba(139,92,246,0.3);
    transform: translateX(4px);
}
.ps-vs-item-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: linear-gradient(135deg, rgba(139,92,246,0.15), rgba(6,182,212,0.1));
    color: #a78bfa;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1rem;
}
.ps-vs-item-body {
    flex: 1;
    min-width: 0;
}
.ps-vs-item-name {
    font-size: 0.88rem;
    font-weight: 700;
    color: #e8e8ef;
    margin-bottom: 2px;
}
.ps-vs-item-desc {
    font-size: 0.75rem;
    color: #94a3b8;
    line-height: 1.4;
}
.ps-vs-item-price {
    text-align: right;
    flex-shrink: 0;
    padding-left: 12px;
}
.ps-vs-price-old {
    font-size: 0.82rem;
    color: #64748b;
    text-decoration: line-through;
    font-weight: 600;
}
.ps-vs-price-real {
    font-size: 0.7rem;
    color: #4ade80;
    font-weight: 700;
    display: block;
    margin-top: 2px;
}

.ps-vs-total {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 18px 20px;
    background: linear-gradient(135deg, rgba(34,197,94,0.08), rgba(6,182,212,0.04));
    border: 1px solid rgba(34,197,94,0.25);
    border-radius: 14px;
    flex-wrap: wrap;
}
.ps-vs-total-label {
    font-size: 0.82rem;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 700;
}
.ps-vs-total-num {
    font-size: 1.6rem;
    font-weight: 800;
    color: #4ade80;
    letter-spacing: -0.5px;
}
.ps-vs-total-num .ps-currency {
    font-size: 1rem;
    font-weight: 700;
    vertical-align: super;
}
.ps-vs-save {
    padding: 8px 16px;
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: white;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 800;
    box-shadow: 0 4px 14px rgba(34,197,94,0.4);
    white-space: nowrap;
}

/* ============ COMPARISON TABLE ============ */
.ps-comparison {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 20px;
    padding: 24px;
    margin-bottom: 24px;
    overflow-x: auto;
}
.ps-comp-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.05rem;
    font-weight: 800;
    color: #e8e8ef;
    margin-bottom: 20px;
}
.ps-comp-title i { color: #f59e0b; }

.ps-comp-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    min-width: 480px;
}
.ps-comp-table th,
.ps-comp-table td {
    padding: 14px 12px;
    text-align: center;
    font-size: 0.85rem;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.ps-comp-table thead th {
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #94a3b8;
    font-weight: 700;
    padding-bottom: 16px;
    border-bottom: 2px solid rgba(255,255,255,0.08);
}
.ps-comp-table thead th.ps-comp-pro {
    background: linear-gradient(180deg, rgba(245,158,11,0.1), transparent);
    color: #fbbf24;
    border-radius: 12px 12px 0 0;
    position: relative;
}
.ps-comp-table thead th.ps-comp-pro::after {
    content: '⭐';
    position: absolute;
    top: 8px;
    right: 12px;
    font-size: 0.7rem;
}
.ps-comp-table tbody td:first-child {
    text-align: left;
    color: #cbd5e1;
    font-weight: 600;
    padding-left: 8px;
}
.ps-comp-table tbody td.ps-comp-pro {
    background: rgba(245,158,11,0.03);
    color: #e8e8ef;
    font-weight: 700;
}
.ps-comp-table tbody tr:hover td {
    background: rgba(255,255,255,0.02);
}
.ps-comp-table tbody tr:hover td.ps-comp-pro {
    background: rgba(245,158,11,0.06);
}
.ps-comp-check {
    color: #22c55e;
    font-size: 1rem;
}
.ps-comp-x {
    color: #475569;
    font-size: 0.9rem;
}

/* ============ LIVE ACTIVITY ============ */
.ps-live {
    background: linear-gradient(135deg, rgba(6,182,212,0.06), rgba(139,92,246,0.04));
    border: 1px solid rgba(6,182,212,0.2);
    border-radius: 16px;
    padding: 20px 22px;
    margin-bottom: 24px;
}
.ps-live-head {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
    font-size: 0.85rem;
    font-weight: 700;
    color: #e8e8ef;
}
.ps-live-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #22c55e;
    box-shadow: 0 0 10px #22c55e;
    animation: psLivePulse 1.5s ease-in-out infinite;
    flex-shrink: 0;
}
@keyframes psLivePulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(0.85); }
}
.ps-live-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.ps-live-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    background: rgba(0,0,0,0.2);
    border-radius: 10px;
    font-size: 0.8rem;
    color: #cbd5e1;
    animation: psLiveFadeIn 0.5s ease;
    border-left: 2px solid rgba(34,197,94,0.5);
}
@keyframes psLiveFadeIn {
    from { opacity: 0; transform: translateX(-8px); }
    to { opacity: 1; transform: translateX(0); }
}
.ps-live-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: linear-gradient(135deg, #06b6d4, #8b5cf6);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 800;
    flex-shrink: 0;
}
.ps-live-text strong {
    color: #e8e8ef;
    font-weight: 700;
}
.ps-live-time {
    font-size: 0.7rem;
    color: #64748b;
    margin-left: auto;
    flex-shrink: 0;
}

/* ============ GUARANTEE ============ */
.ps-guarantee {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 24px 28px;
    background: linear-gradient(135deg, rgba(34,197,94,0.08), rgba(6,182,212,0.04));
    border: 1.5px solid rgba(34,197,94,0.3);
    border-radius: 20px;
    margin-bottom: 24px;
    flex-wrap: wrap;
    position: relative;
    overflow: hidden;
}
.ps-guarantee::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 20% 50%, rgba(34,197,94,0.08), transparent 60%);
    pointer-events: none;
}
.ps-guarantee-badge {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 8px 28px rgba(34,197,94,0.4);
    position: relative;
    z-index: 1;
}
.ps-guarantee-badge i { font-size: 1.3rem; }
.ps-guarantee-badge span {
    font-size: 0.55rem;
    font-weight: 800;
    margin-top: 2px;
    letter-spacing: 0.5px;
}
.ps-guarantee-body { flex: 1; min-width: 200px; position: relative; z-index: 1; }
.ps-guarantee-body h4 {
    font-size: 1.05rem;
    font-weight: 800;
    color: #e8e8ef;
    margin-bottom: 6px;
}
.ps-guarantee-body h4 strong { color: #4ade80; }
.ps-guarantee-body p {
    font-size: 0.85rem;
    color: #94a3b8;
    line-height: 1.55;
    margin: 0;
}

/* ============ CTA FINAL ============ */
.ps-cta {
    text-align: center;
    padding: 36px 28px;
    background: linear-gradient(135deg, rgba(245,158,11,0.1), rgba(239,68,68,0.05));
    border: 1.5px solid rgba(245,158,11,0.35);
    border-radius: 22px;
    position: relative;
    overflow: hidden;
}
.ps-cta::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 50% 0%, rgba(245,158,11,0.1), transparent 60%);
    pointer-events: none;
}
.ps-cta-price {
    font-size: 0.85rem;
    color: #94a3b8;
    margin-bottom: 6px;
    position: relative;
    z-index: 1;
}
.ps-cta-price s { color: #64748b; }
.ps-cta-amount {
    font-size: 2.6rem;
    font-weight: 800;
    color: #fbbf24;
    letter-spacing: -1.5px;
    line-height: 1;
    margin-bottom: 8px;
    position: relative;
    z-index: 1;
}
.ps-cta-amount .ps-currency {
    font-size: 1.2rem;
    font-weight: 700;
    vertical-align: super;
    margin-right: 2px;
}
.ps-cta-amount .ps-period {
    font-size: 0.9rem;
    color: #94a3b8;
    font-weight: 500;
    margin-left: 4px;
}
.ps-cta h3 {
    font-size: 1.15rem;
    font-weight: 700;
    color: #e8e8ef;
    margin-bottom: 20px;
    position: relative;
    z-index: 1;
}
.ps-cta-buttons {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
    position: relative;
    z-index: 1;
}
.ps-cta-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 15px 32px;
    border-radius: 50px;
    font-size: 0.92rem;
    font-weight: 800;
    text-decoration: none;
    transition: 0.25s;
    border: none;
    cursor: pointer;
    font-family: inherit;
    white-space: nowrap;
    letter-spacing: 0.3px;
}
.ps-cta-btn.primary {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
    box-shadow: 0 6px 24px rgba(245,158,11,0.45);
    animation: psPrimaryPulse 2s ease-in-out infinite;
}
@keyframes psPrimaryPulse {
    0%, 100% { box-shadow: 0 6px 24px rgba(245,158,11,0.45); }
    50% { box-shadow: 0 8px 32px rgba(245,158,11,0.7); }
}
.ps-cta-btn.primary:hover {
    transform: translateY(-2px);
    animation: none;
    box-shadow: 0 12px 36px rgba(245,158,11,0.7);
}
.ps-cta-btn.outline {
    background: transparent;
    border: 1.5px solid rgba(245,158,11,0.4);
    color: #fbbf24;
}
.ps-cta-btn.outline:hover {
    background: rgba(245,158,11,0.1);
    border-color: #f59e0b;
}

.ps-cta-note {
    margin-top: 14px;
    font-size: 0.72rem;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    flex-wrap: wrap;
    position: relative;
    z-index: 1;
}
.ps-cta-note span { display: inline-flex; align-items: center; gap: 4px; }
.ps-cta-note i { color: #22c55e; }

/* ============ RESPONSIVE ============ */
@media (max-width: 640px) {
    .ps-section { margin: 40px auto; }
    .ps-header h2 { font-size: 1.5rem; }
    .ps-value-stack { padding: 24px 18px; }
    .ps-vs-total { padding: 14px 16px; }
    .ps-vs-total-num { font-size: 1.3rem; }
    .ps-guarantee { padding: 20px; gap: 14px; }
    .ps-guarantee-badge { width: 60px; height: 60px; }
    .ps-guarantee-badge i { font-size: 1rem; }
    .ps-cta { padding: 28px 20px; }
    .ps-cta-amount { font-size: 2rem; }
    .ps-cta-buttons { flex-direction: column; }
    .ps-cta-btn { width: 100%; justify-content: center; }
}
</style>

<section class="ps-section">

    <!-- ============ HEADER ============ -->
    <div class="ps-header">
        <div class="ps-header-badge">
            <i class="fas fa-fire"></i>
            KENAPA HARUS UPGRADE SEKARANG
        </div>
        <h2>Berhenti Buang <span>Waktu & Uang</span> untuk Editing Manual</h2>
        <p>Kalau Anda beli tools ini terpisah-pisah, totalnya bisa <strong>Rp 1.5 juta/bulan</strong>. Di Clipora, semua jadi satu dengan harga segelas kopi.</p>
    </div>

    <!-- ============ VALUE STACK ============ -->
    <div class="ps-value-stack">
        <div class="ps-vs-title">
            <i class="fas fa-layer-group"></i>
            Kalau Beli Terpisah, Ini yang Anda Butuhkan
        </div>
        <ul class="ps-vs-list">
            <li class="ps-vs-item">
                <div class="ps-vs-item-icon"><i class="fas fa-scissors"></i></div>
                <div class="ps-vs-item-body">
                    <div class="ps-vs-item-name">Auto-Cut Video (AI)</div>
                    <div class="ps-vs-item-desc">Potong jeda, filler, stutter otomatis</div>
                </div>
                <div class="ps-vs-item-price">
                    <div class="ps-vs-price-old">Rp 300.000</div>
                    <span class="ps-vs-price-real">GRATIS</span>
                </div>
            </li>
            <li class="ps-vs-item">
                <div class="ps-vs-item-icon"><i class="fas fa-closed-captioning"></i></div>
                <div class="ps-vs-item-body">
                    <div class="ps-vs-item-name">Auto Subtitle (Whisper AI)</div>
                    <div class="ps-vs-item-desc">Transkripsi otomatis + burn ke video</div>
                </div>
                <div class="ps-vs-item-price">
                    <div class="ps-vs-price-old">Rp 250.000</div>
                    <span class="ps-vs-price-real">GRATIS</span>
                </div>
            </li>
            <li class="ps-vs-item">
                <div class="ps-vs-item-icon"><i class="fas fa-magic"></i></div>
                <div class="ps-vs-item-body">
                    <div class="ps-vs-item-name">Auto-Clip Generator</div>
                    <div class="ps-vs-item-desc">1 video panjang → 10 klip TikTok/Reels</div>
                </div>
                <div class="ps-vs-item-price">
                    <div class="ps-vs-price-old">Rp 400.000</div>
                    <span class="ps-vs-price-real">GRATIS</span>
                </div>
            </li>
            <li class="ps-vs-item">
                <div class="ps-vs-item-icon"><i class="fas fa-microphone-alt"></i></div>
                <div class="ps-vs-item-body">
                    <div class="ps-vs-item-name">AI Voice Over</div>
                    <div class="ps-vs-item-desc">6 suara premium (ID, EN, JP, CN)</div>
                </div>
                <div class="ps-vs-item-price">
                    <div class="ps-vs-price-old">Rp 150.000</div>
                    <span class="ps-vs-price-real">GRATIS</span>
                </div>
            </li>
            <li class="ps-vs-item">
                <div class="ps-vs-item-icon"><i class="fas fa-language"></i></div>
                <div class="ps-vs-item-body">
                    <div class="ps-vs-item-name">Multi-bahasa Translate</div>
                    <div class="ps-vs-item-desc">15 bahasa untuk subtitle global</div>
                </div>
                <div class="ps-vs-item-price">
                    <div class="ps-vs-price-old">Rp 200.000</div>
                    <span class="ps-vs-price-real">GRATIS</span>
                </div>
            </li>
            <li class="ps-vs-item">
                <div class="ps-vs-item-icon"><i class="fas fa-brain"></i></div>
                <div class="ps-vs-item-body">
                    <div class="ps-vs-item-name">AI Style Memory</div>
                    <div class="ps-vs-item-desc">AI belajar gaya edit Anda</div>
                </div>
                <div class="ps-vs-item-price">
                    <div class="ps-vs-price-old">Rp 200.000</div>
                    <span class="ps-vs-price-real">GRATIS</span>
                </div>
            </li>
        </ul>

        <div class="ps-vs-total">
            <div>
                <div class="ps-vs-total-label">Total Nilai Sebenarnya</div>
                <div class="ps-vs-total-num">
                    <span class="ps-currency">Rp</span>1.500.000
                    <span style="font-size:0.72rem;color:#94a3b8;font-weight:500;">/bulan</span>
                </div>
            </div>
            <div class="ps-vs-save">
                🎉 Hemat 97% dengan Clipora
            </div>
        </div>
    </div>

    <!-- ============ COMPARISON TABLE ============ -->
    <div class="ps-comparison">
        <div class="ps-comp-title">
            <i class="fas fa-balance-scale"></i>
            Bandingkan Gratis vs Pro
        </div>
        <table class="ps-comp-table">
            <thead>
                <tr>
                    <th style="text-align:left;">Fitur</th>
                    <th>Gratis</th>
                    <th class="ps-comp-pro">Pro</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Kuota video</td>
                    <td>3 / hari</td>
                    <td class="ps-comp-pro">30 / hari</td>
                </tr>
                <tr>
                    <td>Durasi maks per video</td>
                    <td>2 menit</td>
                    <td class="ps-comp-pro">10 menit</td>
                </tr>
                <tr>
                    <td>Kualitas export</td>
                    <td>720p HD</td>
                    <td class="ps-comp-pro">1080p Full HD</td>
                </tr>
                <tr>
                    <td>Watermark</td>
                    <td><span class="ps-comp-x">❌ Ada</span></td>
                    <td class="ps-comp-pro"><span class="ps-comp-check">✅ Tanpa</span></td>
                </tr>
                <tr>
                    <td>Auto-Clip Generator</td>
                    <td><span class="ps-comp-x">❌</span></td>
                    <td class="ps-comp-pro"><span class="ps-comp-check">✅</span></td>
                </tr>
                <tr>
                    <td>AI Voice Over</td>
                    <td><span class="ps-comp-x">❌</span></td>
                    <td class="ps-comp-pro"><span class="ps-comp-check">✅</span></td>
                </tr>
                <tr>
                    <td>Multi-bahasa Translate</td>
                    <td><span class="ps-comp-x">❌</span></td>
                    <td class="ps-comp-pro"><span class="ps-comp-check">✅</span></td>
                </tr>
                <tr>
                    <td>AI Style Memory</td>
                    <td><span class="ps-comp-x">❌</span></td>
                    <td class="ps-comp-pro"><span class="ps-comp-check">✅</span></td>
                </tr>
                <tr>
                    <td>Prioritas proses</td>
                    <td><span class="ps-comp-x">❌</span></td>
                    <td class="ps-comp-pro"><span class="ps-comp-check">✅</span></td>
                </tr>
                <tr>
                    <td>Harga</td>
                    <td>Rp 0</td>
                    <td class="ps-comp-pro">
                        <?php if ($psHasPromo): ?>
                            <span style="color:#64748b;text-decoration:line-through;font-size:0.78rem;">Rp <?= $formatRp($psMonthlyPrice) ?></span>
                            <br>Rp <?= $formatRp($psEffectivePrice) ?>/bln
                        <?php else: ?>
                            Rp <?= $formatRp($psEffectivePrice) ?>/bln
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- ============ LIVE ACTIVITY ============ -->
    <div class="ps-live">
        <div class="ps-live-head">
            <div class="ps-live-dot"></div>
            <span>Aktivitas Real-time</span>
        </div>
        <div class="ps-live-list" id="psLiveList">
            <!-- Diisi dari JS -->
        </div>
    </div>

    <!-- ============ GUARANTEE ============ -->
    <div class="ps-guarantee">
        <div class="ps-guarantee-badge">
            <i class="fas fa-shield-alt"></i>
            <span>100%</span>
        </div>
        <div class="ps-guarantee-body">
            <h4>Garansi <strong>7 Hari Uang Kembali</strong></h4>
            <p>Coba Clipora Pro selama 7 hari. Kalau tidak puas — untuk alasan apapun — kami kembalikan uang Anda 100%. Tanpa pertanyaan, tanpa drama.</p>
        </div>
    </div>

    <!-- ============ CTA FINAL ============ -->
    <div class="ps-cta">
        <?php if ($psHasPromo): ?>
            <div class="ps-cta-price">
                Harga normal: <s>Rp <?= $formatRp($psMonthlyPrice) ?>/bulan</s>
            </div>
        <?php endif; ?>
        <div class="ps-cta-amount">
            <span class="ps-currency">Rp</span><?= $formatRp($psEffectivePrice) ?><span class="ps-period">/bulan</span>
        </div>
        <h3>Siap Mulai Posting Konsisten?</h3>
        <div class="ps-cta-buttons">
            <button class="ps-cta-btn primary" onclick="document.querySelector('.pricing-grid')?.scrollIntoView({behavior:'smooth',block:'center'})">
                <i class="fas fa-crown"></i> Ambil Harga Ini Sekarang
            </button>
            <a href="https://wa.me/<?= htmlspecialchars($psWhatsapp) ?>?text=Halo,%20saya%20mau%20tanya%20soal%20Clipora%20Pro"
               target="_blank" rel="noopener" class="ps-cta-btn outline">
                <i class="fab fa-whatsapp"></i> Tanya Dulu
            </a>
        </div>
        <div class="ps-cta-note">
            <span><i class="fas fa-check-circle"></i> Tanpa kontrak</span>
            <span><i class="fas fa-check-circle"></i> Cancel kapan saja</span>
            <span><i class="fas fa-check-circle"></i> Aktivasi instan</span>
        </div>
    </div>

</section>

<script>
(function() {
    'use strict';

    // ============================================================
    // LIVE ACTIVITY FEED (social proof)
    // ============================================================
    const NAMES = [
        { name: 'Andi', city: 'Jakarta' },
        { name: 'Sinta', city: 'Bandung' },
        { name: 'Budi', city: 'Surabaya' },
        { name: 'Maya', city: 'Yogyakarta' },
        { name: 'Rendra', city: 'Medan' },
        { name: 'Dewi', city: 'Semarang' },
        { name: 'Fajar', city: 'Makassar' },
        { name: 'Lina', city: 'Palembang' },
        { name: 'Adi', city: 'Bekasi' },
        { name: 'Nina', city: 'Tangerang' }
    ];

    const ACTIONS = [
        'baru saja upgrade ke Pro',
        'memproses 3 video sekaligus',
        'memakai Auto-Clip Generator',
        'download 10 klip TikTok',
        'save 2 jam editing hari ini',
        'render 5 video full HD'
    ];

    const TIMES = ['baru saja', '1 menit lalu', '3 menit lalu', '5 menit lalu', '8 menit lalu'];

    const list = document.getElementById('psLiveList');
    if (!list) return;

    function randomPick(arr) { return arr[Math.floor(Math.random() * arr.length)]; }

    function renderLiveItem() {
        const person = randomPick(NAMES);
        const action = randomPick(ACTIONS);
        const time = randomPick(TIMES);
        const initial = person.name.charAt(0).toUpperCase();

        const el = document.createElement('div');
        el.className = 'ps-live-item';
        el.innerHTML = `
            <div class="ps-live-avatar">${initial}</div>
            <div class="ps-live-text">
                <strong>${person.name}</strong> dari <strong>${person.city}</strong> ${action}
            </div>
            <div class="ps-live-time">${time}</div>
        `;
        return el;
    }

    // Seed 3 item pertama
    for (let i = 0; i < 3; i++) {
        list.appendChild(renderLiveItem());
    }

    // Rotate — item paling atas dihapus, tambah baru di bawah
    setInterval(() => {
        const items = list.querySelectorAll('.ps-live-item');
        if (items.length >= 3) {
            items[0].style.transition = 'opacity 0.3s, transform 0.3s';
            items[0].style.opacity = '0';
            items[0].style.transform = 'translateX(-20px)';
            setTimeout(() => items[0].remove(), 300);
        }
        setTimeout(() => {
            list.appendChild(renderLiveItem());
        }, 320);
    }, 4000);

    console.log('🔥 [PowerSell] Live activity feed started');
})();
</script>