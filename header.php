<!--
    Name: Gabriel Krawec
    Date: 2024-01-29
    Description: PHP for the header.
-->
<?php 
if(session_status() == PHP_SESSION_NONE)
{
    session_start();
}
?>
<head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<link rel="stylesheet" href="styles.css">

</head>
<header>
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <!--Logo-->
                <p class="glitch">
                    <span aria-hidden="true">FIXER_NET</span>
                    <a href="index.php">FIXER_NET</a>
                </p>
            </div>
            <!--Welcome Message-->
            <div class="col-sm-3">
                <?php if (isset($_SESSION['logged_in'])) :?>
                    <p>Welcome to FIXER_NET, <?= $_SESSION['username']?>.</p> 
            </div>  
            <div class="col-sm-1">
                <img src="<?=$_SESSION['profile_picture']?>" alt="">
            </div>
                <?php endif ?>
        </div>
        <!--Navbar-->
        <nav class="nav justify-content-end">
            <?php if (isset($_SESSION['is_admin'])):?>
                <li class="nav-item">
                    <a class="nav-link" href="post.php">New Gig</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_users.php">Manage Users</a>
                </li>                
            <?php endif ?>
            <?php if (isset($_SESSION['logged_in'])):?>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_account.php?user_id=<?=$_SESSION['user_id']?>">Manage Account</a>
                </li>
            <?php endif ?>
            <?php if (!isset($_SESSION['logged_in'])):?>
                <li class="nav-item">
                    <a class="nav-link" href="login.php">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="create_account.php">Create Account</a>
                </li>
            <?php endif ?>
        </nav>
    </div>
   
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</header>