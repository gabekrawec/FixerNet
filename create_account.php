<?php 
require('connect.php');
require 'C:\xampp-windows-x64-8.2.12-0-VS16\xampp\htdocs\WD2\Final Project\php-image-resize-master\lib\ImageResize.php';
require 'C:\xampp-windows-x64-8.2.12-0-VS16\xampp\htdocs\WD2\Final Project\php-image-resize-master\lib\ImageResizeException.php';

$image_upload_detected = isset($_FILES['profile_picture']) && ($_FILES['profile_picture']['error'] === 0);
$upload_error_detected = isset($_FILES['profile_picture']) && ($_FILES['profile_picture']['error'] > 0);

//All this code is pretty messy, but images are complicated and there's a lot to do! 

if(isset($_POST['submit']) && !empty($_POST['username']) && !empty($_POST['password']))
{
    //Check if the username is already taken
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $username_taken_query = "SELECT COUNT(username) FROM users WHERE username = :username";
    $username_taken_statement = $db->prepare($username_taken_query);
    $username_taken_statement->bindValue(':username', $username);
    $username_taken_statement->execute();

    $username_taken = $username_taken_statement->fetchColumn();
    
    if($username_taken == false)
    {
        //Save the profile picture if one was uploaded
        if ($image_upload_detected) 
        { 
            //Creates file path for profile picture
            function file_upload_path($original_filename, $upload_subfolder_name = 'profile_pictures') 
            {
                $current_folder = dirname(__FILE__);
                
                // Build an array of paths segment names to be joins using OS specific slashes.
                $path_segments = [$current_folder, $upload_subfolder_name, basename($original_filename)];
                
                // The DIRECTORY_SEPARATOR constant is OS specific.
                return join(DIRECTORY_SEPARATOR, $path_segments);
            }
                    
            // file_is_an_image() - Checks the mime-type & extension of the uploaded file for "image-ness".
            function file_is_an_image($temporary_path, $new_path) 
            {
                $allowed_mime_types      = ['image/gif', 'image/jpeg', 'image/png'];
                $allowed_file_extensions = ['gif', 'jpg', 'jpeg', 'png'];
                
                $actual_file_extension   = pathinfo($new_path, PATHINFO_EXTENSION);
                $actual_mime_type        = mime_content_type($temporary_path);
                
                $file_extension_is_valid = in_array($actual_file_extension, $allowed_file_extensions);
                $mime_type_is_valid      = in_array($actual_mime_type, $allowed_mime_types);
                
                return $file_extension_is_valid && $mime_type_is_valid;
            }
            $image_filename        = $_FILES['profile_picture']['name'];
            $temporary_image_path  = $_FILES['profile_picture']['tmp_name'];
            $new_image_path        = file_upload_path($image_filename);

            if (file_is_an_image($temporary_image_path, $new_image_path)) 
            {
                move_uploaded_file($temporary_image_path, $new_image_path);

                $filename_without_extension = pathinfo($new_image_path, PATHINFO_FILENAME);
                $file_extension             = pathinfo($new_image_path, PATHINFO_EXTENSION);

                //Save thumbnail
                $thumbnail_image = new \Gumlet\ImageResize($new_image_path);
                $thumbnail_image->resizetoWidth(50);
                $thumbnail_image->crop(50, 50);
                $thumbnail_image->save(dirname($new_image_path) . DIRECTORY_SEPARATOR . $filename_without_extension . $username . '.' . $file_extension);

                $profile_picture = ("profile_pictures" . DIRECTORY_SEPARATOR . $filename_without_extension . $username . '.' . $file_extension);

                //Sanitizes the profile picture just in case the user named it something naughty
                $profile_picture = filter_var($profile_picture, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            }
            else
            {
                header("Location: create_account.php");
                exit;
            }
        }
        else
        {
            $profile_picture = "profile_pictures\default.jpg";
        }

        //Hash the password
        $password = password_hash($password, PASSWORD_DEFAULT);

        //INSERT the data into the database
        $query = "INSERT INTO users (username, password, profile_picture) VALUES (:username, :password, :profile_picture)";
        $statement = $db->prepare($query);

        $statement->bindValue(':username', $username);
        $statement->bindValue(':password', $password);
        $statement->bindValue(':profile_picture', $profile_picture);

        $statement->execute();

        unlink($new_image_path);

        header("Location: login.php");
    }
    else
    {
        //This is pretty bare bones but it works for now
        echo '<script>alert("Username taken!")</script>'; 
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
    <title>Create a new account</title>
</head>
<div class="container">
    <body>
        <?php include('header.php'); ?>
        <!-- Remember that alternative syntax is good and html inside php is bad -->
        <form method="post" action="create_account.php" enctype='multipart/form-data'>
        <div class="accountInfo">
                <h1>Create Account</h1>
                <label for="username">Username</label>
                <input type="text" id="username" name="username">
                <label for="password">Password</label>
                <input type="password" id="password" name='password'>
                <label for='profile_picture'>Upload a profile picture:</label>
                <input type='file' name='profile_picture' id='profile_picture'>

                <?php if ($upload_error_detected): ?>
                <p>Upload Error: <?= $_FILES['profile_picture']['error'] ?></p>
                <?php endif ?>

                <button type="submit" name="submit" id="submit">Create Account</button>
            </div>
        </form>  
    </body>
</div>
</html>