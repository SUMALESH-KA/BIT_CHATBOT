<?php

require 'vendor/autoload.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

// Database connection
$conn = mysqli_connect("localhost", "root", "", "bot") or die("Database Error");

// Get user input
$getMesg = mysqli_real_escape_string($conn, $_POST['text']);

// Attempt to match the query using REGEXP in local MySQL database
$check_data = "SELECT replies FROM chatbot WHERE '$getMesg' REGEXP queries";
$run_query = mysqli_query($conn, $check_data) or die("Error");

if (mysqli_num_rows($run_query) > 0) {
    $fetch_data = mysqli_fetch_assoc($run_query);
    $replay = $fetch_data['replies'];
    echo $replay;
} else {
    // If no match is found, query Firebase
    $replay = getFirebaseResponse($getMesg);
    echo $replay;
}

// Function to retrieve information from Firebase
function getFirebaseResponse($message) {
    // Initialize Firebase
    $serviceAccount = ServiceAccount::fromJsonFile('C:/Users/kasum/Downloads/bit-voice-assistant-addb1-firebase-adminsdk-yu7gy-5fb6dec010.json');
    $firebase = (new Factory)
        ->withServiceAccount($serviceAccount)
        ->withDatabaseUri('https://bit-voice-assistant-addb1-default-rtdb.firebaseio.com/')
        ->create();
    
    $database = $firebase->getDatabase();

    // Reference to 'responses' in Firebase Realtime Database
    $reference = $database->getReference('responses');
    $snapshot = $reference->getSnapshot();
    $responses = $snapshot->getValue();

    // Implement logic to find the most relevant response
    foreach ($responses as $key => $response) {
        if (strpos($message, $key) !== false) {
            return $response;
        }
    }

    return "Sorry, I can't understand you!";
}

?>
