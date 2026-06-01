<?php
/**
 * Bagaicha - Register Page
 * Processes user registrations including phone and address.
 */
require_once dirname(__DIR__) . '/config/bootstrap.php';

if (!empty($_SESSION['user_email'])) {
    header('Location: ' . url('profile.php'));
    exit;
}

$message = "";
$message_type = ""; // 'success' or 'error'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($fname) || empty($lname) || empty($email) || empty($phone) || empty($address) || empty($password) || empty($confirm_password)) {
        $message = "Please fill in all the fields!";
        $message_type = "error";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match!";
        $message_type = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address!";
        $message_type = "error";
    } elseif (!preg_match("/^(98|97)\d{8}$/", $phone)) {
        $message = "Please enter a valid Nepalese phone number (10 digits, starting with 98 or 97)!";
        $message_type = "error";
    } else {
        // Check if email already exists
        $email_check = $db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $email_check->bindValue(':email', $email, SQLITE3_TEXT);
        $res_email = $email_check->execute();
        $email_exists = $res_email->fetchArray()[0];

        if ($email_exists > 0) {
            $message = "Email address is already registered!";
            $message_type = "error";
        } else {
            // Hash the password securely using bcrypt
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $db->prepare("INSERT INTO users (fname, lname, email, phone, address, password) VALUES (:fname, :lname, :email, :phone, :address, :password)");
            $stmt->bindValue(':fname', $fname, SQLITE3_TEXT);
            $stmt->bindValue(':lname', $lname, SQLITE3_TEXT);
            $stmt->bindValue(':email', $email, SQLITE3_TEXT);
            $stmt->bindValue(':phone', $phone, SQLITE3_TEXT);
            $stmt->bindValue(':address', $address, SQLITE3_TEXT);
            $stmt->bindValue(':password', $hashed_password, SQLITE3_TEXT);

            $result = $stmt->execute();
            if ($result) {
                $_SESSION['registration_success'] = "Registration successful! You can now log in.";
                header("Location: " . url('login.php'));
                exit;
            } else {
                $message = "Error: Something went wrong. Please try again.";
                $message_type = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php 
    $page_title = "Register | Bagaicha";
    $page_description = "Create a secure Bagaicha customer account to easily manage orders, save delivery details, and shop premium Nepalese bonsais.";
    require INCLUDES_PATH . '/partials/head.php'; 
    ?>
</head>

<body class="bg-gray-50/30 text-gray-800 antialiased font-sans">
    <!-- Header -->
    <?php require INCLUDES_PATH . '/header.php'; ?>

    <!-- Register Form Container -->
    <main class="min-h-[85vh] flex items-center justify-center py-16 px-6 md:px-12 bg-gray-50/30">
        <div class="bg-white rounded-3xl border border-gray-100 shadow-xl w-full max-w-lg p-8 md:p-10 animate-fade-in">
            <h2 class="text-2xl font-extrabold text-gray-800 text-center mb-6">Create an Account</h2>
            
            <?php require INCLUDES_PATH . '/partials/alert.php'; ?>

            <form action="/register.php" method="POST" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="fname" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">First Name</label>
                        <input type="text" id="fname" name="fname" placeholder="Ram" required class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 transition-colors">
                    </div>
                    <div>
                        <label for="lname" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Last Name</label>
                        <input type="text" id="lname" name="lname" placeholder="Sharma" required class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 transition-colors">
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="example@gmail.com" required class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 transition-colors">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="phone" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Phone Number</label>
                        <input type="text" id="phone" name="phone" placeholder="98XXXXXXXX" required class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 transition-colors">
                    </div>
                    <div>
                        <label for="address" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Delivery Address</label>
                        <input type="text" id="address" name="address" placeholder="Kathmandu" required class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 transition-colors">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password" required class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 transition-colors">
                </div>

                <div>
                    <label for="confirm_password" class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" required class="w-full bg-white border border-gray-200 focus:border-primary focus:outline-none rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 transition-colors">
                </div>

                <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-white font-bold rounded-xl py-3.5 text-sm transition-colors cursor-pointer shadow-sm hover:shadow-md mt-4">Register</button>
            </form>

            <p class="text-center text-xs md:text-sm text-gray-500 mt-6">
                Already have an account? <a href="/login.php" class="text-primary font-bold hover:underline">Log In</a>
            </p>
        </div>
    </main>

    <!-- Footer -->
    <?php require INCLUDES_PATH . '/footer.php'; ?>
</body>

</html>
