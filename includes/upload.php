<!-- includes/upload.php -->
<!-- Upload Area -->
<div class="upload-area" id="uploadArea">
    <div class="icon-wrapper"><i class="fas fa-cloud-upload-alt"></i></div>
    <h3>📤 Upload Video</h3>
    <p>Taruh file di sini atau klik untuk memilih</p>
    <div class="file-types">MP4, MOV, AVI, MKV • Maks 500MB</div>
    <input type="file" id="videoInput" accept="video/*" style="display:none;">
</div>

<!-- Video Preview -->
<div class="video-preview" id="videoPreview" style="display:none !important;">
    <video id="videoPlayer" controls></video>
    <div class="video-info">
        <span><i class="fas fa-file-video"></i> <span id="fileName">video.mp4</span></span>
        <span><i class="fas fa-weight"></i> <span id="fileSize">0 MB</span></span>
    </div>
</div>