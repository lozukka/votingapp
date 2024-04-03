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
        'current_hash' => '0 (genesis block)' // Hash for the genesis block (can be any unique value)
    );
    $blockchain[] = $genesisBlock;

    //Tallennetaan genesis block tietokantaan 
    $insertGenesisQuery = "INSERT INTO blockchain (timestamp, previous_hash, current_hash, data) 
                            VALUES (:timestamp, :previousHash, :currentHash, :data)"; 
        $stmt = $pdo->prepare($insertGenesisQuery); 
        $stmt->execute([ 
            'timestamp' => $genesisBlock['timestamp'], 
            'previousHash' => $genesisBlock['previous_hash'], 
            'currentHash' => $genesisBlock['current_hash'], 
            'data' => $genesisBlock['name'] . '->' . $genesisBlock['vote'] 
        ]); 
    } 

// Tarkistetaan, että lomaketta on lähetetty POST-metodilla
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Tarkistetaan, että tarvittavat kentät ovat asetettu
    if (isset($_POST["name"]) && isset($_POST["vote"])) {

        // Haetaan lomakkeen tiedot
        $name = htmlspecialchars($_POST["name"]);
        $vote = htmlspecialchars($_POST["vote"]);

        // Haetaan edellisen lohkon hash
        $previousBlock = end($blockchain);
        $previousHash = isset($previousBlock['current_hash']) ? $previousBlock['current_hash'] : null;

        // Muodostetaan uusi lohko
        $block = array(
            'name' => $name,
            'vote' => $vote,
            'timestamp' => time(), // Lisätään aikaleima lohkoon
            'previous_hash' => $previousHash // Edellisen lohkon hash
        );

        // Lasketaan uuden lohkon hash
        $block['current_hash'] = hash('sha256', json_encode($block));

        // Lisätään uusi lohko lohkoketjuun
        $blockchain[] = $block;

        // Päivitä edellisen lohkon hash-arvo uuteen lohkoon 
        $updatePreviousHashQuery = "UPDATE blockchain SET previous_hash = :previousHash WHERE blockchain_index = :index"; 
        $stmt = $pdo->prepare($updatePreviousHashQuery); 
        $stmt->execute(['previousHash' => $block['current_hash'], 'index' => count($blockchain) - 2]); 


        // Tallennetaan päivitetty lohkoketju takaisin tietokantaan

    // Tietokantaan lisättävä SQL-kysely
    $insertBlockQuery = "INSERT INTO blockchain (timestamp, previous_hash, current_hash, data) 
    VALUES (:timestamp, :previousHash, :currentHash, :data)";

        $stmt = $pdo->prepare($insertBlockQuery); 
        $stmt->execute([ 
                'timestamp' => $block['timestamp'], 
                'previousHash' => $block['previous_hash'], 
                'currentHash' => $block['current_hash'], 
                'data' => $block['name'] . '->' . $block['vote'] 
            ]); 

    
        // Tulostetaan lohkoketju
        echo '<h2>Results</h2>';
        echo '<table border="1">';
        echo '<tr><th>Name->Vote</th><th>Timestamp</th><th>Hash</th><th>Previous Hash</th></tr>';

        // Tulostetaan lohkot paitsi genesis block
        $selectBlockchainQuery = "SELECT * FROM blockchain"; 
            $stmt = $pdo->query($selectBlockchainQuery); 
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC))  {
                echo '<tr>';
                echo '<td>' . $row['data'] . '</td>';
                echo '<td>' . date('Y-m-d H:i:s', $row['timestamp']) . '</td>';
                echo '<td>' . $row['current_hash'] . '</td>';
                echo '<td>' . $row['previous_hash'] . '</td>';
                echo '</tr>';
        }

        echo '</table>';
    } else {
        echo 'Virhe: Kaikkia tarvittavia kenttiä ei ole asetettu.';
    }
} else {
    echo 'Virhe: Lomaketta ei ole lähetetty oikealla HTTP-metodilla.';
}

} catch (PDOException $e) {
    //Virheenkäsittely:
    echo "Tietokantayhteys epäonnistui: " . $e->getMessage();
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
