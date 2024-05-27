<?php
if(session_status() == PHP_SESSION_NONE)
{
    session_start();
}
/*******w******** 
    
    Name: Gabriel Krawec
    Date: 2024-01-30
    Description: PHP for the "New Post" page.

****************/

require('connect.php');
//require('authenticate.php');

if (isset($_POST['submit']) && !empty($_POST['post_title']) && !empty($_POST['post_content']) && !empty($_POST['job_pay'])) {

    //  Sanitize user input to escape HTML entities and filter out dangerous characters.
    $post_author = $_SESSION['user_id'];
    
    //Since this comes from a dropdown, sanitizing/validating is probably a little excessive but who cares
    $category_id = filter_input(INPUT_POST, 'category', FILTER_VALIDATE_INT);
    $category_id = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_NUMBER_INT);
    
    $post_title = filter_input(INPUT_POST, 'post_title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $job_pay = filter_input(INPUT_POST, 'job_pay', FILTER_SANITIZE_NUMBER_FLOAT);
    $job_pay = filter_input(INPUT_POST, 'job_pay', FILTER_VALIDATE_FLOAT);

    $post_content = filter_input(INPUT_POST, 'post_content', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    
    //  Build the parameterized SQL query and bind to the above sanitized values.
    $query = "INSERT INTO posts (post_author, category_id, post_title, job_pay, post_content) VALUES (:post_author, :category_id, :post_title, :job_pay, :post_content)";
    //  We created this $db variable back in db_connect.php
    $statement = $db->prepare($query);
    
    //  Bind values to the parameters
    $statement->bindValue(':post_author', $post_author);
    $statement->bindValue(':category_id', $category_id);
    $statement->bindValue(':post_title', $post_title);
    $statement->bindValue(':job_pay', $job_pay);
    $statement->bindValue(':post_content', $post_content);
    
    //  Execute the INSERT.
    //  execute() will check for possible SQL injection and remove if necessary
    $statement->execute();

     // Redirect after update.
    header("Location: index.php");
}
else if (isset($_POST['submit']) && (empty($_POST['post_content']) || empty($_POST['post_title'])))
{
    header("Location: error.php");
}
else
{
    $category_query = "SELECT category_id, category_name FROM categories";
    $category_statement = $db->prepare($category_query);
    $category_statement->execute();
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
    <title>Create a new gig!</title>
</head>
<div class="container">
    <body>
        <?php include('header.php'); ?>
        <!-- Remember that alternative syntax is good and html inside php is bad -->
        <form method="post" action="post.php">
        <div class="post">
                <h1>New Job Posting</h1>
                <div class="inputs">
                <label for="post_title">Title</label>
                </div>
                <input id="post_title" name="post_title" size="60">
                <div class="inputs">
                <label for="category">Category</label>
                <select name="category" id="category">
                    <?php while ($categories = $category_statement->fetch()): ?>
                        <option value=<?=$categories['category_id']?>><?=$categories['category_name']?></option>
                    <?php endwhile ?>
                </select>
                </div>
                <div class="inputs">
                <label for="job_pay">Pay</label>
                </div>
                <div class="inputs">
                <input id="job_pay" name="job_pay" size="60">
                </div>
                <div class="inputs">
                <label for="post_content">Job Description</label>
                </div>
                <div class="inputs">
                <textarea name="post_content" id="post_content" cols="60" rows="15"></textarea>
                </div>
                <button type="submit" name="submit">Post</button>
            </div>
        </form>  
    </body>
</div>
</html>