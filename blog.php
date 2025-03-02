<?php
require 'config.php';
$loggedIn = isset($_SESSION['user_id']);
$dashboardImages = [
    ["name" => "Urban Areas", "url" => "https://via.placeholder.com/300x300?text=Urban+Areas"],
    ["name" => "Climate Zones", "url" => "https://via.placeholder.com/300x300?text=Climate+Zones"]
];

$blogPosts = [
    ['title' => 'Introduction to GIS Mapping', 'date' => 'March 01, 2025', 'content' => 'Learn the basics of GIS mapping and its transformative power.'],
    ['title' => 'OpenStreetMap Contributions', 'date' => 'February 28, 2025', 'content' => 'Guide to contributing to OSM and its global impact.']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>GIS Insights - Blog</title>
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
            <div class="blog-section">
                <h2>Blog</h2>
                <p>Welcome to the GIS Insights blog! Find articles on GIS techniques and updates.</p>
                <?php foreach ($blogPosts as $post): ?>
                    <div class="portfolio-item">
                        <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                        <p><em>Posted on: <?php echo htmlspecialchars($post['date']); ?></em></p>
                        <p><?php echo htmlspecialchars($post['content']); ?></p>
                    </div>
                <?php endforeach; ?>
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