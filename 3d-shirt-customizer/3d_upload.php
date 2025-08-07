<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D Product Upload</title>
    <link rel="stylesheet" href="home.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #ecf0f1;
        }

        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            width: 100%;
        }

        .form-container h1 {
            color: #d18d8d;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-container .form-group {
            margin-bottom: 15px;
        }

        .form-container label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .form-container input,
        .form-container textarea,
        .form-container button {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #640f0f;
            border-radius: 5px;
        }

        .form-container button {
            background-color: #640f0f;
            color: white;
            border: none;
            cursor: pointer;
        }

        .form-container button:hover {
            background-color: #941414;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Upload Your Custom 3D Design</h1>
        <form action="addprod_3d.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="product_quantity">Quantity:</label>
                <input type="number" id="product-quantity" name="product_quantity" step="1" required>
            </div>
            <div class="form-group">
                <label for="product-address">Address:</label>
                <textarea id="product-adrdress" name="product_address" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label>Upload ZIP File:</label>
                <input type="file" name="product_zip" accept=".zip" required>
            </div>
            <div class="back-button-container">
                <button id="back-button" onclick="goBackToHomePage()">Back to Home</button>
            </div>

            <script>
                // JavaScript function to handle redirection to index.html
                function goBackToHomePage() {
                    window.location.href = "index.html";
                }
            </script>

            <button type="submit">Submit</button>
        </form>
    </div>
</body>
</html>
