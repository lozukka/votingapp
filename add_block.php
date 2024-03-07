<?php
session_start();

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

//Luo tietokantayhteys 
$servername = ""; //Palvelimen nimi tai IP-osoite
$username = ""; //tietokannan käyttäjätunnus
$password = ""; //tietokannan salasana
$database = ""; //tietokannan nimi

try {
    //Yrittää luoda tietokantayhteyttä, käyttäen PDO-luokkaa
    $pdo = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    //PDO virhetilan asetukset:
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Tietokantayhteys onnistui!";
} catch (PDOException $e) {
    //Virheenkäsittely:
    die("Tietokantayhteys epäonnistui: " . $e->getMessage());
}

// Tarkistetaan, että lomaketta on lähetetty POST-metodilla
if ($_SERVER["REQUEST_METHOD"] == "POST") {

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

//Luodaan lohkoketjutaulukko
$createTableQuery = "
CREATE TABLE IF NOT EXISTS blockchain (
    blockchain_index INTEGER PRIMARY KEY AUTO_INCREMENT,
    timestamp TEXT,
    previous_hash TEXT,
    current_hash TEXT,
    data TEXT)
    ";
$pdo->exec($createTableQuery);

// Ladataan olemassa oleva lohkoketju tietokannasta
$selectBlockchainQuery = "SELECT * FROM blockchain";
$stmt = $pdo->query($selectBlockchainQuery);
$blockchain = $stmt->fetchAll(PDO::FETCH_ASSOC);

//Luodaan genesis block, jos lohkoketjua ei vielä ole
if (count($blockchain) == 0) {
$genesisBlock = array(
    'name' => 'Genesis',
    'vote' => 'First Block',
    'timestamp' => time(),
    'previous_hash' => null,
    'hash' => '0 (genesis block)' // Hash for the genesis block (can be any unique value)
);

$blockchain[] = $genesisBlock;
}
    // Tarkistetaan, että tarvittavat kentät ovat asetettu
    if (isset($_POST["name"]) && isset($_POST["vote"])) {

        // Haetaan lomakkeen tiedot
        $name = htmlspecialchars($_POST["name"]);
        $vote = htmlspecialchars($_POST["vote"]);

        // Haetaan edellisen lohkon hash
        $previousBlock = end($blockchain);
        $previousHash = isset($previousBlock['hash']) ? $previousBlock['hash'] : null;

        // Muodostetaan uusi lohko
        $block = array(
            'name' => $name,
            'vote' => $vote,
            'timestamp' => time(), // Lisätään aikaleima lohkoon
            'previous_hash' => $previousHash // Edellisen lohkon hash
        );

        // Lasketaan uuden lohkon hash
        $block['hash'] = hash('sha256', json_encode($block));

        // Lisätään uusi lohko lohkoketjuun
        $blockchain[] = $block;

// Tallennetaan päivitetty lohkoketju takaisin tietokantaan
foreach ($blockchain as $block) {
    $name = $block['name'];
    $vote = $block['vote'];
    $timestamp = $block['timestamp'];
    $previousHash = $block['previous_hash'];
    $hash = $block['hash'];

    // Tietokantaan lisättävä SQL-kysely
    $insertBlockQuery = "INSERT INTO blockchain (timestamp, previous_hash, current_hash, data) 
    VALUES ('$timestamp', '$previousHash', '$hash', '$name->$vote')";

    // Suoritetaan SQL-kysely
    try {
        $pdo->exec($insertBlockQuery);
        echo "Lohko tallennettu onnistuneesti tietokantaan<br>";
    } catch (PDOException $e) {
        echo "Virhe tallennettaessa lohkoa tietokantaan: " . $e->getMessage();
    }
}
        // Tulostetaan lohkoketju
        echo '<h2>Results</h2>';
        echo '<table border="1">';
        echo '<tr><th>Name</th><th>Vote</th><th>Timestamp</th><th>Hash</th><th>Previous Hash</th></tr>';

        // Tulostetaan lohkot paitsi genesis block
        for ($i = 1; $i < count($blockchain); $i++) {
            $block = $blockchain[$i];
            echo '<tr>';
            echo '<td>' . $block['name'] . '</td>';
            echo '<td>' . $block['vote'] . '</td>';
            echo '<td>' . date('Y-m-d H:i:s', $block['timestamp']) . '</td>';
            echo '<td>' . $block['hash'] . '</td>';
            echo '<td>' . $block['previous_hash'] . '</td>';
            echo '</tr>';
        }

        echo '</table>';
    } else {
        echo 'Virhe: Kaikkia tarvittavia kenttiä ei ole asetettu.';
    }
} else {
    echo 'Virhe: Lomaketta ei ole lähetetty oikealla HTTP-metodilla.';
}
echo "<p>You are logged in as: " . $_SESSION['username'] . "</p>";
echo "<p>Back to <a href='index.php'>main page</a>.</p>";
echo "<p>You are logged in. <a href='logout.php'>Log out.</a></p>";
?>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Harjoitus 11</title>
    <style>

        table {
            width: 80%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
</hmtl>