<?php
require 'config.php';
$loggedIn = isset($_SESSION['user_id']);
$dashboardImages = [
    ["name" => "Urban Areas", "url" => "https://via.placeholder.com/300x300?text=Urban+Areas"],
    ["name" => "Climate Zones", "url" => "https://via.placeholder.com/300x300?text=Climate+Zones"]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>GIS Insights - About</title>
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
            <div class="about-section">
                <h2>About GIS Insights</h2>
                <p>GIS Insights is a platform dedicated to exploring and sharing Geographic Information System (GIS) knowledge and insights. Whether you're mapping population density, analyzing traffic patterns, or tracking environmental changes, this site provides tools and resources to deepen your understanding of spatial data.</p>
                <p>Created by Kimathi Joram, a GIS enthusiast at Dedan Kimathi University of Technology (DeKUT) in Nyeri, Kenya, this platform connects learners and professionals with DeKUT as its focal point.</p>
                
                <h3>DeKUT Map</h3>
                <p>Interactive map centered on DeKUT (-0.3976, 36.9570). <?php if ($loggedIn) echo "Click to explore locations."; else echo "Sign in to interact!"; ?></p>
                <div id="map"></div>
                
                <h3>Features</h3>
                <ul>
                    <li><strong>Interactive Mapping:</strong> Powered by Leaflet.</li>
                    <li><strong>Insight Sharing:</strong> Contribute GIS insights.</li>
                    <li><strong>User Profiles:</strong> Manage your details.</li>
                    <li><strong>GIS Activities:</strong> Explore 50+ topics.</li>
                    <li><strong>Learning Resources:</strong> Video tutorials.</li>
                </ul>
                
                <h3>Join Us</h3>
                <p>Student, professional, or enthusiast? <a href="signup.php">Sign up</a> or <a href="login.php">sign in</a> today!</p>
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

    <script src="vendor/leaflet/leaflet.js"></script>
    <script>
        let map, marker;
        const loggedIn = <?php echo json_encode($loggedIn); ?>;
        const dashboardImages = <?php echo json_encode($dashboardImages); ?>;

        function initMap() {
            try {
                if (typeof L === 'undefined') throw new Error("Leaflet not loaded");
                map = L.map('map').setView([-0.3976, 36.9570], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OpenStreetMap' }).addTo(map);
                L.marker([-0.3976, 36.9570]).addTo(map).bindPopup("DeKUT").openPopup();
                map.on('click', function(e) {
                    if (loggedIn) {
                        if (marker) map.removeLayer(marker);
                        marker = L.marker([e.latlng.lat, e.latlng.lng]).addTo(map).bindPopup("Selected location").openPopup();
                    }
                });
            } catch (error) {
                console.error("Map error:", error);
                document.getElementById('map').innerHTML = "<p style='color: red;'>Error: Unable to load map.</p>";
            }
        }

        initMap();

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