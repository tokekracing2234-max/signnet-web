const videoElement = document.getElementById('input_video');
const canvasElement = document.getElementById('output_canvas');
const canvasCtx = canvasElement.getContext('2d');
const statusText = document.getElementById('status');

let lastPredictTime = 0;
let isModeChangingNotification = false;
let isSwitchingModel = false;

let lastPredictedLabel = "-";
let consecutiveCount = 0;
const CONSECUTIVE_NEEDED = 2;
let modelCache = {};

window.currentDetectionMode = 'huruf';

async function getStoredModel(dbName, storeName, key) {
    return new Promise((resolve) => {
        const openRequest = indexedDB.open(dbName, 1);
        openRequest.onupgradeneeded = function() {
            openRequest.result.createObjectStore(storeName);
        };
        openRequest.onsuccess = function() {
            const db = openRequest.result;
            const transaction = db.transaction(storeName, 'readonly');
            const store = transaction.objectStore(storeName);
            const getRequest = store.get(key);
            getRequest.onsuccess = () => resolve(getRequest.result || null);
            getRequest.onerror = () => resolve(null);
        };
        openRequest.onerror = () => resolve(null);
    });
}

async function saveModelToStorage(dbName, storeName, key, data) {
    return new Promise((resolve) => {
        const openRequest = indexedDB.open(dbName, 1);
        openRequest.onsuccess = function() {
            const db = openRequest.result;
            const transaction = db.transaction(storeName, 'readwrite');
            const store = transaction.objectStore(storeName);
            store.put(data, key);
            transaction.oncomplete = () => resolve(true);
        };
        openRequest.onerror = () => resolve(false);
    });
}

async function clearOldModels(dbName, storeName, currentKey, keyPrefix) {
    return new Promise((resolve) => {
        const openRequest = indexedDB.open(dbName, 1);
        openRequest.onsuccess = function() {
            const db = openRequest.result;
            const transaction = db.transaction(storeName, 'readwrite');
            const store = transaction.objectStore(storeName);
            const getAllKeys = store.getAllKeys();
            getAllKeys.onsuccess = function() {
                getAllKeys.result.forEach(key => {
                    if (key !== currentKey && String(key).startsWith(keyPrefix)) {
                        store.delete(key);
                        console.log(`🗑️ Cache lama dihapus: ${key}`);
                    }
                });
            };
            transaction.oncomplete = () => resolve(true);
        };
        openRequest.onerror = () => resolve(false);
    });
}

async function loadModelForKategori(kategori) {
    if (modelCache[kategori]) {
        return modelCache[kategori];
    }

    const DB_NAME = "SignNetCache";
    const STORE_NAME = "models";
    const KEY_PREFIX = `rf_model_${kategori}_`;

    const options = {
        executionProviders: ['wasm'],
        enableCpuMemArena: true,
        enableMemPattern: true,
        extra: { session: { set_denormal_as_zero: "1" } }
    };

    statusText.style.display = 'block';
    statusText.innerHTML = `<i class="fas fa-search"></i> Memeriksa versi model ${kategori.toUpperCase()}...`;

    const labelsResponse = await fetch(`/models/labels_${kategori}.json`);
    if (!labelsResponse.ok) {
        throw new Error(`Gagal memuat labels_${kategori}.json dari server`);
    }
    const labels = await labelsResponse.json();

    let serverVersion = "default";
    try {
        const versionRes = await fetch(`/models/model_version_${kategori}.txt?t=` + Date.now());
        if (versionRes.ok) {
            serverVersion = (await versionRes.text()).trim();
        }
    } catch (e) {
        console.warn(`Gagal fetch versi model ${kategori}, pakai default`);
    }

    const MODEL_KEY = `${KEY_PREFIX}${serverVersion}`;
    console.log(`[${kategori}] Model version: ${serverVersion} | Cache key: ${MODEL_KEY}`);

    statusText.innerHTML = `<i class="fas fa-search"></i> Memeriksa cache lokal model ${kategori.toUpperCase()}...`;
    let cachedBuffer = await getStoredModel(DB_NAME, STORE_NAME, MODEL_KEY);

    let modelBuffer;

    if (!cachedBuffer) {
        statusText.innerHTML = `<i class="fas fa-cloud-download-alt"></i> Mengunduh Model ${kategori.toUpperCase()} (Pertama kali saja)...`;

        const response = await fetch(`/models/rf_model_${kategori}.onnx.gz`);
        if (!response.ok) throw new Error(`Gagal download rf_model_${kategori}.onnx.gz dari server`);

        const compressedBuffer = await response.arrayBuffer();

        statusText.innerHTML = `<i class="fas fa-save"></i> Menyimpan model ${kategori.toUpperCase()} ke cache lokal...`;
        await saveModelToStorage(DB_NAME, STORE_NAME, MODEL_KEY, compressedBuffer);
        console.log(`Model gz [${kategori}] berhasil disimpan ke IndexedDB!`);

        await clearOldModels(DB_NAME, STORE_NAME, MODEL_KEY, KEY_PREFIX);

        statusText.innerHTML = `<i class="fas fa-compress-arrows-alt"></i> Mengekstrak model ${kategori.toUpperCase()}...`;
        const uint8 = new Uint8Array(compressedBuffer);
        modelBuffer = pako.ungzip(uint8).buffer;

    } else {
        console.log(`[CACHE HIT] Model ${kategori} ditemukan di cache lokal!`);
        statusText.innerHTML = `<i class="fas fa-compress-arrows-alt"></i> Mengekstrak model ${kategori.toUpperCase()} dari cache...`;
        const uint8 = new Uint8Array(cachedBuffer);
        modelBuffer = pako.ungzip(uint8).buffer;
    }

    statusText.innerHTML = `<i class="fas fa-bolt"></i> Mengaktifkan model ${kategori.toUpperCase()}...`;
    const session = await ort.InferenceSession.create(modelBuffer, options);
    console.log(`ONNX Session [${kategori}] berhasil diaktifkan!`);

    modelCache[kategori] = { session, labels };
    return modelCache[kategori];
}

async function initModelAndLabels() {
    try {
        statusText.style.display = 'block';
        statusText.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Inisialisasi Model AI...';

        await loadModelForKategori(window.currentDetectionMode);

        statusText.innerHTML = '<i class="fas fa-check-circle" style="color: #10b981;"></i> AI Siap! Posisikan tangan Anda';
    } catch (error) {
        console.error("⚠️ Gagal memuat aset model:", error);
        statusText.innerHTML = `<i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i> Gagal: ${error.message}`;
    }
}

window.updateButtonVisuals = function(mode) {
    const btnHuruf = document.getElementById('btnModeHuruf');
    const btnAngka = document.getElementById('btnModeAngka');

    if (btnHuruf && btnAngka) {
        const style = getComputedStyle(document.documentElement);
        const textMutedColor = style.getPropertyValue('--text-muted').trim() || '#71717a';
        const blueColor = style.getPropertyValue('--blue').trim() || '#2563eb';
        const greenColor = style.getPropertyValue('--green').trim() || '#10b981';

        if (mode === 'huruf') {
            btnHuruf.style.background = blueColor;
            btnHuruf.style.color = '#fff';
            btnHuruf.style.boxShadow = '0 2px 8px rgba(37, 99, 235, 0.35)';
            btnAngka.style.background = 'transparent';
            btnAngka.style.color = textMutedColor;
            btnAngka.style.boxShadow = 'none';
        } else if (mode === 'angka') {
            btnAngka.style.background = greenColor;
            btnAngka.style.color = '#fff';
            btnAngka.style.boxShadow = '0 2px 8px rgba(16, 185, 129, 0.35)';
            btnHuruf.style.background = 'transparent';
            btnHuruf.style.color = textMutedColor;
            btnHuruf.style.boxShadow = 'none';
        }
    }
}

window.changeMode = async function(mode) {
    if (mode === window.currentDetectionMode) return;

    window.currentDetectionMode = mode;
    window.updateButtonVisuals(mode);

    lastPredictedLabel = "-";
    consecutiveCount = 0;
    updateUI("-", 0);

    isModeChangingNotification = true;
    statusText.style.display = 'block';

    if (modelCache[mode]) {
        statusText.innerHTML = mode === 'huruf'
            ? '<i class="fas fa-font" style="color: #3b82f6;"></i> Mode <b>HURUF (A - Z)</b> aktif'
            : '<i class="fas fa-hashtag" style="color: #10b981;"></i> Mode <b>ANGKA (0 - 9)</b> aktif';

        setTimeout(() => { isModeChangingNotification = false; }, 1000);
        return;
    }

    isSwitchingModel = true;
    statusText.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Memuat model ${mode.toUpperCase()} untuk pertama kali...`;

    try {
        await loadModelForKategori(mode);
        statusText.innerHTML = mode === 'huruf'
            ? '<i class="fas fa-font" style="color: #3b82f6;"></i> Mode <b>HURUF (A - Z)</b> siap'
            : '<i class="fas fa-hashtag" style="color: #10b981;"></i> Mode <b>ANGKA (0 - 9)</b> siap';
    } catch (error) {
        console.error(`⚠️ Gagal memuat model ${mode}:`, error);
        statusText.innerHTML = `<i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i> Gagal memuat model ${mode.toUpperCase()}: ${error.message}`;
    } finally {
        isSwitchingModel = false;
        setTimeout(() => { isModeChangingNotification = false; }, 1000);
    }
}

function extractFeatures(results) {
    let features = new Array(126).fill(0);
    if (results.multiHandLandmarks && results.multiHandLandmarks.length > 0) {
        let handList = results.multiHandLandmarks.map((lm, i) => ({
            lm, label: results.multiHandedness[i].label
        })).sort((a, b) => a.label.localeCompare(b.label));

        const base_x = handList[0].lm[0].x;
        const base_y = handList[0].lm[0].y;
        const base_z = handList[0].lm[0].z;

        handList.forEach((hand, index) => {
            if (index < 2) {
                let offset = index * 63;
                hand.lm.forEach((lm, lmIdx) => {
                    let i = offset + (lmIdx * 3);
                    features[i]     = lm.x - base_x;
                    features[i + 1] = lm.y - base_y;
                    features[i + 2] = lm.z - base_z;
                });
            }
        });
    }
    return features;
}

function updateUI(prediksi, akurasi) {
    document.getElementById('prediction-result').innerText = prediksi;
    document.getElementById('accuracy-value').innerText = akurasi + "%";

    const bar = document.getElementById('accuracy-bar');
    bar.style.width = akurasi + "%";

    const score = parseFloat(akurasi);
    if (score >= 70) {
        bar.style.background = '#10b981';
    } else if (score >= 50) {
        bar.style.background = '#f59e0b';
    } else {
        bar.style.background = '#ef4444';
    }
}

function drawGuide(ctx, width, height) {
    ctx.strokeStyle = "rgba(59,130,246,0.45)";
    ctx.lineWidth = 3;
    ctx.setLineDash([12, 8]);
    const rectW = width * 0.58;
    const rectH = height * 0.70;
    const rectX = (width - rectW) / 2;
    const rectY = (height - rectH) / 2;

    ctx.strokeRect(rectX, rectY, rectW, rectH);
    ctx.setLineDash([]);
    ctx.fillStyle = "rgba(255,255,255,0.9)";
    ctx.font = "bold 14px Inter";
    ctx.textAlign = "center";
    ctx.fillText("POSISIKAN TANGAN DI SINI", width / 2, rectY - 14);
}

async function runLocalPrediction(features) {
    const active = modelCache[window.currentDetectionMode];
    if (!active || !active.session) return;

    try {
        const inputTensor = new ort.Tensor('float32', new Float32Array(features), [1, 126]);
        const feeds = { 'float_input': inputTensor };
        const outputMap = await active.session.run(feeds, ['label', 'probabilities']);
        const labelTensor = outputMap['label'];
        const probTensor  = outputMap['probabilities'];

        if (!labelTensor?.data) return;

        const predictedIndex = Number(labelTensor.data[0]);
        const stringLabel = active.labels[predictedIndex] || "-";

        const isDigit = (val) => /^\d+$/.test(val); 
        const modeMatch = (window.currentDetectionMode === 'huruf' && !isDigit(stringLabel)) || 
                          (window.currentDetectionMode === 'angka' && isDigit(stringLabel));

       if (!modeMatch) {
            consecutiveCount = 0;
            lastPredictedLabel = "-";
            updateUI("-", 0);

            isModeChangingNotification = true; 
            statusText.innerHTML = `<i class="fas fa-exclamation-circle" style="color: #ef4444;"></i> Mode ${window.currentDetectionMode.toUpperCase()} aktif. Gestur tidak sesuai!`;

            setTimeout(() => { 
                isModeChangingNotification = false; 
            }, 1500);
            
            return;
        }

        let confidenceScore = "0";
        if (probTensor?.data) {
            confidenceScore = (probTensor.data[predictedIndex] * 100).toFixed(1);
        }
        console.log(`[${window.currentDetectionMode}] Label: ${stringLabel} | Confidence: ${confidenceScore}% | Index: ${predictedIndex}`);

        if (active.labels.length > 0 && predictedIndex < active.labels.length) {
            if (parseFloat(confidenceScore) > 25.0) {

                if (stringLabel === lastPredictedLabel) {
                    consecutiveCount++;
                } else {
                    consecutiveCount = 1;
                    lastPredictedLabel = stringLabel;
                }

                if (consecutiveCount >= CONSECUTIVE_NEEDED) {
                    updateUI(stringLabel, confidenceScore);
                    if (!isModeChangingNotification)
                        statusText.innerHTML = `<i class="fas fa-hand-sparkles" style="color: #10b981;"></i> Tangan terdeteksi.`;
                }

            } else {
                consecutiveCount = 0;
                lastPredictedLabel = "-";
                updateUI("-", "0");
                if (!isModeChangingNotification)
                    statusText.innerHTML = '<i class="fas fa-hand-paper" style="color: #f59e0b;"></i> Posisi gestur kurang jelas...';
            }
        }

    } catch (err) {
        console.error("Gagal prediksi ONNX:", err);
    }
}

function onResults(results) {
    const wrapper = canvasElement.parentElement;
    const w = wrapper.clientWidth;
    const h = wrapper.clientHeight;
    if (canvasElement.width !== w || canvasElement.height !== h) {
        canvasElement.width = w;
        canvasElement.height = h;
    }

    canvasCtx.save();
    canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);
    canvasCtx.translate(canvasElement.width, 0);
    canvasCtx.scale(-1, 1);

    const imgWidth = results.image.width;
    const imgHeight = results.image.height;

    const inputRatio = imgWidth / imgHeight;
    const outputRatio = canvasElement.width / canvasElement.height;

    let srcX = 0, srcY = 0, srcWidth = imgWidth, srcHeight = imgHeight;

    if (inputRatio > outputRatio) {
        srcWidth = imgHeight * outputRatio;
        srcX = (imgWidth - srcWidth) / 2;
    } else {
        srcHeight = imgWidth / outputRatio;
        srcY = (imgHeight - srcHeight) / 2;
    }

    canvasCtx.drawImage(
        results.image,
        srcX, srcY, srcWidth, srcHeight,
        0, 0, canvasElement.width, canvasElement.height
    );

    if (results.multiHandLandmarks) {
        const totalHands = results.multiHandLandmarks.length;
        for (let i = 0; i < totalHands; i++) {
            const originalLandmarks = results.multiHandLandmarks[i];
            const handedness = results.multiHandedness[i].label;
            const handColor = handedness === 'Right' ? '#6366f1' : '#10b981';

            const adjustedLandmarks = originalLandmarks.map(landmark => {
                const pixelX = landmark.x * imgWidth;
                const pixelY = landmark.y * imgHeight;

                return {
                    x: (pixelX - srcX) / srcWidth,
                    y: (pixelY - srcY) / srcHeight,
                    z: landmark.z
                };
            });

            drawConnectors(canvasCtx, adjustedLandmarks, HAND_CONNECTIONS, {
                color: handColor,
                lineWidth: 3
            });
            drawLandmarks(canvasCtx, adjustedLandmarks, {
                color: '#ffffff',
                lineWidth: 1,
                radius: 2
            });
        }
    }

    canvasCtx.restore();
    drawGuide(canvasCtx, canvasElement.width, canvasElement.height);

    if (isSwitchingModel) {
        return;
    }

    if (results.multiHandLandmarks && results.multiHandLandmarks.length > 0) {
        const now = Date.now();
        if (now - lastPredictTime > 200) {
            lastPredictTime = now;
            const features = extractFeatures(results);
            runLocalPrediction(features);
        }
    } else {
        consecutiveCount = 0;
        lastPredictedLabel = "-";
        updateUI("-", 0);
        if (!isModeChangingNotification) {
            statusText.innerHTML = '<i class="fas fa-video" style="color: #9ca3af;"></i> Mencari Tangan di Area Kamera...';
        }
    }
}

// REGISTRASI ENGINE MEDIAPIPE HANDS
const hands = new Hands({
    locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/hands/${file}`
});

hands.setOptions({
    maxNumHands: 2,
    modelComplexity: 1,
    minDetectionConfidence: 0.6,
    minTrackingConfidence: 0.6
});

hands.onResults(onResults);

let isProcessingFrame = false;
let lastFrameTime = 0;
const FPS_THROTTLE = 1000 / 20;

async function predictLoop() {
    if (videoElement.paused || videoElement.ended) {
        requestAnimationFrame(predictLoop);
        return;
    }

    const now = Date.now();

    if (!isProcessingFrame && videoElement.readyState >= 3 && (now - lastFrameTime >= FPS_THROTTLE)) {
        isProcessingFrame = true;
        lastFrameTime = now;

        try {
            await hands.send({ image: videoElement });
        } catch (err) {
            console.error("MediaPipe Stream Error:", err);
        } finally {
            isProcessingFrame = false;
        }
    }

    requestAnimationFrame(predictLoop);
}

window.addEventListener('DOMContentLoaded', async () => {
    await initModelAndLabels();

    try {
        const constraints = {
            video: {
                facingMode: 'user',
                width: 640,
                height: 480,
                frameRate: { ideal: 24 }
            },
            audio: false
        };

        const stream = await navigator.mediaDevices.getUserMedia(constraints);
        videoElement.srcObject = stream;
        videoElement.setAttribute('playsinline', true);
        videoElement.setAttribute('webkit-playsinline', true);
        videoElement.muted = true;
        videoElement.play();

        videoElement.onloadeddata = () => {
            predictLoop();
        };
    } catch (err) {
        console.error("Gagal memuat kamera device:", err);
        statusText.innerHTML = '<i class="fas fa-exclamation-triangle" style="color: #ef4444;"></i> Akses Kamera Ditolak / Tidak Ditemukan';
    }

    window.updateButtonVisuals(window.currentDetectionMode);
});