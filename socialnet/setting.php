<?php

session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/*
|--------------------------------------------------------------------------
| Check login
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["username"])) {

    header("Location: /socialnet/signin.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Database connection
|--------------------------------------------------------------------------
*/

require_once "config.php";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/*
|--------------------------------------------------------------------------
| Current user
|--------------------------------------------------------------------------
*/

$current_username = $_SESSION["username"];

$message = "";

/*
|--------------------------------------------------------------------------
| Update description
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $description = trim($_POST["description"]);

    $sql = "UPDATE account
            SET description = ?
            WHERE username = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $description, $current_username);

    if ($stmt->execute()) {
        $message = "Profile updated successfully.";
    } else {
        $message = "Error updating profile.";
    }

    $stmt->close();
}

/*
|--------------------------------------------------------------------------
| Get current description
|--------------------------------------------------------------------------
*/

$sql = "SELECT description
        FROM account
        WHERE username = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param("s", $current_username);

$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();

$current_description = $user["description"] ?? "";

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setting</title>

    <style>

        body {
            font-family: Verdana, sans-serif;
            margin: 0;
            background-color: #f4f4f4;
        }

        /*
        |--------------------------------------------------------------------------
        | Navbar
        |--------------------------------------------------------------------------
        */

        .navbar {
            background-color: #2e8b57;
            padding: 15px;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin-right: 20px;
            font-weight: bold;
        }

        .navbar a:hover {
            text-decoration: underline;
        }

        /*
        |--------------------------------------------------------------------------
        | Content
        |--------------------------------------------------------------------------
        */

        .container {
            width: 80%;
            margin: 30px auto;

            background: white;
            padding: 30px;

            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        textarea {
            width: 100%;
            height: 200px;

            padding: 10px;
            margin-top: 10px;

            border-radius: 5px;
            border: 1px solid #ccc;

            resize: vertical;
            box-sizing: border-box;
        }

        button {
            margin-top: 15px;
            padding: 10px 20px;

            border: none;
            border-radius: 5px;

            background-color: #2e8b57;
            color: white;

            cursor: pointer;
        }

        button:hover {
            background-color: #005fa3;
        }

        .message {
            margin-top: 15px;
            color: green;
        }

    </style>

</head>
<body>

<!-- Navbar -->

<div class="navbar">

    <a href="/socialnet/index.php">Home</a>

    <a href="/socialnet/setting.php">Setting</a>

    <a href="/socialnet/profile.php">Profile</a>

    <a href="/socialnet/about.php">About</a>

    <a href="/socialnet/signout.php">Sign Out</a>

</div>

<!-- Content -->

<div class="container">

    <h1>Setting Page</h1>

    <p>
        Edit your profile description below.
    </p>

    <form method="POST">

        <textarea
            name="description"
            placeholder="Write something about yourself..."
        ><?php echo htmlspecialchars($current_description); ?></textarea>

        <br>

        <button type="submit">
            Save Profile
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

<?php

$conn->close();

?>
