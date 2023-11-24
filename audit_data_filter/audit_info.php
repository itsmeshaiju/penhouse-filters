    <?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    $servername = "localhost";  
    $username = "admin";
    $password = "admin123";
    $dbname = "penhouse_audit";

    $connection = new mysqli($servername, $username, $password, $dbname);

    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }   

    // Get the audit_no from the URL parameter
    if (isset($_GET['audit_no'])) {
        $audit_no = $_GET['audit_no'];
        
        // $location_id = 'location_id';
    // Fetch data for the selected audit_no
        // $query = "select m.audit_starttime, m.audit_endtime, o.outlet_name, u.username, w.stock_location,count(a.sku) as sku
        //           from audit_master m
        //           inner join pos_outlet o on m.outlet_id = o.id
        //           inner join audit_users u on m.user_id = u.user_id
        //           inner join penhouse_warehouse w on o.id = w.outlet_id 
        //           inner join audit_data a on a.audit_id=m.audit_id
        //           where m.audit_no = ? 
        //           group by m.audit_no";

        $query="select m.audit_starttime, m.audit_endtime, o.outlet_name, u.username, w.stock_location,count(a.sku) as sku,sum(a.qty) as total_qty
        from audit_master m
        inner join pos_outlet o on m.outlet_id = o.id
        inner join audit_users u on m.user_id = u.user_id
        inner join penhouse_warehouse w on o.id = w.outlet_id 
        inner join audit_data a on a.audit_id=m.audit_id
        where a.location_id=w.warehouse_id
        and m.audit_no= ? 
        group by m.audit_no,m.audit_starttime,m.audit_endtime,o.outlet_name,u.username,w.stock_location";   

        // echo $query;
        // exit;

        $stmt = $connection->prepare($query);

        if (!$stmt) {
            die("Error in preparing the query: " . $connection->error);
        }   

        // $location_id = 'location_id';
        
        $stmt->bind_param("s", $audit_no);
        $stmt->execute();
        $result=$stmt->get_result();

        // echo $result->num_rows;
        // exit;
        
        if ($result->num_rows> 0) { 
            ?>  

            <!DOCTYPE html>
            <html lang="en">

            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <link rel="stylesheet" href="/penhouse-filters/audit_data_filter/assets/audit_info.css">
                <title>Audit Information</title>
            </head>

            <body>
                <div class="container">
                    <h2>Audit Information</h2>
                    <form action="" method="post">
                        <?php
                        $auditdetails = $result->fetch_assoc();
                        ?>
                        

                            <label for="startdate">Audit Start Time:</label>
                            <input type="text" id="startdate" name="startdate" value="<?= $auditdetails['audit_starttime'] ?>" readonly><br><br>

                            <label for="enddate">Audit End Time:</label>
                            <input type="text" id="enddate" name="enddate" value="<?= $auditdetails['audit_endtime'] ?>" readonly><br><br>

                            <label for="outlet_name">Outlet:</label>
                            <input type="text" id="outlet_name" name="outlet_name" value="<?= $auditdetails['outlet_name'] ?>" readonly><br><br>

                            <label for="audit_done_by">Audit done by:</label>
                            <input type="text" id="audit_done_by" name="audit_done_by" value="<?= $auditdetails['username'] ?>" readonly><br><br>



                        <?php
                        $result->data_seek (0);
                        $row_number=1;
                        ?>
                    

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
                            <?php
                            while($auditdetails=$result->fetch_assoc()){
                            ?>  
                            
                                <tr>
                                    <td><?=$row_number?></td>
                                    <td><?= $auditdetails['stock_location']?></td>
                                    <td><?= $auditdetails['sku']?></td>   
                                    <td><?= $auditdetails['total_qty']?></td>
                                
                                </tr>
                            <?php
                                $row_number++;
                            }
                            ?>
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
