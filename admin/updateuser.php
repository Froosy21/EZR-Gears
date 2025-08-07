<?php
session_start();
include('../LogReg/database.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT fname, lname, phonenum, email, userpass FROM users WHERE id=$id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        echo "No user found";
        exit;
    }
} elseif (isset($_POST['id'])) {
    $id = $_POST['id'];
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $phonenum = $_POST['phonenum'];
    $email = $_POST['email'];
    $userpass = $_POST['userpass'];

    $sql = "UPDATE users SET fname='$fname', lname='$lname', phonenum='$phonenum', email='$email', userpass='$userpass' WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        echo "User updated successfully";
        header('Location: home.php');
    } else {
        echo "Error updating user: " . $conn->error;
    }
    $conn->close();
    exit;
} else {
    echo "Invalid request";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update User</title>
</head>
<body>
    <h1>Update User</h1>
    <form action="updateuser.php" method="POST">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
        <label for="fname">First Name:</label>
        <input type="text" name="fname" id="fname" value="<?php echo htmlspecialchars($row['fname']); ?>" required><br>
        <label for="lname">Last Name:</label>
        <input type="text" name="lname" id="lname" value="<?php echo htmlspecialchars($row['lname']); ?>" required><br>
        <label for="phonenum">Phone Number:</label>
        <input type="text" name="phonenum" id="phonenum" value="<?php echo htmlspecialchars($row['phonenum']); ?>" required><br>
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($row['email']); ?>" required><br>
        <label for="userpass">Password:</label>
        <input type="password" name="userpass" id="userpass" value="<?php echo htmlspecialchars($row['userpass']); ?>" required><br>
        <input type="submit" value="Update">
    </form>
</body>
</html>