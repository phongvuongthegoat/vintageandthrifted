<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
   header('location:user_login.php');
};

if (isset($_POST['order'])) {

   $name = $_POST['name'];
   $name = filter_var($name);
   $number = $_POST['number'];
   $number = filter_var($number);
   $email = $_POST['email'];
   $email = filter_var($email);
   $method = isset($_POST['method']) ? $_POST['method'] : '';
   $method = filter_var($method);
   $address = 'flat no. ' . $_POST['flat'] . ', ' . $_POST['street'] . ', ' . $_POST['city'] . ', ' . $_POST['state'] . ', ' . $_POST['country'] . ' - ' . $_POST['pin_code'];
   $address = filter_var($address);
   $total_products = $_POST['total_products'];
   $total_price = $_POST['total_price'];
   $card_number = $_POST['cardNumber'];
   $card_expiry_month = $_POST['month'];
   $card_expiry_year = $_POST['year'];
   $cvc = $_POST['cvc'];
   $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
   $check_cart->execute([$user_id]);

   if ($check_cart->rowCount() > 0) {

      $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, card_number, card_expiry_month, card_expiry_year, cvc ) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)");
      $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $total_price, $card_number, $card_expiry_month, $card_expiry_year, $cvc]);

      $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
      $delete_cart->execute([$user_id]);

      $get_order =  $conn->prepare("SELECT * FROM `orders` WHERE `user_id` = ? ORDER BY `placed_on` DESC LIMIT 1;      ");
      $get_order->execute([6]);

      if ($get_order->rowCount() > 0) {
         $fetch_product = $get_order->fetch(PDO::FETCH_ASSOC);
         $customer_name = $fetch_product['name'];
         $customer_email = $fetch_product['email'];
         $id_orders = $fetch_product['id'];
         $total_products = $fetch_product['total_products'];

         $mail_body = 'Thank you for shopping with Vintage & Thrifted!
         Your payment has been processed! <br>
         Order ID: ' . $id_orders . '<br>';

         $pattern = '/([A-Za-z\s]+)\s\((\d+)\s*x\s*(\d+)\)/';
         preg_match_all($pattern, $total_products, $matches, PREG_SET_ORDER);
         foreach ($matches as $match) {
            $productName = $match[1];
            $price = $match[2];
            $quantity = $match[3];
            $mail_body .= 'Product: ' . $productName . '<br>';
            $mail_body .= 'Quantity: ' . $quantity . '<br>';
            $mail_body .= 'Total: ' . $price . '<br>';
         }
         $mail_body .= "See you next time!";

         include 'PHPMailer/class.smtp.php';
         include 'PHPMailer/class.phpmailer.php';

         $mail = new PHPMailer();
         $mail->CharSet = "UTF-8";
         // $mail->Encoding = "16bit";
         $mail->IsSMTP();
         $mail->Host = "smtp.gmail.com";
         $mail->Port = 465;
         $mail->SMTPAuth = true;
         $mail->SMTPSecure = 'ssl';
         $mail->Username = "phong.vuong2320@gmail.com"; // tên người dùng SMTP hoặc tên người dùng gmail của bạn
         $mail->Password = "qjajyyvgutogqamb"; // mật khẩu email hoặc mật khẩu 2 lớp
         $from = ""; // Trả lời email này
         // $to = 'phong.vuong2320@gmail.com'; // email người nhận
         $to = $email; // email người nhận
         // $name = ""; //tên người nhận
         $mail->From = $from;
         $mail->FromName = ""; //tên người gui
         $mail->AddAddress($to, $name);
         $mail->AddReplyTo($from, "");
         $mail->isHTML(true);                                  // Set email format to HTML
         $mail->Subject = '[Create Order Success]';
         $mail->Body    = $mail_body;
         $mail->AltBody = '';
         // $mail->SMTPDebug = 2; // bật để debug
         if (!$mail->Send()) {
            echo "<h1>Loi khi goi mail: " . $mail->ErrorInfo . '</h1>';
         } else {
            $message[] = 'Order placed Successfully!';
         }
      }
   } else {
      // $message[] = 'Your Cart is Empty';
   }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Payment</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>

<body>

   <?php include 'components/user_header.php'; ?>

   <div class="container" style="text-align: center;padding: 20px;">
      <h1>THANK YOU</h1>
      <br><br>
      <br><br>



      <h2>Your order is being processed. Please check your email for confirmation</h2>
   </div>
   <!-- <h6>Checkout</h6>
      <span></span>
      <h1>Payment Method</h1> -->
   <!-- <form action="/">

         <label for="cardno">Card Number
            <input type="text" name="cardno" id="cardno" maxlength="19" onkeypress="cardspace()" />
         </label>
         <div class="float">
            <label for="validtill">Expiry Date
               <input type="text" name="validtill" id="validtill" maxlength="7" onkeypress="addSlashes()" />
            </label>
            <label for="cvv">CVC
               <input type="text" name="cvv" id="cvv" maxlength="3" />
            </label>
         </div>
         <label for="checkbox">
            <input type="checkbox" name="checkbox" id="checkbox" />
            <p>Payment Address is the same as the Delivery Address</p>
         </label>
         <button>Pay</button>
      </form> -->

   <?php include 'components/footer.php'; ?>

   <script src="js/script.js"></script>

</body>

</html>