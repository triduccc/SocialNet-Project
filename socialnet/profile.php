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

if (!isset($csrf_secret) || empty($csrf_secret)) {
    die("CSRF configuration error.");
}

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/*
|--------------------------------------------------------------------------
| Determine profile owner
|--------------------------------------------------------------------------
*/

if (isset($_GET["owner"]) && !empty(trim($_GET["owner"]))) {

    $owner = trim($_GET["owner"]);

    if ($owner !== $_SESSION["username"]) {

        if (!isset($_GET["csrf"], $_GET["expires"])) {
            http_response_code(403);
            die("Invalid CSRF token.");
        }

        $csrf = trim($_GET["csrf"]);
        $expires = trim($_GET["expires"]);

        if (!ctype_digit($expires)) {
            http_response_code(403);
            die("Invalid CSRF token.");
        }

        $expires_timestamp = (int) $expires;

        if ($expires_timestamp < time()) {
            http_response_code(403);
            die("Expired CSRF token.");
        }

        $payload = $_SESSION["username"] . "|" . $owner . "|" . $expires_timestamp;
        $expected_csrf = hash_hmac("sha256", $payload, $csrf_secret);

        if (!hash_equals($expected_csrf, $csrf)) {
            http_response_code(403);
            die("Invalid CSRF token.");
        }
    }

} else {

    $owner = $_SESSION["username"];
}

/*
|--------------------------------------------------------------------------
| Get owner information
|--------------------------------------------------------------------------
*/

$sql = "SELECT username, fullname, description
        FROM account
        WHERE username = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param("s", $owner);

$stmt->execute();

$result = $stmt->get_result();

/*
|--------------------------------------------------------------------------
| Check if user exists
|--------------------------------------------------------------------------
*/

if ($result->num_rows != 1) {

    echo "User not found.";

    exit();
}

$user = $result->fetch_assoc();

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>

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

        .profile-box {
            margin-top: 20px;

            border: 1px solid #ddd;
            border-radius: 5px;

            padding: 20px;

            background-color: #fafafa;
        }

        .description {
            margin-top: 15px;
            white-space: pre-wrap;
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

<!-- Main Content -->

<div class="container">

    <h1>Profile Page</h1>

    <div class="profile-box">

        <p>
            <strong>Username:</strong>
            <?php echo htmlspecialchars($user["username"]); ?>
        </p>

        <p>
            <strong>Full Name:</strong>
            <?php echo htmlspecialchars($user["fullname"]); ?>
        </p>

        <hr>

        <h3>Profile Description</h3>

        <div class="description">

            <?php

            if (!empty($user["description"])) {

                echo nl2br(
                    htmlspecialchars($user["description"])
                );

            } else {

                echo "No profile description yet.";
            }

            ?>

        </div>

    </div>

</div>

</body>
</html>

<?php

$conn->close();

?>
