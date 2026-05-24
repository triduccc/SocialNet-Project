<?php

session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../socialnet/config.php";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $input_username = $_POST["username"];
    $input_password = $_POST["password"];

    if (!empty($input_username) && !empty($input_password)) {

        $input_username = trim($input_username);
        $input_password = trim($input_password);

        $stmt = $conn->prepare("SELECT id, username, fullname, password FROM account WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $input_username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows >= 1) {
                $user = $result->fetch_assoc();

                if (password_verify($input_password, $user["password"])) {

                    $_SESSION["username"] = $user["username"];
                    $_SESSION["fullname"] = $user["fullname"];
                    $_SESSION["id"] = $user["id"];

                    header("Location: /socialnet/index.php");
                    exit();

                } else {
                    $message = "Invalid password.";
                }

            } else {
                $message = "User does not exist.";
            }

            $stmt->close();
        } else {
            $message = "Internal error.";
        }

    } else {
        $message = "All fields are required.";
    }
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In</title>

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
            margin-bottom: 20px;
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
            border-radius: 5px;

            background-color: #2e8b57;
            color: white;

            cursor: pointer;
        }

        button:hover {
            background-color: #0077cc;
        }

        .message {
            margin-top: 15px;
            text-align: center;
            color: red;
        }

    </style>

</head>
<body>

<div class="container">

    <h1>Sign In</h1>

    <form method="POST">

        <input
            type="text"
            name="username"
            placeholder="Username"
            required
        >

        <input
            type="password"
            name="password"
            placeholder="Password"
            required
        >

        <button type="submit">
            Sign In
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
