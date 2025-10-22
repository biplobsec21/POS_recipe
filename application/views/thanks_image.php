<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanks Message</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: white;
            font-family: 'Open Sans', sans-serif;
            color: black;
        }

        .thanks-card {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: transparent;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .thanks-message {
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        .dashboard-btn {
            width: 100%;
            font-size: 18px;
            background-color: transparent;
            border: 2px solid #28a745;
            color: #28a745;
            transition: all 0.3s ease;
        }

        .dashboard-btn:hover {
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

        /* Styling for the image */
        .thanks-image {
            display: block;
            margin: 100px auto 20px auto;
            max-width: 100%;
            height: 300px;
            border-radius: 10px;
        }

    </style>
</head>
<body>
    <div class="container">
        <!-- Image included at the top of the page -->
        <!--<img src="thanks.jpeg" alt="Thank you image" class="thanks-image">-->
<div style="display: flex; justify-content: center; align-items: center; height: 70vh;">
    <img src="https://old.adoralaboratories.com/theme/thanks.jpeg" alt="Thank you" style="max-width: 60%; height: auto;">
</div>

        <div class="thanks-card card">
            <div class="card-body">
                <h2 class="thanks-message text-success">বিল পরিশোধ করার জন্য ধন্যবাদ।</h2>
                <p class="text-center">“Dashboard” বাটনে ক্লিক করে মূল কাজে ফেরত যান।</p>
                
                <!-- Dashboard Button -->
                <div class="form-group text-center">
                    <a href="<?php echo base_url();?>" class="btn dashboard-btn">Dashboard</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Section -->
    <footer class="footer">
        <p>RAMK Soft Tech &copy; 2024.</p>
    </footer>

    <!-- Bootstrap JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
