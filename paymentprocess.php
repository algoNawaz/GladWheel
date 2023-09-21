
<?php
    require('./connection.php');
    require('./env.php');
?>
  <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <link
        rel="icon"
        type="image/x-icon"
        href="WhatsApp Image 2023-09-08 at 15.54.36.jpg"
      />

      <title>GladWheel</title>
      <style>
        button{
            display: block;
            color:white;
            font-size: 1.2rem;
            cursor: pointer;
            background-color:black;
            padding:0.25 0.75rem;
            border:none;
            outline:none;
        }
      </style>
</head>
<?php

          function checkPaymentStatus($merchantTransactionId){

                $checksum2=hash("sha256","/pg/v1/status/".$GLOBALS['merchantId']."/".$merchantTransactionId.$GLOBALS['saltKey'])."###".$GLOBALS['saltIndex'];

                $curl=curl_init();

                curl_setopt_array($curl, [
                CURLOPT_URL => "https://api-preprod.phonepe.com/apis/pg-sandbox/pg/v1/status/".$GLOBALS['merchantId']."/".$merchantTransactionId,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [
                    "Content-Type: application/json",
                    "X-VERIFY:".$checksum2,
                    "X-MERCHANT-ID:".$GLOBALS['merchantId'],
                    "accept: application/json"
                ],
                ]);
  
                $response = curl_exec($curl);
                $err = curl_error($curl);
  
                curl_close($curl);
  
                if ($err) {
                    echo "<div class='formError'> ".$err." <i class='fa-solid fa-xmark formError-close-btn' ></i> </div>";
                } 
                else {
                    $data=json_decode($response);
                    
                    return $data;
                }
           }

      if(isset($_POST['merchantId']) && isset($_POST['transactionId'])){

        //check for the real-time status of the payment

        //$_POST[transactionId] is actually merchantTransactionId

        $merchantTransactionId=$_POST['transactionId'];

        $payload=checkPaymentStatus($merchantTransactionId);

        $paymentStatus=$payload->code;

        $transactionId=$payload->data->transactionId;


        if($paymentStatus==='PAYMENT_SUCCESS'){

          $sql="update user set transactionId=? WHERE merchantTransactionId=?";

          $statement=$mysqli->prepare($sql);

          $statement->bind_param("ss",$transactionId,$merchantTransactionId);

          $statement->execute();

          $statement->close();
          
          echo "<div class='formError'> Payment Successfully Received <i class='fa-solid fa-xmark formError-close-btn' ></i> </div>";
          echo "<button>Back to home page</button>";

        }


        else if($paymentStatus==='PAYMENT_PENDING'){
            $initialInterval = 20;
            $totalTimeout = 900; //fifteen minutes

            $endTime = time() + $totalTimeout;


            echo "Payment Pending. Please wait!";

            while (time() < $endTime) {
      
                if ($paymentStatus !== "PAYMENT_PENDING") {
                    break;
                }

                if (time() - $initialInterval <= 25) {
                  sleep(3);
                  $paymentStatus=checkPaymentStatus($_POST['transactionId']);
                } elseif (time() - $initialInterval <= 85) {
                  sleep(3);
                  $paymentStatus=checkPaymentStatus($_POST['transactionId']);
                } elseif (time() - $initialInterval <= 145) {
                  sleep(6);
                  $paymentStatus=checkPaymentStatus($_POST['transactionId']);
                } elseif (time() - $initialInterval <= 205) {
                  sleep(10);
                  $paymentStatus=checkPaymentStatus($_POST['transactionId']);
                } else {
                  sleep(30);
                  $paymentStatus=checkPaymentStatus($_POST['transactionId']);
                }
            }

            
            if($paymentStatus==='PAYMENT_SUCCESS'){
              $sql="insert into user (name,email,phone,state,city,address,pincode,transactionId) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
              $statement=$mysqli->prepare($sql);
              $statement->bind_param("ssssssss",$name,$email,$phone,$state,$city,$address,$pincode,$transactionId);
              $result=$statement->execute();
              print_r($result);
              echo "Payment Successfully Received. Redirecting...";
              echo "<button>Back to home page</button>";

            }
            else{
              echo "<div class='formError'> ".$data->data->responseCodeDescription." if the amount has been debited it will be credited into you account  Redirecting...<i class='fa-solid fa-xmark formError-close-btn' ></i> </div>";
              echo "<button>Back to home page</button>";

            }
        }

        else{
          echo "<div class='formError'> ".$data->data->responseCodeDescription. " Redirecting... <i class='fa-solid fa-xmark formError-close-btn' ></i> </div>";
          echo "<button>Back to home page</button>";

        }
      }
    ?>


<script>
    let btns=document.querySelector('button');
    btns.addEventListener('click',()=>{
        window.location.href='<?php echo $GLOBALS['baseUrl'];?>/index.php';
    })
</script>