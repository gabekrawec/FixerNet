<?php 
if(session_status() == PHP_SESSION_NONE)
{
    session_start();
}
/*******w******** 
    
    Name: Gabriel Krawec
    Date: 2024-01-30
    Description: PHP for the page that displays when the user selects a post.

****************/
require('connect.php');
$post_id = filter_input(INPUT_GET, 'post_id', FILTER_SANITIZE_NUMBER_INT);
$post_id = filter_input(INPUT_GET, 'post_id', FILTER_VALIDATE_INT);

$post_query = "SELECT * FROM posts WHERE post_id = :post_id";
$post_statement = $db->prepare($post_query);
$post_statement->bindValue(':post_id', $post_id, PDO::PARAM_INT);
$post_statement->execute();
$post = $post_statement->fetch();

//Redirects to index if the user enters an invalid url.
if(!isset($post['post_id'])) 
{
    header("Location: index.php");
}

$comment_query = "SELECT comment_id
                    , comment_author
                    , post_id
                    , date_posted
                    , comment_content
                    , username
                        FROM comments JOIN users ON comment_author=user_id  
                        WHERE post_id = :post_id
                        ORDER BY date_posted DESC";
$comment_statement = $db->prepare($comment_query);
$comment_statement->bindValue(':post_id', $post_id, PDO::PARAM_INT);
$comment_statement->execute();

if(isset($_POST['submit']) && !empty($_POST['comment_content']))
{
    $comment_author = $_SESSION['user_id'];
    $comment_content  = filter_input(INPUT_POST, 'comment_content', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    //Runs the comment content through a profanity API
    $curl = curl_init();
    
    // Set URL, ensure results are being returned as a string
    curl_setopt($curl, CURLOPT_URL, "https://www.purgomalum.com/service/plain?text=$comment_content");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); 
    $comment_content = curl_exec($curl);
    curl_close($curl);
    

    $user_comment_query = "INSERT INTO comments (comment_author, post_id, comment_content)
                                         VALUES (:comment_author, :post_id, :comment_content)";
    $user_comment_statement = $db->prepare($user_comment_query);
    $user_comment_statement->bindValue(':comment_author', $comment_author, PDO::PARAM_INT);
    $user_comment_statement->bindValue(':post_id', $post_id, PDO::PARAM_INT);
    $user_comment_statement->bindValue(':comment_content', $comment_content);
    $user_comment_statement->execute();

    // Redirect to the same page to refresh comments
    header("Location: selected_post.php?post_id=$post_id");
    exit; 
}
if (isset($_POST['delete'])){
    $comment_id = filter_input(INPUT_POST, 'comment_id', FILTER_SANITIZE_NUMBER_INT);
    $comment_id = filter_input(INPUT_POST, 'comment_id', FILTER_VALIDATE_INT);

    $comment_delete_query = "DELETE FROM comments WHERE comment_id = :comment_id LIMIT 1";
    $comment_delete_statement = $db->prepare($comment_delete_query);
    $comment_delete_statement->bindValue(':comment_id', $comment_id, PDO::PARAM_INT);
    $comment_delete_statement->execute();

    // Redirect after update.
    header("Location: selected_post.php?post_id=$post_id");
    exit; 
}
if (isset($_POST['disemvowel']))
{
    //Retrieve the comment to be disemvoweled
    $comment_id = filter_input(INPUT_POST, 'comment_id', FILTER_SANITIZE_NUMBER_INT);
    $comment_id = filter_input(INPUT_POST, 'comment_id', FILTER_VALIDATE_INT);
    
    $retrieve_comment_query = "SELECT comment_content FROM comments WHERE comment_id = :comment_id";
    $retrieve_comment_statement = $db->prepare($retrieve_comment_query);
    $retrieve_comment_statement->bindValue(':comment_id', $comment_id, PDO::PARAM_INT);
    $retrieve_comment_statement->execute();

    //Disemvowel the comment
    $comment_content = $retrieve_comment_statement->fetch();
    $comment_content = filter_var($comment_content, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $disemvoweled_comment = disemvowel($comment_content);

    $disemvowel_comment_query = "UPDATE comments SET comment_content = :disemvoweled_comment WHERE comment_id = :comment_id";
    $disemvowel_comment_statement = $db->prepare($disemvowel_comment_query);
    $disemvowel_comment_statement->bindValue(':disemvoweled_comment', $disemvoweled_comment[0]);
    $disemvowel_comment_statement->bindValue(':comment_id', $comment_id);
    $disemvowel_comment_statement->execute();
    

    // Redirect after update.
    header("Location: selected_post.php?post_id=$post_id");
    exit; 
}
function disemvowel($comment)
{
    return str_replace(array('a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'), '*', $comment);
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
    
    
    <title><?=$post['post_title']?></title>
</head>
<div class="container">
    <body>
        <?php include('header.php'); ?>
        <!-- Remember that alternative syntax is good and html inside php is bad -->
        <div id="container">
            <div class="post">
                <h2><?=$post['post_title']?></h2></a>
                <div class="timestamp">
                    <p><?=date('F jS, Y H:i', strtotime($post['date_posted']))?><?php if (isset($_SESSION['is_admin'])):?><a href="edit.php?post_id=<?=$row['post_id']?>">- Edit</a></p></a><?php endif ?></p>  
                </div>
                <p><?=$post['post_content']?></p> 
            </div>
            <div id="comments">
                <h3>Comments</h3>
                <?php if(isset($_SESSION['logged_in'])):?>
                    <form action="selected_post.php?post_id=<?=$post['post_id']?>" method="post">
                        <div class="inputs">
                            <label for="comment_content">Write a comment</label>
                        </div>
                        <textarea name="comment_content" id="comment_content" cols="60" rows="6"></textarea>
                        <button type="submit" name="submit">Post</button>
                    </form>
                <?php endif ?>
                <?php if(!isset($_SESSION['logged_in'])):?>
                    <p> <a href="login.php">Login</a> or <a href="create_account.php">Sign Up</a> to post a comment</p>
                <?php endif ?>
                <?php while ($comment = $comment_statement->fetch()): ?>

                <form action="selected_post.php?post_id=<?=$post['post_id']?>" method="post">
                    <!--We need this hidden input for deleting to work properly-->
                    <input type="hidden" name="comment_id" value="<?= $comment['comment_id'] ?>">
                    <h4><?=$comment['username']?></h4>
                    <p><?=date('F jS, Y, h:i a', strtotime($comment['date_posted']))?>
                    <!--Users can delete their own comments, admins can delete all comments.-->
                    <?php if(isset($_SESSION['is_admin'])):?>
                    <button type="submit" name="disemvowel"onclick="return confirm('Are you sure you want to remove the vowels from this comment?')">Disemvowel Comment</button>
                    <?php endif?>
                    <?php if(isset($_SESSION['is_admin']) || (isset($_SESSION['user_id']) && ($comment['comment_author'] == $_SESSION['user_id']))):?>
                    <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete this comment?')"> Delete Comment</button>
                    <?php endif?>
                    
                    
                    <p><?=$comment['comment_content']?></p> 
                </form>
                <?php endwhile ?>
            </div>
        </div>
    </body>
</div>
</html>