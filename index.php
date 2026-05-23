<?php
if (isset($_SERVER['REQUEST_URI'])) {
    $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if ($requestPath === '/') {
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        $location = '/index.php' . ($queryString !== '' ? '?' . $queryString : '');

        header('Location: ' . $location, true, 302);
        exit;
    }
}

$audioDir = __DIR__ . '/audio';
$audioWebRoot = 'audio.php?file=';
$supportedExtensions = ['flac', 'mp3'];
$playlist = [];
$siteButtonWebRoot = 'images/button.png';

if (is_dir($audioDir)) {
    $audioFiles = scandir($audioDir);

    foreach ($audioFiles as $audioFile) {
        $filePath = $audioDir . '/' . $audioFile;

        if (!is_file($filePath)) {
            continue;
        }

        $extension = strtolower(pathinfo($audioFile, PATHINFO_EXTENSION));

        if (!in_array($extension, $supportedExtensions, true)) {
            continue;
        }

        $playlist[] = $audioFile;
    }

    natcasesort($playlist);
    $playlist = array_values($playlist);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>saber</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="sidebar-marquee" aria-label="Honorable mentions">
        <div class="marquee-track">
            <div class="marquee-content">
                DOGBONE +++ ALEXIOSTHESIXTH +++ MOOSYU +++ TRADEMARKHELL.NET +++ SPAX.ZONE +++ LUCASTHEGUY +++ JBC.LOL +++ TOASTY.PLACE +++ RICE.PLACE +++ FMHY.NET +++ MYMETYPE +++ SIGNALIS +++ BANG1338 +++ LEO +++ ALEXANDER PUSHKIN +++ DWAYNE +++ DIMDEN.DEV +++ 32ENOKI.NET +++ LITEKIN AND LEITZENTRALE ARE LOVEBIRDS +++ V1DIA +++ NOMAAKIP.XYZ +++ HAMPUS.LOL +++ FLUXER +++ MEOWERGIRL +++ PILKEY +++ ADAMNGSHRINE.COM === HONORABLE MENTIONS
            </div>
            <div class="marquee-content" aria-hidden="true">
                DOGBONE +++ ALEXIOSTHESIXTH +++ MOOSYU +++ TRADEMARKHELL.NET +++ SPAX.ZONE +++ LUCASTHEGUY +++ JBC.LOL +++ TOASTY.PLACE +++ RICE.PLACE +++ FMHY.NET +++ MYMETYPE +++ SIGNALIS +++ BANG1338 +++ LEO +++ ALEXANDER PUSHKIN +++ DWAYNE +++ DIMDEN.DEV +++ 32ENOKI.NET +++ LITEKIN AND LEITZENTRALE ARE LOVEBIRDS +++ V1DIA +++ NOMAAKIP.XYZ +++ HAMPUS.LOL +++ FLUXER +++ MEOWERGIRL +++ PILKEY +++ ADAMNGSHRINE.COM === HONORABLE MENTIONS
            </div>
        </div>
    </div>

    <main>
        <div class="content-shell">
        <img class="ascii-shape" src="ascii-art.png" alt="" aria-hidden="true">

        <h1>saber</h1>
        <h2>About me</h2>
        <p>
            HI! I'm Saber<br>
            I FUCKING LOVE HOMELABBING AND LISTENING TO MUSIC (check out <a href="https://aeon-fm.signalis.jp/">my koito</a> instance)!
        </p>
        <p>
            I mainly do programming and playing the guitar; while I currently only can do a couple of languages I'm sure open to learning a bit more than just Rust, TS and PHP.<br>
            Next to programming I also love homelabbing and selfhosting — it's pretty fun. I currently have Navidrome, Vaultwarden and a Forgejo instance and much more all behind a private Tailscale network.
        </p>
        <p class="section-separator" aria-hidden="true">---</p>

        <h2>Projects</h2>
        <p class="projects-list">
            <a href="https://git.gay/Fluxcord">Fluxcord</a>
            <a href="https://hoffnungfuerdiezukunft.net">HOFFNUNGFÜRDIEZUKUNFT</a>
            <a href="https://github.com/vesaber/Fluxer-Rust">Fluxer-Rust</a>
        </p>
        <p class="section-separator" aria-hidden="true">---</p>

        <h2>Contact</h2>
        <p class="projects-list contact-list">
            <span>Fluxer: <strong>saber#0001</strong></span>
            <span>Discord: <a href="https://discord.com/users/1378434721662369854"><strong>vesaber</strong></a></span>
            <span>Github: <a href="https://github.com/vesaber"><strong>vesaber</strong></a></span>
        </p>
        <p class="section-separator" aria-hidden="true">---</p>
        <h2>Link me!</h2>
        <div class="site-button-row">
            <a class="site-button-link" href="/index.php" aria-label="saber 88x31 button">
                <img class="site-button-image" src="<?= htmlspecialchars($siteButtonWebRoot, ENT_QUOTES, 'UTF-8') ?>" alt="saber 88x31 button">
            </a>
        </div>
        </div>
    </main>

    <div class="footer-widgets">
        <div class="neko-counter">
            <img src="https://count.getloli.com/@vesaber?theme=rule34" alt="Neko visitor counter">
        </div>
        <div class="media-player">
            <div class="player-controls">
                <span class="track-name" id="trackName">LOADING PLAYLIST...</span>
                <span class="player-row-break" aria-hidden="true"></span>
                <button id="prevBtn" type="button">[PREV]</button>
                <button id="playBtn" type="button">[PLAY]</button>
                <button id="nextBtn" type="button">[NEXT]</button>
                <div class="seek-wrap">
                    <span id="timeDisplay">00:00 / 00:00</span>
                    <input class="progress-bar" id="seekSlider" type="range" min="0" max="1000" value="0" step="1" aria-label="Seek track">
                </div>
                <label class="volume-wrap" for="volumeSlider">
                    <span class="volume-label">VOL</span>
                    <input class="volume-slider" id="volumeSlider" type="range" min="0" max="100" value="14" aria-label="Volume">
                </label>
                <audio id="audioPlayer" preload="metadata"></audio>
            </div>
        </div>
    </div>

    <script>
        const asciiShape = document.querySelector('.ascii-shape');
        const asciiVisibleTopInset = 58;
        const audioPlayer = document.getElementById('audioPlayer');
        const prevBtn = document.getElementById('prevBtn');
        const playBtn = document.getElementById('playBtn');
        const nextBtn = document.getElementById('nextBtn');
        const seekSlider = document.getElementById('seekSlider');
        const timeDisplay = document.getElementById('timeDisplay');
        const trackName = document.getElementById('trackName');
        const volumeSlider = document.getElementById('volumeSlider');
        const audioRoot = <?= json_encode($audioWebRoot, JSON_UNESCAPED_SLASHES) ?>;
        const playlist = <?= json_encode($playlist, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
        const defaultVolume = 0.14;
        const metadataByteLimit = 4194303;
        let currentTrackIndex = 0;
        let trackLoadSequence = 0;
        const trackMetadataCache = new Map();
        const textDecoder = new TextDecoder('utf-8');

        function formatTime(seconds) {
            if (!Number.isFinite(seconds)) {
                return '00:00';
            }

            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        }

        function updateProgress() {
            const duration = audioPlayer.duration || 0;
            const currentTime = audioPlayer.currentTime || 0;
            const progress = duration ? (currentTime / duration) * 100 : 0;

            seekSlider.value = duration ? String(Math.round((currentTime / duration) * Number(seekSlider.max))) : '0';
            seekSlider.disabled = !Number.isFinite(duration) || duration <= 0;
            seekSlider.style.setProperty('--seek-progress', `${progress}%`);
            timeDisplay.textContent = `${formatTime(currentTime)} / ${formatTime(duration)}`;
            playBtn.textContent = audioPlayer.paused ? '[PLAY]' : '[PAUSE]';
        }

        function seekFromSlider() {
            const duration = audioPlayer.duration;

            if (!Number.isFinite(duration) || duration <= 0) {
                return;
            }

            const ratio = Number(seekSlider.value) / Number(seekSlider.max);
            const seekTime = Math.min(duration - 0.05, Math.max(0, duration * ratio));

            try {
                audioPlayer.currentTime = seekTime;
            } catch (error) {
                updateProgress();
                return;
            }

            seekSlider.style.setProperty('--seek-progress', `${ratio * 100}%`);
            timeDisplay.textContent = `${formatTime(seekTime)} / ${formatTime(duration)}`;
        }

        function setTrackLabel(label) {
            trackName.textContent = label.toUpperCase();
        }

        function cleanFallbackLabel(fileName) {
            return fileName
                .replace(/\.[^.]+$/, '')
                .replace(/^\s*\d+\s*[\)._-]\s*/, '')
                .replace(/[_-]+/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();
        }

        function buildTrackUrl(fileName) {
            return `${audioRoot}${encodeURIComponent(fileName)}`;
        }

        function readUInt24BE(view, offset) {
            return (view.getUint8(offset) << 16)
                | (view.getUint8(offset + 1) << 8)
                | view.getUint8(offset + 2);
        }

        function parseVorbisComments(bytes) {
            const view = new DataView(bytes.buffer, bytes.byteOffset, bytes.byteLength);
            let offset = 0;

            if (bytes.byteLength < 8 || textDecoder.decode(bytes.subarray(0, 4)) !== 'fLaC') {
                return {};
            }

            offset += 4;

            while (offset + 4 <= bytes.byteLength) {
                const header = view.getUint8(offset);
                const blockType = header & 0x7f;
                const blockLength = readUInt24BE(view, offset + 1);
                offset += 4;

                if (offset + blockLength > bytes.byteLength) {
                    break;
                }

                if (blockType === 4) {
                    const blockView = new DataView(bytes.buffer, bytes.byteOffset + offset, blockLength);
                    let blockOffset = 0;

                    if (blockLength < 8) {
                        return {};
                    }

                    const vendorLength = blockView.getUint32(blockOffset, true);
                    blockOffset += 4 + vendorLength;

                    if (blockOffset + 4 > blockLength) {
                        return {};
                    }

                    const commentCount = blockView.getUint32(blockOffset, true);
                    blockOffset += 4;
                    const comments = {};

                    for (let i = 0; i < commentCount; i += 1) {
                        if (blockOffset + 4 > blockLength) {
                            break;
                        }

                        const commentLength = blockView.getUint32(blockOffset, true);
                        blockOffset += 4;

                        if (blockOffset + commentLength > blockLength) {
                            break;
                        }

                        const comment = textDecoder.decode(
                            new Uint8Array(bytes.buffer, bytes.byteOffset + offset + blockOffset, commentLength)
                        );
                        blockOffset += commentLength;

                        const separatorIndex = comment.indexOf('=');
                        if (separatorIndex === -1) {
                            continue;
                        }

                        const key = comment.slice(0, separatorIndex).toLowerCase();
                        const value = comment.slice(separatorIndex + 1).trim();

                        if (value && !(key in comments)) {
                            comments[key] = value;
                        }
                    }

                    return comments;
                }

                offset += blockLength;
            }

            return {};
        }

        async function readTrackMetadata(fileName) {
            if (trackMetadataCache.has(fileName)) {
                return trackMetadataCache.get(fileName);
            }

            const metadataPromise = fetch(buildTrackUrl(fileName), {
                    headers: {
                        Range: `bytes=0-${metadataByteLimit}`
                    }
                })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(`Track request failed with ${response.status}`);
                    }

                    return response.arrayBuffer();
                })
                .then((buffer) => parseVorbisComments(new Uint8Array(buffer)))
                .catch((error) => {
                    console.error(error);
                    return {};
                });

            trackMetadataCache.set(fileName, metadataPromise);
            return metadataPromise;
        }

        async function resolveTrackLabel(fileName) {
            const metadata = await readTrackMetadata(fileName);
            const parts = [metadata.artist, metadata.title].filter(Boolean);

            if (parts.length) {
                return parts.join(' - ');
            }

            return cleanFallbackLabel(fileName);
        }

        function syncVolume() {
            const volume = Math.min(Math.max(Number(volumeSlider.value) / 100, 0), 1);
            audioPlayer.volume = volume;
        }

        function getRandomTrackIndex(excludeIndex = -1) {
            if (!playlist.length) {
                return -1;
            }

            if (playlist.length === 1) {
                return 0;
            }

            let randomIndex = Math.floor(Math.random() * playlist.length);

            while (randomIndex === excludeIndex) {
                randomIndex = Math.floor(Math.random() * playlist.length);
            }

            return randomIndex;
        }

        function loadTrack(index) {
            if (!playlist.length) {
                setTrackLabel('NO TRACKS IN AUDIO FOLDER');
                audioPlayer.removeAttribute('src');
                audioPlayer.load();
                updateProgress();
                return;
            }

            currentTrackIndex = (index + playlist.length) % playlist.length;
            const fileName = playlist[currentTrackIndex];
            const loadSequence = trackLoadSequence + 1;
            trackLoadSequence = loadSequence;

            audioPlayer.src = buildTrackUrl(fileName);
            setTrackLabel(cleanFallbackLabel(fileName));
            updateProgress();

            resolveTrackLabel(fileName).then((label) => {
                if (trackLoadSequence === loadSequence && playlist[currentTrackIndex] === fileName) {
                    setTrackLabel(label);
                }
            });
        }

        function loadPlaylist() {
            if (!playlist.length) {
                setTrackLabel('AUDIO FOLDER IS EMPTY');
                return;
            }

            loadTrack(getRandomTrackIndex());
        }

        function playRandomTrack(autoplay = true) {
            if (!playlist.length) {
                return;
            }

            loadTrack(getRandomTrackIndex(currentTrackIndex));
            if (autoplay) {
                audioPlayer.play().catch(() => {});
            }
        }

        function updateAsciiLayout() {
            const imageWidth = asciiShape.naturalWidth;
            const imageHeight = asciiShape.naturalHeight;

            if (!imageWidth || !imageHeight) {
                return;
            }

            const visibleHeight = imageHeight - asciiVisibleTopInset;
            const viewportHeight = document.documentElement.clientHeight;
            const maxHeight = viewportHeight;

            const scaleFactor = maxHeight / visibleHeight;
            const shapeScale = parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--ascii-shape-scale')) || 1;
            const finalWrapWidth = imageWidth * scaleFactor * shapeScale;
            const finalWrapHeight = imageHeight * scaleFactor * shapeScale;
            const topOffset = -asciiVisibleTopInset * scaleFactor * shapeScale;

            document.documentElement.style.setProperty('--viewport-height', `${viewportHeight}px`);
            document.documentElement.style.setProperty('--ascii-box-width', `${Math.ceil(finalWrapWidth)}px`);
            document.documentElement.style.setProperty('--ascii-box-height', `${Math.ceil(finalWrapHeight)}px`);
            document.documentElement.style.setProperty('--ascii-offset-top', `${Math.floor(topOffset)}px`);
        }

        if (asciiShape.complete) {
            updateAsciiLayout();
        } else {
            asciiShape.addEventListener('load', updateAsciiLayout, { once: true });
        }

        window.addEventListener('resize', updateAsciiLayout);

        playBtn.addEventListener('click', () => {
            if (!playlist.length) {
                return;
            }

            if (audioPlayer.paused) {
                audioPlayer.play().catch(() => {});
            } else {
                audioPlayer.pause();
            }
        });

        prevBtn.addEventListener('click', () => {
            playRandomTrack(!audioPlayer.paused);
        });

        nextBtn.addEventListener('click', () => {
            playRandomTrack(!audioPlayer.paused);
        });

        seekSlider.addEventListener('input', () => {
            seekFromSlider();
        });

        seekSlider.addEventListener('change', () => {
            seekFromSlider();
            updateProgress();
        });

        audioPlayer.addEventListener('loadedmetadata', updateProgress);
        audioPlayer.addEventListener('durationchange', updateProgress);
        audioPlayer.addEventListener('timeupdate', updateProgress);
        audioPlayer.addEventListener('play', () => {
            updateProgress();
        });
        audioPlayer.addEventListener('pause', () => {
            updateProgress();
        });
        audioPlayer.addEventListener('ended', updateProgress);
        volumeSlider.addEventListener('input', syncVolume);
        audioPlayer.addEventListener('ended', () => {
            playRandomTrack();
        });

        audioPlayer.volume = defaultVolume;
        volumeSlider.value = String(defaultVolume * 100);
        loadPlaylist();
    </script>
</body>
</html>
