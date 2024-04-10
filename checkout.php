<?php

include 'components/connect.php';

session_start();

if (isset($_SESSION['user_id'])) {
   $user_id = $_SESSION['user_id'];
} else {
   $user_id = '';
   header('location:user_login.php');
};

// if(isset($_POST['order'])){
//    var_dump($_POST['order']);
//    $name = $_POST['name'];
//    $name = filter_var($name, FILTER_SANITIZE_STRING);
//    $number = $_POST['number'];
//    $number = filter_var($number, FILTER_SANITIZE_STRING);
//    $email = $_POST['email'];
//    $email = filter_var($email, FILTER_SANITIZE_STRING);
//    $method = $_POST['method'];
//    $method = filter_var($method, FILTER_SANITIZE_STRING);
//    $address = 'flat no. '. $_POST['flat'] .', '. $_POST['street'] .', '. $_POST['city'] .', '. $_POST['state'] .', '. $_POST['country'] .' - '. $_POST['pin_code'];
//    $address = filter_var($address, FILTER_SANITIZE_STRING);
//    $total_products = $_POST['total_products'];
//    $total_price = $_POST['total_price'];

//    $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
//    $check_cart->execute([$user_id]);

//    if($check_cart->rowCount() > 0){

//       $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price) VALUES(?,?,?,?,?,?,?,?)");
//       $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $total_price]);

//       //$delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
//       //$delete_cart->execute([$user_id]);

//       //$message[] = 'Order placed Successfully!';
//    ///}else{
//       //$message[] = 'Your Cart is Empty';
//    }

// }

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Check Out</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>

<body>

   <?php include 'components/user_header.php'; ?>

   <section class="checkout-orders">

      <form action="payment.php" method="POST">

         <h3>Your Orders</h3>

         <div class="display-orders">
            <?php
            $grand_total = 0;
            $cart_items[] = '';
            $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $select_cart->execute([$user_id]);
            if ($select_cart->rowCount() > 0) {
               while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) {
                  $cart_items[] = $fetch_cart['name'] . ' (' . $fetch_cart['price'] . ' x ' . $fetch_cart['quantity'] . ') - ';
                  $total_products = implode($cart_items);
                  $grand_total += ($fetch_cart['price'] * $fetch_cart['quantity']);
            ?>
                  <p> <?= $fetch_cart['name']; ?> <span>(<?= '$' . $fetch_cart['price'] . '/- x ' . $fetch_cart['quantity']; ?>)</span> </p>
            <?php
               }
            } else {
               echo '<p class="empty">your cart is empty!</p>';
            }
            ?>
            <input type="hidden" name="total_products" value="<?= $total_products; ?>">
            <input type="hidden" name="total_price" value="<?= $grand_total; ?>" value="">
            <div class="grand-total">Sub Total: <span>$<?= $grand_total; ?>/-</span></div>
         </div>

         <h3>Delivery Address</h3>

         <div class="flex">
            <div class="inputBox">
               <span>Name:</span>
               <input type="text" name="name" placeholder="" class="box" maxlength="20" required>
            </div>
            <div class="inputBox">
               <span>Phone Number :</span>
               <input type="number" name="number" placeholder="" class="box" min="0" max="9999999999" onkeypress="if(this.value.length == 10) return false;" required>
            </div>
            <div class="inputBox">
               <span>Email :</span>
               <input type="email" name="email" placeholder="" class="box" maxlength="50" required>
            </div>
            <!-- <div class="inputBox">
               <span>Payment Method :</span>
               <select name="method" class="box" required>

                  <option value="credit card">Credit Card</option>
                  <option value="paytm">Apple Pay</option>
                  <option value="paypal">PayPal</option>
               </select>
            </div> -->

            <div class="inputBox">
               <span>Address Line 01 :</span>
               <input type="text" name="flat" placeholder="" class="box" maxlength="50" required>
            </div>
            <div class="inputBox">
               <span>Address Line 02 :</span>
               <input type="text" name="street" placeholder="" class="box" maxlength="50">
            </div>
            <div class="inputBox">
               <span>Suburb: </span>
               <input type="text" name="city" placeholder="" class="box" maxlength="50" required>
            </div>
            <div class="inputBox">
               <span>State: </span>
               <input type="text" name="state" placeholder="" class="box" maxlength="50" required>
            </div>
            <div class="inputBox">
               <span>Country :</span>
               <input type="text" name="country" placeholder="" class="box" maxlength="50" required>
            </div>
            <div class="inputBox">
               <span>Post Code :</span>
               <input type="number" min="0" name="pin_code" placeholder="" min="0" max="999999" onkeypress="if(this.value.length == 6) return false;" class="box" required>
            </div>

         </div>

         <h3 style="margin-top: 20px;">Payment Method</h3>

         <div class="flex" style="justify-content: space-between;">


            <div class="inputBox" style="width: 35%;">
               <span>Card Number :</span>
               <input type="text" id="numberField" name="cardNumber" placeholder="" class="box" maxlength="16" required>
            </div>

            <div class="inputBox" style="width: 40%;">
               <span>Expiry</span>

               <div style="display: flex; gap:10px;">

                  <select id="month" name="month" class="box" required style="width: 50%;">
                     <?php
                     $currentYear = date('Y');
                     $currentMonth = date('n');
                     for ($i = 1; $i <= 12; $i++) {
                        $monthValue = str_pad($i, 2, '0', STR_PAD_LEFT);
                        if ($currentYear == 2023 && $i < $currentMonth) {
                           echo "<option value=\"$i\" disabled>$monthValue</option>";
                        } else {
                           echo "<option value=\"$i\">$monthValue</option>";
                        }
                     }
                     ?>
                  </select>

                  <select id="year" name="year" class="box" required style="width: 50%; ">
                     <?php
                     $currentYear = date('Y');
                     $futureYear = $currentYear + 15; // Cho phép chọn nhiều năm ở tương lai
                     for ($i = $currentYear; $i <= $futureYear; $i++) {
                        echo "<option value=\"$i\">$i</option>";
                     }
                     ?>
                  </select>
               </div>
            </div>

            <div class="inputBox" style="width: 20%;">
               <span>CVC :</span>
               <input type="text" name="cvc" placeholder="" maxlength="3" class="box" required>
            </div>

         </div>

         <input type="submit" name="order" class="btn <?= ($grand_total > 1) ? '' : 'disabled'; ?>" value="Proceed to Checkout">

      </form>

   </section>

   <?php include 'components/footer.php'; ?>

   <script src="js/script.js"></script>

   <script>
      document.getElementById('year').addEventListener('change', function() {
         var selectedYear = this.value;
         var monthSelect = document.getElementById('month');
         var currentMonth = new Date().getMonth() + 1; // Lấy tháng hiện tại
         monthSelect.innerHTML = ''; // Xóa các tùy chọn tháng hiện tại

         for (var i = 1; i <= 12; i++) {
            var option = document.createElement('option');

            option.value = i;
            var monthLabel = (i < 10 ? '0' + i : i);
            option.textContent = monthLabel;

            // Kiểm tra nếu tháng 1 của năm 2023 và không phải tháng hiện tại
            if (selectedYear == <?php echo $currentYear; ?> && (i < currentMonth || selectedYear == 2023 && i == 1)) {
               option.disabled = true;
            }
            monthSelect.appendChild(option);
         }
      }).dispatchEvent(new Event('change')); // Tự động kích hoạt sự kiện change để áp dụng ban đầu
   </script>

   <script>
      document.getElementById('numberField').addEventListener('input', function() {
         var maxLength = 16; // Độ dài tối đa cho trường số
         if (this.value.length > maxLength) {
            this.value = this.value.slice(0, maxLength); // Cắt giá trị nếu độ dài vượt quá giới hạn
         }
      });
   </script>

</body>

</html>