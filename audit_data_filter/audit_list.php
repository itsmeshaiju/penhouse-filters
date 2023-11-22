<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "penhouse_audit";

$connection = new mysqli($servername, $username, $password, $dbname);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

$query = "select id, outlet_name from pos_outlet";
$result = mysqli_query($connection, $query);
if (!$result) {
    echo 'Error Occurred.';
} else {
    $outlet_data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $outlet_data[] = $row;
    }
}

$query = "select user_id, username from audit_users";
$result = mysqli_query($connection, $query);

if (!$result) {
    echo 'Error fetching data.';
} else {
    $username = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $username[] = $row;
    }
}

$query = "select audit_no from audit_master";
$result = mysqli_query($connection, $query);

if (!$result) {
    echo 'Error fetching data.';
} else {
    $audit_no = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $audit_no[] = $row['audit_no'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/PenhouseAudit/audit_data_filter/assets/audit_list.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    

    <title>Document</title>

</head>

<body>
    <div class="container">
        <h2>Search the Stock Audit</h2>
        <form action="" method="post" onsubmit="return validateForm();">
        <label for="outlet_name">Select outlet:</label>
            <select id="outlet_name" name="outlet_name"  onchange="loadAuditors()">
         <option value="Select Outlet">Select Outlet</option>
            <?php
            foreach ($outlet_data as $outlet) {
              $outlet_id = $outlet['id'];
             $outlet_name = $outlet['outlet_name'];
                $selected = ($_POST['outlet_name'] == $outlet_id) ? 'selected' : '';
                echo '<option value="' . $outlet_id . '" ' . $selected . '>' . $outlet_name . '</option>';
                }
                ?>
            </select><br><br>
             
            <label for="username">Select auditor:</label>
            <select id="username" name="username">
                <option value="Select Auditor">Select Auditor</option>
                <?php
                foreach ($username as $user) {
                    $user_id = $user['user_id'];
                    $username = $user['username'];
                    $selected = ($_POST['username'] == $user_id) ? 'selected' : '';
                    echo '<option value="' . $user_id . '" ' . $selected . '>' . $username . '</option>';
                }
                ?>
            </select><br><br>

            <label for="audit_no">Audit number:</label>
            <select id="audit_no" name="audit_no">
                <option value="Select Audit No">Select Audit No</option>
                <?php
                foreach ($audit_no as $audit_number) {
                    $selected = ($_POST['audit_no'] == $audit_number) ? 'selected' : '';
                    echo '<option value="' . $audit_number . '" ' . $selected . '>' . $audit_number . '</option>';
                }
                ?>
            </select><br><br>

            <label for="from_date">From:</label>
            <input type="date" id="from_date" name="from_date" value="<?php echo isset($_POST['from_date']) ? $_POST['from_date'] : ''; ?>">
            <label for="to_date">To:</label>
            <input type="date" id="to_date" name="to_date" value="<?php echo isset($_POST['to_date']) ? $_POST['to_date'] : ''; ?>">

            <br><br>

            <button type="submit" name="list_button">List</button>
        </form>
        <br><br>
        <table border="1">
            <thead>
                <tr>
                    <th>Audit No</th>
                    <th>Outlet ID</th>
                    <th>Date</th>
                    <th>Done By</th>
                    <th>Total SKU</th>
                    <th>Total Count</th>
                    <th>View</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $selectedOutletName = false;
                $selectedAuditorName = false;
                $selectedAuditno = false;
                $fromDate = false;
                $toDate = false;
                if (isset($_POST['list_button'])) {
                    if (isset($_POST['outlet_name']) && $_POST['outlet_name'] != 'Select Outlet') {
                        $selectedOutletName = $_POST['outlet_name'];
                    }
                    if (isset($_POST['username']) && $_POST['username'] != 'Select Auditor') {
                        $selectedAuditorName = $_POST['username'];
                    }
                    if (isset($_POST['audit_no']) && $_POST['audit_no'] != 'Select Audit No') {
                        $selectedAuditno = $_POST['audit_no'];
                    }
                    if (isset($_POST['from_date']) && isset($_POST['to_date'])) {
                        $fromDate = $_POST['from_date'];
                        $toDate = $_POST['to_date'];
                    }
                }
                $outlet_query = "select m.audit_no, o.outlet_name, m.audit_starttime, u.username,
                count(ad.sku) as total_sku, sum(ad.qty) as total_count
                    from audit_master m
                         inner join pos_outlet o on m.outlet_id = o.id
                        inner join audit_users u on m.user_id = u.user_id
                        inner join audit_data ad on ad.audit_id = m.audit_id
                        where 1";

                if ($selectedOutletName) {
                    $outlet_query .= " and o.id = ?";
                }
                if ($selectedAuditorName) {
                    $outlet_query .= " and u.user_id = ?";
                }
                if ($selectedAuditno ) {
                    $outlet_query .= " and m.audit_no = ?";
                }
                if ($fromDate && $toDate) {
                    $outlet_query .= " and m.audit_starttime between ? and ?";
                }
                $outlet_query .= " and m.audit_status = 2 group by m.audit_no";

                $stmt = $connection->prepare($outlet_query);
                if ($stmt) {
                    $param_types = "";
                    $param_values = [];
                    if ($selectedOutletName) {
                        $param_types .= "s";
                        $param_values[] = &$selectedOutletName;
                    }
                    if ($selectedAuditorName) {
                        $param_types .= "s";
                        $param_values[] = &$selectedAuditorName;
                    }
                    if ($selectedAuditno ) {
                        $param_types .= "s";
                        $param_values[] = &$selectedAuditno;
                    }
                    if ($fromDate && $toDate) {
                        $param_types .= "ss";
                        $param_values[] = &$fromDate;
                        $param_values[] = &$toDate;
                    }

                    if (!empty($param_types) && !empty($param_values)) {
                        array_unshift($param_values, $param_types);
                        call_user_func_array(array($stmt, 'bind_param'), $param_values);
                    }

                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    // if ($result->num_rows > 0) {
                       
                    //     $rowCount = $result->num_rows;
                    
                    //     echo "Result Count: " . $rowCount;
                    // }
                    // exit();

                } else {
                    echo 'Error in preparing the query: ' . $connection->error;
                }

                while ($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . $row['audit_no'] . '</td>';
                    echo '<td>' . $row['outlet_name'] . '</td>';
                    echo '<td>' . $row['audit_starttime'] . '</td>';
                    echo '<td>' . $row['username'] . '</td>';
                    echo '<td>' . $row['total_sku'] . '</td>';
                    echo '<td>' . $row['total_count'] . '</td>';
                    echo '<td><a href="\PenhouseAudit\audit_data_filter\audit_info.php?audit_no=' . $row['audit_no'] . '">View</a></td>';
                    echo '</tr>';
                }

                mysqli_close($connection);
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>

<script>
    function validateForm() {
        var fromDate = document.getElementById("from_date").value;
        var toDate = document.getElementById("to_date").value;

        if (fromDate > toDate) {
            alert("The 'From' date should be less than the 'To' date. Please make a valid selection.");
            return false;
        }

        return true;
    }
</script>


<!-- <script>
    function loadAuditors() {
        var outletId = document.getElementById("outlet_name").value;

        $.ajax({
            type: "POST",
            url: "\PenhouseAudit\audit_data_filter\get_auditors.php", // Update the URL to point to your get_auditors.php file
            data: { outlet_id: outletId },
            dataType: 'json', // Specify that the response will be in JSON format
            success: function (data) {
                // Update the content of the auditor dropdown
                var auditorDropdown = $("#username");
                auditorDropdown.empty(); // Clear existing options

                // Add the default option
                auditorDropdown.append('<option value="Select Auditor">Select Auditor</option>');

                // Add auditors based on the response
                $.each(data, function (index, auditor) {
                    auditorDropdown.append('<option value="' + auditor.user_id + '">' + auditor.username + '</option>');
                });
            },
            error: function (xhr, status, error) {
                console.error('Error fetching auditors:', error);
            }
        });
    }
</script>



 -->
