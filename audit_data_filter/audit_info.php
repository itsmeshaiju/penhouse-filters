<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "penhouse_audit";

$connection = new mysqli($servername, $username, $password, $dbname);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Get the audit_no from the URL parameter
if (isset($_GET['audit_no'])) {
    $audit_no = $_GET['audit_no'];

    // Fetch data for the selected audit_no
    $query = "select m.audit_starttime, m.audit_endtime, o.outlet_name, u.username, w.stock_location
              from audit_master m
              inner join pos_outlet o on m.outlet_id = o.id
              inner join audit_users u on m.user_id = u.user_id
              inner join penhouse_warehouse w on o.id = w.outlet_id
              where m.audit_no = ?";

    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $audit_no);
    $stmt->execute();
    $stmt->bind_result($audit_starttime, $audit_endtime, $outlet_name, $username, $stock_location);
    if ($stmt->fetch()) {
        ?>

        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link rel="stylesheet" href="\PenhouseAudit\audit_data_filter\assets\audit_info.css">
            <title>Audit Information</title>
        </head>

        <body>
            <div class="container">
                <h2>Audit Information</h2>
                <form action="" method="post">
                    <label for="startdate">Audit Start Time:</label>
                    <input type="text" id="startdate" name="startdate" value="<?= $audit_starttime ?>" readonly><br><br>

                    <label for="enddate">Audit End Time:</label>
                    <input type="text" id="enddate" name="enddate" value="<?= $audit_endtime ?>" readonly><br><br>

                    <label for="outlet_name">Outlet:</label>
                    <input type="text" id="outlet_name" name="outlet_name" value="<?= $outlet_name ?>" readonly><br><br>

                    <label for="audit_done_by">Audit done by:</label>
                    <input type="text" id="audit_done_by" name="audit_done_by" value="<?= $username ?>" readonly><br><br>

                    <br><br>
                </form>

                <table border="1">
                    <thead>
                        <tr>
                        <th>Sl</th>
                            <th>Location</th>
                            <th>No of SKU's Scanned</th>
                            <th>Total Count</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td><?= $stock_location ?></td>
                           
                        </tr>
                    </tbody>
                </table>
            </div>
        </body>

        </html>

    <?php
    } else {
        echo 'Audit not found.';
    }

    $stmt->close();
} else {
    echo 'Audit number not provided.';
}

mysqli_close($connection);
?>
