<?php
session_start();
require_once 'db.php';
require 'vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $userId        = $_POST['user_id'] ?? null;
        $adminId       = $_POST['admin_id'] ?? null;
        $propertyId    = $_POST['property_id'] ?? null;
        $propertyName  = $_POST['property_name'] ?? '';
        $roomTypeId    = $_POST['room_type_id'] ?? null;
        $guestName     = $_POST['guest_name'] ?? '';
        $checkIn       = $_POST['check_in'] ?? '';
        $checkOut      = $_POST['check_out'] ?? '';
        $amount        = $_POST['amount'] ?? 0;
        $city          = $_POST['city'] ?? "Not Provided"; 
        $occupancy     = $_POST['occupancy'] ?? "2 Adults";
        $extraBed      = $_POST['extra_bed'] ?? "0";
        $guests        = $_POST['guests'] ?? 2;
        $gstNumber     = $_POST['gst_number'] ?? '';
        $gstCompany    = $_POST['gst_company_name'] ?? '';
        $gstAddress    = $_POST['gst_company_address'] ?? '';
        $roomTypeName = 'Unknown Room Type';
        if ($roomTypeId) {
            $stmt = $pdo->prepare("SELECT name FROM room_types WHERE id = :room_type_id");
            $stmt->execute([':room_type_id' => $roomTypeId]);
            $roomType = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($roomType) {
                $roomTypeName = $roomType['name'];
            }
        }
        
        $checkInDate = new DateTime($checkIn);
        $checkOutDate = new DateTime($checkOut);
        $nights = $checkOutDate->diff($checkInDate)->days;

        $mealInput = trim($_POST['meal_type'] ?? $_GET['meal_name'] ?? 'EP');
        $mealMap = [
            "EP"  => "room_only",
            "CP"  => "with_breakfast",
            "MAP" => "breakfast_lunch_dinner",
            "Breakfast" => "with_breakfast",
            "With Breakfast" => "with_breakfast",
            "Breakfast+lunch/dinner" => "breakfast_lunch_dinner",
            "Room Only" => "room_only",
            "room_only" => "room_only",
            "with_breakfast" => "with_breakfast",
            "breakfast_lunch_dinner" => "breakfast_lunch_dinner"
        ];

        $mealInputUpper = strtoupper(trim($mealInput));
        $mealType = "room_only";
        foreach ($mealMap as $key => $value) {
            if (strtoupper($key) === $mealInputUpper) {
                $mealType = $value;
                break;
            }
        }
        $stmt = $pdo->query("SELECT MAX(booking_id) as last_id FROM bookings");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $nextId = ($row && $row['last_id']) ? $row['last_id'] + 1 : 1;
        $bookingCode = "BKG" . str_pad($nextId, 4, "0", STR_PAD_LEFT);
        $paymentType = isset($_POST['payment_type']) ? $_POST['payment_type'] : 'pay_at_property';
        $stmt = $pdo->prepare("
            INSERT INTO bookings
            (booking_code, user_id, property_id, room_type_id, room_type_name, meal_type, property_name, guest_name, 
             check_in, check_out, amount, status, created_at, 
             first_name, last_name, guest_email, guest_phone, payment_type,
             gst_number, gst_company_name, gst_company_address) 
            VALUES 
            (:booking_code, :user_id, :property_id, :room_type_id, :room_type_name, :meal_type, :property_name, :guest_name, 
             :check_in, :check_out, :amount, :status, NOW(), 
             :first_name, :last_name, :guest_email, :guest_phone, :payment_type,
             :gst_number, :gst_company_name, :gst_company_address)
        ");
        
        $stmt->execute([
            ':booking_code'   => $bookingCode,
            ':user_id'        => $userId,
            ':property_id'    => $propertyId,
            ':room_type_id'   => $roomTypeId,
            ':room_type_name' => $roomTypeName,
            ':meal_type'      => $mealType,
            ':property_name'  => $propertyName,
            ':guest_name'     => $guestName,
            ':check_in'       => $checkIn,
            ':check_out'      => $checkOut,
            ':amount'         => $amount,
            ':status'         => 'Confirmed',
            ':first_name'     => $_POST['first_name'] ?? '',
            ':last_name'      => $_POST['last_name'] ?? '',
            ':guest_email'    => $_POST['guest_email'] ?? '',
            ':guest_phone'    => $_POST['guest_phone'] ?? '',
            ':payment_type'   => $paymentType,
            ':gst_number'     => $gstNumber,
            ':gst_company_name' => $gstCompany,
            ':gst_company_address' => $gstAddress
        ]);
        
        $lastId = $pdo->lastInsertId();
        $period = new DatePeriod(
            new DateTime($checkIn),
            new DateInterval('P1D'),
            new DateTime($checkOut)
        );
        
        foreach ($period as $date) {
            $currentDate = $date->format("Y-m-d");
            $stmt = $pdo->prepare("
                SELECT id, total_rooms, booked_rooms, available_rooms 
                FROM room_inventory 
                WHERE property_id = :property_id 
                  AND room_type_id = :room_type_id 
                  AND meal_type = :meal_type 
                  AND date = :date
                LIMIT 1
            ");
            $stmt->execute([
                ':property_id'  => $propertyId,
                ':room_type_id' => $roomTypeId,
                ':meal_type'    => $mealType,
                ':date'         => $currentDate
            ]);
            $inventory = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($inventory) {
                $stmt = $pdo->prepare("
                    UPDATE room_inventory 
                    SET booked_rooms = booked_rooms + 1, 
                        available_rooms = available_rooms - 1 
                    WHERE id = :id
                ");
                $stmt->execute([':id' => $inventory['id']]);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO room_inventory 
                    (property_id, room_type_id, meal_type, date, total_rooms, booked_rooms, available_rooms) 
                    VALUES (:property_id, :room_type_id, :meal_type, :date, 1, 1, 0)
                ");
                $stmt->execute([
                    ':property_id'  => $propertyId,
                    ':room_type_id' => $roomTypeId,
                    ':meal_type'    => $mealType,
                    ':date'         => $currentDate
                ]);
            }
        }
        
        $stmt = $pdo->prepare("
            SELECT 
                booking_code, 
                property_name, 
                guest_name, 
                check_in, 
                check_out, 
                amount, 
                payment_type,
                status,
                first_name,
                last_name,
                guest_email,
                guest_phone,
                meal_type,
                room_type_name,
                gst_number,
                gst_company_name,
                gst_company_address
            FROM bookings 
            WHERE booking_id = :booking_id
        ");
        $stmt->execute([':booking_id' => $lastId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$booking) {
            throw new Exception("Booking not found.");
        }
        $bookingCode = $booking['booking_code'];
        $propertyName = $booking['property_name'];
        $guestName = $booking['guest_name'];
        $checkIn = $booking['check_in'];
        $checkOut = $booking['check_out'];
        $amount = $booking['amount'];
        $paymentType = $booking['payment_type'];
        $status = $booking['status'];
        $firstName = $booking['first_name'];
        $lastName = $booking['last_name'];
        $guestEmail = $booking['guest_email'];
        $guestPhone = $booking['guest_phone'];
        $mealType = $booking['meal_type'];
        $roomType = $booking['room_type_name'];
        $gstNumber = $booking['gst_number'];
        $gstCompany = $booking['gst_company_name'];
        $gstAddress = $booking['gst_company_address'];
        $checkInDate = new DateTime($checkIn);
        $checkOutDate = new DateTime($checkOut);
        $nights = $checkOutDate->diff($checkInDate)->days;
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $pricePerNight = $nights > 0 ? $amount / $nights : $amount;
        $taxes = $amount * 0.18;
        $totalAmount = $amount + $taxes;
   
        function getMealPlanDescription($mealType) {
            $descriptions = [
                "room_only" => "Room Only",
                "with_breakfast" => "Breakfast",
                "breakfast_lunch_dinner" => "Breakfast, Lunch & Dinner"
            ];
            
            return $descriptions[$mealType] ?? "Room Only";
        }

        $checkInDisplay = date("d M 'y", strtotime($checkIn));
        $checkOutDisplay = date("d M 'y", strtotime($checkOut));
        $bookedOn = date("d M 'y h:i A");
        $gstSection = "
        <table>
            <tr>
                <th class='section-title'>GST DETAILS</th>
            </tr>
            <tr>
                <td>
                    <span class='highlight'>GST Number:</span> " . (!empty($gstNumber) ? $gstNumber : 'Not Provided') . "<br>
                    <span class='highlight'>Company Name:</span> " . (!empty($gstCompany) ? $gstCompany : 'Not Provided') . "<br>
                    <span class='highlight'>Company Address:</span> " . (!empty($gstAddress) ? $gstAddress : 'Not Provided') . "
                </td>
            </tr>
        </table>";
        
        $html = "
        <html>
        <head>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    font-size: 10px;
                    line-height: 1.2;
                    margin: 0;
                    padding: 10px;
                    color: #333;
                }
                .voucher {
                    max-width: 800px;
                    margin: 0 auto;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 10px;
                }
                th, td {
                    padding: 5px;
                    border: 1px solid #ddd;
                    vertical-align: top;
                }
                th {
                    background-color: #f5f5f5;
                    font-weight: bold;
                    text-align: left;
                }
                .header {
                    text-align: center;
                    margin-bottom: 10px;
                    padding-bottom: 8px;
                    border-bottom: 1px solid #ddd;
                }
                .header h1 {
                    font-size: 14px;
                    margin: 0;
                    color: #2c3e50;
                }
                .header p {
                    font-size: 9px;
                    margin: 2px 0 0 0;
                    color: #666;
                }
                .section-title {
                    font-weight: bold;
                    background-color: #f0f0f0;
                    font-size: 11px;
                }
                .booking-id {
                    font-size: 11px;
                    font-weight: bold;
                    color: #e74c3c;
                    text-align: center;
                    margin: 5px 0;
                }
                .status {
                    font-size: 9px;
                    padding: 2px 5px;
                    background: #27ae60;
                    color: white;
                    border-radius: 2px;
                    display: inline-block;
                }
                .footer {
                    text-align: center;
                    margin-top: 10px;
                    padding-top: 8px;
                    border-top: 1px solid #ddd;
                    font-size: 9px;
                    color: #666;
                }
                .notes {
                    font-size: 9px;
                    background: #f9f9f9;
                    padding: 5px;
                }
                .highlight {
                    font-weight: bold;
                }
                .gst-section {
                    background: #f0f8ff;
                    padding: 8px;
                    border: 1px solid #b9d6e6;
                    margin-bottom: 10px;
                }
            </style>
        </head>
        <body>
            <div class='voucher'>
                <div class='header'>
                    <h1>TRIPSORUS BOOKING VOUCHER</h1>
                    <p>Official Booking Confirmation</p>
                </div>
                
                <div class='booking-id'>Booking ID: $bookingCode</div>
                
                <table>
                    <tr>
                        <th colspan='3' class='section-title'>GUEST INFORMATION</th>
                    </tr>
                    <tr>
                        <td width='33%'><span class='highlight'>Guest Name:</span><br>$firstName $lastName</td>
                        <td width='33%'><span class='highlight'>Contact:</span><br>" . ($guestPhone ?? 'N/A') . "</td>
                        <td width='33%'><span class='highlight'>Email:</span><br>" . ($guestEmail ?? 'N/A') . "</td>
                    </tr>
                    <tr>
                        <td colspan='3'><span class='highlight'>Status:</span> <span class='status'>$status</span></td>
                    </tr>
                </table>
                
                <table>
                    <tr>
                        <th colspan='3' class='section-title'>STAY DETAILS</th>
                    </tr>
                    <tr>
                        <td width='33%'><span class='highlight'>Check-in:</span><br>$checkInDisplay<br>12:00 PM</td>
                        <td width='33%'><span class='highlight'>Check-out:</span><br>$checkOutDisplay<br>11:00 AM</td>
                        <td width='33%'><span class='highlight'>Duration:</span><br>$nights Night" . ($nights > 1 ? 's' : '') . "<br>$guests Adult" . ($guests > 1 ? 's' : '') . "</td>
                    </tr>
                </table>
                
                <table>
                    <tr>
                        <th colspan='3' class='section-title'>PROPERTY INFORMATION</th>
                    </tr>
                    <tr>
                        <td width='33%'><span class='highlight'>Property:</span><br>$propertyName</td>
                        <td width='33%'><span class='highlight'>Location:</span><br>$city</td>
                        <td width='33%'><span class='highlight'>Room Type:</span><br>$roomType</td>
                    </tr>
                    <tr>
                        <td colspan='3'><span class='highlight'>Meal Plan:</span> " . getMealPlanDescription($mealType) . "</td>
                    </tr>
                </table>
                
                <table>
                    <tr>
                        <th colspan='3' class='section-title'>PAYMENT DETAILS</th>
                    </tr>
                    <tr>
                        <td width='33%'><span class='highlight'>Room Charges:</span><br>₹ " . number_format($amount, 2) . "</td>
                        <td width='33%'><span class='highlight'>Taxes & Fees:</span><br>₹ " . number_format($taxes, 2) . "</td>
                        <td width='33%'><span class='highlight'>Total Amount:</span><br>₹ " . number_format($totalAmount, 2) . "</td>
                    </tr>
                    <tr>
                        <td colspan='3'><span class='highlight'>Payment Method:</span> $paymentType</td>
                    </tr>
                </table>
                
                $gstSection
                
                <table>
                    <tr>
                        <th class='section-title'>IMPORTANT NOTES</th>
                    </tr>
                    <tr>
                        <td class='notes'>
                            <p><strong>Check-in:</strong> Present this voucher and valid ID at reception</p>
                            <p><strong>Cancellation:</strong> Free until 24 hours before check-in</p>
                            <p><strong>Meals:</strong> " . getMealPlanDescription($mealType) . " included as specified</p>
                            <p><strong>Special Requests:</strong> " . (isset($_POST['special_requests']) && !empty($_POST['special_requests']) ? htmlspecialchars($_POST['special_requests']) : 'None') . "</p>
                        </td>
                    </tr>
                </table>
                
                <div class='footer'>
                    <p>Generated on " . date('M j, Y \a\t g:i A') . " | For support: support@tripsorus.com</p>
                </div>
            </div>
        </body>
        </html>";

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdfPath = __DIR__ . "/Booking_$bookingCode.pdf";
        file_put_contents($pdfPath, $dompdf->output());
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtpout.secureserver.net';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'noreply@tripsorus.com';
            $mail->Password = $_ENV['MAIL_PASSWORD']; 
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;
            $mail->setFrom("noreply@tripsorus.com", "Tripsorus");

  if (!empty($guestEmail)) {
    $mail->addAddress($guestEmail, "$firstName $lastName");
}

$adminEmail = 'noreply@tripsorus.com'; 
$stmt = $pdo->prepare("SELECT admin_id FROM properties WHERE id = :property_id LIMIT 1");
$stmt->execute([':property_id' => $propertyId]);
$propertyData = $stmt->fetch(PDO::FETCH_ASSOC);

if ($propertyData && !empty($propertyData['admin_id'])) {
    $stmt = $pdo->prepare("SELECT email FROM user WHERE id = :admin_id LIMIT 1");
    $stmt->execute([':admin_id' => $propertyData['admin_id']]);
    $adminUser = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($adminUser && !empty($adminUser['email'])) {
        $adminEmail = $adminUser['email'];
    }
}

            $mail->addAddress("noreply@tripsorus.com", "Tripsorus");
            $mail->addAddress($adminEmail, "Property Owner");
            $mail->addAttachment($pdfPath);
            $mail->isHTML(true);
            $mail->Subject = "Booking Confirmation - $propertyName";
            $mail->Body    = "<p>Dear $firstName $lastName,</p>
                              <p>Your booking at <strong>$propertyName</strong> is confirmed.</p>
                              <p>Booking Code: $bookingCode</p>
                              <p>Check-in: $checkIn<br>Check-out: $checkOut</p>
                              <p>Total: Rs " . number_format($totalAmount, 2) . "</p>
                              <p>We've attached your booking confirmation PDF.</p>";

            $mail->send();
        } catch (Exception $e) {
            error_log("Email could not be sent: {$mail->ErrorInfo}");
        }
        unlink($pdfPath);

        header("Location: confirmation.php?booking_id=" . $lastId);
        exit;
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
    exit();
}
?>