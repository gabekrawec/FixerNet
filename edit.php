<?php
require('connect.php');


// UPDATE post if post_title, post_content, job_pay, and post_id are present in POST.
if (isset($_POST['submit']) && !empty($_POST['post_title']) && !empty($_POST['post_content']) && isset($_POST['post_id']) && !empty($_POST['job_pay'])){

    // Sanitize user input to escape HTML entities and filter out dangerous characters.
    $post_title   = filter_input(INPUT_POST, 'post_title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $post_content = filter_input(INPUT_POST, 'post_content', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    //Sanitize, then validate our numbers. job_pay is a decimal in the database so we use float instead.
    $job_pay      = filter_input(INPUT_POST, 'job_pay', FILTER_SANITIZE_NUMBER_FLOAT);
    $job_pay      = filter_input(INPUT_POST, 'job_pay', FILTER_VALIDATE_FLOAT);

    $post_id      = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);
    $post_id      = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
    
    // Build the parameterized SQL query and bind to the above sanitized values.
    $query     = "UPDATE posts SET post_title = :post_title, post_content = :post_content, job_pay = :job_pay WHERE post_id = :post_id";
    $statement = $db->prepare($query);
    $statement->bindValue(':post_title', $post_title);        
    $statement->bindValue(':post_content', $post_content);
    $statement->bindValue(':job_pay', $job_pay);
    $statement->bindValue(':post_id', $post_id, PDO::PARAM_INT);
    
    // Execute the UPDATE.
    $statement->execute();
    
    // Redirect after update.
    header("Location: index.php");
    exit;
}
//Redirect to error page if user tries to submit an empty post.
else if (isset($_POST['submit']) && (empty($_POST['post_title']) || empty($_POST['post_content']) || empty($_POST['job_pay'])) && isset($_POST['post_id'])) {
    header("Location: error.php");
}
// Retrieve post to be edited, if post_id GET parameter is in URL.
else if (isset($_GET['post_id'])) {
    
    // Sanitize the post_id. Like above but this time from INPUT_GET.
    $post_id = filter_input(INPUT_GET, 'post_id', FILTER_SANITIZE_NUMBER_INT);
    $post_id = filter_input(INPUT_GET, 'post_id', FILTER_VALIDATE_INT);

    // Build the parametrized SQL query using the filtered post_id.
    $query = "SELECT * FROM posts WHERE post_id = :post_id";
    $statement = $db->prepare($query);
    $statement->bindValue(':post_id', $post_id, PDO::PARAM_INT);

    // Execute the SELECT and fetch the single row returned.
    $statement->execute();
    $post = $statement->fetch();

    //Redirects to index if the user enters an invalid url.
    if(!isset($post['post_id'])) {
        header("Location: index.php");
    }
}
else{
    $post_id = false;  //False if we are not UPDATING or SELECTING.
}
if (isset($_POST['delete'])){
    $post_id = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);
    $post_id = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);

    $query = "DELETE FROM posts WHERE post_id = :post_id LIMIT 1";
    $statement = $db->prepare($query);
    $statement->bindValue(':post_id', $post_id, PDO::PARAM_INT);
    $statement->execute();

     // Redirect after update.
     header("Location: index.php");
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
        <?php include('header.php'); ?>
        <!-- Remember that alternative syntax is good and html inside php is bad -->
        <form method="post" action="edit.php">
        <div class="post">
                <!-- Hidden input for primary key. -->
                <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
                <h1>Edit Job Posting</h1>
                <div class="inputs">
                <label for="post_title">Title</label>
                </div>
                <input id="post_title" name="post_title" size="60" value="<?= $post['post_title'] ?>">
                <div class="inputs">
                <label for="job_pay">Pay</label>
                </div>
                <div class="inputs">
                <input id="job_pay" name="job_pay" size="60" value="<?= $post['job_pay'] ?>">
                </div>
                <div class="inputs">
                <label for="post_content">Job Description</label>
                </div>
                <div class="inputs">
                <textarea name="post_content" id="post_content" cols="60" rows="15"><?= $post['post_content'] ?></textarea>
                </div>
                <button type="submit" name="submit">Post</button>
                <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete this post?')">Delete Post</button>
            </div>
        </form>  
    </body>
</div>
</html>