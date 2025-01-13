<?php
/*

bWAPP, or a buggy web application, is a free and open-source deliberately insecure web application.
It helps security enthusiasts, developers, and students to discover and prevent web vulnerabilities.
bWAPP covers all major known web vulnerabilities, including all risks from the OWASP Top 10 project!
It is for security-testing and educational purposes only.

Enjoy!

Malik Mesellem
Twitter: @MME_IT

bWAPP is licensed under a Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
(http://creativecommons.org/licenses/by-nc-nd/4.0/). Copyright © 2014 MME BVBA. All rights reserved.

*/

include("security.php");
include("security_level_check.php");
include("admin/settings.php");

// Redirect HTTP to HTTPS
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}

// Load bugs from file
$bugs = file("bugs.txt");

// Handle bug selection
if (isset($_POST["form_bug"]) && isset($_POST["bug"])) {
    $key = filter_input(INPUT_POST, "bug", FILTER_VALIDATE_INT);
    if ($key !== false && isset($bugs[$key])) {
        $bug = explode(",", trim($bugs[$key]));
        header("Location: " . htmlspecialchars($bug[1]));
        exit;
    }
}

// Handle security level setting
if (isset($_POST["form_security_level"]) && isset($_POST["security_level"])) {
    $security_level_cookie = filter_input(INPUT_POST, "security_level", FILTER_SANITIZE_STRING);
    $allowed_levels = ["0", "1", "2"];
    if (!in_array($security_level_cookie, $allowed_levels)) {
        $security_level_cookie = "0";
    }

    $secure_cookie_options = [
        "expires" => time() + 60 * 60 * 24 * 365,
        "path" => "/",
        "domain" => $_SERVER['HTTP_HOST'],
        "secure" => true,
        "httponly" => true,
        "samesite" => "Strict"
    ];

    if ($evil_bee == 1) {
        setcookie("security_level", "666", $secure_cookie_options);
    } else {
        setcookie("security_level", $security_level_cookie, $secure_cookie_options);
    }

    header("Location: ba_pwd_attacks.php");
    exit;
}

// Determine current security level
$security_level = isset($_COOKIE["security_level"]) ? $_COOKIE["security_level"] : "0";
$security_level = in_array($security_level, ["0", "1", "2", "666"]) ? $security_level : "0";
$security_level_label = ["0" => "low", "1" => "medium", "2" => "high", "666" => "666"][$security_level];

// Handle login form submission
$message = "";
if (isset($_POST["form"])) {
    $login = filter_input(INPUT_POST, "login", FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_STRING);

    if ($login === $GLOBALS['login'] && $password === $GLOBALS['password']) {
        session_start();
        session_regenerate_id(true); // Prevent session fixation
        $_SESSION["login"] = $login;
        $message = "<font color=\"green\">Successful login!</font>";
    } else {
        $message = "<font color=\"red\">Invalid credentials! Did you forget your password?</font>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="stylesheets/stylesheet.css" media="screen">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <script src="js/html5.js"></script>
    <title>bWAPP - Broken Authentication</title>
</head>

<body>
<header>
    <h1>bWAPP</h1>
    <h2>an extremely buggy web app!</h2>
</header>

<div id="menu">
    <table>
        <tr>
            <td><a href="portal.php">Bugs</a></td>
            <td><a href="password_change.php">Change Password</a></td>
            <td><a href="user_extra.php">Create User</a></td>
            <td><a href="security_level_set.php">Set Security Level</a></td>
            <td><a href="reset.php" onclick="return confirm('All settings will be cleared. Are you sure?');">Reset</a></td>
            <td><a href="credits.php">Credits</a></td>
            <td><a href="http://itsecgames.blogspot.com" target="_blank">Blog</a></td>
            <td><a href="logout.php" onclick="return confirm('Are you sure you want to leave?');">Logout</a></td>
            <td><font color="red">Welcome <?php echo htmlspecialchars($_SESSION["login"] ?? 'Guest'); ?></font></td>
        </tr>
    </table>
</div>

<div id="main">
    <h1>Broken Auth. - Password Attacks</h1>
    <p>Enter your credentials <i>(bee/bug)</i>.</p>
    <form action="<?php echo htmlspecialchars($_SERVER["SCRIPT_NAME"]); ?>" method="POST">
        <p><label for="login">Login:</label><br>
            <input type="text" id="login" name="login" size="20" autocomplete="off"></p>
        <p><label for="password">Password:</label><br>
            <input type="password" id="password" name="password" size="20" autocomplete="off"></p>
        <button type="submit" name="form" value="submit">Login</button>
    </form>
    <br>
    <?php echo $message; ?>
</div>

<div id="security_level">
    <form action="<?php echo htmlspecialchars($_SERVER["SCRIPT_NAME"]); ?>" method="POST">
        <label>Set your security level:</label><br>
        <select name="security_level">
            <option value="0" <?php echo $security_level === "0" ? "selected" : ""; ?>>low</option>
            <option value="1" <?php echo $security_level === "1" ? "selected" : ""; ?>>medium</option>
            <option value="2" <?php echo $security_level === "2" ? "selected" : ""; ?>>high</option>
        </select>
        <button type="submit" name="form_security_level" value="submit">Set</button>
        <font size="4">Current: <b><?php echo $security_level_label; ?></b></font>
    </form>
</div>

<div id="bug">
    <form action="<?php echo htmlspecialchars($_SERVER["SCRIPT_NAME"]); ?>" method="POST">
        <label>Choose your bug:</label><br>
        <select name="bug">
            <?php
            foreach ($bugs as $key => $value) {
                $bug = explode(",", trim($value));
                echo "<option value='" . htmlspecialchars($key) . "'>" . htmlspecialchars($bug[0]) . "</option>";
            }
            ?>
        </select>
        <button type="submit" name="form_bug" value="submit">Hack</button>
    </form>
</div>

<footer>
    <p>bWAPP is licensed under <a href="http://creativecommons.org/licenses/by-nc-nd/4.0/" target="_blank">Creative Commons</a>.</p>
</footer>

</body>
</html>
