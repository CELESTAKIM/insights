<?php
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_email = trim($_POST['admin_email']);
    $file = $_FILES['explore_file'];

    if ($admin_email !== 'celestakim018@gmail.com') {
        $error = "Only the admin (celestakim018@gmail.com) can upload content.";
    } elseif (empty($file['name'])) {
        $error = "Please select a file to upload.";
    } else {
        $allowed_types = ['video/mp4', 'video/webm', 'image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowed_types)) {
            $error = "Only MP4, WebM, JPG, PNG, or GIF files are allowed.";
        } else {
            $upload_dir = 'uploads/explore/';
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $file_path = $upload_dir . time() . '.' . $ext;
            move_uploaded_file($file['tmp_name'], $file_path);

            $title = pathinfo($file['name'], PATHINFO_FILENAME);
            $type = strpos($file['type'], 'video') === 0 ? 'video' : 'image';
            $query = "INSERT INTO explore_content (title, type, url) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, "sss", $title, $type, $file_path);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $success = "Content uploaded successfully!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>GIS Insights - Upload Explore Content</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <span class="brand-mobile">CELESTA KIM GIS Insights</span>
                <button class="hamburger" onclick="toggleMenu()">☰</button>
                <nav class="nav-menu">
                    <a href="index.php" style="font-weight: bold;">GIS Insights</a>
                    <div class="dropdown">
                        <a href="explore.php">Explore</a>
                        <div class="dropdown-content">
                            <a href="explore.php#population">Population Density</a>
                            <a href="explore.php#traffic">Traffic Patterns</a>
                            <a href="explore.php#deforestation">Deforestation Tracking</a>
                            <a href="explore.php#flood">Flood Risk</a>
                            <a href="explore.php#wildlife">Wildlife Habitats</a>
                        </div>
                    </div>
                    <a href="about.php">About</a>
                    <a href="portfolio.php">Portfolio</a>
                    <a href="insight.php">Insights</a>
                    <a href="blog.php">Blog</a>
                    <a href="index.php#dashboard">Dashboard</a>
                    <a href="index.php#profile">Profile</a>
                    <a href="logout.php">Logout</a>
                </nav>
            </div>
        </header>

        <main class="main-content">
            <div class="form-container">
                <h2>Upload Explore Content</h2>
                <p>Only the admin can upload content here.</p>
                <?php if ($error): ?>
                    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
                <?php elseif ($success): ?>
                    <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <input type="email" name="admin_email" placeholder="Admin Email" required><br>
                    <input type="file" name="explore_file" accept="video/*,image/*" required><br>
                    <button type="submit">Upload</button>
                </form>
                <p><a href="explore.php">Back to Explore</a></p>
            </div>
        </main>

        <footer>
            <div class="footer-content">
                <nav>
                    <p style="font-family: 'Inconsolata', monospace;">GIS Insights</p>
                    <a href="about.php">About</a>
                    <a href="terms.php">Terms</a>
                    <a href="blog.php">Blog</a>
                </nav>
                <div>
                    <p style="font-family: 'Inconsolata', monospace;">© 2025 GIS Insights</p>
                    <p style="font-family: 'Inconsolata', monospace;">Created by: Kimathi Joram - GIS Enthusiast at DeKUT</p>
                    <div class="social-links">
                        <a href="https://x.com/celestakim018" target="_blank" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.youtube.com/@CELESTAKIM_GIS" target="_blank" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                        <a href="https://www.linkedin.com/in/celesta-kim-21020232b/" target="_blank" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                        <a href="https://github.com/CELESTAKIM" target="_blank" aria-label="GitHub"><i class="fab fa-github"></i></a>
                        <a href="https://www.facebook.com/Eng.CelestaKim/" target="_blank" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="https://www.instagram.com/celestakim_gis/" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.pinterest.com/celestakim018/" target="_blank" aria-label="Pinterest"><i class="fab fa-pinterest"></i></a>
                        <a href="https://mastodon.social/@CELESTAKIM_GIS" target="_blank" aria-label="Mastodon"><i class="fas fa-globe"></i></a>
                    </div>
                    <div class="partnerships">
                        <p>Partnerships: 
                            <!-- <a href="https://www.linkedin.com/company/kenya-space-agency-official/posts/?feedView=all">KSA  </a> | 
                            <a href="https://tasks.hotosm.org">  HOT Task OSM</a> |  -->
                            <a href="https://x.com/GeospatialDev">GDeVDeKUT</a> | 
                            <!-- <a href="https://udemy.com">Udemy</a> -->
                        </p>
                    </div>
                    <button id="download-btn" onclick="downloadImages()">Download Images</button>
                    <div class="hidden-upload hidden">
                        <form method="POST" action="upload_explore.php" enctype="multipart/form-data">
                            <input type="email" name="admin_email" placeholder="Admin Email" required>
                            <input type="file" name="explore_file" accept="video/*,image/*" required>
                            <button type="submit">Upload</button>
                        </form>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script>
        function downloadImages() {
            const textContent = '<?php echo json_encode($dashboardImages); ?>'.map(img => `${img.name}: ${img.url}`).join('\n');
            const blob = new Blob([textContent], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'dashboard_images.txt';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        function toggleMenu() {
            const nav = document.querySelector('.nav-menu');
            nav.classList.toggle('active');
        }

        document.querySelector('.nav-menu').addEventListener('mouseleave', function() {
            if (window.innerWidth <= 768) this.classList.remove('active');
        });
    </script>
</body>
</html>