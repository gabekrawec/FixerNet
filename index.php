<?php

/*******w******** 
    
    Name: Gabriel Krawec
    Date: 2024-01-29
    Description: PHP for the home page of the blog.

****************/
require('connect.php');
if(session_status() == PHP_SESSION_NONE)
{
    session_start();
}

$posts_per_page = 3;
$page_count = 1;

if(isset($_GET['current_page_number']))
{
    //Sanitize then validate the current page number, in case the user enters something nasty in the URL.
    $current_page_number = filter_input(INPUT_GET, 'current_page_number', FILTER_SANITIZE_NUMBER_INT);
    $current_page_number = filter_input(INPUT_GET, 'current_page_number', FILTER_VALIDATE_INT);

    $current_page_number = $_GET['current_page_number'];
}
else
{
    $current_page_number = 1;
}
$offset_value = ($current_page_number - 1) * $posts_per_page;

//We need to paginate everything if th user hasn't searched
if(!isset($_POST['search']))
{
    $page_count_query = "SELECT post_id FROM POSTS";
    $page_count_statement = $db->prepare($page_count_query);
    $page_count_statement->execute();

while($page_count_statement->fetch())
{
    $page_count += 1;
}

$page_count = ceil($page_count / $posts_per_page);
}

$category_query = "SELECT c.category_id, c.category_name FROM categories c";
$category_statement = $db->prepare($category_query);
$category_statement->execute();



if(!isset($_SESSION['search_criteria']))
{
    $_SESSION['search_criteria'] = '';
}
if(!isset($_SESSION['category_criteria']))
{
    $_SESSION['category_criteria'] = '';
}
if(!isset($_SESSION['sort_criteria']))
{
    $_SESSION['sort_criteria'] = ' ORDER BY p.date_posted DESC';
}


//Because we're using sessions, we need some kind of mechanism to get back to the full list of results.
if(isset($_POST['reset_results']))
{
    $_SESSION['search_criteria'] = '';
    $_SESSION['category_criteria'] = '';
    $_SESSION['sort_criteria'] = ' ORDER BY p.date_posted DESC';
}

if(isset($_POST['search']) && !empty($_POST['searchbar']))
{
    $keywords = filter_input(INPUT_POST, 'searchbar', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $_SESSION['search_criteria'] = " WHERE p.post_title LIKE '%$keywords%' OR p.post_content LIKE '%$keywords%'";

       $category = $_POST['category'];
       if($category == 'All')
       {
            $_SESSION['category_criteria'] = '';
       }
       else
       {
            $_SESSION['category_criteria'] = " AND p.category_id = $category";
       }
}

if(isset($_POST['sort_by']))
{
    $orderby = $_POST['sort_by'];
    $_SESSION['sort_criteria'] = " ORDER BY $orderby";


}

$post_query = "SELECT p.post_id
                , p.post_title
                , p.category_id
                , p.post_content
                , p.date_posted 
                , p.last_updated
                , c.category_name
                , u.username
                , u.profile_picture
                FROM posts p
                JOIN categories c ON p.category_id=c.category_id
                JOIN users u ON p.post_author=u.user_id"
                . $_SESSION['search_criteria']
                . $_SESSION['category_criteria']
                . $_SESSION['sort_criteria']
                . " LIMIT $posts_per_page OFFSET $offset_value";             

// A PDO::Statement is prepared from the query.
$statement = $db->prepare($post_query);

// Execution on the DB server is delayed until we execute().
$statement->execute(); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FIXER_NET</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jersey+10&family=Oswald:wght@200..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
  </head>
</head>
<div class="container">
<body>
    <!-- Remember that alternative syntax is good and html inside php is bad -->
    <?php include('header.php'); ?>
    
    <!--Search tools-->
    <form action="index.php" method="post">
        <label for="searchbar">Search:</label>
        <input type="text" name="searchbar">
        <label for="category">Category:</label>
        <select name="category" id="category">
            <option value="All">All</option>
            <?php while ($categories = $category_statement->fetch()): ?>
                <option value=<?=$categories['category_id']?>><?=$categories['category_name']?></option>
            <?php endwhile ?>
        </select>
        <button type="submit" name="search">Go</button>
    </form>
    <form action="index.php" method="post">
        <label for="sort_by">Sort By:</label>
        <select name="sort_by" id="sort_by">
            <option value="p.date_posted DESC">Newest</option>
            <option value="p.date_posted">Oldest</option>
            <option value="p.last_updated DESC">Date Updated</option>
            <option value="p.post_title">Title</option>
        </select>
        <button type="submit" name="sort">Sort Posts</button>
    </form>
    <?php if($_SESSION['search_criteria'] != ''): ?>
    <form action="index.php" method="post">
        <button type="submit" name="reset_results">Reset results</button>
    </form>
    <?php endif ?>

    <!--Content-->
    <div class="col-md-8">
        <?php while ($row = $statement->fetch()): ?>        
            <div class="job-posting">
                    <a href="selected_post.php?post_id=<?=$row['post_id']?>"><h2><?=$row['post_title']?></h2> <img src="<?=$row['profile_picture']?>" alt=""></a>
                <div class="job-info">
                    <h3><?=$row['category_name']?></h3>
                    <p>Posted by <?=$row['username']?> on <?=date('F jS, Y, h:i a', strtotime($row['date_posted']))?><?php if (isset($_SESSION['is_admin'])):?><a href="edit.php?post_id=<?=$row['post_id']?>">- Edit</a></p></a><?php endif ?>  
                </div>
                <p><?=substr($row['post_content'], 0, 200)?>
                <?php if(strlen($row['post_content']) > 200): ?>
                    <a href="selected_post.php?post_id=<?=$row['post_id']?>">... Read full post</a>
                <?php endif ?>
                </p> 
            </div>
        <?php endwhile ?>
    </div>
    <div class="pages">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php if(($current_page_number - 1) <= 0): ?> disabled <?php endif?>"><a class="page-link" href="index.php?current_page_number=<?=$current_page_number - 1?>">Previous</a></li>      
            <li class="page-item <?php if(($current_page_number + 1) > $page_count): ?> disabled <?php endif?>"><a class="page-link" href="index.php?current_page_number=<?=$current_page_number + 1?>">Next</a></li>
        </ul>
        <div class="row">
            <div class="col-med-12 text-center">
                Page <?=$current_page_number?> of <?=$page_count?>      
            </div> 
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</div>
</html>