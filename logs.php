<?php
//Luo tietokantayhteys 
$servername = ""; //Palvelimen nimi tai IP-osoite
$username = ""; //tietokannan käyttäjätunnus
$password = ""; //tietokannan salasana 
$database = ""; //tietokannan nimi

try {
    // Yhdistetään tietokantaan
    $pdo = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Lomakkeen tiedot
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Haetaan käyttäjän tiedot tietokannasta
    $query = "SELECT * FROM users WHERE email = ?";
    $statement = $pdo->prepare($query);
    $statement->execute([$email]);
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    // Tarkistetaan, onko käyttäjä olemassa ja salasana täsmää
    if ($user && password_verify($password, $user['password_hash'])) {
        // Käyttäjä löytyi ja salasana täsmää, ohjataan index.php-sivulle
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit();
    } else {
        // Virheelliset kirjautumistiedot
        echo "Incorrect password or email. " . "<br>";
        echo "Return " . '<a href="login.php">front page</a>';
    }

} catch(PDOException $e) {
    echo "Virhe: " . $e->getMessage();
}
?>
