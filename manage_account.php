<?php
require('connect.php');
include('header.php');

if(session_status() == PHP_SESSION_NONE)
{
    session_start();
}

require 'C:\xampp-windows-x64-8.2.12-0-VS16\xampp\htdocs\WD2\Final Project\php-image-resize-master\lib\ImageResize.php';
require 'C:\xampp-windows-x64-8.2.12-0-VS16\xampp\htdocs\WD2\Final Project\php-image-resize-master\lib\ImageResizeException.php';

$image_upload_detected = isset($_FILES['profile_picture']) && ($_FILES['profile_picture']['error'] === 0);
$upload_error_detected = isset($_FILES['profile_picture']) && ($_FILES['profile_picture']['error'] > 0);
$username = $_SESSION['username'];


if(isset($_POST['submit']) && $image_upload_detected)
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

        //Deletes the user's old profile pic. 
        $old_profile_picture = $_SESSION['profile_picture'];
        unlink($old_profile_picture);

        //Technically a devious user could mess around with their session token, but we're not using GET or POST,
        //So I'll consider sanitizing/validating this outside the scope of webdev 2.
        $query = "UPDATE users SET profile_picture = :profile_picture WHERE user_id = :user_id";

        $statement = $db->prepare($query);
        $statement->bindValue(':profile_picture', $profile_picture);
        $statement->bindValue(':user_id', $_SESSION['user_id']);

        $statement->execute();
        unlink($new_image_path);

        $_SESSION['profile_picture'] = $profile_picture;
        header("Location: manage_account.php");
    }
    else
    {
        header("Location: manage_account.php");
        exit;
    }
            
}
else if(isset($_POST['delete_photo']) && ($_SESSION['profile_picture'] != 'profile_pictures\default.jpg'))
{
    $query = "UPDATE users SET profile_picture = 'profile_pictures\default.jpg' WHERE user_id = :user_id";
    $statement = $db->prepare($query);
    $statement->bindValue(':user_id', $_SESSION['user_id']);
    $statement->execute();

    unlink($_SESSION['profile_picture']);

    $_SESSION['profile_picture'] = 'profile_pictures\default.jpg';
    header("Location: manage_account.php");

}
else if (isset($_GET['user_id'])) {

    //sanitize then validate the 
    $user_id = filter_input(INPUT_GET, 'user_id', FILTER_SANITIZE_NUMBER_INT);
    $user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);

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
        <title>Edit Job Posting</title>
    </head>
    <div class="container">
        <body>
            <img src="<?=$_SESSION['profile_picture']?>" alt="">
            <form action="manage_account.php" method="post" enctype='multipart/form-data'>
                <label for='profile_picture'>Change your profile picture:</label>
                <input type='file' name='profile_picture' id='profile_picture'>
                <input type='submit' name='submit' value='Upload Image'>
                
                <button type="submit" name="delete_photo" id="delete_photo">Delete profile picture</button>
            </form>
        </body>
    </div>
</html>