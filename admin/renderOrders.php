<?php
include('../LogReg/database.php');
require '../vendor/autoload.php';
use GuzzleHttp\Client;

function getPaymongoPaymentStatus($paymentId) {
    $secretKey = 'sk_test_Rq5WQbJcwvAnu4ewEgurmSfz';
    $client = new Client(['base_uri' => 'https://api.paymongo.com/']);

    try {
        $response = $client->request('GET', "v1/links/{$paymentId}", [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($secretKey . ':'),
                'Accept' => 'application/json',
            ]
        ]);
        
        $data = json_decode($response->getBody(), true);
        if (isset($data['data']['attributes']['status'])) {
            return $data['data']['attributes']['status'];
        }
    } catch (Exception $e) {
        error_log("PayMongo API Error: " . $e->getMessage());
    }
    
    return null;
}

// Modified query to include shipping fee and payment_id
$sql = "
    SELECT 
        MIN(o.id) as order_id,
        o.email,
        o.address,
        o.phonenum,
        o.order_date,
        o.payment_id,
        GROUP_CONCAT(
            CONCAT(o.product_name, ' (', o.quantity, ')')
            SEPARATOR ', '
        ) as items,
        SUM(o.price) + MAX(o.shipping_fee) as total_price,
        MAX(o.payment_status) as payment_status
    FROM orders o
    GROUP BY o.payment_id, o.email, o.address, o.phonenum, o.order_date
    ORDER BY o.order_date DESC
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table>
            <tr>
                <th>Order ID</th>
                <th>Email</th>
                <th>Items</th>
                <th>Total Price</th>
                <th>Address</th>
                <th>Phone</th>
                <th>Order Date</th>
                <th>Payment Status</th>
            </tr>";

    while($row = $result->fetch_assoc()) {
        // Check PayMongo status
        $paymongoStatus = getPaymongoPaymentStatus($row['payment_id']);
        
        if ($paymongoStatus && $paymongoStatus !== $row['payment_status']) {
            // Update database with new status
            $updateSql = "UPDATE orders SET payment_status = ? WHERE payment_id = ?";
            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param("ss", $paymongoStatus, $row['payment_id']);
            $stmt->execute();
            $stmt->close();
            
            // Update current row's status
            $row['payment_status'] = $paymongoStatus;
        }

        $paymentStatusClass = $row['payment_status'] === 'paid' ? 'style="color: green;"' : 'style="color: red;"';
        
        // Format the total price with comma for thousands
        $formattedTotal = '₱' . number_format($row['total_price'], 2);
        
        // Format the date
        $formattedDate = date('Y-m-d H:i:s', strtotime($row['order_date']));
        
        echo "<tr>
                <td>".$row['order_id']."</td>
                <td>".$row['email']."</td>
                <td>".$row['items']."</td>
                <td>".$formattedTotal."</td>
                <td>".$row['address']."</td>
                <td>".$row['phonenum']."</td>
                <td>".$formattedDate."</td>
                <td ".$paymentStatusClass.">".$row['payment_status']."</td>
            </tr>";
    }
    echo "</table>";
} else {
    echo "<p>No orders found</p>";
}
?>