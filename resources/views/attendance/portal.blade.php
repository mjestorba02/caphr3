<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Portal</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="bg-white shadow-lg rounded-xl p-8 w-full max-w-md">
        <h1 class="text-2xl font-bold text-center mb-6">Attendance Portal</h1>

        <!-- Step 1: Name input -->
        <div id="step1">
            <label class="block mb-2 text-gray-700 font-semibold">Enter your name</label>
            <input id="name" type="text" class="w-full border p-2 rounded mb-4" placeholder="e.g. Mark John">
            <button id="checkNameBtn" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Next</button>
        </div>

        <!-- Step 2: Password input -->
        <div id="step2" class="hidden">
            <label class="block mb-2 text-gray-700 font-semibold">Enter your password</label>
            <input id="password" type="password" class="w-full border p-2 rounded mb-4" placeholder="Password">
            <div class="flex justify-between">
                <button id="backBtn" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Back</button>
                <button id="verifyBtn" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Verify</button>
            </div>
        </div>

        <p id="message" class="mt-4 text-center text-gray-600 font-medium"></p>
    </div>

    <script>
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
        axios.defaults.headers.common['Accept'] = 'application/json';

        const step1 = document.getElementById('step1');
        const step2 = document.getElementById('step2');
        const nameInput = document.getElementById('name');
        const passwordInput = document.getElementById('password');
        const messageBox = document.getElementById('message');
        const backBtn = document.getElementById('backBtn');
        const checkNameBtn = document.getElementById('checkNameBtn');
        const verifyBtn = document.getElementById('verifyBtn');

        // Add a heading to show mode
        const modeDisplay = document.createElement('p');
        modeDisplay.className = 'text-center font-semibold text-gray-700 mb-4';
        step2.prepend(modeDisplay);

        // ---- Function to check name ----
        function handleCheckName() {
            const name = nameInput.value.trim();
            if (!name) {
                messageBox.textContent = 'Please enter your name.';
                messageBox.className = 'mt-4 text-center text-red-600 font-semibold';
                return;
            }

            axios.post('{{ route('attendance.checkName') }}', { name })
                .then(res => {
                    if (res.data.exists) {
                        step1.classList.add('hidden');
                        step2.classList.remove('hidden');
                        messageBox.textContent = '';

                        if (res.data.hasTimeIn) {
                            modeDisplay.innerHTML = `
                                <span class="text-blue-600">Time Out Mode</span><br>
                                <span class="text-sm text-gray-500">
                                    Time-in recorded at: ${res.data.date} ${res.data.time_in}
                                </span>
                            `;
                        } else {
                            modeDisplay.innerHTML = `<span class="text-green-600">Time In Mode</span>`;
                        }

                        passwordInput.focus();
                    } else {
                        messageBox.textContent = 'Name not found.';
                        messageBox.className = 'mt-4 text-center text-red-600 font-semibold';
                    }
                })
                .catch(err => {
                    console.error(err);
                    messageBox.textContent = 'Error checking name.';
                    messageBox.className = 'mt-4 text-center text-yellow-600 font-semibold';
                });
        }

        // ---- Function to verify password ----
        function handleVerify() {
            const name = nameInput.value.trim();
            const password = passwordInput.value.trim();

            if (!password) {
                messageBox.textContent = 'Please enter your password.';
                messageBox.className = 'mt-4 text-center text-red-600 font-semibold';
                return;
            }

            axios.post('{{ route('attendance.verify') }}', { name, password })
                .then(res => {
                    if (res.data.success) {
                        messageBox.textContent = res.data.message;
                        messageBox.className = 'mt-4 text-center text-green-600 font-semibold';
                    } else {
                        messageBox.textContent = res.data.message;
                        messageBox.className = 'mt-4 text-center text-red-600 font-semibold';
                    }
                })
                .catch(err => {
                    console.error("Error verifying:", err.response ? err.response.data : err);
                    messageBox.textContent = 'Error verifying password.';
                    messageBox.className = 'mt-4 text-center text-yellow-600 font-semibold';
                });
        }

        // ---- Button Click Events ----
        checkNameBtn.addEventListener('click', handleCheckName);
        verifyBtn.addEventListener('click', handleVerify);
        backBtn.addEventListener('click', () => {
            step2.classList.add('hidden');
            step1.classList.remove('hidden');
            passwordInput.value = '';
            messageBox.textContent = '';
            modeDisplay.textContent = '';
            nameInput.focus();
        });

        // ---- Enter Key Support ----
        nameInput.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleCheckName();
            }
        });

        passwordInput.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleVerify();
            }
        });
    </script>
</body>
</html>