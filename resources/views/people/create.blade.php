@extends('layouts.app')

@section('content')
<div class="bg-white p-6 rounded shadow">
    <h1 class="text-xl font-bold mb-4">Register a Face</h1>

    <div class="grid md:grid-cols-2 gap-6">
        <div>
            <video id="video" autoplay muted class="w-full rounded border bg-black" width="400" height="300"></video>
            <div class="mt-2 flex gap-2">
                <button id="startBtn" class="bg-indigo-600 text-white px-4 py-2 rounded">Start Camera</button>
                <button id="captureBtn" class="bg-green-600 text-white px-4 py-2 rounded" disabled>Capture Face</button>
            </div>
            <p id="status" class="mt-2 text-sm text-gray-600">Loading models...</p>
        </div>

        <form id="registerForm" class="space-y-3">
            <div>
                <label class="block text-sm font-medium">Name</label>
                <input type="text" name="name" required class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium">Email (optional)</label>
                <input type="email" name="email" class="w-full border rounded px-3 py-2">
            </div>
            <input type="hidden" name="descriptor" id="descriptorInput">
            <input type="hidden" name="photo" id="photoInput">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded w-full" disabled id="submitBtn">
                Save Person
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const modelUrl = 'https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js@0.22.2/weights';
    const video = document.getElementById('video');
    const status = document.getElementById('status');
    const startButton = document.getElementById('startBtn');
    const captureButton = document.getElementById('captureBtn');
    const submitButton = document.getElementById('submitBtn');

    try {
        await faceapi.nets.tinyFaceDetector.loadFromUri(modelUrl);
        await faceapi.nets.faceLandmark68Net.loadFromUri(modelUrl);
        await faceapi.nets.faceRecognitionNet.loadFromUri(modelUrl);
        status.textContent = 'Models loaded. Click Start Camera.';
        startButton.disabled = false;
    } catch (error) {
        console.error('Unable to load face recognition models.', error);
        status.textContent = 'Unable to load face recognition models. Refresh and try again.';
        return;
    }

    startButton.onclick = async () => {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            video.srcObject = stream;
            captureButton.disabled = false;
            startButton.disabled = true;
        } catch (error) {
            console.error('Unable to access the camera.', error);
            status.textContent = 'Unable to access the camera. Check browser permissions.';
        }
    };

    captureButton.onclick = async () => {
        status.textContent = 'Detecting face...';
        const detection = await faceapi
            .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!detection) {
            status.textContent = 'No face detected. Try again.';
            return;
        }

        document.getElementById('descriptorInput').value = JSON.stringify(Array.from(detection.descriptor));

        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        document.getElementById('photoInput').value = canvas.toDataURL('image/jpeg', 0.8);

        submitButton.disabled = false;
        status.textContent = 'Face captured. Fill in name and save.';
    };

    document.getElementById('registerForm').onsubmit = async (event) => {
        event.preventDefault();
        submitButton.disabled = true;

        try {
            const response = await fetch('{{ route('people.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(Object.fromEntries(new FormData(event.target))),
            });

            if (!response.ok) {
                throw new Error(`Request failed with status ${response.status}`);
            }

            window.location.href = '{{ route('people.index') }}';
        } catch (error) {
            console.error('Unable to save the person.', error);
            status.textContent = 'Failed to save the person. Please try again.';
            submitButton.disabled = false;
        }
    };
});
</script>
@endsection
