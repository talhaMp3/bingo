<?php
session_start();
include_once './include/connection.php';


// Error logger
function logError($message)
{
    $logDir = __DIR__ . '/logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true); // Create logs folder if missing
    }

    $log = "[" . date('Y-m-d H:i:s') . "] $message" . PHP_EOL;
    file_put_contents($logDir . '/login.log', $log, FILE_APPEND);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $remember = isset($_POST['remember_me']);

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Please enter both email and password.";
        header("Location: login.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: login.php");
        exit;
    }

    $stmt = $conn->prepare("SELECT id, email, full_name, profile_image, password, status FROM customers WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result || $result->num_rows === 0) {
        $_SESSION['error'] = "Account not found.";
        logError("Login failed for: $email - Account not found");
        header("Location: login.php");
        exit;
    }

    $user = $result->fetch_assoc();

    if ($user['status'] !== 'active') {
        $_SESSION['error'] = "Your account is currently {$user['status']}. Please contact support.";
        logError("Blocked login for $email due to status: {$user['status']}");
        header("Location: login.php");
        exit;
    }

    if (!password_verify($password, $user['password'])) {
        $_SESSION['error'] = "Incorrect password.";
        logError("Incorrect password attempt for $email");
        header("Location: login.php");
        exit;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['profile_image'] = $user['profile_image'] ?? null;

    $update = $conn->prepare("UPDATE customers SET last_login = NOW() WHERE id = ?");
    $update->bind_param("i", $user['id']);
    $update->execute();

    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $expire_time = date('Y-m-d H:i:s', strtotime('+7 days'));

        $token_update = $conn->prepare("UPDATE customers SET remember_token = ?, remember_expires = ? WHERE id = ?");
        $token_update->bind_param("ssi", $token, $expire_time, $user['id']);
        $token_update->execute();

        setcookie(
            "remember_token",
            $token,
            [
                'expires' => time() + (7 * 24 * 60 * 60),
                'path' => '/',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );
    }

    $_SESSION['toast'] = [
        'type' => 'success',
        'message' => 'Login successful!'
    ];
    header("Location: index.php");
    exit;
}
include_once './layout/header.php';
?>



<main class="pt-12">

    <!-- hero section start -->
    <section class="inner-hero-section px-xl-20 px-lg-10 px-sm-7"
        style="background-image: url(assets/images/inner-page-banner.png);">
        <div class="container-fluid">
            <span class="text-animation-word text-h1 text-n100 mb-3">Login</span>
            <ul class="breadcrumb d-inline-flex align-items-center gap-lg-2 gap-1">
                <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                <li class="breadcrumb-item active"><a href="#">Login</a></li>
            </ul>
        </div>
    </section>
    <!-- hero section end -->

    <!-- login section start -->
    <section class="login-section pt-120 pb-120 px-xl-20 px-lg-10 px-sm-7">
        <div class="container-fluid">
            <div class="row g-6 justify-content-center">
                <div class="col-xl-5 col-lg-7 col-md-9">
                    <div class="register-form p-xl-15 p-lg-10 p-md-8 p-6 radius-16 border border-n100-1 bg-n20">
                        <div class="register-logo mb-lg-15 mb-md-10 mb-8 mx-auto">
                            <img class="w-100" src="assets/images/logo.png" alt="logo">
                        </div>
                        <div class="col-12 mb-lg-15 mb-md-10 mb-8 mx-auto">
                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger mb-4">
                                    <?= $_SESSION['error'];
                                    unset($_SESSION['error']); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success mb-4">
                                    <?= $_SESSION['success'];
                                    unset($_SESSION['success']); ?>
                                </div>
                            <?php endif; ?>

                        </div>

                        <!-- Normal Login Form -->
                        <form method="POST" class="d-grid gap-lg-6 gap-4 mb-lg-10 mb-md-8 mb-6">

                            <!-- Email -->
                            <div class="d-grid gap-lg-4 gap-2">
                                <label class="text-n100 font-noto-sans text-base fw-normal">Email</label>
                                <input type="email" name="email"
                                    class="py-lg-4 py-2 px-lg-6 px-4 w-100 bg-n0 text-n100 radius-8 border border-n100-1 focus-secondary2"
                                    placeholder="Enter Your Email" required>
                            </div>

                            <!-- Password -->
                            <div class="d-grid gap-lg-4 gap-2">
                                <label class="text-n100 font-noto-sans text-base fw-normal">Password</label>
                                <div
                                    class="d-flex align-items-center py-lg-4 py-2 px-lg-6 px-4 w-100 bg-n0 text-n100 radius-8 border border-n100-1 focus-secondary2">
                                    <input type="password" name="password" class="w-100 border-0" placeholder="Enter Password" required>
                                    <button type="button" class="text-xl password-toggle">
                                        <i class="ph ph-eye-slash"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Remember Me & Forgot Password -->
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <input type="checkbox" name="remember_me" id="remember_me" class="form-check-input">
                                    <label for="remember_me" class="text-n100 font-noto-sans text-sm">Remember Me</label>
                                </div>
                                <a href="forgot-password.php" class="text-secondary2 text-sm">Forgot Password?</a>
                            </div>

                            <!-- Submit -->
                            <button type="submit" class="btn-secondary py-lg-4 py-2 px-lg-6 px-4 radius-8 w-100">Login</button>
                        </form>

                        <!-- Social Login Options -->
                        <ul class="d-center gap-3 mt-lg-8 mt-6 ">
                            <!-- <span class="d-block text-center text-n100">Or Sign in with</span> -->
                            <li>
                                <!-- Google Login -->
                                <div id="g_id_signin" class="mt-3">
                                    <div id="g_id_onload"
                                        data-client_id="29999697984-l7ihjbcmdnettkh5f9fhr78sskghe8qm.apps.googleusercontent.com"
                                        data-context="signin"
                                        data-ux_mode="popup"
                                        data-login_uri="http://localhost:8000/google-login.php"
                                        data-auto_prompt="false">
                                    </div>

                                    <div class="g_id_signin"
                                        data-type="standard"
                                        data-shape="pill"
                                        data-theme="outline"
                                        data-text="signin_with"
                                        data-size="large"
                                        data-logo_alignment="center">
                                    </div>
                                </div>
                            </li>

                        </ul>
                        <div class="text-center mb-lg-4 mb-2 mt-2">
                            <span class="text-n50">Don't have an account?</span>
                            <a href="register.php" class="text-secondary2 fw-medium">Register</a>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- login section end -->

    <!-- gallery slider -->
    <!-- gallery slider start -->
    <div class="overflow-hidden position-relative z-0">
        <div class="swiper gallery-slider">
            <div class="swiper-wrapper align-items-center z-1">
                <div class="swiper-slide w-fit z-1">
                    <div class="gallery-item position-relative">
                        <img src="assets/images/gallery-1.png" alt="gallery logo">
                        <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
                            <a href="#" class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                                <i class="ph ph-instagram-logo"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide w-fit z-1">
                    <div class="gallery-item position-relative">
                        <img src="assets/images/gallery-2.png" alt="gallery logo">
                        <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
                            <a href="#" class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                                <i class="ph ph-instagram-logo"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide w-fit z-1">
                    <div class="gallery-item position-relative">
                        <img src="assets/images/gallery-3.png" alt="gallery logo">
                        <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
                            <a href="#" class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                                <i class="ph ph-instagram-logo"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide w-fit z-1">
                    <div class="gallery-item position-relative">
                        <img src="assets/images/gallery-4.png" alt="gallery logo">
                        <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
                            <a href="#" class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                                <i class="ph ph-instagram-logo"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide w-fit z-1">
                    <div class="gallery-item position-relative">
                        <img src="assets/images/gallery-5.png" alt="gallery logo">
                        <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
                            <a href="#" class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                                <i class="ph ph-instagram-logo"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide w-fit z-1">
                    <div class="gallery-item position-relative">
                        <img src="assets/images/gallery-6.png" alt="gallery logo">
                        <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
                            <a href="#" class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                                <i class="ph ph-instagram-logo"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide w-fit z-1">
                    <div class="gallery-item position-relative">
                        <img src="assets/images/gallery-7.png" alt="gallery logo">
                        <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
                            <a href="#" class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                                <i class="ph ph-instagram-logo"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide w-fit z-1">
                    <div class="gallery-item position-relative">
                        <img src="assets/images/gallery-8.png" alt="gallery logo">
                        <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
                            <a href="#" class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                                <i class="ph ph-instagram-logo"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide w-fit z-1">
                    <div class="gallery-item position-relative">
                        <img src="assets/images/gallery-9.png" alt="gallery logo">
                        <div class="overlay position-absolute top-0 start-0 w-100 h-100 d-center">
                            <a href="#" class="icon-52px bg-n0 text-secondary2 text-xl hover-bg-primary2 hover-text-n0">
                                <i class="ph ph-instagram-logo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- gallery slider end -->

    <!-- call to action -->
    <!-- call to action section start -->
    <section class="call-to-action-section px-xl-20 px-lg-10 px-sm-7 pt-120 pb-120 bg-n100">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="text-center mb-lg-8 mb-6">
                        <h2 class="text-animation-word display-four text-n0 text-uppercase mb-lg-5 mb-3">
                            JOIN THE
                            <span class="text-secondary2 text-decoration-underline">CYCLECITY</span>
                            COMMUNITY
                        </h2>
                        <p class="text-sm text-n30 fw-normal ch-100 mx-auto">
                            Stay updated with the latest in cycling. Sign up for our newsletter to receive exclusive
                            offers, product updates, and tips straight to your inbox. Join our biking community
                            today!
                        </p>
                    </div>
                    <form action="#" class="d-center flex-wrap flex-sm-nowrap cta-form mx-auto">
                        <input type="email" placeholder="Enter your email address"
                            class="bg-transparent text-n0  py-lg-4 py-3 px-lg-6 px-4 border border-n20-1 focus-primary">
                        <button type="submit"
                            class="text-n100 fw-medium text-capitalize bg-n0 font-instrument py-lg-4 py-3 px-lg-6 px-4 hover-text-n0 box-style box-primary2">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <!-- call to action section end -->

</main>
<?= include_once './layout/footer.php'; ?>