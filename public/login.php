<?php
/**
 * Bagaicha - Login Page
 * Verifies bcrypt password hashes and launches PHP user sessions using Email address.
 */
require_once dirname(__DIR__) . '/config/bootstrap.php';

if (!empty($_SESSION['email'])) {
    header('Location: ' . url('profile.php'));
    exit;
}

$message = "";
$message_type = ""; // 'success' or 'error'

// Retrieve registration success messages if redirected from register.php
if (isset($_SESSION['registration_success'])) {
    $message = $_SESSION['registration_success'];
    $message_type = "success";
    unset($_SESSION['registration_success']); // Clear message
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $message = "Please fill in all fields!";
        $message_type = "error";
    } else {
        // Query user by email address
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $res = $stmt->execute();
        $user_row = $res->fetchArray(SQLITE3_ASSOC);

        if ($user_row && password_verify($password, $user_row['password'])) {
            // Login success - save session variables
            $_SESSION['user_id'] = $user_row['id'];
            $_SESSION['fname'] = $user_row['fname'];
            $_SESSION['lname'] = $user_row['lname'];
            $_SESSION['email'] = $user_row['email'];
            $_SESSION['phone'] = $user_row['phone'];
            $_SESSION['address'] = $user_row['address'];

            header("Location: " . url('index.php'));
            exit;
        } else {
            $message = "Invalid email address or password!";
            $message_type = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php 
    $page_title = "Log In | Bagaicha";
    $page_description = "Access your Bagaicha account portal to view your purchase logs, pending orders, and personal credentials.";
    require INCLUDES_PATH . '/partials/head.php'; 
    ?>
</head>

<body class="bg-gray-50/30 text-gray-800 antialiased font-sans">
    <!-- Header -->
    <?php require INCLUDES_PATH . '/header.php'; ?>

    <!-- Login Form Container -->
    <main class="min-h-[70vh] flex items-center justify-center py-16 px-6 md:px-12 bg-gray-50/30">
        <div class="bg-white rounded-3xl border border-gray-100 shadow-xl w-full max-w-md p-8 md:p-10 animate-fade-in">
            <h2 class="text-2xl font-extrabold text-gray-800 text-center mb-6">Log In to Bagaicha</h2>
            
            <?php require INCLUDES_PATH . '/partials/alert.php'; ?>

            <form action="/login.php" method="POST" class="space-y-4">
                <div>
                    <label for="email" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="example@gmail.com" required class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 transition-colors">
                </div>

                <div>
                    <label for="password" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password" required class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 transition-colors">
                </div>

                <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-white font-bold rounded-xl py-3.5 text-sm transition-colors cursor-pointer shadow-sm hover:shadow-md mt-2">Log In</button>
            </form>

            <p class="text-center text-xs md:text-sm text-gray-500 mt-6">
                Don't have an account? <a href="/register.php" class="text-primary font-bold hover:underline">Register Now</a>
            </p>
        </div>
    </main>

    <!-- Footer -->
    <?php require INCLUDES_PATH . '/footer.php'; ?>
</body>

</html>
