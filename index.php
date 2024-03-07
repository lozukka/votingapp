<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Harjoitus 11</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            flex-direction: column;
            justify-content: center;
            height: 100vh;
        }

        h1 {
            width: 350px; 
        }

        .form-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 300px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            background-color: #4caf50;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
    </style>
</head>
<body>
    <div>    
        <h1>Voting Web App</h1>
        <p>We are currently voting: <br> When making a sandwish, do you put ham or cheece on top?</p>
    </div>
    <div class="form-container">
        <form action="add_block.php" method="post">
            <div class="form-group">
                <label for="name">Your name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="vote">Ham or cheese?</label>
                <input type="vote" id="vote" name="vote" required>
            </div>
            <button type="submit">Send</button>
            <p>You are logged in as: <?php echo $_SESSION['username']; ?><a href="logout.php">Log out.</a></p>
        </form>
    </div>
</body>
</html>
