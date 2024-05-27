<?php
require('connect.php');
if (isset($_POST['submit']) && !empty($_POST['username'])) 
{
    $username  = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    //Sanitize, then validate the int. Honestly overkill since the input is hidden, but better
    //safe than sorry.
    $user_id   = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
    $user_id   = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

    $is_admin  = isset($_POST['is_admin']) ? 1 : 0;

    
    //We'll update everything if they picked a new password
    if(!empty($_POST['new_password']))
    {
        $new_password = filter_input(INPUT_POST, 'new_password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $new_password = password_hash($new_password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET username = :username, password = :new_password, is_admin = :is_admin WHERE user_id = :user_id";

        $statement = $db->prepare($query);
        $statement->bindValue(':new_password', $new_password);
    }
    //Otherwise, update everything except the password
    else
    {
        $query = "UPDATE users SET username = :username, is_admin = :is_admin WHERE user_id = :user_id";
        $statement = $db->prepare($query);
    }

    $statement->bindValue(':username', $username);        
    $statement->bindValue(':is_admin', $is_admin);
    $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    
    // Execute the UPDATE.
    $statement->execute();
    
    // Redirect after update.
    header("Location: manage_users.php");
    exit;
}
//Redirect to error page if user tries to submit an empty post.
else if (isset($_POST['submit']) && (empty($_POST['username'])))
{
    echo '<script>alert("Username must be at least one character long!")</script>'; 
}

// Retrieve user to be edited, if user_id GET parameter is in URL.
else if (isset($_GET['user_id'])) {
    // Sanitize the post_id. Like above but this time from INPUT_GET.
    $user_id = filter_input(INPUT_GET, 'user_id', FILTER_SANITIZE_NUMBER_INT);

    // Build the parametrized SQL query using the filtered post_id.
    $query = "SELECT * FROM users WHERE user_id = :user_id";
    $statement = $db->prepare($query);
    $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);

    // Execute the SELECT and fetch the single row returned.
    $statement->execute();
    $user = $statement->fetch();

    //Redirects to index if the user enters an invalid url.
    if(!isset($user['user_id'])) {
        header("Location: index.php");
    }
    
    
}
else{
    $user_id = false;  //False if we are not UPDATING or SELECTING.
}
if (isset($_POST['delete'])){

    //We sanitize then validate the ID here too
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

    $query = "DELETE FROM users WHERE user_id = :user_id LIMIT 1";
    $statement = $db->prepare($query);
    $statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $statement->execute();

     // Redirect after update.
     header("Location: manage_users.php");
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
    
   
    <title>Edit User</title>
</head>
<div class="container">
    <body>
        <?php include('header.php'); ?>
        <!-- Remember that alternative syntax is good and html inside php is bad -->
        <form method="post" action="edit_user.php">
            <div class="user">
                <!-- Hidden input for primary key. -->
                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                <h1>Edit User</h1>
                <img src="<?=$user['profile_picture']?>" alt="">
                <div class="inputs">
                <label for="username">Username</label>
                </div>
                <input id="username" name="username" size="60" value="<?= $user['username'] ?>">
                <div class="inputs">
                <label for="new_password">Update Password</label>
                </div>
                <div class="inputs">
                <input id="new_password" name="new_password" size="60">
                </div>
                <div class="inputs">
                <label for="is_admin">Is Admin</label>
                </div>
                <div class="inputs">
                <input type="checkbox" name="is_admin" id="is_admin" <?php if($user['is_admin'] == 1):?> checked <?php endif ?>>
                </div>
                <button type="submit" name="submit">Save Changes</button>
                <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete this user?')">Delete User</button>
            </div>
        </form>  
    </body>
</div>
</html>