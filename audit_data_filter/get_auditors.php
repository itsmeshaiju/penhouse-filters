<?php

// if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['outlet_id'])) {
//     $outlet_id = $_POST['outlet_id'];

//     // Add your database connection code here
//     $servername = "localhost";
//     $username = "root";
//     $password = "";
//     $dbname = "penhouse_audit";

//     $connection = new mysqli($servername, $username, $password, $dbname);

//     if ($connection->connect_error) {
//         die("Connection failed: " . $connection->connect_error);
//     }

//     // Prepare and execute a query to get auditors for the selected outlet
//     $query = "select user_id, username from audit_users where outlet_id = ?";
//     $stmt = $connection->prepare($query);
//     $stmt->bind_param("s", $outlet_id);
//     $stmt->execute();
//     $result = $stmt->get_result();

//     // Fetch auditors and return them as JSON
//     $auditors = [];
//     while ($row = $result->fetch_assoc()) {
//         $auditors[] = $row;
//     }

//     echo json_encode($auditors);

//     // Close the database connection
//     $stmt->close();
//     $connection->close();
// } else {
//     // Handle invalid requests
//     echo 'Invalid Request';
// }



?>