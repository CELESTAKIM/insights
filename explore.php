<?php
require 'config.php';
$loggedIn = isset($_SESSION['user_id']);
$dashboardImages = [
    ["name" => "Urban Areas", "url" => "https://via.placeholder.com/300x300?text=Urban+Areas"],
    ["name" => "Climate Zones", "url" => "https://via.placeholder.com/300x300?text=Climate+Zones"]
];

$explore_content_query = "SELECT title, type, url FROM explore_content ORDER BY uploaded_at DESC";
$explore_content_result = mysqli_query($db, $explore_content_query);
$explore_content = [];
while ($row = mysqli_fetch_assoc($explore_content_result)) {
    $explore_content[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>GIS Insights - Explore</title>
    <link rel="stylesheet" href="vendor/leaflet/leaflet.css" />
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
                            <a href="#population">Population Density</a>
                            <a href="#traffic">Traffic Patterns</a>
                            <a href="#deforestation">Deforestation Tracking</a>
                            <a href="#flood">Flood Risk</a>
                            <a href="#wildlife">Wildlife Habitats</a>
                        </div>
                    </div>
                    <a href="about.php">About</a>
                    <a href="portfolio.php">Portfolio</a>
                    <a href="insight.php">Insights</a>
                    <a href="blog.php">Blog</a>
                    <?php if ($loggedIn): ?>
                        <a href="index.php#dashboard">Dashboard</a>
                        <a href="index.php#profile">Profile</a>
                        <a href="logout.php">Logout</a>
                    <?php else: ?>
                        <a href="login.php">Sign In</a>
                    <?php endif; ?>
                </nav>
            </div>
        </header>

        <main class="main-content">
            <div class="explore-section">
                <h2>Explore GIS</h2>
                <p>Dive into Geographic Information Systems with curated resources and content.</p>
                <?php if (empty($explore_content)): ?>
                    <p>No content available yet. Check back soon!</p>
                <?php else: ?>
                    <?php foreach ($explore_content as $item): ?>
                        <div id="<?php echo strtolower(str_replace(' ', '-', $item['title'])); ?>">
                            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                            <?php if ($item['type'] === 'video'): ?>
                                <iframe src="<?php echo htmlspecialchars($item['url']); ?>" frameborder="0" allowfullscreen></iframe>
                            <?php else: ?>
                                <img src="<?php echo htmlspecialchars($item['url']); ?>" class="dashboard-image" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
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
                        <!-- <a href="https://www.linkedin.com/company/kenya-space-agency-official/posts/?feedView=all">KSA  </a>  | 
                            <a href="https://tasks.hotosm.org">HOT Task OSM</a> |  -->
                            <a href="https://gdevdekut.org">GDeVDeKUT</a> | 
                            <!-- <a href="https://udemy.com">Udemy</a> -->
                        </p>
                    </div>
                    <?php if ($loggedIn): ?>
                        <button id="download-btn" onclick="downloadImages()">Download Images</button>
                        <div class="hidden-upload hidden">
                            <form method="POST" action="upload_explore.php" enctype="multipart/form-data">
                                <input type="email" name="admin_email" placeholder="Admin Email" required>
                                <input type="file" name="explore_file" accept="video/*,image/*" required>
                                <button type="submit">Upload</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </footer>
    </div>

    <script>
        const dashboardImages = <?php echo json_encode($dashboardImages); ?>;

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