<?php

// Check if form is submitted using POST method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $name = htmlspecialchars($_POST['name']);
    $concern = htmlspecialchars($_POST['concern']);
    $email = htmlspecialchars($_POST['email']);
    $phone = htmlspecialchars($_POST['phone']);
    $date = htmlspecialchars($_POST['date']); // Further validation might be needed
    $time = htmlspecialchars($_POST['time']); // Further validation might be needed

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format");
    }

    // Basic validation for date and time (can be enhanced)
    if (empty($date) || empty($time)) {
        die("Date and time are required");
    }

    // Validate time to be between 10:00 AM and 6:00 PM
    $startTime = strtotime('10:00:00');
    $endTime = strtotime('18:00:00');
    $selectedTime = strtotime($time);

    if ($selectedTime < $startTime || $selectedTime > $endTime) {
        die("Appointment time must be between 10:00 AM and 6:00 PM.");
    }

    // Database connection parameters
    $hostname = 'localhost'; // or your hostname (e.g., '127.0.0.1')
    $username = 'root'; // your MySQL username
    $password = ''; // your MySQL password
    $database_name = 'cloves'; // your MySQL database name

    // Create connection
    $mysqli = new mysqli($hostname, $username, $password, $database_name);

    // Check connection
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    // Check if the selected date and time is already booked
    $stmt = $mysqli->prepare("SELECT id FROM appointment WHERE date = ? AND time = ?");
    $stmt->bind_param("ss", $date, $time);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Appointment already exists for the selected date and time
        echo "The selected date and time is already booked. Please choose a different time.";
    } else {
        // Prepare SQL statement to insert data into database
        $stmt = $mysqli->prepare("INSERT INTO appointment (name, concern, email, phone, date, time) VALUES (?, ?, ?, ?, ?, ?)");

        // Check if the statement was prepared successfully
        if (!$stmt) {
            die("Error preparing statement: " . $mysqli->error);
        }

        // Bind parameters to statement
        $stmt->bind_param("ssssss", $name, $concern, $email, $phone, $date, $time);

        // Execute statement
        if ($stmt->execute()) {
            echo "Appointment booked successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
    }

    // Close statement and connection
    $stmt->close();
    $mysqli->close();
} else {
    echo "Please submit the form.";
}
?>
