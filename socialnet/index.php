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
| Current user info
|--------------------------------------------------------------------------
*/

$current_username = $_SESSION["username"];
$current_fullname = $_SESSION["fullname"];

if (!isset($csrf_secret) || empty($csrf_secret)) {
    die("CSRF configuration error.");
}

/*
|--------------------------------------------------------------------------
| Get other users
|--------------------------------------------------------------------------
*/

$sql = "SELECT username, fullname
        FROM account
        WHERE username != ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param("s", $current_username);

$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>

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

        h1, h2 {
            margin-top: 0;
        }

        .user-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin-top: 10px;

            border-radius: 5px;
            background-color: #fafafa;
        }

        .profile-link {
            display: inline-block;
            margin-top: 10px;

            color: #0077cc;
            text-decoration: none;
        }

        .profile-link:hover {
            text-decoration: underline;
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

    <h1>Home Page</h1>

    <h2>Your Information</h2>

    <p>
        <strong>Username:</strong>
        <?php echo htmlspecialchars($current_username); ?>
    </p>

    <p>
        <strong>Full Name:</strong>
        <?php echo htmlspecialchars($current_fullname); ?>
    </p>

    <hr>

    <h2>Other Users</h2>

    <?php while ($row = $result->fetch_assoc()) : ?>

        <?php

        $profile_owner = $row["username"];
        $profile_expires = time() + 300;
        $profile_payload = $current_username . "|" . $profile_owner . "|" . $profile_expires;
        $profile_csrf = hash_hmac("sha256", $profile_payload, $csrf_secret);

        ?>

        <div class="user-card">

            <p>
                <strong>Username:</strong>
                <?php echo htmlspecialchars($row["username"]); ?>
            </p>

            <p>
                <strong>Full Name:</strong>
                <?php echo htmlspecialchars($row["fullname"]); ?>
            </p>

            <a
                class="profile-link"
                href="/socialnet/profile.php?owner=<?php echo urlencode($profile_owner); ?>&expires=<?php echo urlencode((string) $profile_expires); ?>&csrf=<?php echo urlencode($profile_csrf); ?>"
            >
                View Profile
            </a>

        </div>

    <?php endwhile; ?>

</div>

</body>
</html>

<?php

$stmt->close();
$conn->close();

?>
