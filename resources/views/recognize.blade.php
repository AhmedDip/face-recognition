@extends('layouts.app')

@section('content')
<div class="bg-white p-6 rounded shadow">
    <h1 class="text-xl font-bold mb-4">Live Face Recognition</h1>

    <video id="video" autoplay muted class="w-full max-w-md rounded border bg-black"></video>

    <div class="mt-3 flex gap-2">
        <button id="startBtn" class="bg-indigo-600 text-white px-4 py-2 rounded">Start Camera</button>
        <button id="stopBtn" class="bg-gray-600 text-white px-4 py-2 rounded" disabled>Stop</button>
    </div>

    <div id="result" class="mt-4 text-lg font-semibold"></div>
    <p id="status" class="text-sm text-gray-500 mt-1">Loading models...</p>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const modelUrl = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@0.22.2/weights';
    const video = document.getElementById('video');
    const status = document.getElementById('status');
    const resultEl = document.getElementById('result');
    const startButton = document.getElementById('startBtn');
    const stopButton = document.getElementById('stopBtn');

    let stream = null;
    let interval = null;

    try {
        await faceapi.nets.tinyFaceDetector.loadFromUri(modelUrl);
        await faceapi.nets.faceLandmark68Net.loadFromUri(modelUrl);
        await faceapi.nets.faceRecognitionNet.loadFromUri(modelUrl);

        status.textContent = 'Models loaded. Click Start.';
        startButton.disabled = false;
    } catch (error) {
        console.error('Unable to initialize face recognition.', error);
        status.textContent = 'Unable to load face recognition. Refresh and try again.';
        return;
    }

    startButton.onclick = async () => {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: true });
            video.srcObject = stream;
            startButton.disabled = true;
            stopButton.disabled = false;

            interval = setInterval(async () => {
                try {
                    const detections = await faceapi
                        .detectAllFaces(video, new faceapi.TinyFaceDetectorOptions())
                        .withFaceLandmarks()
                        .withFaceDescriptors();

                    if (detections.length === 0) {
                        resultEl.textContent = 'No face detected';
                        return;
                    }

                    const results = await Promise.all(detections.map(async detection => {
                        const response = await fetch('{{ route('api.recognize') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                descriptor: Array.from(detection.descriptor),
                            }),
                        });

                        if (!response.ok) {
                            throw new Error(`Recognition request failed with status ${response.status}`);
                        }

                        return response.json();
                    }));

                    resultEl.innerHTML = results.map(result =>
                        result.matched
                            ? `<span class="text-green-600">${result.person.name}</span> (distance: ${result.distance.toFixed(2)})`
                            : '<span class="text-red-600">Unknown face</span>'
                    ).join('<br>');
                } catch (error) {
                    console.error('Face recognition request failed.', error);
                    resultEl.textContent = 'Recognition is temporarily unavailable.';
                }
            }, 800);
        } catch (error) {
            console.error('Unable to access the camera.', error);
            status.textContent = 'Unable to access the camera. Check browser permissions.';
        }
    };

    stopButton.onclick = () => {
        clearInterval(interval);
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
        stream = null;
        startButton.disabled = false;
        stopButton.disabled = true;
        resultEl.textContent = '';
    };
});
</script>
@endsection
