
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login | Safeguard Monitoring System</title>

    <meta
        name="description"
        content="Halaman login Safeguard Monitoring System."
    >
    <meta name="application-name" content="Safeguard Monitoring System - HSSE PROJECT">

    <!-- Bootstrap 5 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        :root {
            --primary: #087c68;
            --primary-hover: #066653;
            --text-dark: #263746;
            --text-muted: #718096;
            --border-color: #d5dde5;
        }

        * {
            box-sizing: border-box;
        }

        html {
            min-height: 100%;
        }

        body {
            min-height: 100vh;
            min-height: 100dvh;
            margin: 0;
            padding: 28px 16px;

            display: flex;
            align-items: center;
            justify-content: center;

            font-family: Arial, Helvetica, sans-serif;

            background:
                linear-gradient(
                    180deg,
                    rgba(5, 35, 57, 0.48),
                    rgba(4, 28, 43, 0.78)
                ),
                url("{{ asset('images/backgrounds/login-dieng.webp') }}")
                center center / cover no-repeat fixed;
        }

        /* Pembungkus halaman login */
        .login-wrapper {
            width: 100%;
            max-width: 390px;
            margin: auto;
        }

        /* Identitas perusahaan */
        .brand {
            margin-bottom: 25px;
            text-align: center;
            color: #fff;
        }

        .brand-logo {
            display: block;
            width: 90px;
            height: 90px;
            margin: 0 auto 12px;

            object-fit: contain;
            background: transparent;
            border: none;
        }

        .brand h1 {
            margin: 0;
            font-size: 21px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.4);
        }

        .brand p {
            margin: 0;
            color: #f0f4f7;
            font-size: 12px;
            line-height: 1.6;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.5);
        }

        /* Kartu login */
        .login-card {
            width: 100%;
            padding: 26px;

            background: #fff;
            border: none;
            border-radius: 10px;

            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.24);
        }

        .login-title {
            margin-bottom: 6px;
            color: var(--text-dark);
            font-size: 19px;
            font-weight: 700;
        }

        .login-subtitle {
            margin-bottom: 22px;
            color: var(--text-muted);
            font-size: 12px;
            line-height: 1.6;
        }

        /* Label dan input */
        .form-label {
            margin-bottom: 7px;
            color: var(--text-dark);
            font-size: 12px;
            font-weight: 600;
        }

        .form-control {
            min-height: 42px;
            padding: 10px 12px;

            border: 1px solid var(--border-color);
            border-radius: 5px;

            color: var(--text-dark);
            font-size: 12px;
        }

        .form-control::placeholder {
            color: #9aa6b2;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(8, 124, 104, 0.12);
        }

        /* Input password dan tombol tampilkan password */
        .password-wrapper {
            position: relative;
        }

        .password-wrapper .form-control {
            padding-right: 72px;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);

            padding: 4px 0;
            border: none;
            background: transparent;

            color: var(--primary);
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
        }

        .toggle-password:focus-visible {
            outline: 2px solid var(--primary);
            outline-offset: 3px;
        }

        /* Ingat saya dan lupa password */
        .login-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;

            margin: 4px 0 21px;
            font-size: 11px;
        }

        .form-check {
            min-height: auto;
            margin: 0;
        }

        .form-check-input {
            margin-top: 2px;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .form-check-label {
            color: #475569;
            cursor: pointer;
        }

        .forgot-link {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
        }

        .forgot-link:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }

        /* Tombol masuk */
        .btn-login {
            width: 100%;
            min-height: 43px;

            border: 1px solid var(--primary);
            border-radius: 5px;

            background: var(--primary);
            color: #fff;

            font-size: 13px;
            font-weight: 700;

            transition:
                background-color 0.2s ease,
                border-color 0.2s ease;
        }

        .btn-login:hover {
            background: var(--primary-hover);
            border-color: var(--primary-hover);
            color: #fff;
        }

        .btn-login:focus-visible {
            outline: 3px solid rgba(8, 124, 104, 0.3);
            outline-offset: 2px;
        }

        /* Pesan validasi */
        .alert {
            margin-bottom: 18px;
            padding: 10px 12px;
            border-radius: 5px;
            font-size: 12px;
            line-height: 1.5;
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        /* Footer */
        .login-footer {
            margin-top: 20px;
            color: #f0f4f7;
            font-size: 11px;
            text-align: center;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.5);
        }

        /* Tampilan layar kecil */
        @media (max-width: 480px) {
            body {
                padding: 24px 16px;
                background-attachment: scroll;
            }

            .login-wrapper {
                max-width: 350px;
            }

            .brand {
                margin-bottom: 20px;
            }

            .brand-logo {
                width: 82px;
                height: 82px;
            }

            .brand h1 {
                font-size: 19px;
            }

            .login-card {
                padding: 22px;
            }
        }

        /* Mengurangi animasi bagi pengguna yang memilihnya */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                transition-duration: 0.01ms !important;
                animation-duration: 0.01ms !important;
            }
        }
    </style>
</head>

<body>
    <main class="login-wrapper">

        <!-- Identitas perusahaan -->
        <header class="brand">
            <img
                src="{{ asset('images/logo/geo-dipa.png') }}"
                alt="Logo Geo Dipa Energi"
                class="brand-logo"
            >

            <h1>Safeguard Monitoring System</h1>
        </header>

        <!-- Form login -->
        <section class="login-card" aria-labelledby="login-heading">

            <h2 id="login-heading" class="login-title">Login</h2>

            <p class="login-subtitle">
                Masuk untuk mengakses Safeguard Monitoring System.
            </p>

            <!-- Pesan error dari Laravel -->
            @if ($errors->any())
                <div
                    class="alert alert-danger"
                    role="alert"
                    aria-live="polite"
                >
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login.process') }}" method="POST">
                @csrf

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">
                        Email
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        placeholder="nama@perusahaan.com"
                        value="{{ old('email') }}"
                        autocomplete="username"
                        aria-describedby="email-error"
                        required
                        autofocus
                    >

                    @error('email')
                        <div id="email-error" class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label">
                        Password
                    </label>

                    <div class="password-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            id="togglePassword"
                            class="toggle-password"
                            aria-controls="password"
                            aria-pressed="false"
                        >
                            Lihat
                        </button>
                    </div>
                </div>

                <!-- Opsi login -->
                <div class="login-options">
                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="remember"
                            name="remember"
                            value="1"
                            @checked(old('remember'))
                        >

                        <label
                            class="form-check-label"
                            for="remember"
                        >
                            Ingat saya
                        </label>
                    </div>

                    @if (\Illuminate\Support\Facades\Route::has('password.request'))
                        <a
                            href="{{ route('password.request') }}"
                            class="forgot-link"
                        >
                            Lupa password?
                        </a>
                    @else
                        <span
                            class="text-secondary"
                            title="Fitur pemulihan password belum tersedia"
                        >
                            Lupa password?
                        </span>
                    @endif
                </div>

                <!-- Tombol submit -->
                <button type="submit" class="btn btn-login">
                    Masuk
                </button>
            </form>
        </section>

        <!-- Footer -->
        <footer class="login-footer">
            Safeguard Monitoring System &copy; {{ date('Y') }}
        </footer>

    </main>

    <!-- Interaksi tampilkan password -->
    <script>
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');

        togglePassword.addEventListener('click', function () {
            const isPassword = passwordInput.type === 'password';

            passwordInput.type = isPassword ? 'text' : 'password';
            togglePassword.textContent = isPassword ? 'Sembunyikan' : 'Lihat';
            togglePassword.setAttribute('aria-pressed', String(isPassword));
        });
    </script>
</body>
</html>
