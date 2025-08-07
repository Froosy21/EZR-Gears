<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayMongo Payments</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="container mx-auto bg-white shadow-md rounded-lg overflow-hidden">
        <div class="bg-blue-500 text-white p-4">
            <h1 class="text-2xl font-bold">PayMongo Payments</h1>
        </div>
        
        <div class="p-4">
            <?php
            require '../vendor/autoload.php';
            include('../LogReg/database.php');
            use GuzzleHttp\Client;

            function fetchAllPayments() {
                $secretKey = 'sk_test_Rq5WQbJcwvAnu4ewEgurmSfz';
                $client = new Client(['base_uri' => 'https://api.paymongo.com/']);
                try {
                    $response = $client->request('GET', 'v1/payments', [
                        'headers' => [
                            'Authorization' => 'Basic ' . base64_encode($secretKey . ':'),
                            'Accept' => 'application/json',
                        ]
                    ]);
                    return json_decode($response->getBody(), true)['data'];
                } catch (Exception $e) {
                    echo "<div class='text-red-500'>Error fetching payments: " . $e->getMessage() . "</div>";
                    return [];
                }
            }

            $payments = fetchAllPayments();
            ?>

            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border p-2">Payment ID</th>
                        <th class="border p-2">Amount</th>
                        <th class="border p-2">Currency</th>
                        <th class="border p-2">Status</th>
                        <th class="border p-2">Email</th>
                        <th class="border p-2">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr class="hover:bg-gray-100">
                            <td class="border p-2"><?php echo htmlspecialchars($payment['id']); ?></td>
                            <td class="border p-2">
                                <?php 
                                $amount = $payment['attributes']['amount'] / 100; 
                                echo number_format($amount, 2); 
                                ?>
                            </td>
                            <td class="border p-2"><?php echo htmlspecialchars($payment['attributes']['currency']); ?></td>
                            <td class="border p-2">
                                <span class="
                                    <?php 
                                    echo $payment['attributes']['status'] === 'paid' 
                                        ? 'text-green-600' 
                                        : 'text-red-600'; 
                                    ?>
                                ">
                                    <?php echo htmlspecialchars($payment['attributes']['status']); ?>
                                </span>
                            </td>
                            <td class="border p-2"><?php echo htmlspecialchars($payment['attributes']['billing']['email'] ?? 'N/A'); ?></td>
                            <td class="border p-2"><?php echo date('Y-m-d H:i:s', strtotime($payment['attributes']['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (empty($payments)): ?>
                <div class="text-center text-gray-500 p-4">No payments found</div>
            <?php endif; ?>
            
            <!-- Back to Admin Home Button -->
            <div class="text-center mt-4">
                <a href="https://ezr-gears.com/EzRebornProgram/admin/home.php">
                    <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Back to Admin Home
                    </button>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
