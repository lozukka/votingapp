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

    // Luodaan käyttäjät taulu, jos sitä ei vielä ole olemassa
$createUsersTableQuery = "
CREATE TABLE IF NOT EXISTS users (
     id INT AUTO_INCREMENT PRIMARY KEY,
     username VARCHAR(255) NOT NULL,
     email VARCHAR(255) NOT NULL,
     password_hash VARCHAR(255) NOT NULL
    )
";
$pdo->exec($createUsersTableQuery);

    // Lomakkeen tiedot
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hashataan salasana
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // SQL-kysely käyttäjän lisäämiseksi tietokantaan
    $query = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
    $statement = $pdo->prepare($query);
    $statement->execute([$name, $email, $hashed_password]);

    // Ohjataan käyttäjä sisäänkirjautumissivulle
    header("Location: login.php");
    exit(); // Varmistetaan, että scriptin suoritus päättyy tässä

} catch(PDOException $e) {
    echo "Virhe: " . $e->getMessage();
}
?>
