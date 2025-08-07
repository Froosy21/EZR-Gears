<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ezrdb";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sql = "SELECT users.name, users.username, orders.product_name, orders.quantity, orders.price, orders.order_date
        FROM orders
        JOIN users ON orders.user_id = users.id
        ORDER BY orders.order_date DESC";

$result = $conn->query($sql);

$orders = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
} else {
    echo "0 results";
}

$conn->close();

?>