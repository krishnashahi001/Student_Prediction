<?php
// OtpHandler.php - Class for handling OTP generation and sending

class OtpHandler {
    private $conn;

    public function __construct() {
        $this->conn = new mysqli("localhost", "root", "", "studentdb");
        if ($this->conn->connect_error) {
            die("Database connection failed");
        }
    }

    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    // Find user by roll number or email
    public function findUser($identifier) {
        $stmt = $this->conn->prepare("SELECT id, rollno, fullname, email, contactno FROM studentdata WHERE rollno = ? OR email = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $identifier, $identifier);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            $stmt->close();
        }
        return false;
    }

    // Generate a 6-digit OTP
    public function generateOtp() {
        return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    // Send OTP via email
    public function sendOtp($email, $otp) {
        $subject = "Password Reset OTP";
        $message = "Your OTP for password reset is: $otp\n\nThis OTP is valid for 5 minutes.";
        $headers = "From: noreply@sppa.com";

        return mail($email, $subject, $message, $headers);
    }

    // Verify OTP
    public function verifyOtp($inputOtp, $storedOtp, $expiry) {
        if (time() > $expiry) {
            return false; // Expired
        }
        return $inputOtp === $storedOtp;
    }

    // Update password
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("UPDATE studentdata SET password = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $hashedPassword, $userId);
            $success = $stmt->execute();
            $stmt->close();
            return $success;
        }
        return false;
    }
}
?>