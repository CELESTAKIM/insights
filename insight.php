<?php
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$insights_query = "SELECT insight, latitude, longitude, created_at FROM insights WHERE user_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($db, $insights_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$insights_result = mysqli_stmt_get_result($stmt);
$insights = [];
while ($row = mysqli_fetch_assoc($insights_result)) {
    $insights[] = $row;
}
mysqli_stmt_close($stmt);

$dashboardImages = [
    ["name" => "Urban Areas", "url" => "https://via.placeholder.com/300x300?text=Urban+Areas"],
    ["name" => "Climate Zones", "url" => "https://via.placeholder.com/300x300?text=Climate+Zones"]
];

if (isset($_POST['download_excel'])) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="my_insights.xls"');
    header('Cache-Control: max-age=0');
    echo "Insight\tLatitude (X)\tLongitude (Y)\tDate Added\n";
    foreach ($insights as $insight) {
        echo htmlspecialchars($insight['insight']) . "\t" . 
             htmlspecialchars($insight['latitude']) . "\t" . 
             htmlspecialchars($insight['longitude']) . "\t" . 
             htmlspecialchars($insight['created_at']) . "\n";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>GIS Insights - My Insights</title>
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
            <div class="insight-section">
                <h2>My Insights</h2>
                <p>Record of your insights with coordinates and timestamps.</p>
                <?php if (empty($insights)): ?>
                    <p>No insights added yet. Go to <a href="index.php#insight-form">Dashboard</a> to share one!</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Insight</th>
                                <th>Latitude (X)</th>
                                <th>Longitude (Y)</th>
                                <th>Date Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($insights as $insight): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($insight['insight']); ?></td>
                                    <td><?php echo htmlspecialchars($insight['latitude']); ?></td>
                                    <td><?php echo htmlspecialchars($insight['longitude']); ?></td>
                                    <td><?php echo htmlspecialchars($insight['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <form method="POST" action="insight.php" style="display: inline;">
                        <button type="submit" name="download_excel">Download as Excel</button>
                    </form>
                    <button onclick="alert('To upload to Google Sheets, download the Excel file and manually import it into Google Sheets.')">Upload to Google Sheets</button>
                <?php endif; ?>
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
                            <a href="https://tasks.hotosm.org">HOT Task OSM</a> |  -->
                            <a href="https://gdevdekut.org">GDeVDeKUT</a> | 
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
        const dashboardImages = <?php echo json_encode($dashboardImages); ?>;
        const insights = <?php echo json_encode($insights); ?>;

        function downloadImages() {
            const textContent = dashboardImages.map(img => `${img.name}: ${img.url}`).join('\n');
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