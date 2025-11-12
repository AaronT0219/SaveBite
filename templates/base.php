<?php 

include '../config.php'; 

session_start();
if (!isset($_SESSION['email'])) {
    header("Location: ../Login/login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaveBite</title>

    <link rel="icon" type="image/x-icon" href="../assets/images/icon_pure.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../templates/base.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js"></script>
    
    <!-- Additional page-specific CSS -->
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include '../sidebar/sidebar.php'; ?>
        
        <!-- content top container -->
        <div id="main-content-container" class="container-fluid p-0">
            <div id="topNav-main-content" class="w-100 pt-4 px-4 text-end">
                <i data-lucide="bell" data-page="notification" class="notification-icon"></i>
            </div>

            <!-- Main content area -->
            <div id="main-content" class="w-100">
                <!-- Page content will be loaded here -->
                <?php if (isset($pageContent)): ?>
                    <?php echo $pageContent; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/pageLoader.js"></script>
    
    <!-- Additional page-specific JS -->
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>