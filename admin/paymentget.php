<?php
require '../vendor/autoload.php'; // Include Guzzle if you're using it

use GuzzleHttp\Client;

function getAllPayments() {
    $secretKey = 'sk_test_Rq5WQbJcwvAnu4ewEgurmSfz'; // Replace with your PayMongo secret key
    $url = 'https://api.paymongo.com/v1/payments';

    // Create a new Guzzle HTTP client
    $client = new Client([
        'base_uri' => 'https://api.paymongo.com/',
    ]);

    try {
        // Send GET request to fetch all payments
        $response = $client->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($secretKey . ':'),
                'Accept' => 'application/json',
            ]
        ]);

        // Decode the JSON response
        $payments = json_decode($response->getBody(), true);
        return $payments['data']; // Return the payment data
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
}

$payments = getAllPayments();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Payments</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Payments List</h1>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Amount (PHP)</th>
                    <th>Status</th>
                    <th>Description</th>
                    <th>Created At</th>
                    <th>Email</th>
                    <th>Payment Link</th> <!-- New column for payment link -->
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($payments)) : ?>
                    <?php foreach ($payments as $payment) : 
                        // Fetch billing details if available
                        $billing = $payment['attributes']['billing'];
                        $payerEmail = isset($billing['email']) ? $billing['email'] : 'N/A';
                        $payerName = isset($billing['name']) ? $billing['name'] : 'N/A';

                        // Fetch the payment link if available
                        $paymentLink = isset($payment['attributes']['payment_link']) ? $payment['attributes']['payment_link'] : 'N/A';
                    ?>
                        <tr>
                            <td><?php echo $payment['id']; ?></td>
                            <td><?php echo number_format($payment['attributes']['amount'] / 100, 2); ?></td>
                            <td><?php echo ucfirst($payment['attributes']['status']); ?></td>
                            <td><?php echo $payment['attributes']['description']; ?></td>
                            <td><?php echo date('Y-m-d H:i:s', $payment['attributes']['created_at']); ?></td>
                            <td><?php echo $payerEmail; ?></td>
                            <td>
                                <?php if ($paymentLink !== 'N/A') : ?>
                                    <a href="<?php echo $paymentLink; ?>" target="_blank">View Payment</a>
                                <?php else : ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7">No payments found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
