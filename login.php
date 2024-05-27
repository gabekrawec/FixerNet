<?php
require('connect.php');

//This session will be used to tell if the user is logged in
session_start();

if (isset($_POST['submit']) && !empty($_POST['username']) && !empty($_POST['password']))
{
    //Check if username exists in database
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $query = "SELECT user_id, password, username, is_admin, profile_picture FROM users WHERE username = :username";
    $statement = $db->prepare($query);
    $statement-> bindValue(':username', $username);
    $statement->execute();

    $user_found = $statement->fetch();

    if($user_found)
    {
        //Check password
        $hashed_password = $user_found['password'];
        if(password_verify($password, $hashed_password))
        {
            //Success! Now to set the session variables. Just doing username right now, but
            //once pfps are set up I could put one in the corner or w/e
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $user_found['username'];
            $_SESSION['user_id'] = $user_found['user_id'];
            $_SESSION['profile_picture'] = $user_found['profile_picture'];
            if($user_found['is_admin'] == 1)
            {
                $_SESSION['is_admin'] = true;
            }
            
            

            header("Location: index.php");
        }
        else
        {
            echo '<script>alert("Wrong password!")</script>'; 
        }
    }
    else
    {
        echo '<script>alert("User not found!")</script>';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jersey+10&family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <title>Login to your account</title>
</head>
<div class="container">
    <body>
        <?php include('header.php'); ?>
        <!-- Remember that alternative syntax is good and html inside php is bad -->
        <form method="post" action="login.php">
        <div class="accountInfo">
                <h1>Login</h1>
                <label for="username">Username</label>
                <input type="text" id="username" name="username">
                <label for="password">Password</label>
                <input type="password" id="password" name='password'>

                <button type="submit" name="submit" id="submit">Login</button>
            </div>
        </form>  
    </body>
</div>
</html>