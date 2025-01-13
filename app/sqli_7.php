<?php

/*

bWAPP, or a buggy web application, is a free and open source deliberately insecure web application.
It helps security enthusiasts, developers and students to discover and to prevent web vulnerabilities.
bWAPP covers all major known web vulnerabilities, including all risks from the OWASP Top 10 project!
It is for security-testing and educational purposes only.

Enjoy!

Malik Mesellem
Twitter: @MME_IT

bWAPP is licensed under a Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License (http://creativecommons.org/licenses/by-nc-nd/4.0/). Copyright © 2014 MME BVBA. All rights reserved.

*/

include("security.php");
include("security_level_check.php");
include("selections.php");
include("functions_external.php");
include("connect_i.php");

$entry = "";
$owner = "";
$message = "";

function sqli($data, $link)
{
    switch ($_COOKIE["security_level"] ?? "0") {
        case "1":
            $data = sqli_check_1($data);
            break;
        case "2":
            $data = sqli_check_3($link, $data);
            break;
        default:
            $data = no_check($data);
            break;
    }

    return $data;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" type="text/css" href="stylesheets/stylesheet.css" media="screen" />
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
    <script src="js/html5.js"></script>
    <title>bWAPP - SQL Injection</title>
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
            <td><font color="red">Welcome <?php if (isset($_SESSION["login"])) { echo htmlspecialchars(ucwords($_SESSION["login"])); } ?></font></td>
        </tr>
    </table>
</div>
<div id="main">
    <h1>SQL Injection - Stored (Blog)</h1>
    <form action="<?php echo htmlspecialchars($_SERVER["SCRIPT_NAME"]); ?>" method="POST">
        <p><label for="entry">Add an entry to our blog:</label><br />
            <textarea name="entry" id="entry" cols="80" rows="3"></textarea></p>
        <button type="submit" name="blog" value="add">Add Entry</button>
        <?php
        if (isset($_POST["blog"])) {
            $entry = $_POST["entry"];
            $owner = $_SESSION["login"] ?? "";

            if (empty($entry)) {
                $message = "<font color=\"red\">Please enter some text...</font>";
            } else {
                $sql = "INSERT INTO blog (date, entry, owner) VALUES (now(), ?, ?)";
                $stmt = $link->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("ss", $entry, $owner);
                    if ($stmt->execute()) {
                        $message = "<font color=\"green\">The entry was added to our blog!</font>";
                    } else {
                        $message = "<font color=\"red\">Error: " . htmlspecialchars($stmt->error) . "</font>";
                    }
                    $stmt->close();
                } else {
                    $message = "<font color=\"red\">Error: Unable to prepare statement.</font>";
                }
            }
        }
        echo "&nbsp;&nbsp;" . $message;
        ?>
    </form>
    <br />
    <table id="table_yellow">
        <tr height="30" bgcolor="#ffb717" align="center">
            <td width="20">#</td>
            <td width="100"><b>Owner</b></td>
            <td width="100"><b>Date</b></td>
            <td width="445"><b>Entry</b></td>
        </tr>
        <?php
        $sql = "SELECT * FROM blog";
        $result = $link->query($sql);
        if ($result) {
            while ($row = $result->fetch_object()) {
                ?>
                <tr height="40">
                    <td align="center"><?php echo htmlspecialchars($row->id); ?></td>
                    <td><?php echo htmlspecialchars($row->owner); ?></td>
                    <td><?php echo htmlspecialchars($row->date); ?></td>
                    <td><?php echo htmlspecialchars($row->entry); ?></td>
                </tr>
                <?php
            }
            $result->close();
        } else {
            ?>
            <tr height="50">
                <td colspan="4" width="665">Error: <?php echo htmlspecialchars($link->error); ?></td>
            </tr>
            <?php
        }
        ?>
    </table>
</div>
</body>
</html>
