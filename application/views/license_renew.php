<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>License Expired Message</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: white;
            font-family: 'Open Sans', sans-serif;
            color: black;
        }

        .license-card {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: transparent;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .license-message {
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        .pay-bill-btn {
            width: 100%;
            font-size: 18px;
            background-color: transparent;
            border: 2px solid #28a745;
            color: #28a745;
            transition: all 0.3s ease;
        }

        .pay-bill-btn:hover {
            background-color: #28a745;
            color: white;
        }

        .footer {
            text-align: center;
            padding: 10px;
            width: 100%;
            position: relative;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
            margin-top: 40px;
            background-color: #f8f9fa;
        }

        /* Dynamic input field font color */
        #trxId {
            color: black; /* Default color */
        }

        .error {
            color: red !important; /* Less than 10 characters */
        }

        .valid {
            color: green !important; /* 10 characters */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="license-card card">
            <div class="card-body">
                <!-- Added the image here -->
                <div class="text-center">
                    <img src="<?php echo base_url('theme/expired.webp'); ?> " alt="License Expired" class="img-fluid" style="max-width: 50%; height: auto;">
                </div>

                <h2 class="license-message text-danger">License expired!</h2>
                <!-- <h5 class="license-message">Please pay the bill by bKash <br>bKash Number: 01678045810.</h5> -->
                <h5 class="license-message">বিকাশের মাধ্যমে বিল পরিশোধ করুন <br>বিকাশ নাম্বার: ০১৬৭৮০৪৫৮১০</h5>
                <!-- <p class="text-center">Please submit your bKash TrxID below to renew your license.</p> -->
                <p class="text-center">আপনার ট্রানজেকশন আইডিটি ( TrxId ) নিচে লিখে "Submit" বাটন চাপুন।</p>
                
                <!-- Form to collect bKash TrxID -->
                <form id="trxIdForm" action="<?php echo base_url('license/submit_transaction'); ?>" method="post">
                     <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" 
           value="<?php echo $this->security->get_csrf_hash(); ?>">
                    <div class="form-group">
                        <!-- <label for="trxId">bKash TrxID (10 Characters)</label> -->
                        <input type="text" class="form-control" name="trxId" id="trxId" placeholder="bKash TrxID" maxlength="10" required>
                        <!-- <small id="trxIdHelp" class="form-text text-muted">TrxID must be exactly 10 characters long.</small> -->
                    </div>
                    <button type="submit" class="btn pay-bill-btn">Submit</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer Section -->
    <footer class="footer">
        <p>RAMK Soft Tech &copy; 2024.</p>
    </footer>

    <!-- Bootstrap and JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        const trxIdInput = document.getElementById('trxId');
        const trxIdForm = document.getElementById('trxIdForm');

        // Add input event listener to dynamically change font color
        trxIdInput.addEventListener('input', function () {
            const trxIdValue = trxIdInput.value;

            if (trxIdValue.length < 10) {
                trxIdInput.classList.remove('valid');
                trxIdInput.classList.add('error'); // Font color red for < 10 characters
            } else if (trxIdValue.length === 10) {
                trxIdInput.classList.remove('error');
                trxIdInput.classList.add('valid'); // Font color green for exactly 10 characters
            }
        });

        // Form submit event listener
        trxIdForm.addEventListener('submit', function (event) {
            event.preventDefault(); // Prevent actual form submission

            const trxIdValue = trxIdInput.value;

            if (trxIdValue.length === 10) {
                alert('Your TrxID: ' + trxIdValue + ' has been submitted. Thank you!');
                // Programmatically submit the form after the alert
        trxIdForm.submit();
            } else {
                alert('TrxID must be exactly 10 characters long.');
            }
        });
    </script>
</body>
</html>
