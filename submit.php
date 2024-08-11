<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "form_data";

// Function to connect to the database
function connectDatabase() {
    global $servername, $username, $password, $dbname;
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// Function to insert data into the database
function insertData($year, $month, $title, $file_path) {
    $conn = connectDatabase();
    $sql = "INSERT INTO FacultySubmissions (year, month, title, file_path) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $year, $month, $title, $file_path);

    if ($stmt->execute()) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

// Handling form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action == 'insert') {
        $year = $_POST['year'];
        $month = $_POST['month'];
        $title = $_POST['title'];
        $file_path = 'uploads/' . basename($_FILES["file"]["name"]);

        // Validate the file upload
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf']; // Example allowed types
        $file_type = mime_content_type($_FILES["file"]["tmp_name"]);

        if (!in_array($file_type, $allowed_types)) {
            die("Invalid file type.");
        }

        if ($_FILES["file"]["size"] > 5000000) { // 5MB limit
            die("File is too large.");
        }

        // Check if the uploads directory exists
        if (!is_dir('uploads')) {
            if (!mkdir('uploads', 0755, true)) {
                die("Failed to create directory.");
            }
        }

        // Save the uploaded file to the server
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $file_path)) {
            insertData($year, $month, $title, $file_path);
        } else {
            echo "Sorry, there was an error uploading your file.";
            echo "Error details: ";
            print_r(error_get_last());
        }
    }

    // Handling AJAX requests
    if ($action == 'retrieve') {
        $records = retrieveData();
        header('Content-Type: application/json');
        echo json_encode($records);
        exit;
    }

    if ($action == 'update') {
        $id = $_POST['id'];
        $new_title = $_POST['new_title'];
        updateData($id, $new_title);
        exit;
    }

    if ($action == 'delete') {
        $id = $_POST['id'];
        deleteData($id);
        exit;
    }
}

// Function to retrieve data from the database
function retrieveData() {
    $conn = connectDatabase();
    $sql = "SELECT id, year, month, title, file_path FROM FacultySubmissions";
    $result = $conn->query($sql);
    $records = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
    }
    $conn->close();
    return $records;
}

// Function to update data in the database
function updateData($id, $new_title) {
    $conn = connectDatabase();
    $sql = "UPDATE FacultySubmissions SET title=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_title, $id);

    if ($stmt->execute()) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}

// Function to delete data from the database
function deleteData($id) {
    $conn = connectDatabase();
    $sql = "DELETE FROM FacultySubmissions WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "Record deleted successfully";
    } else {
        echo "Error deleting record: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
