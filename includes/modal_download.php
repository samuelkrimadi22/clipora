<?php
// download-page.php — V10.0.0 (Redesign with Info)
require_once __DIR__ . '/config/config.php';

// require_once __DIR__ . '/includes/modal_download.php';   // Dimatikan - pakai download-page.php


// ============================================================
// Ambil info dari URL parameter (dikirim dari main.js)
// ============================================================
$origDur = floatval($_GET['origDur'] ?? 0);
$newDur = floatval($_GET['newDur'] ?? 0);
$silences = intval($_GET['silences'] ?? 0);
$fillerCount = intval($_GET['filler'] ?? 0);
$aiCount = intval($_GET['ai'] ?? 0);
$stutterCount = intval($_GET['stutter'] ?? 0);
$falseStartCount = intval($_GET['falseStart'] ?? 0);
$subtitleBurned = intval($_GET['subtitleBurned'] ?? 0) === 1;
$filesize = floatval($_GET['filesize'] ?? 0);
$filename = $_GET['filename'] ?? '';

$saved = $origDur - $newDur;
$savedPct = $origDur > 0 ? round(($saved / $origDur) * 100) : 0;
$totalRemoved = $silences + $fillerCount + $aiCount + $stutterCount + $falseStartCount;

function fmtDur($sec) {
    if ($sec <= 0) return '-';
    if ($sec < 60) return round($sec, 1) . 's';
    $m = floor($sec / 60);
    $s = round($sec % 60);
    return $m . 'm ' . $s . 's';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Download Video - Clipora</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f0f1e;
            color: #e8e8ef;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            max-width: 520px;
            width: 100%;
            background: #1a1a2e;
            border: 1px solid rgba(139, 92, 246, 0.3);
            border-radius: 16px;
            padding: 0;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            overflow: hidden;
        }
        
        /* ============ HEADER ============ */
        .page-header {
            padding: 24px 24px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }
        
        .header-row {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .header-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: rgba(34, 197, 94, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .header-icon i {
            color: #22c55e;
            font-size: 1.5rem;
        }
        
        .header-text h1 {
            font-size: 1.15rem;
            margin: 0;
            color: #fff;
            font-weight: 700;
        }
        
        .header-text p {
            font-size: 0.8rem;
            color: #94a3b8;
            margin: 4px 0 0;
        }
        
        /* ============ BODY ============ */
        .page-body {
            padding: 20px 24px 24px;
        }
        
        /* ============ INFO BAR ============ */
        .dl-info-bar {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 12px;
            margin-bottom: 14px;
        }
        
        .dl-info-name {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #e8e8ef;
            overflow: hidden;
        }
        
        .dl-info-name i {
            color: #8b5cf6;
            flex-shrink: 0;
        }
        
        .dl-info-name span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .dl-info-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.75rem;
            color: #94a3b8;
            flex-wrap: wrap;
        }
        
        .dl-dot { opacity: 0.4; }
        
        .dl-saved {
            margin-left: auto;
            padding: 3px 10px;
            border-radius: 20px;
            background: rgba(34, 197, 94, 0.12);
            border: 1px solid rgba(34, 197, 94, 0.25);
            color: #22c55e;
            font-weight: 700;
            font-size: 0.7rem;
        }
        
        /* ============ ANALYSIS GRID ============ */
        .dl-analysis {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 16px;
        }
        
        .dl-analysis-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            padding: 10px 6px;
            background: rgba(139, 92, 246, 0.06);
            border: 1px solid rgba(139, 92, 246, 0.12);
            border-radius: 10px;
            font-size: 0.7rem;
            color: #94a3b8;
            text-align: center;
        }
        
        .dl-analysis-item i {
            color: #a78bfa;
            font-size: 0.9rem;
            margin-bottom: 2px;
        }
        
        .dl-analysis-item span {
            font-size: 1rem;
            font-weight: 700;
            color: #e8e8ef;
        }
        
        .dl-analysis-item small {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        
        /* ============ DETAIL BOX ============ */
        .dl-detail {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 16px;
        }
        
        .dl-detail-head {
            font-size: 0.78rem;
            color: #c4b5fd;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .dl-detail-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 0.82rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        }
        
        .dl-detail-row:last-child { border-bottom: none; }
        
        .dl-detail-row .label { color: #94a3b8; }
        .dl-detail-row .value { color: #e8e8ef; font-weight: 600; }
        .dl-detail-row .value.green { color: #4ade80; }
        .dl-detail-row .value.red { color: #f87171; }
        
        /* ============ PRIMARY BUTTON ============ */
        .dl-primary {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 18px;
            background: linear-gradient(135deg, #8b5cf6, #06b6d4);
            border-radius: 14px;
            color: #fff;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 12px;
            box-shadow: 0 4px 16px rgba(139, 92, 246, 0.3);
        }
        
        .dl-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(139, 92, 246, 0.5);
        }
        
        .dl-primary:active { transform: translateY(0); }
        
        .dl-primary-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }
        
        .dl-primary-text {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }
        
        .dl-primary-text strong {
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.2px;
        }
        
        .dl-primary-text small {
            font-size: 0.72rem;
            opacity: 0.9;
        }
        
        .dl-primary-arrow {
            opacity: 0.7;
            font-size: 0.9rem;
            transition: transform 0.2s;
        }
        
        .dl-primary:hover .dl-primary-arrow {
            transform: translateX(4px);
            opacity: 1;
        }
        
        /* ============ SHARE ============ */
        .dl-share {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 12px;
            margin-bottom: 14px;
        }
        
        .dl-share-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
        }
        
        .dl-share-label i { color: #f59e0b; }
        
        .dl-share-icons {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        
        .dl-share-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.04);
            color: #e8e8ef;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            transition: 0.2s;
            padding: 0;
        }
        
        .dl-share-icon:hover {
            transform: translateY(-2px);
            border-color: currentColor;
        }
        
        .dl-share-icon.whatsapp { color: #25D366; }
        .dl-share-icon.whatsapp:hover { background: rgba(37, 211, 102, 0.15); }
        .dl-share-icon.instagram { color: #E4405F; }
        .dl-share-icon.instagram:hover { background: rgba(228, 64, 95, 0.15); }
        .dl-share-icon.facebook { color: #1877F2; }
        .dl-share-icon.facebook:hover { background: rgba(24, 119, 242, 0.15); }
        .dl-share-icon.twitter { color: #1DA1F2; }
        .dl-share-icon.twitter:hover { background: rgba(29, 161, 242, 0.15); }
        .dl-share-icon.tiktok { color: #ff0050; }
        .dl-share-icon.tiktok:hover { background: rgba(255, 0, 80, 0.15); }
        .dl-share-icon.copy { color: #f59e0b; }
        .dl-share-icon.copy:hover { background: rgba(245, 158, 11, 0.15); }
        
        /* ============ PROCESS AGAIN ============ */
        .dl-process-again {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 12px;
            background: rgba(139, 92, 246, 0.06);
            border: 1px solid rgba(139, 92, 246, 0.2);
            border-radius: 12px;
            color: #a78bfa;
            font-size: 0.82rem;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: 0.2s;
            text-decoration: none;
            box-sizing: border-box;
        }
        
        .dl-process-again:hover {
            background: rgba(139, 92, 246, 0.12);
            border-color: rgba(139, 92, 246, 0.4);
            transform: translateY(-1px);
        }
        
        /* ============ RESPONSIVE ============ */
        @media (max-width: 480px) {
            .page-body { padding: 16px; }
            .dl-analysis { gap: 6px; }
            .dl-analysis-item { padding: 8px 4px; font-size: 0.65rem; }
            .dl-analysis-item span { font-size: 0.9rem; }
            .dl-share { flex-direction: column; gap: 10px; align-items: stretch; }
            .dl-share-label { justify-content: center; }
            .dl-share-icons { justify-content: center; }
            .dl-primary { padding: 14px; }
            .dl-primary-icon { width: 38px; height: 38px; font-size: 1rem; }
            .dl-primary-text strong { font-size: 0.88rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- ============================================ -->
        <!-- HEADER -->
        <!-- ============================================ -->
        <div class="page-header">
            <div class="header-row">
                <div class="header-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="header-text">
                    <h1>Video Berhasil Dirapikan!</h1>
                    <p>Download & upload ke sosmed</p>
                </div>
            </div>
        </div>
        
        <!-- ============================================ -->
        <!-- BODY -->
        <!-- ============================================ -->
        <div class="page-body">
            
            <!-- INFO BAR -->
            <div class="dl-info-bar">
                <div class="dl-info-name">
                    <i class="fas fa-file-video"></i>
                    <span><?= htmlspecialchars($filename ?: 'video.mp4') ?></span>
                </div>
                <div class="dl-info-meta">
                    <span><?= $filesize > 0 ? number_format($filesize, 1) . ' MB' : '-' ?></span>
                    <span class="dl-dot">·</span>
                    <span><?= fmtDur($newDur) ?></span>
                    <?php if ($savedPct > 0): ?>
                    <span class="dl-saved">-<?= $savedPct ?>%</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- ANALYSIS GRID -->
            <div class="dl-analysis">
                <div class="dl-analysis-item">
                    <i class="fas fa-volume-mute"></i>
                    <span><?= $silences ?></span>
                    <small>Jeda</small>
                </div>
                <div class="dl-analysis-item">
                    <i class="fas fa-comment-dots"></i>
                    <span><?= $fillerCount ?></span>
                    <small>Filler</small>
                </div>
                <div class="dl-analysis-item">
                    <i class="fas fa-fire"></i>
                    <span><?= $subtitleBurned ? '✓' : '-' ?></span>
                    <small>Subtitle</small>
                </div>
            </div>
            
            <!-- DETAIL ANALISIS -->
            <?php if ($totalRemoved > 0 || $savedPct > 0): ?>
            <div class="dl-detail">
                <div class="dl-detail-head">
                    <i class="fas fa-chart-bar"></i> Detail Analisis AI
                </div>
                <?php if ($origDur > 0): ?>
                <div class="dl-detail-row">
                    <span class="label">⏱️ Durasi Asli</span>
                    <span class="value"><?= fmtDur($origDur) ?></span>
                </div>
                <div class="dl-detail-row">
                    <span class="label">✂️ Dipotong</span>
                    <span class="value red"><?= fmtDur($saved) ?> (<?= $savedPct ?>%)</span>
                </div>
                <div class="dl-detail-row">
                    <span class="label">✅ Hasil Akhir</span>
                    <span class="value green"><?= fmtDur($newDur) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($aiCount > 0): ?>
                <div class="dl-detail-row">
                    <span class="label">🧠 Kata berulang</span>
                    <span class="value"><?= $aiCount ?> item</span>
                </div>
                <?php endif; ?>
                <?php if ($stutterCount > 0): ?>
                <div class="dl-detail-row">
                    <span class="label">🗣️ Gagap</span>
                    <span class="value"><?= $stutterCount ?> item</span>
                </div>
                <?php endif; ?>
                <?php if ($falseStartCount > 0): ?>
                <div class="dl-detail-row">
                    <span class="label">⏸️ Salah mulai</span>
                    <span class="value"><?= $falseStartCount ?> item</span>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- DOWNLOAD BUTTONS -->
            <?php
            $filesParam = $_GET['files'] ?? '';
            $fileList = array_filter(explode('|', $filesParam));
            
            if (!empty($fileList)):
                $idx = 1;
                foreach ($fileList as $file):
                    $fileClean = trim($file);
                    if (empty($fileClean)) continue;
                    
                    $downloadUrl = 'download.php?file=' . urlencode($fileClean);
            ?>
            <a href="<?= $downloadUrl ?>" class="dl-primary" download>
                <div class="dl-primary-icon">
                    <i class="fas fa-download"></i>
                </div>
                <div class="dl-primary-text">
                    <strong>Download Klip #<?= $idx ?></strong>
                    <small><?= htmlspecialchars($fileClean) ?></small>
                </div>
                <i class="fas fa-chevron-right dl-primary-arrow"></i>
            </a>
            <?php
                    $idx++;
                endforeach;
            else:
            ?>
            <div style="padding:20px;text-align:center;color:#94a3b8;font-size:0.85rem;">
                <i class="fas fa-exclamation-triangle" style="font-size:2rem;color:#f59e0b;margin-bottom:8px;display:block;"></i>
                Tidak ada file untuk didownload
            </div>
            <?php endif; ?>
            
            <!-- SHARE -->
            <div class="dl-share">
                <span class="dl-share-label">
                    <i class="fas fa-share-nodes"></i> Bagikan
                </span>
                <div class="dl-share-icons">
                    <button class="dl-share-icon whatsapp" onclick="shareVideo('whatsapp')" title="WhatsApp"><i class="fab fa-whatsapp"></i></button>
                    <button class="dl-share-icon instagram" onclick="shareVideo('instagram')" title="Instagram"><i class="fab fa-instagram"></i></button>
                    <button class="dl-share-icon facebook" onclick="shareVideo('facebook')" title="Facebook"><i class="fab fa-facebook-f"></i></button>
                    <button class="dl-share-icon twitter" onclick="shareVideo('twitter')" title="Twitter"><i class="fab fa-twitter"></i></button>
                    <button class="dl-share-icon tiktok" onclick="shareVideo('tiktok')" title="TikTok"><i class="fab fa-tiktok"></i></button>
                    <button class="dl-share-icon copy" onclick="shareVideo('copy')" title="Copy Caption"><i class="fas fa-copy"></i></button>
                </div>
            </div>
            
            <!-- PROCESS AGAIN -->
            <a href="index.php" class="dl-process-again">
                <i class="fas fa-rotate"></i>
                <span>Proses Video Lain</span>
            </a>
            
        </div>
    </div>
    
    <script>
        // ============================================================
        // SHARE VIDEO
        // ============================================================
        function generateCaption() {
            var appUrl = window.location.origin;
            var templates = [
                'Baru selesai edit video pakai Clipora! Hasilnya rapi banget — jeda hilang, subtitle otomatis. Coba gratis di ' + appUrl + ' 🎬',
                'Gila, Clipora bikin video saya rapi dalam 2 menit! ✂️ Tanpa skill editing. Coba di ' + appUrl,
                'Video auto rapi pakai AI Clipora 🚀 Hasil profesional, prosesnya cepet. Daftar gratis: ' + appUrl,
                'Dari berantakan jadi rapi ✨ Clipora = editing otomatis pakai AI. Cobain: ' + appUrl
            ];
            return templates[Math.floor(Math.random() * templates.length)];
        }
        
        function copyToClipboard(text) {
            if (navigator.clipboard && window.isSecureContext) {
                return navigator.clipboard.writeText(text);
            }
            return new Promise(function(resolve, reject) {
                try {
                    var ta = document.createElement('textarea');
                    ta.value = text;
                    ta.style.position = 'fixed';
                    ta.style.opacity = '0';
                    document.body.appendChild(ta);
                    ta.select();
                    document.execCommand('copy');
                    ta.remove();
                    resolve();
                } catch (e) { reject(e); }
            });
        }
        
        function showToast(msg, type) {
            type = type || 'success';
            var colors = {
                success: '#22c55e',
                error: '#ef4444',
                info: '#06b6d4'
            };
            var toast = document.createElement('div');
            toast.style.cssText = 
                'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);' +
                'padding:12px 20px;border-radius:10px;font-size:0.85rem;font-weight:600;' +
                'color:#fff;z-index:99999;box-shadow:0 8px 24px rgba(0,0,0,0.4);' +
                'background:' + (colors[type] || colors.success) + ';' +
                'animation:slideUp 0.3s ease;';
            toast.textContent = msg;
            document.body.appendChild(toast);
            setTimeout(function() { toast.remove(); }, 2500);
        }
        
        function shareVideo(platform) {
            var appUrl = window.location.origin;
            var caption = generateCaption();
            var encCaption = encodeURIComponent(caption);
            var encUrl = encodeURIComponent(appUrl);
            
            switch (platform) {
                case 'whatsapp':
                    window.open('https://wa.me/?text=' + encodeURIComponent(caption + '\n\n' + appUrl), '_blank');
                    showToast('📱 Membuka WhatsApp...', 'info');
                    break;
                case 'facebook':
                    window.open('https://www.facebook.com/sharer/sharer.php?u=' + encUrl + '&quote=' + encCaption, '_blank', 'width=600,height=500');
                    showToast('📘 Membuka Facebook...', 'info');
                    break;
                case 'twitter':
                    window.open('https://twitter.com/intent/tweet?text=' + encCaption + '&url=' + encUrl, '_blank', 'width=600,height=500');
                    showToast('🐦 Membuka Twitter...', 'info');
                    break;
                case 'instagram':
                    copyToClipboard(caption + '\n\n' + appUrl).then(function() {
                        showToast('✅ Caption dicopy! Tempel di Instagram.', 'success');
                        setTimeout(function() { window.open('https://www.instagram.com/', '_blank'); }, 800);
                    });
                    break;
                case 'tiktok':
                    copyToClipboard(caption).then(function() {
                        showToast('✅ Caption dicopy! Tempel di TikTok.', 'success');
                        setTimeout(function() { window.open('https://www.tiktok.com/upload', '_blank'); }, 800);
                    });
                    break;
                case 'copy':
                    copyToClipboard(caption + '\n\n' + appUrl).then(function() {
                        showToast('✅ Caption dicopy!', 'success');
                    });
                    break;
            }
        }
    </script>
    
    <style>
        @keyframes slideUp {
            from { opacity: 0; transform: translate(-50%, 20px); }
            to { opacity: 1; transform: translate(-50%, 0); }
        }
    </style>
    
</body>
</html>

<?php
// Footer dipanggil di paling bawah agar tidak merusak struktur HTML
require_once __DIR__ . '/includes/footer.php';
?>