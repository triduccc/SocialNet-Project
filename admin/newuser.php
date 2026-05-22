<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../socialnet/config.php";

// Ensure a CSRF token exists for the session and form
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Verify CSRF token
    $posted_token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $posted_token)) {
        // Token missing or invalid; reject the request
        $message = "Invalid CSRF token. Request denied.";
    } else {

        $new_username = trim($_POST["username"]);
        $new_fullname = trim($_POST["fullname"]);
        $new_password = trim($_POST["password"]);

        if (
            !empty($new_username) &&
            !empty($new_fullname) &&
            !empty($new_password)
        ) {

            // hash password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO account (username, fullname, password)
                    VALUES (?, ?, ?)";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "sss",
                $new_username,
                $new_fullname,
                $hashed_password
            );

            if ($stmt->execute()) {
                $message = "User created successfully!";
                // Regenerate token after successful state change to prevent replay
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            } else {
                $message = "Error: " . $stmt->error;
            }

            $stmt->close();

        } else {
            $message = "All fields are required.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>

    <style>
        body {
            font-family: Verdana, sans-serif;
            background-color: #f4f4f4;

            display: flex;
            justify-content: center;
            align-items: center;

            height: 100vh;
            margin: 0;
        }

        .container {
            background: white;
            padding: 30px;
            width: 400px;

            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-top: 10px;

            border: 1px solid #ccc;
            border-radius: 5px;

            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            margin-top: 15px;

            border: none;
            background-color: #5c5740;
            color: white;

            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #005fa3;
        }

        .message {
            margin-top: 15px;
            text-align: center;
            color: green;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>

<div class="container">

    <h1>Create New User</h1>


    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

        <input
            type="text"
            name="username"
            placeholder="Username"
            required
        >

        <input
            type="text"
            name="fullname"
            placeholder="Full Name"
            required
        >

        <input
            type="password"
            name="password"
            placeholder="Password"
            required
        >

        <button type="submit">
            Create User
        </button>

    </form>

    <?php if (!empty($message)) : ?>
        <div class="message">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
